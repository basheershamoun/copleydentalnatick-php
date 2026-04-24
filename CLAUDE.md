# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

Marketing website for Copley Dental (Natick). The PHP/Slim 3 + Twig codebase is a **build tool, not a runtime** — a prerender script walks every Slim route and generates static HTML into `dist/`, which deploys to Cloudflare Pages. Forms submit client-side via `site.js` to an external CRM (`copleydentalforms.arzs.app`). No PHP runs in production.

`vendor/` is committed (no `composer.json`).

## Commands

```sh
make up                # Start PHP 7.4 + Apache dev container at http://localhost:8090
make build             # Prerender 110 routes → dist/ + copy assets + site.js
make preview           # Serve dist/ at http://localhost:8091 (python3)
make clean             # Remove dist/
make deploy-cloudflare # No-op placeholder (Cloudflare Pages auto-deploys committed dist/)
make down              # Stop container
make shell             # Shell into container
make docker-rebuild    # Rebuild Docker image from scratch
```

`make build` requires the container running (`make up` first). It runs `bin/prerender.php` inside Docker, then copies `static/`, `site.js`, `sitemap.xml`, `robots.txt`, and `deploy/_headers` into `dist/`.

## Deployment

Deployed via **Cloudflare Pages Git integration** (GitHub repo connected in the Cloudflare dashboard). There is no build step on Cloudflare's side — `dist/` is committed to the repo and served as-is.

**To deploy:** run `make build` locally, `git add dist && git commit && git push`. Cloudflare Pages picks up the push and publishes automatically.

Cloudflare Pages project settings:
- Framework preset: **None**
- Build command: empty (or `make deploy-cloudflare`, which is a no-op)
- Build output directory: `dist`

Wrangler is **not** used anymore. `make deploy-cloudflare` exists as a no-op placeholder — kept so there's a conventional target to hang a future deploy step off of if needed.

## Directory layout

```
copleydentalnatick.com/
├── index.php              ← 3 lines: requires bootstrap.php, calls $app->run()
├── bootstrap.php          ← Slim 3 app setup: container, controllers, all routes
├── config.ini             ← runtime config (asset URL, template dir, reCAPTCHA keys)
├── .htaccess              ← HTTPS redirect + rewrite (used by dev container only)
├── .user.ini              ← PHP ini overrides (kept for potential cPanel fallback)
├── sitemap.xml, robots.txt
│
├── bin/
│   └── prerender.php      ← CLI script: walks router, dispatches GET, writes dist/
├── deploy/
│   └── _headers           ← Cloudflare Pages cache + security headers
├── dist/                  ← ★ build output (COMMITTED to git — Cloudflare Pages serves it directly)
│
├── src/                   ← application code
│   ├── controllers.php    ← all controller classes (~660 lines)
│   ├── forms.php          ← server-side validation (dead code — forms now use site.js)
│   ├── emails.php         ← email templates (dead code — CRM handles email)
│   ├── data/              ← JSON data + model classes
│   │   ├── models.php     ← ServiceModel / GalleryModel / StateModel / HourModel
│   │   ├── services.json  ← 15 services with slugs, templates, titles
│   │   ├── gallery.json   ← 41 gallery cases
│   │   ├── hours.json, request_appointment.json
│   └── templates/         ← Twig templates (source of truth for all page content)
│       ├── base.html      ← shared layout (loads jQuery, copley.js, site.js)
│       ├── home.html
│       ├── services/      ← one subfolder per service + shared partials
│       ├── our_team/, contact_us/, ask_doctor/, request_appointment/
│       ├── dental_information/, patient_information/
│       ├── social_responsibility/, gallery/, faqs/, errors/
│
├── static/                ← static assets (copied to dist/static/ at build time)
│   ├── css/               (style.css, normalize.css, responsiveslides.css)
│   ├── js/                (copley.js, jquery.min.js, responsiveslides, blazy/, datepicker/)
│   ├── images/            (~142 files)
│   ├── pdf/               (patient forms)
│   └── videos/            (small mp4s only — large videos moved to YouTube)
│
├── vendor/                ← committed Composer deps (Slim 3, Twig, Pimple, Symfony)
│
├── Dockerfile, docker-compose.yml, Makefile
├── CLAUDE.md, README.md, .gitignore
```

## Architecture

**Bootstrap and routing.** `bootstrap.php` does all the heavy lifting: autoloads, parses `config.ini`, creates the Slim app, wires the DI container (`$container`), registers 13 controller classes, declares all routes (~110), and returns `$app`. `index.php` is a 3-line wrapper that calls `$app->run()` for live HTTP serving. `bin/prerender.php` requires the same `bootstrap.php` but never calls `run()` — it dispatches synthetic requests via `$app->process()`.

**Prerender pipeline.** `bin/prerender.php` iterates `$app->getContainer()->get('router')->getRoutes()`, expands dynamic params from `services.json` (15 slugs) and `gallery.json` (41 cases), dispatches each GET route in-process, and writes the response body to `dist/<path>/index.html`. It hard-fails on any non-200 response and guards against silent 404 bodies. A synthetic 404 is rendered to `dist/404.html` for Cloudflare's fallback.

**Forms.** All 7 forms use `site.js` (copied from `../copleydental/public/js/site.js` at build time — shared with the Boston site). The JS intercepts submit, validates client-side, and POSTs FormData to `https://copleydentalforms.arzs.app/api/contact/{randomId}`. The CRM handles reCAPTCHA server-side via domain allowlist. Each form template follows this HTML contract:
- Container: `<div id="form-container-{id}" data-redirect-to="/thank-you-url/">`
- Status: `<span id="status-{id}" class="hidden">`
- Form: `<form id="form-{id}" class="contact-form" data-form-name="...">`
- Inputs: `class="form-input"`, `required` where needed
- Core fields: `firstName`, `lastName`, `email`, `phone` — anything else auto-concatenated into `message` as "Label: value" lines
- Hidden fields (e.g. `plan`) are auto-included in the message body

**Site location prefix.** `base.html` sets `window.SITE_LOCATION = 'Natick'` before loading `site.js`. When set, `site.js`'s `formatMessage()` prepends `Natick: ` to every outgoing message so the shared CRM can tell Natick submissions apart from the Boston site's. The Boston site does not set this global, so its submissions remain unprefixed. If you fork `site.js` out of the sibling repo or modify its `formatMessage` logic, preserve this opt-in behavior.

**Dead code still present (intentionally).** `forms.php` and `emails.php` contain the old server-side validation and email-sending logic. They're dead (prerender only dispatches GET, never POST) but still `require`'d by `bootstrap.php` because controller classes reference form types. Do not delete them — it breaks the autoload chain.

**Controllers.** All in `src/controllers.php`. Every controller extends a `Controller` base with `$view`, `$config`, `$router`. GET handlers are what the prerender executes. POST handlers are dead code — leave them alone.

**Twig globals** (set in `bootstrap.php`'s view factory): `static` (asset base URL, currently `/static/`), `services` (full ServiceModel list), `site_key`/`secret_key` (reCAPTCHA — `secret_key` is unused now but kept to avoid undefined-variable errors), `slug_services`/`slug_invisalign`/`slug_dental_veneers`/`slug_lumineers`.

**`$basePath`** is hardcoded to `''` in `bootstrap.php` (was previously derived from the HTTP request). The site is always served from document root on Cloudflare Pages.

## Conventions

- **Adding a service page:** add an entry to `src/data/services.json` with slug + template path, create the Twig template under `src/templates/services/<name>/index.html`. The dynamic route `/services/{service}/` handles it automatically. Run `make build` to regenerate `dist/`.
- **Adding a form:** follow the site.js HTML contract above. Use camelCase field names. Core fields (`firstName`, `lastName`, `email`, `phone`) map to the CRM's API fields; everything else auto-appends to `message`. Set `data-redirect-to` to the thank-you page URL.
- **Thank-you URLs** have mixed underscore/hyphen naming (e.g. `/contact-us/thank_you/`, `/request_appointment/thank-you/`). This is intentional — preserved for SEO and existing inbound links. Do not "normalize" without adding `_redirects` rules.
- **Asset URLs** are root-relative (`/static/...`) via `config.ini`'s `static_url=/static/`. Templates reference them as `{{ static }}images/foo.png`.
- **CSR mission videos** are embedded from YouTube (not self-hosted). The old `static/videos/heavy/` directory was deleted.
- **`vendor/` is vendored.** Do not run `composer install` — there is no `composer.json`. Do not modify vendored files.
- **No tests, no linter, no CI.** Verify changes by running `make build` then `make preview` and visually checking affected pages.
- **After any template or data change:** run `make build` to regenerate `dist/`, then `make preview` to verify. The prerender is fast (~5 seconds for all 110 URLs). Commit the regenerated `dist/` along with the source changes so Cloudflare Pages publishes both together.
