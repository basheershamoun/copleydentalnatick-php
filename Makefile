.PHONY: up down restart logs shell build preview clean deploy docker-rebuild

# --- Dev container -------------------------------------------------------

up:
	docker compose up -d --build
	@echo "Site running at http://localhost:8090"

down:
	docker compose down

restart:
	docker compose restart

logs:
	docker compose logs -f web

shell:
	docker compose exec web bash

docker-rebuild:
	docker compose build --no-cache

# --- Static build --------------------------------------------------------

# Path to the shared site.js (navbar + form handler). Override if needed:
#   make build SITE_JS_SRC=/absolute/path/to/site.js
SITE_JS_SRC ?= ../copleydental/public/js/site.js

build: clean
	@echo "==> Prerendering Slim routes into dist/"
	docker compose exec -T web php bin/prerender.php
	@echo "==> Copying static assets into dist/static/"
	cp -R static dist/static
	@echo "==> Copying site.js from $(SITE_JS_SRC)"
	cp "$(SITE_JS_SRC)" dist/static/js/site.js
	@echo "==> Copying root files (sitemap, robots, _headers)"
	cp sitemap.xml dist/sitemap.xml
	cp robots.txt dist/robots.txt
	cp deploy/_headers dist/_headers
	@echo "==> Build complete. $$(find dist -type f | wc -l) files in dist/"

preview:
	@echo "Serving dist/ at http://localhost:8091"
	python3 -m http.server 8091 --directory dist

clean:
	rm -rf dist

deploy: build
	wrangler pages deploy dist --project-name=copleydentalnatick
