# Meet AJ

Personal portfolio and technical article site for **AmirHossein Jalalian** (infrastructure, networking, virtualization and DevOps), running as a Laravel application with a Filament admin panel.

Verified against the running code on **2026-09-17**. Single source of truth for project state: [docs/current/PROJECT-STATUS.md](docs/current/PROJECT-STATUS.md).

## Overview

The site was originally a static English/Persian progressive web app: one homepage, six service quote pages, 23 HTML articles, PHP contact endpoints, a sitemap and a service worker. It now runs as a **Laravel 13 + Blade + Filament 5** application:

- the public site renders from Blade views rebuilt from the original HTML, so URLs, CSS hooks and JavaScript contracts are unchanged;
- articles and services live in the database and are editable in the admin panel;
- contact and quote submissions are stored as requests with a light workflow;
- English and Persian share the same URLs, switched client-side with RTL support.

The original HTML in the repository root remains the import and rollback source. It is deliberately **outside** the web document root.

## Architecture

```
Browser → public/index.php → Laravel 13 → Blade → Eloquent → SQLite (local) / MySQL (intended production)
Admin   → /admin → Filament 5 → Livewire 4 → models + policies → same database
```

No SPA, no Node build step, no queue worker, no Redis, no scheduler. Detail: [docs/current/ARCHITECTURE.md](docs/current/ARCHITECTURE.md).

## Technology stack

| Component | Version |
|-----------|---------|
| Laravel | 13.31.0 |
| PHP | 8.4.25 |
| Filament | 5.8.2 |
| Livewire | 4.4.5 |
| PHPUnit | 11.5.56 |
| Database | SQLite locally, MySQL/MariaDB intended in production |
| Front end | Blade with the original CSS/JS; no Tailwind, no Vite, no npm |

## Requirements

- PHP **8.4** with `bcmath`, `ctype`, `curl`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql` (or `pdo_sqlite` locally), `session`, `tokenizer`, `xml`, `zip`
- Composer 2
- MySQL/MariaDB for production, or SQLite for local work
- A web server whose document root is the `public/` directory

There is no Node.js requirement.

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

On a machine where `php` is not on PATH, prefix commands with the interpreter you have, for example `.\.runtime\php84\php.exe artisan about`.

## Environment configuration

`.env.example` is for local development; `.env.production.example` is the production template. Keys that matter:

| Key | Local | Production |
|-----|-------|------------|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | **`false`** |
| `APP_URL` | `http://127.0.0.1:8000` | `https://meetaj.ir` |
| `APP_TIMEZONE` | your choice | `Asia/Tehran` |
| `DB_CONNECTION` | `sqlite` | `mysql` |
| `SESSION_DRIVER`, `CACHE_STORE` | `file` | `file` |
| `QUEUE_CONNECTION` | `sync` | `sync` |
| `SESSION_SECURE_COOKIE` | — | `true` |
| `CONTACT_NOTIFICATION_EMAIL` | optional | optional; empty disables notification mail |

Never commit `.env`.

## Database setup

### Migration

```bash
php artisan migrate            # production: php artisan migrate --force
php artisan migrate:status
```

Nine migrations create eleven tables: `users`, `password_reset_tokens`, `sessions`, `categories`, `articles`, `article_redirects`, `tags`, `article_tag`, `requests`, `services`, plus Laravel's `migrations`. Full schema: [docs/current/DATABASE.md](docs/current/DATABASE.md).

### Seeding

```bash
php artisan db:seed
```

`DatabaseSeeder` rebuilds the Blade views from the original HTML, then imports articles and services. You can also run the importers directly:

```bash
php artisan articles:import-legacy      # 23 articles + 23 redirects
php artisan services:import-legacy      # 6 services with their AED prices
php artisan articles:sync-tags          # tag vocabulary and links
```

Both importers accept `--dry-run` and `--refresh`. **`--refresh` deletes existing rows** and discards editorial changes — never run it on a database with edits.

## Local development

```bash
php artisan site:publish-assets --views   # copy assets into public/ and rebuild Blade
php artisan serve                          # http://127.0.0.1:8000
```

`php artisan optimize:clear` after changing routes, config or views.

## Admin panel

`/admin`, built with Filament 5. Guests are redirected to `/admin/login`. No default account ships with the repository:

```bash
php artisan cms:create-user
```

Roles are `admin` and `editor`; passwords need at least 12 characters.

| Section | Resources |
|---------|-----------|
| Content | Articles, Categories, Tags, Services (admin only) |
| Communications | Requests (admin only) |
| Administration | Users (admin only) |

Detail: [docs/current/ADMIN.md](docs/current/ADMIN.md).

## Articles

23 published English articles with 23 legacy `.html` → clean-URL redirects, 10 categories (5 concepts × EN/FA), 8 tags and 38 tag links. The library at `/articles` supports `?q=` search and `?tag=` filtering with 9 results per page, and each article page has breadcrumbs, tags, share links and up to three related articles. Content integrity against the original HTML is verified by `php artisan site:compare-content` (currently **Failures: 0**). Detail: [docs/current/ARTICLES.md](docs/current/ARTICLES.md).

## Services

Six database-driven services rendered on the homepage and at `/services/{slug}`, with `/services/{slug}.html` issuing a single 301. Prices are editorial data held in the `services` table (AED 2,500–6,900) and are never hardcoded in Blade. Detail: [docs/current/SERVICES.md](docs/current/SERVICES.md).

## Requests

`GET /forms/get-csrf-token.php` and `POST /forms/contact.php` keep the original contract: CSRF via the legacy `csrf_token` field, a `website` honeypot, validation with plain-text errors, and a limit of 5 submissions per IP per hour. Submissions become `requests` rows with a seven-stage status workflow and admin-only internal notes. Detail: [docs/current/REQUESTS.md](docs/current/REQUESTS.md).

## Languages

English and Persian are public on the **same** URLs, applied client-side through `data-en` / `data-fa` attributes, `assets/js/i18n.js` and `assets/css/rtl.css`. German exists in the CMS language list for drafts only: publishing a German article or service throws a validation error, and `/de` returns 404. `hreflang` is not implemented. Detail: [docs/current/MULTILINGUAL.md](docs/current/MULTILINGUAL.md).

## SEO

Canonical URLs, Open Graph, Twitter cards, JSON-LD (Person/WebSite on the homepage, `Service` with a real `Offer`, `Article` with `BreadcrumbList`), a dynamic `/sitemap.xml` listing clean URLs only, `/robots.txt` disallowing `/admin`, `/livewire` and `/forms`, and 301s for `/index.html` and every legacy `.html` path. Detail: [docs/current/SEO.md](docs/current/SEO.md).

## PWA

`public/manifest.json`, `public/sw.js` (cache `meet-aj-v2.0.0-cms-3`) and `public/offline.html`. The worker serves documents network-first and static assets cache-then-network, and never touches `/admin`, `/livewire`, `/forms`, `/storage/livewire-tmp` or `.php` paths. Install and offline behaviour have **not** been tested in a browser. Detail: [docs/current/PWA.md](docs/current/PWA.md).

## Testing

```bash
php artisan test                                                    # 39 tests, 647 assertions, 1 skipped
vendor/bin/phpunit -c phpunit.mysql.xml --filter MysqlSchemaTest     # MySQL schema check
php artisan site:compare-content                                     # Failures: 0
```

The skipped test is `MysqlSchemaTest`, which only runs when a MySQL connection is bound. Detail: [docs/current/TESTING.md](docs/current/TESTING.md); per-URL evidence: [docs/qa/QA-MATRIX.md](docs/qa/QA-MATRIX.md).

## Deployment

### DirectAdmin

The full procedure — PHP 8.4 selector, Composer or a pre-built `vendor/`, MySQL creation, file layout above the web root, document root set to `.../laravel/public`, `.env`, permissions, `storage:link`, migrate, import, caches, SSL, post-deploy checks and rollback — is in [docs/current/DEPLOYMENT.md](docs/current/DEPLOYMENT.md).

**Deployment has not been executed.** Nothing in this repository proves that meetaj.ir is running this application.

### Scheduler

Laravel's scheduler is **not used** and no cron entry is required. If a future feature needs it, add `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1` at that point and not before.

## Security

CSRF (including the legacy field contract), a honeypot, two layers of rate limiting, centralised validation in `StoreContactRequest`, hashed passwords with a 12-character minimum, Filament session auth, six policies, and a `SecurityHeaders` middleware (`nosniff`, `Referrer-Policy`, `SAMEORIGIN`, HSTS on HTTPS, `no-store` on admin/Livewire/forms). `APP_DEBUG` must be `false` in production. No penetration test has been performed. Detail: [docs/current/SECURITY.md](docs/current/SECURITY.md).

## Project structure

```
app/          Laravel code (Filament resources, controllers, models, policies, services, commands)
articles/     23 original article HTML files (import source)
assets/       Original CSS / JS / images (publish source)
config/       Configuration, including cms.php
database/     9 migrations, seeders, local SQLite file
design-system/ Visual language notes
docs/         Documentation (see the index below)
forms/        Original static PHP endpoints, kept as reference
public/       Web document root — the only directory a server should expose
resources/    Blade views and admin CSS
routes/       web.php, console.php
scripts/      validate-environment.php, verify-originals.php
services/     6 original service HTML files (import source)
tests/        7 feature test files, 39 tests
```

Detail: [docs/current/PROJECT-STRUCTURE.md](docs/current/PROJECT-STRUCTURE.md).

## Documentation index

Full navigation map: [docs/README.md](docs/README.md).

| Area | Start here |
|------|------------|
| Current state | [docs/current/PROJECT-STATUS.md](docs/current/PROJECT-STATUS.md) |
| Architecture | [docs/current/ARCHITECTURE.md](docs/current/ARCHITECTURE.md) |
| Database | [docs/current/DATABASE.md](docs/current/DATABASE.md) |
| Admin | [docs/current/ADMIN.md](docs/current/ADMIN.md) |
| Features | [docs/current/FEATURES.md](docs/current/FEATURES.md) |
| Design system | [docs/current/DESIGN-SYSTEM.md](docs/current/DESIGN-SYSTEM.md) |
| QA evidence | [docs/qa/FINAL-QA-REPORT.md](docs/qa/FINAL-QA-REPORT.md) |
| Decisions | [docs/decisions/ADR/README.md](docs/decisions/ADR/README.md) |
| History | [docs/phases/phase-01-environment.md](docs/phases/phase-01-environment.md), [docs/historical/README.md](docs/historical/README.md) |

## Known limitations

1. **Not deployed.** DirectAdmin cutover, production database, SMTP and HTTPS verification are all outstanding.
2. **No CMS user exists locally**, so interactive admin QA is blocked until `php artisan cms:create-user` is run.
3. **No performance measurement** of any kind has been made — no Lighthouse, no load test.
4. **PWA install and offline behaviour** have never been exercised in a browser.
5. **`hreflang` is not implemented**; English and Persian share canonical URLs.
6. **German is draft-only** and no German content exists.
7. **Scheduled publishing is query-based**: a future `published_at` simply stays hidden, with nothing to flip it later.
8. **`/admin/users` and `/admin/cms-users`** resolve to the same Users screen; that is one feature at two paths.
9. **Six unused per-service Blade templates** remain in `resources/views/services/` with stale asset versions; only `services/show.blade.php` is routed.
10. **Imported article images** still point at `/assets/...` unless an editor uploads a replacement.

Every limitation above is tracked with a status in [docs/current/PROJECT-STATUS.md](docs/current/PROJECT-STATUS.md).
