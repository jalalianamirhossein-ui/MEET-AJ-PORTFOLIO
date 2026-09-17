# Meet AJ Portfolio

Laravel CMS for [meetaj.ir](https://meetaj.ir) — the public portfolio of **AmirHossein Jalalian** (network, DevOps, and IT infrastructure). The live design, article HTML, service pages, contact contract, and SEO URLs from the original static site are preserved.

This repository is a **Laravel 13** application. The original `index.html`, `articles/*.html`, and `services/*.html` files remain in the project root as the import/source of truth. They are **not** the public document root.

## Overview

Visitors see a Blade-rendered site: homepage sections, six service quote pages, 23 technical articles, a bilingual (English / Persian) switcher, and a PWA. Editors use **Filament 5** at `/admin`. Contact submissions keep the original form endpoints and are stored in the `requests` table.

## Features

- Public homepage (`/`) with the original section IDs (`#hero` … `#contact`) and a database-driven service catalog
- Six service **landing** pages at `/services/{slug}` (hero → included → pricing → process → FAQ → quote CTA; form hidden until request; legacy `/services/{slug}.html` 301s once)
- 23 published English articles at `/articles/{slug}` with 301 redirects from `*.html`
- Article search (`/articles?q=`) and tag filters (`/articles?tag=`) with pagination; listing queries omit article HTML bodies
- Tags, related articles, share links (LinkedIn / WhatsApp / Telegram / copy), breadcrumbs + BreadcrumbList JSON-LD
- Category filters on the homepage and `/articles` listing
- Bilingual UI via `data-en` / `data-fa` (English LTR, Persian RTL)
- Contact and service-request forms: CSRF token endpoint, honeypot, validation, rate limit, database persistence (`requests.service_id` when a service is selected)
- Filament admin: articles, categories, tags, services (admin-only), contact request pipeline (admin-only), users (admin-only)
- SEO: canonical, Open Graph, Twitter, JSON-LD (Article + BreadcrumbList), `/sitemap.xml`, `/robots.txt`
- PWA: `manifest.json`, `sw.js`, `/offline.html`
- Overlay stylesheet `assets/css/visual-upgrade.css` (shared design tokens, service landings, no Tailwind)

German is **not** a public language. `/de` returns 404. German article rows cannot be published.

## Design system

Public chrome is unified in `assets/css/visual-upgrade.css` (cache `v=1314`): Article visual DNA (blue `#2563eb`, Poppins/Vazirmatn, 12px buttons, H2 bars). Homepage is editorial (skills lists, resume timeline); services are landings with CTA-gated quote forms. Cards are for grouping only. See [docs/article-visual-dna.md](docs/current/ARTICLE-VISUAL-DNA.md) and [docs/design-system.md](docs/current/DESIGN-SYSTEM.md). Do not add Tailwind, React, Vue, or extra CDNs.

## Technology stack

Verified 2026-09-16 from `composer.lock` and `php artisan about` on this workstation:

| Component | Version |
|-----------|---------|
| PHP | 8.4.25 (constraint `^8.4`) |
| Laravel | 13.31.0 |
| Filament | 5.8.2 |
| Livewire | 4.4.5 |
| PHPUnit | 11.5.56 |
| Database (local) | SQLite (`database/database.sqlite`) |
| Database (intended production) | MySQL / MariaDB |
| Frontend | Blade, Bootstrap 5 (vendored), custom CSS/JS |

There is **no** `package.json`. Node/npm is not required to build or run the site.

## Architecture

```
Browser
   │
   ▼
Laravel 13 (public/ front controller)
   │
   ├── Blade views  →  services / presentation JSON  →  SQLite or MySQL
   │
   └── /admin  →  Filament 5  →  Livewire 4  →  models / policies  →  database
```

Document root in production must be **`public/`**, not the repository root.

## Public website

| URL | Purpose |
|-----|---------|
| `/` | Homepage (EN default markup; FA via client switcher) |
| `/index.html` | 301 → `/` |
| `/articles` | Article listing (same filters/cards as `#portfolio`) |
| `/articles/{slug}` | Article detail |
| `/articles/{slug}.html` | 301 → clean slug (query string preserved) |
| `/services/{slug}` | Service detail (published English only) |
| `/services/{slug}.html` | 301 → `/services/{slug}` (query string preserved) |
| `GET /forms/get-csrf-token.php` | JSON `{ token, success }` |
| `POST /forms/contact.php` | Plain-text `OK` / `400` / `429` |
| `/sitemap.xml`, `/robots.txt` | SEO |
| `/manifest.json`, `/sw.js`, `/offline.html` | PWA |

Languages: **EN production**, **FA production** (same URLs, `dir`/`lang` swapped in the browser). **DE** has no public routes.

## CMS

`/admin` (Filament login at `/admin/login`).

| Resource | Who | Notes |
|----------|-----|--------|
| Articles | Admin + editor | CRUD, publish/draft, SEO, image upload, slug 301 history |
| Categories | Admin + editor | Per-language slug uniqueness |
| Services | Admin only | Catalog, prices, publish/draft, SEO. See [docs/SERVICES.md](docs/current/SERVICES.md) |
| Tags | Admin + editor | Unique name/slug; attach to articles |
| Requests | Admin only | CRM-lite statuses (New → Cancelled) plus internal notes (never public) |
| Users | Admin only | Sidebar **Administration** group. Table shows name/email/role, never hashes. Create accounts with `php artisan cms:create-user` |

Roles: `admin`, `editor`. Passwords: hashed, minimum 12 characters. No default password is shipped.

## Articles

- Importer: `php artisan articles:import-legacy` reads `articles/*.html`
- 23 legacy articles, unique `(language, slug)` and `(translation_key, language)`
- Public listing/detail query **published English** rows (`language = en`)
- Persian copy lives in `data-fa` attributes inside HTML (not separate FA URLs)
- Categories: Microsoft, Linux, MikroTik, VMware, Others (filter classes)
- Tags: DevOps, Linux, Microsoft, MikroTik, Networking, Security, VMware, Windows Server (from real content; `articles:sync-tags`)
- Slug changes write `article_redirects` (`cascadeOnDelete` with the article)
- Do not run `--refresh` on a database that already has editorial edits

## Contact

Contract is unchanged from the static site:

1. `GET /forms/get-csrf-token.php` — session CSRF JSON, `Cache-Control: no-store`
2. `POST /forms/contact.php` — field `csrf_token` (also accepted via `AcceptLegacyCsrfToken`)
3. Honeypot field `website`: if filled, response is still `OK` and **nothing is stored**
4. Validation failures: HTTP 400, first error as `text/plain`
5. Rate limit: 5 posts per IP per hour → HTTP 429 (`throttle:30,1` on the route plus `RateLimiter` 5/hour)
6. Success: row in `requests`, body `OK`
7. Email: optional `CONTACT_NOTIFICATION_EMAIL`. If unset or SMTP fails, the row is still saved

## Languages

See [docs/MULTILINGUAL.md](docs/current/MULTILINGUAL.md). Switcher: compact `#lang-switcher` listbox (`#lang-toggle` button), `localStorage` / cookie `lang` (`en` \| `fa`), stylesheet `rtl.css`. Placeholders with `data-fa-placeholder` are applied by `assets/js/i18n.js`. DE is injected only if the page has `[data-de]` (public pages do not).

## URL migration

| Legacy | Current |
|--------|---------|
| `/index.html` | `/` (301) |
| `/articles/{slug}.html` | `/articles/{slug}` (301) |
| `/services/{slug}.html` | `/services/{slug}` (301) |

## Installation

Requirements: PHP 8.4 with extensions used by Laravel (including `pdo_sqlite` locally and `pdo_mysql` for production), Composer 2, and write access to `storage/` and `bootstrap/cache`.

```bash
composer install
cp .env.example .env
php artisan key:generate
# Set DB_* in .env (sqlite file or mysql)
php artisan migrate
php artisan storage:link
php artisan site:publish-assets --views
php artisan articles:import-legacy
php artisan services:import-legacy
php artisan cms:create-user
```

Do not invent Node steps. Do not commit `.env`.

On this Windows workstation `php` is not on PATH; the verified binary is `.runtime/php84/php.exe`.

## Development

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Public assets are copies under `public/assets/` produced by `site:publish-assets`. Edit sources in `assets/` (and `index.html` / `articles/` / `services/`) then publish.

## Testing

Default suite uses SQLite `:memory:` (`phpunit.xml`):

```bash
php artisan test
# or: vendor/bin/phpunit
```

MariaDB/MySQL schema suite (separate config, skipped in the default run):

```bash
vendor/bin/phpunit -c phpunit.mysql.xml
```

Content compare against original HTML (uses the live configured database):

```bash
php artisan site:compare-content
```

Latest default-suite result on this machine: **39 tests, 647 assertions, 1 skipped** (`MysqlSchemaTest` unless MySQL is bound), **0 failures**. `site:compare-content`: **Failures: 0**.

## Article import

```bash
php artisan articles:import-legacy            # insert missing
php artisan articles:import-legacy --dry-run  # report only
php artisan articles:import-legacy --refresh  # deletes articles + redirects, then re-imports
php artisan articles:sync-tags                # catalog tags from live titles/categories (no invented topics)
```

## Service import

```bash
php artisan services:import-legacy            # insert missing
php artisan services:import-legacy --dry-run  # report only
php artisan services:import-legacy --refresh  # deletes services, then re-imports from HTML
```

Do not run `--refresh` after editorial price or copy changes. Details: [docs/SERVICES.md](docs/current/SERVICES.md).

## Admin user

```bash
php artisan cms:create-user
php artisan cms:create-user --name="…" --email="…" --role=admin
```

Password is prompted unless `--password=` is passed. Minimum 12 characters. Never commit passwords.

## Deployment

See [DEPLOYMENT.md](docs/current/DEPLOYMENT.md). DirectAdmin production cutover is **documented, not executed** in this environment.

## Security

Production must use `APP_DEBUG=false`, HTTPS, `SESSION_SECURE_COOKIE=true`, and a unique `APP_KEY`. Details: [docs/SECURITY.md](docs/current/SECURITY.md). This repository has **not** been penetration-tested.

## Documentation

Start here:

| File | Role |
|------|------|
| [docs/DOCUMENTATION-INDEX.md](docs/historical/DOCUMENTATION-INDEX.md) | Index of every project Markdown file |
| [docs/PROJECT-STATUS.md](docs/current/PROJECT-STATUS.md) | Authoritative current status |
| [docs/QA-MATRIX.md](docs/qa/QA-MATRIX.md) | Authoritative QA evidence |
| [docs/SERVICES.md](docs/current/SERVICES.md) | Service catalog + CMS |
| [docs/features.md](docs/current/FEATURES.md) | Search, tags, related, share, request pipeline |
| [docs/admin-ui-qa.md](docs/qa/ADMIN-QA.md) | Filament UI QA (code + PHPUnit; browser login BLOCKED) |
| [docs/article-visual-dna.md](docs/current/ARTICLE-VISUAL-DNA.md) | Article detail visual reference (do not restyle those pages) |
| [docs/design-system.md](docs/current/DESIGN-SYSTEM.md) | Public overlay tokens |
| [docs/full-site-design-audit.md](docs/qa/DESIGN-AUDIT.md) | Visual system audit vs Article DNA |
| [docs/full-site-visual-qa.md](docs/qa/VISUAL-QA.md) | Rendered visual QA |
| [docs/service-detail-ui-qa.md](docs/historical/service-detail-ui-qa.md) | Six service landing QA |
| [docs/final-project-qa-report.md](docs/qa/FINAL-QA-REPORT.md) | Acceptance PASS/FAIL |
| [DEPLOYMENT.md](docs/current/DEPLOYMENT.md) | DirectAdmin / production |
| [docs/historical/README.md](docs/historical/README.md) | Superseded plans and snapshots |

## Git

Git is handled separately after visual approval. This workspace copy is **not** used for `git init` / commit / push in the visual redesign pass.
