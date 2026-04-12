<?php
/**
 * Prerender every GET route of the Slim app into dist/<path>/index.html.
 *
 * Usage: php bin/prerender.php
 *
 * Walks the Slim router, enumerates dynamic route params from JSON data files,
 * dispatches each GET request in-process via $app->process(), and writes the
 * rendered body to dist/. Used as the static build step for Cloudflare Pages.
 */

// Project root is the parent of bin/. Switch there so all relative paths
// inside bootstrap.php and the view factory resolve correctly.
chdir(__DIR__ . '/..');

$app = require __DIR__ . '/../bootstrap.php';

$distRoot = __DIR__ . '/../dist';

// Collect every GET URL to prerender.
$urls = [];
foreach ($app->getContainer()->get('router')->getRoutes() as $route) {
    if (!in_array('GET', $route->getMethods(), true)) {
        continue;
    }
    $pattern = $route->getPattern();

    if (strpos($pattern, '{service}') !== false) {
        $services = (new ServiceModel('publico/datos/services.json'))->get_services();
        foreach ($services as $s) {
            $urls[str_replace('{service}', $s['slug'], $pattern)] = true;
        }
        continue;
    }

    if (strpos($pattern, '{case}') !== false) {
        $cases = (new GalleryModel('publico/datos/gallery.json'))->get_cases();
        foreach ($cases as $c) {
            $urls[str_replace('{case}', $c['slug'], $pattern)] = true;
        }
        continue;
    }

    // Static path — include verbatim. Skip any unknown param placeholder.
    if (strpos($pattern, '{') === false) {
        $urls[$pattern] = true;
    }
}

$urlList = array_keys($urls);
sort($urlList);

printf("Prerendering %d URLs to %s\n", count($urlList), $distRoot);

$totalBytes = 0;
$errors = [];

foreach ($urlList as $url) {
    $env = \Slim\Http\Environment::mock([
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI'    => $url,
    ]);
    $req = \Slim\Http\Request::createFromEnvironment($env);
    $res = $app->process($req, new \Slim\Http\Response());

    $status = $res->getStatusCode();
    $body   = (string) $res->getBody();

    if ($status !== 200) {
        fprintf(STDERR, "FAIL %s status=%d\n", $url, $status);
        $errors[] = $url;
        continue;
    }

    // Guard against silently emitting the 404 body for a route that
    // exists but rendered as a 404 template for some reason.
    if (stripos($body, '<title>Page not found</title>') !== false) {
        fprintf(STDERR, "FAIL %s body looks like a 404 page\n", $url);
        $errors[] = $url;
        continue;
    }

    $rel = rtrim($url, '/');
    if ($rel === '') {
        $outPath = $distRoot . '/index.html';
    } else {
        $outPath = $distRoot . $rel . '/index.html';
    }

    if (!is_dir(dirname($outPath))) {
        mkdir(dirname($outPath), 0755, true);
    }
    file_put_contents($outPath, $body);
    $totalBytes += strlen($body);
    printf("  ok %s (%d bytes)\n", $url, strlen($body));
}

// Render a synthetic 404 by dispatching a deliberate miss.
$env404 = \Slim\Http\Environment::mock([
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/__prerender_404__',
]);
$req404 = \Slim\Http\Request::createFromEnvironment($env404);
$res404 = $app->process($req404, new \Slim\Http\Response());
if (!is_dir($distRoot)) {
    mkdir($distRoot, 0755, true);
}
file_put_contents($distRoot . '/404.html', (string) $res404->getBody());
printf("  ok /404.html (%d bytes, synthetic miss)\n", strlen((string) $res404->getBody()));

printf("\nDone. %d URLs, %d bytes total.\n", count($urlList) - count($errors), $totalBytes);

if (!empty($errors)) {
    fprintf(STDERR, "\n%d URLs failed:\n", count($errors));
    foreach ($errors as $u) {
        fprintf(STDERR, "  - %s\n", $u);
    }
    exit(1);
}
