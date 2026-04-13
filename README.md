# Copley Dental Natick — Static Site Generator

Dental practice marketing site. PHP/Slim 3 + Twig templates are the **source of truth**. A prerender script walks the Slim router and generates static HTML into `dist/`, which deploys to Cloudflare Pages.

## What was done

- Extracted `bootstrap.php` from `index.php` so both the live PHP app and the CLI prerender share the same Slim setup
- Wrote `bin/prerender.php` — iterates all 110 GET routes, dispatches each through `$app->process()`, writes `dist/<path>/index.html`
- Rewrote 7 form templates to use the `site.js` client-side form handler (POSTs to CRM at `copleydentalforms.arzs.app`)
- Removed server-side reCAPTCHA (CRM handles abuse prevention)
- Replaced 3 self-hosted mission videos (221MB) with YouTube embeds
- Removed Flash `.swf` files (dead since 2020)
- Removed 57MB `error_log` (8 years of deprecation warnings)
- Removed cPanel artifacts (`ssl/`, `ssfm/`, `scctmp/`, `cgi-bin/`, `public_html/`, `.well-known/`, `.ftpquota`, `test.txt`, static `404.html`/`502.html`/`index.html`)
- Switched asset URLs from absolute to relative (`/static/`) for host-agnostic output
- Added Docker dev environment (`Dockerfile` + `docker-compose.yml`)
- Added Cloudflare Pages deploy config (`deploy/_headers`)

## Commands

```sh
make up          # Start PHP dev container at http://localhost:8090
make build       # Prerender all routes + copy assets → dist/
make preview     # Serve dist/ at http://localhost:8091
make clean       # Remove dist/
make deploy      # Upload dist/ to Cloudflare Pages
make down        # Stop container
make shell       # Shell into container
```

## How it works

1. `make up` starts a PHP 7.4 + Apache container with the source mounted
2. `make build` runs `bin/prerender.php` inside the container, which:
   - Reads all routes from the Slim router
   - Expands dynamic params from `services.json` (15 services) and `gallery.json` (41 cases)
   - Dispatches each GET request in-process and writes the response to `dist/`
   - Copies `static/` (CSS, JS, images, PDFs) and `site.js` into `dist/`
3. `make preview` serves `dist/` locally for verification
4. `make deploy` pushes `dist/` to Cloudflare Pages

## Forms

All forms use `site.js` (from `../copleydental/public/js/site.js`). It intercepts submit, validates client-side, and POSTs FormData to the CRM endpoint. No PHP involved at runtime.

Required HTML contract per form:
- Container: `<div id="form-container-{id}" data-redirect-to="/thank-you-url/">`
- Status: `<span id="status-{id}" class="hidden">`
- Form: `<form id="form-{id}" class="contact-form">`
- Inputs: `class="form-input"`, names in camelCase
- Core fields: `firstName`, `lastName`, `email`, `phone` — everything else auto-concatenated into `message`
