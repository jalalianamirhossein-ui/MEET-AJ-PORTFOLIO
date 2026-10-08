# Meet AJ

Personal portfolio and technical article site for **AmirHossein Jalalian** (infrastructure, networking, virtualization and DevOps), running as a Laravel application with a Filament admin panel.

Enterprise hardening reviewed on **2026-10-08**. See the [security audit](docs/SECURITY-AUDIT-REPORT.md), [refactoring report](docs/PROJECT-REFACTORING-REPORT.md), [deployment/rollback](DEPLOYMENT.md), and [development](DEVELOPMENT.md). Current project state: [docs/current/PROJECT-STATUS.md](docs/current/PROJECT-STATUS.md).

## Overview

The site was originally a static English/Persian progressive web app: one homepage, legacy service sources, 25 HTML articles, PHP contact endpoints, a sitemap and a service worker. It now runs as a **Laravel 13 + Blade + Filament 5** application:

- the public site renders from Blade views rebuilt from the original HTML, so URLs, CSS hooks and JavaScript contracts are unchanged;
- homepage sections, articles, services and testimonials live in the database and are editable in the admin panel;
- contact and quote submissions are stored as requests with a light workflow;
- 28 maintained article sources support the article library; 27 local rows have paired FA/EN localization metadata at one clean URL, using the same saved language preference as the homepage, with instant switching and RTL support.

The maintained HTML in `resources/legacy/` remains the import and view-generation source. Exact pre-upgrade articles are archived in `docs/enterprise-articles/originals.zip`. It is deliberately **outside** the web document root.

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
| Filament | 5.8.3 |
| Livewire | 4.4.5 |
| PHPUnit | 11.5.56 |
| Database | SQLite locally, MySQL/MariaDB intended in production |
| Front end | Blade with the original CSS/JS; no Tailwind, no Vite, no npm |
| Vendored slider | Swiper 12.1.2; [frontend dependency inventory](resources/assets/vendor/DEPENDENCIES.md) |

## Requirements

- PHP **8.4** with `bcmath`, `ctype`, `curl`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql` (or `pdo_sqlite` locally), `session`, `tokenizer`, `xml`, `zip`
- Composer 2
- MySQL/MariaDB for production, or SQLite for local work
- A web server whose document root is the `public/` directory

Node.js is optional for frontend and documentation checks; serving the site has no Node.js requirement.

## Installation

For a new checkout only; preserve `.env` and its key when updating an existing installation.

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
| `APP_DEBUG` | `false` | **`false`** |
| `APP_URL` | `http://127.0.0.1:8000` | `https://meetaj.ir` |
| `APP_TIMEZONE` | your choice | `Asia/Tehran` |
| `DB_CONNECTION` | `sqlite` | `mysql` |
| `SESSION_DRIVER`, `CACHE_STORE` | `file` | `file` |
| `QUEUE_CONNECTION` | `sync` | `sync` |
| `SESSION_SECURE_COOKIE` | — | `true` |
| `FORCE_HTTPS` | `false` by default | `true` by default; verify proxy scheme forwarding |
| `TRUSTED_PROXIES` | empty | actual proxy IPs/CIDRs, or empty for direct hosting |
| `CONTACT_NOTIFICATION_EMAIL` | optional | optional; empty disables notification mail |

Never commit `.env`. Deleting `.env.production.example` does not remove a configured application environment; restore that template from Git if needed. For an existing installation, recover `.env` and its original `APP_KEY` from a protected backup. Generate a key only for a new installation; see [environment recovery](docs/current/DEPLOYMENT.md#6-environment).

## Database setup

### Migration

```bash
php artisan migrate            # production: php artisan migrate --force
php artisan migrate:status
```

Twenty-five application migration files create the CMS schema and repair data integrity, including `homepage_contents`, the resume-content repair, `testimonials`, `services`, articles, requests, taxonomy and legacy redirects. Full schema: [docs/current/DATABASE.md](docs/current/DATABASE.md).

### Seeding

```bash
php artisan db:seed
```

`DatabaseSeeder` synchronizes homepage sections, rebuilds the article-library view from the original HTML, then imports articles and services. The homepage view itself is CMS-backed and is never overwritten by the legacy publisher. You can also run the importers directly:

```bash
php artisan articles:import-legacy      # import missing articles + redirects
php artisan articles:import-legacy --update-existing --dry-run # preview source replacements
php artisan articles:import-legacy --update-existing          # apply reviewed replacements
php artisan services:import-legacy      # 6 services with their AED prices
php artisan articles:sync-tags          # tag vocabulary and links
```

`--update-existing` preserves article IDs, translation keys, publication status and publication dates, but replaces content/metadata for changed sources; back up the database and review CMS edits first. A plain import skips existing article bodies. Both importers accept `--dry-run` and `--refresh`. **`--refresh` deletes existing rows** and discards editorial changes — never run it on a database with edits.

## Local development

```bash
php artisan site:publish-assets --views   # publish assets and rebuild the generated article listing view
php artisan filament:assets               # publish admin CSS and Filament assets
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
| Content | Homepage sections, Articles, Categories, Tags, Testimonials, Services (admin only) |
| Communications | Requests (admin only) |
| Administration | Users (admin only) |

Detail: [docs/current/ADMIN.md](docs/current/ADMIN.md).

## Articles

Published English articles with legacy `.html` → clean-URL redirects, bilingual categories, tags and related content. The library at `/articles` supports `?q=` search and `?tag=` filtering, and each article page has breadcrumbs, tags, share links and related articles. Content integrity against maintained HTML sources is verified by `php artisan site:compare-content` (2026-10-06 local comparison: **27 failures**, requiring review of source/database differences). Detail: [docs/current/ARTICLES.md](docs/current/ARTICLES.md).

## Services

The local database has thirteen published services; twelve render in the homepage catalog and details drawer. Six legacy records retain fixed AED prices (2,500–6,900); seven use custom quotes, and Technical Consulting is hidden from the catalog. Standalone `/services/{slug}` pages and their legacy `.html` redirects were removed. Prices are editorial data held in the `services` table and are never hardcoded in Blade. Detail: [docs/current/SERVICES.md](docs/current/SERVICES.md).

## Requests

`GET /forms/get-csrf-token.php` and `POST /forms/contact.php` keep the original contract: CSRF via the legacy `csrf_token` field, a `website` honeypot, validation with plain-text errors, and a limit of 5 submissions per IP per hour. Submissions become `requests` rows with a seven-stage status workflow and admin-only internal notes. Detail: [docs/current/REQUESTS.md](docs/current/REQUESTS.md).

## Languages

The homepage, library and articles share a saved language preference. `/articles/{slug}` renders Persian when the `lang=fa` preference cookie is set, and English by default. The floating toggle changes text, direction and metadata in place without changing the URL or reloading; code blocks are shared. Both editions use the same canonical URL, and old language-query URLs redirect to it. No separate language alternates are emitted. German remains draft-only and `/de` returns 404. Detail: [docs/current/MULTILINGUAL.md](docs/current/MULTILINGUAL.md).

## SEO

Canonical URLs, Open Graph, Twitter cards, JSON-LD on the homepage and articles, a dynamic `/sitemap.xml` listing the homepage plus published clean article URLs (28 published local article rows) with modification dates, `/robots.txt` disallowing `/admin`, `/livewire` and `/forms`, and 301s for `/index.html`, legacy article `.html` paths and retired language queries. Removed service detail paths return 404. Detail: [docs/current/SEO.md](docs/current/SEO.md).

## PWA

`public/manifest.json`, `public/sw.js` (cache `meet-aj-v2.0.0-cms-3`) and `public/offline.html`. The worker serves documents network-first and static assets cache-then-network, and never touches `/admin`, `/livewire`, `/forms`, `/storage/livewire-tmp` or `.php` paths. Install and offline behaviour have **not** been tested in a browser. Detail: [docs/current/PWA.md](docs/current/PWA.md).

## Testing

```bash
php vendor/phpunit/phpunit/phpunit                                  # full feature suite
php vendor/phpunit/phpunit/phpunit -c phpunit.mysql.xml --filter MysqlSchemaTest     # MySQL schema check
php artisan site:compare-content                                     # inspect source/database differences
```

Current verification is recorded in [the structure and documentation audit](docs/qa/STRUCTURE-DOCUMENTATION-AUDIT-2026-10-06.md). The 2026-10-01 suite result is historical. The skipped test is `MysqlSchemaTest`, which only runs when a MySQL connection is bound. Homepage CMS details: [docs/current/HOMEPAGE-CMS.md](docs/current/HOMEPAGE-CMS.md). Testing detail: [docs/current/TESTING.md](docs/current/TESTING.md).

## Deployment

### DirectAdmin

The full procedure — PHP 8.4 selector, Composer or a pre-built `vendor/`, MySQL creation, file layout above the web root, document root set to `.../laravel/public`, `.env`, permissions, `storage:link`, migrate, import, caches, SSL, post-deploy checks and rollback — is in [docs/current/DEPLOYMENT.md](docs/current/DEPLOYMENT.md).

**Production was reported as deployed by the owner.** Read-only public HTTP checks on 2026-10-08 observed HTTP 200 without an HTTPS redirect, broad HSTS on HTTPS, and secure session attributes. Server configuration, remote release, database and SMTP remain unverified. Deploy the reviewed HTTPS/proxy/header fixes using [the deployment guide](DEPLOYMENT.md).

### Scheduler

Laravel's scheduler is **not used** and no cron entry is required. If a future feature needs it, add `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1` at that point and not before.

## Security

CSRF, a honeypot, two layers of rate limiting, request validation, hashed passwords, Filament session auth, and eight policies protect application entry points. Global middleware enforces production HTTPS, adds compatible CSP/permissions restrictions and host-only HSTS, and prevents caching of private/signed responses. Article text is normalized once before HTML sanitization. All CMS image fields use safe upload storage. `APP_DEBUG` must be `false` in production. No production penetration test was performed. Detail: [security architecture](docs/current/SECURITY.md) and [findings](docs/SECURITY-AUDIT-REPORT.md).

## Project structure

```
app/          Laravel application and admin panel code
bootstrap/    Application bootstrap and generated cache
config/       Laravel and CMS configuration
database/     Migrations, seeders, ignored local SQLite database
docs/         Documentation, design system, QA and historical references
public/       Web document root and published assets
resources/    Asset sources, article packages, static files, legacy content and Blade views
routes/       Web and console routes
scripts/      PHP/Python maintenance tools, article generators and documentation checks
deploy/       Ubuntu VPS installation and update scripts
storage/      Uploads, caches, logs and temporary output
tests/        PHPUnit feature tests and Node frontend tests
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
| Homepage CMS | [docs/current/HOMEPAGE-CMS.md](docs/current/HOMEPAGE-CMS.md) |
| Features | [docs/current/FEATURES.md](docs/current/FEATURES.md) |
| Design system | [docs/current/DESIGN-SYSTEM.md](docs/current/DESIGN-SYSTEM.md) |
| QA evidence | [docs/qa/FULL-AUDIT-2026-10-01.md](docs/qa/FULL-AUDIT-2026-10-01.md), [docs/qa/DESIGN-SYSTEM-AUDIT-2026-09-21.md](docs/qa/DESIGN-SYSTEM-AUDIT-2026-09-21.md), [docs/qa/FINAL-QA-REPORT.md](docs/qa/FINAL-QA-REPORT.md), [docs/qa/QA-MATRIX.md](docs/qa/QA-MATRIX.md) |
| Decisions | [docs/decisions/ADR/README.md](docs/decisions/ADR/README.md) |
| History | [docs/phases/phase-01-environment.md](docs/phases/phase-01-environment.md), [docs/historical/README.md](docs/historical/README.md) |

## Known limitations

1. **Remote application release/configuration unverified.** Public HTTP behavior was checked read-only; server/database/SMTP validation and deploying these fixes remain operator work.
2. **No CMS user exists locally**, so interactive admin QA is blocked until `php artisan cms:create-user` is run.
3. **No performance measurement** of any kind has been made — no Lighthouse, no load test.
4. **PWA install and offline behaviour** have never been exercised in a browser.
5. **Article upgrades require an explicit database update.** Pulling source files alone does not replace already-imported content.
6. **German is draft-only** and no German content exists.
7. **Scheduled publishing is query-based**: a future `published_at` simply stays hidden, with nothing to flip it later.
8. **Admin route aliases must follow the current route list.** The inspected checkout registers `/admin/users`; it does not register `/admin/cms-users`.
9. **Standalone service detail pages** were removed; service records remain for the homepage catalog and are synchronized from `HomepageServiceCatalog`.
10. **Imported article images** still point at `/assets/...` unless an editor uploads a replacement.

Every limitation above is tracked with a status in [docs/current/PROJECT-STATUS.md](docs/current/PROJECT-STATUS.md).

Image sources, article-body folders and CMS upload paths: [image guide](docs/current/IMAGES.md).
