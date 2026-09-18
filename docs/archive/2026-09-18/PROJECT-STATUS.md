# Project status — Meet AJ

**Authority:** SINGLE authoritative current-state document. Everything else in `docs/current/` expands one section of this file.
**Date verified:** 2026-09-17
**Verification method:** `php artisan about`, `php artisan migrate:status`, `php artisan route:list`, `php artisan test`, `php artisan site:compare-content`, direct SQLite schema/row inspection, and reading `app/`, `routes/`, `database/`, `resources/`, `config/`, `tests/`.
**Runtime used:** `.runtime/php84/php.exe` (PHP is not on PATH on this workstation).

Status vocabulary used in every document: **PASS** | **FAIL** | **BLOCKED** | **NOT TESTED**.
Where a statement cannot be proven from code or a command, it is marked **UNKNOWN / NOT VERIFIED**.

---

## 1. Application

| Item | Value | Status |
|------|-------|--------|
| Application name | Meet AJ (`php artisan about`) | PASS |
| Application version | **No version constant, tag or `config('app.version')` exists** | UNKNOWN / NOT VERIFIED |
| Public site | Laravel-rendered Blade, EN + FA on the same URLs | PASS (local) |
| Admin | Filament panel at `/admin` | PASS (local, HTTP + PHPUnit) |
| Intended production domain | `meetaj.ir` | BLOCKED (no deploy executed) |

There is no semantic application version in the repository. Do not invent one. The service worker constant `meet-aj-v2.0.0-cms-3` is a **cache name**, not an application version.

## 2. Technology versions (verified 2026-09-17)

| Component | Version | Source |
|-----------|---------|--------|
| Laravel | **13.31.0** | `php artisan about` |
| PHP | **8.4.25** | `php artisan about` |
| Filament | **v5.8.2** | `php artisan about` (Filament section) |
| Livewire | **v4.4.5** | `php artisan about` |
| PHPUnit | **11.5.56** | `php artisan test` header |
| Node / npm | **not used** (no `package.json`, no Vite, no Tailwind build) | repository inspection |

Local environment reported by `php artisan about`: environment `local`, debug **ENABLED**, timezone **UTC**, URL `127.0.0.1:8000`, cache/session drivers `file`, queue `sync`, mail `log`, database `sqlite`, `public/storage` **LINKED**, views **CACHED**, Filament views **NOT PUBLISHED**.
`.env.example` / `.env.production.example` require `APP_DEBUG=false` and `APP_TIMEZONE=Asia/Tehran` for production. The local values are a development setting, not a code defect.

## 3. Database

Engine in use locally: **SQLite** at `database/database.sqlite`. Intended production engine: **MySQL / MariaDB** (not provisioned).

Nine migrations, all **Ran** (batches 1–3). Eleven tables exist, including Laravel's `migrations` table:

| Table | Rows (2026-09-17) |
|-------|-------------------|
| `users` | 0 |
| `password_reset_tokens` | 0 |
| `sessions` | 0 |
| `categories` | 10 |
| `articles` | 23 |
| `article_redirects` | 23 |
| `tags` | 8 |
| `article_tag` | 38 |
| `requests` | 0 |
| `services` | 6 |
| `migrations` | 9 |

There is **no** `pages` table and **no** `contact_requests` table. Full column, index, foreign-key and delete-behaviour detail: [DATABASE.md](DATABASE.md).

**`users` currently holds 0 rows on this workstation**, so no interactive admin login is possible right now without running `php artisan cms:create-user`. This is why several admin UI checks are BLOCKED rather than PASS.

## 4. Architecture

```
Browser → public/index.php → Laravel 13 → Blade views → Eloquent → SQLite (local) / MySQL (intended production)
Admin   → /admin → Filament 5 → Livewire 4 → models + policies → same database
```

No SPA, no Node build step, no queue worker, no Redis, no scheduler in use. Detail: [ARCHITECTURE.md](ARCHITECTURE.md).

Routes: **36 total** from `php artisan route:list` after `optimize:clear` — 11 application routes (`/`, `/index.html`, three article routes, two service routes, two `/forms/*.php` routes, `/sitemap.xml`, `/robots.txt`), 14 Filament `/admin` routes (including two URLs that resolve to the same Users index route name), and 11 Livewire / Filament asset and export routes.

## 5. Public website

| Area | Status |
|------|--------|
| Homepage `/` with original section IDs, hero, about, services catalog, articles teaser, contact | PASS (local) |
| Article library `/articles` with search and tag filter | PASS (local) |
| 23 article detail pages | PASS (local) |
| 6 service detail pages | PASS (local) |
| Legacy `.html` URLs 301 to clean URLs | PASS (local) |
| Contact endpoints `/forms/get-csrf-token.php` and `/forms/contact.php` | PASS (local) |
| Production rendering on meetaj.ir | NOT TESTED |

Feature-by-feature description: [FEATURES.md](FEATURES.md). Asset cache versions currently in the Blade heads: `visual-upgrade.css?v=1405`, `main.js?v=1201`, `i18n.js?v=1201`, `lang-toggle.css?v=1202`.

## 6. CMS / Admin

Filament 5 panel at `/admin`, brand colour `#2563eb`, navigation groups **Content**, **Communications**, **Administration**.

| Resource | Group | Access | Status |
|----------|-------|--------|--------|
| Dashboard (3 widgets) | — | any authenticated CMS user | PASS (routes + code) |
| Articles | Content | admin + editor | PASS (PHPUnit CRUD) |
| Categories | Content | admin + editor | PASS (PHPUnit) |
| Tags | Content | admin + editor | PASS (route + code) |
| Services | Content | admin only | PASS (PHPUnit authorization) |
| Requests | Communications | admin only | PASS (PHPUnit authorization) |
| Users | Administration | admin only | PASS (routes + code); interactive CRUD NOT TESTED |

Detail: [ADMIN.md](ADMIN.md).

## 7. Articles

23 imported English articles, all `status = published` with a non-null `published_at`, 23 matching `article_redirects` rows, 10 categories (5 EN + 5 FA sharing `translation_key`), 8 tags, 38 article↔tag links.
`php artisan site:compare-content` on 2026-09-17: **Failures: 0** across all 23 articles. Detail: [ARTICLES.md](ARTICLES.md).

## 8. Services

Six published English services, ordered by `sort_order`, prices read from the `services` table (AED): Network Design 4900, System Administration 3900, DevOps & Automation 6900, Monitoring & Security 4200, Virtualization Solutions 5900, Technical Consulting 2500. All six use `price_type = fixed` and `price_label = "Fixed Price"`. Detail: [SERVICES.md](SERVICES.md).

## 9. Requests

Inbound contact submissions are stored in `requests` with a seven-value status workflow and admin-only `internal_notes`. The table is **empty** on this workstation (0 rows). Detail: [REQUESTS.md](REQUESTS.md).

## 10. Languages

EN and FA are the public languages on the **same** URLs (`data-en` / `data-fa` attributes plus `assets/js/i18n.js`, RTL through `rtl.css`). DE exists in `config('cms.languages')` for draft rows only; publishing a German article or service throws `ValidationException` and `/de` returns 404. `hreflang` is **not** implemented. Detail: [MULTILINGUAL.md](MULTILINGUAL.md).

## 11. SEO

Canonical URLs, Open Graph, Twitter cards, JSON-LD (home, services, articles), `/sitemap.xml`, `/robots.txt`, 301 redirects for `/index.html` and all legacy `.html` paths. Status PASS locally (PHPUnit); production crawler behaviour NOT TESTED. Detail: [SEO.md](SEO.md).

## 12. PWA

`public/manifest.json`, `public/sw.js` (cache `meet-aj-v2.0.0-cms-3`), `public/offline.html`, with `/admin`, `/livewire`, `/forms` and `/storage/livewire-tmp` excluded from the worker. Files and logic PASS by code review and local HTTP; installability, offline browsing and Lighthouse are **NOT TESTED**. Detail: [PWA.md](PWA.md).

## 13. Security

CSRF (including the legacy `csrf_token` field contract), honeypot, two-layer rate limiting, `StoreContactRequest` validation, hashed passwords with a 12-character minimum, Filament session auth, six policies, and the `SecurityHeaders` middleware. No penetration test and no formal CVE audit were performed. Detail: [SECURITY.md](SECURITY.md).

## 14. Testing

| Command | Result (2026-09-17) | Status |
|---------|---------------------|--------|
| `php artisan test` | **39 tests, 647 assertions, 1 skipped, 0 failures** | PASS |
| `php artisan site:compare-content` | **Failures: 0** | PASS |
| `vendor/bin/phpunit -c phpunit.mysql.xml --filter MysqlSchemaTest` | 1 test, 7 assertions, OK (last run 2026-09-16 against MariaDB on `127.0.0.1:3307`) | PASS |
| Lighthouse / performance budget | never executed | NOT TESTED |
| Production smoke tests | no production environment | BLOCKED |

The skipped test is `MysqlSchemaTest`, which only runs when a MySQL connection is bound. Detail: [TESTING.md](TESTING.md); evidence matrix: [../qa/QA-MATRIX.md](../qa/QA-MATRIX.md).

## 15. Deployment status

Documented DirectAdmin procedure exists and is complete, but **no deployment has been executed** from this environment. PHP 8.4 selector, production MySQL database, `.env`, document-root switch to `public/`, SSL, and post-deploy checks are all **BLOCKED / NOT TESTED**. Detail: [DEPLOYMENT.md](DEPLOYMENT.md).

## 16. Known blockers

1. **No production deployment.** DirectAdmin cutover, production database, SMTP, HTTPS and post-deploy verification are BLOCKED. Nothing in this repository proves meetaj.ir runs the Laravel CMS.
2. **No CMS user exists locally** (`users` = 0 rows), so interactive Filament CRUD (article editing, Users screen, responsive admin tables) is BLOCKED until `php artisan cms:create-user` is run.
3. **No performance measurement.** No Lighthouse, WebPageTest, or query profiling run exists. All performance claims are code-level only.
4. **PWA install / offline behaviour** has never been exercised in a browser.

## 17. Known limitations

1. **`hreflang` is not implemented.** EN and FA share canonical URLs.
2. **German is draft-only.** No German content exists; `/de` is 404 by design.
3. **Scheduled publishing is query-based.** A future `published_at` simply stays invisible; there is no queue or cron to flip it.
4. **Two URLs resolve to the Users index** (`/admin/users` and `/admin/cms-users`) because `AdminPanelProvider` registers an extra `authenticatedRoutes` entry with the same route name. This is one feature, not two.
5. **Six unused static service Blade files** remain in `resources/views/services/` (`devops-automation.blade.php`, `monitoring-security.blade.php`, `network-design.blade.php`, `system-administration.blade.php`, `technical-consulting.blade.php`, `virtualization-solutions.blade.php`). `ServiceController@show` renders `services.show` only, so these files are dead templates and still reference stale asset versions (`visual-upgrade.css?v=1108`, `i18n.js?v=1000`). They were **not** deleted because this pass is documentation-only.
6. **Imported `featured_image` values still point at `/assets/...`** rather than Filament storage unless an editor uploads a replacement.
7. **Source-content leftovers** (not CMS defects): a generic overlay category label on some cards, and the service page “Back to Services” link staying English in FA.
8. **Local `.env` runs with debug enabled and UTC**, unlike the documented production configuration.

## 18. Git

A `.git` directory is present and `.git/HEAD` points at `refs/heads/feature/laravel-migration`. `git.exe` is **not** on PATH on this workstation and **no git command was executed** in this documentation pass — no init, no add, no commit, no push. Remote configuration was not re-verified in this pass: **UNKNOWN / NOT VERIFIED**.
