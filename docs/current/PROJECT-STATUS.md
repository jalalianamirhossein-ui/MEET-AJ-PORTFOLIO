# Project status — Meet AJ

**2026-09-20 CMS update:** Homepage identity, navigation, hero, About, stats, skills, resume, contact and footer content are database-backed through `homepage_contents`. See [the homepage CMS guide](HOMEPAGE-CMS.md) and [the current directory map](PROJECT-STRUCTURE.md).

**Authority:** SINGLE authoritative current-state document. Everything else in `docs/current/` expands one section of this file.
**Date verified:** 2026-09-21
**Local environment recheck:** [2026-09-20 repair and verification](../qa/LOCAL-ENVIRONMENT-REPAIR.md). Counts below describe this checkout, not a production server.
**Verification method:** `php artisan migrate:status`, `php artisan optimize:clear`, `php artisan route:list`, full `vendor/bin/phpunit` (**67 tests / 1127 assertions / 1 skipped / 0 failures**), targeted `HomepageContentTest`, `php artisan site:compare-content` (Failures: 0), and reading `app/`, `routes/`, `resources/`, `docs/`, and `tests/`.
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

## 2. Technology versions (verified 2026-09-20)

| Component | Version | Source |
|-----------|---------|--------|
| Laravel | **13.31.0** | `php artisan about` |
| PHP | **8.4.25** | `php artisan about` |
| Filament | **v5.8.2** | `php artisan about` (Filament section) |
| Livewire | **v4.4.5** | `php artisan about` |
| PHPUnit | **11.5.56** | `php artisan test` header |
| Node / npm | **not used** (no `package.json`, no Vite, no Tailwind build) | repository inspection |

Local environment reported by `php artisan about`: environment `local`, debug **OFF**, timezone **Asia/Tehran**, URL `127.0.0.1:8000`, cache/session drivers `file`, queue `sync`, mail `log`, database `sqlite`, `public/storage` **LINKED**, views **CACHED**, Filament views **NOT PUBLISHED**. The storage link was restored during the local recheck; `config/app.php` now reads `APP_TIMEZONE` (UTC fallback).
`.env.example` / `.env.production.example` require `APP_DEBUG=false` and `APP_TIMEZONE=Asia/Tehran` for production. The local values are a development setting, not a code defect.

## 3. Database

Engine in use locally: **SQLite** at `.runtime/cms.sqlite`, selected by the local `DB_DATABASE`. Intended production engine: **MySQL / MariaDB** (not provisioned).

Seventeen application migrations, all **Ran** (batches 1–6), plus Laravel's `migrations` ledger:

| Table | Rows (2026-09-20) |
|-------|-------------------|
| `users` | 0 (create a personal account with `php artisan cms:create-user`) |
| `password_reset_tokens` | 0 |
| `sessions` | (local session files; not counted here) |
| `categories` | 10 |
| `articles` | 25 imported legacy articles |
| `article_redirects` | 25 |
| `tags` | 8 |
| `article_tag` | 39 |
| `requests` | 0 |
| `services` | 13 published; 12 shown in the homepage catalog |
| `testimonials` | 9 |
| `homepage_contents` | 7 |
| `migrations` | 17 |

There is **no** `pages` table and **no** `contact_requests` table. Full column, index, foreign-key and delete-behaviour detail: [DATABASE.md](DATABASE.md).

**`users` currently holds 0 rows in this checkout.** The login page was verified on 2026-09-20. Authenticated browser results from 2026-09-18 are historical evidence; current role/CRUD coverage is provided by the isolated PHPUnit suite.

## 4. Architecture

```
Browser → public/index.php → Laravel 13 → Blade views → Eloquent → SQLite (local) / MySQL (intended production)
Admin   → /admin → Filament 5 → Livewire 4 → models + policies → same database
```

No SPA, no Node build step, no queue worker, no Redis, no scheduler in use. Detail: [ARCHITECTURE.md](ARCHITECTURE.md).

Routes include the homepage, article library/detail/legacy redirects, two form endpoints, sitemap, robots and manifest; there are no standalone service detail routes. Filament adds the dashboard, login/logout, homepage sections, Articles, Categories, Tags, Services, Testimonials, Requests and Users, plus Livewire asset routes.

## 5. Public website

| Area | Status |
|------|--------|
| Homepage `/` with CMS-backed site chrome, hero, about, stats, skills, resume, services catalog, article teaser, testimonials and contact | PASS (local + PHPUnit) |
| Homepage Expertise / تخصص‌ها (5 pastel category columns, LTR left / RTL right accents) | PASS (browser EN+FA + PHPUnit markup) |
| Shared public sidebar + icy-blue mobile menu (`<1200px`) | PASS (browser) |
| Shared Testimonials Swiper (one slider; RTL via `html[dir]`, not a second FA carousel) | PASS (PHPUnit + browser) |
| Article library `/articles` with search and tag filter | PASS (local) |
| 25 imported article detail pages | PASS (content comparison) |
| Standalone service detail pages | Intentionally removed; services remain homepage catalog records |
| Legacy `.html` URLs 301 to clean URLs | PASS (local) |
| Contact endpoints `/forms/get-csrf-token.php` and `/forms/contact.php` | PASS (local) |
| Production rendering on meetaj.ir | NOT TESTED |

Feature-by-feature description: [FEATURES.md](FEATURES.md). Asset cache versions currently in the Blade heads: `visual-upgrade.css?v=1711`, `site-modules.css?v=1840`, `lang-toggle.css?v=1403`, `main.js?v=1412`, `i18n.js?v=1403`.

## 6. CMS / Admin

Filament 5 panel at `/admin`, **White + Red** admin identity (canvas `#ffffff`, primary `#be123c`, gray Slate, danger `#7f1d1d`). The public site stays blue. Navigation groups **Content**, **Communications**, **Administration**.

| Resource | Group | Access | Status |
|----------|-------|--------|--------|
| Dashboard (3 widgets) | — | any authenticated CMS user | PASS (routes + code + browser) |
| Articles | Content | admin + editor | PASS (PHPUnit CRUD + browser chips) |
| Categories | Content | admin + editor | PASS (PHPUnit + editable `accent_color` ColorPicker; slug fallback until a colour is saved) |
| Tags | Content | admin + editor | PASS (route + code + editor nav) |
| Homepage sections | Content | admin + editor | PASS (7 records, bilingual JSON, publish toggle and sort order) |
| Services | Content | admin only | PASS (PHPUnit authorization; hidden from editor nav) |
| Testimonials | Content | admin + editor | PASS (bilingual copy, avatar, publish toggle and sort order) |
| Requests | Communications | admin only | PASS (PHPUnit + browser; editor 403) |
| Users | Administration | admin only | PASS (routes + code + admin nav); user CRUD NOT TESTED |

Detail: [ADMIN.md](ADMIN.md).

## 7. Articles

25 imported English articles, all `status = published` with a non-null `published_at`, 25 matching `article_redirects` rows, 10 categories (5 EN + 5 FA sharing `translation_key`), 8 tags, 39 article↔tag links.
`php artisan site:compare-content` on 2026-09-21: **Failures: 0** across all 25 articles. Detail: [ARTICLES.md](ARTICLES.md).

## 8. Services

Thirteen published English service records exist; twelve have `show_in_catalog = true`. The six legacy services retain their fixed AED prices; seven additional services use custom quotes. Technical Consulting is retained but hidden from the homepage catalog. Standalone service detail pages are removed. Detail: [SERVICES.md](SERVICES.md).

## 9. Requests

Inbound contact submissions are stored in `requests` with a seven-value status workflow and admin-only `internal_notes`. This checkout currently has no request rows. The earlier 2026-09-18 POST report describes a previous local database; current persistence coverage passes in isolated PHPUnit tests. Detail: [REQUESTS.md](REQUESTS.md).

## 10. Languages

EN and FA are the public languages on the **same** URLs (`data-en` / `data-fa` attributes plus `assets/js/i18n.js`, RTL through `rtl.css`). DE exists in `config('cms.languages')` for draft rows only; publishing a German article or service throws `ValidationException` and `/de` returns 404. `hreflang` is **not** implemented. Detail: [MULTILINGUAL.md](MULTILINGUAL.md).

## 11. SEO

Canonical URLs, Open Graph, Twitter cards, JSON-LD (home and articles), `/sitemap.xml`, `/robots.txt`, and the `/index.html` 301. Service detail URLs return 404. Status PASS locally (PHPUnit); production crawler behaviour NOT TESTED. Detail: [SEO.md](SEO.md).

## 12. PWA

`public/manifest.json`, `public/sw.js` (cache `meet-aj-v2.0.0-cms-3`), `public/offline.html`, with `/admin`, `/livewire`, `/forms` and `/storage/livewire-tmp` excluded from the worker. Files and logic PASS by code review and local HTTP; installability, offline browsing and Lighthouse are **NOT TESTED**. Detail: [PWA.md](PWA.md).

## 13. Security

CSRF (including the legacy `csrf_token` field contract), honeypot, two-layer rate limiting, `StoreContactRequest` validation, hashed passwords with a 12-character minimum, Filament session auth, six policies, and the `SecurityHeaders` middleware. No penetration test and no formal CVE audit were performed. Detail: [SECURITY.md](SECURITY.md).

## 14. Testing

| Command | Result (2026-09-20) | Status |
|---------|---------------------|--------|
| `vendor/bin/phpunit` | **67 tests, 1127 assertions, 1 skipped, 0 failures** | PASS |
| `php artisan site:compare-content` | **Failures: 0** | PASS |
| Live `POST /forms/contact.php` | HTTP 200 `OK`; SQLite row id 5 | PASS |
| Cursor browser first-load + FA + Contact hash + 1920/1440/1024/768/390 | Testimonials + Contact visible; no horizontal overflow; English article titles in FA UI | PASS |
| Cursor browser Admin White/Red + contrast re-test | Login/sidebar/table type `#1e293b`; active crimson; forced `html.dark` still readable; Requests PHP unchanged | PASS |
| Cursor browser `/admin/categories` Accent color | ColorPicker + helper + Preview + Reset; empty Linux stays `#15803d` | PASS |
| Cursor browser homepage Expertise EN + FA | 5 columns; pastel pills; LTR left / RTL right 3px markers | PASS |
| `vendor/bin/phpunit -c phpunit.mysql.xml --filter MysqlSchemaTest` | 1 test, 7 assertions, OK (last run 2026-09-16 against MariaDB on `127.0.0.1:3307`) | PASS (not re-run today) |
| Lighthouse / performance budget | never executed | NOT TESTED |
| Production smoke tests | no production environment | BLOCKED |

The skipped test is `MysqlSchemaTest`, which only runs when a MySQL connection is bound. Detail: [TESTING.md](TESTING.md); evidence matrix: [../qa/QA-MATRIX.md](../qa/QA-MATRIX.md).

## 15. Deployment status

Documented DirectAdmin procedure exists and is complete, but **no deployment has been executed** from this environment. PHP 8.4 selector, production MySQL database, `.env`, document-root switch to `public/`, SSL, and post-deploy checks are all **BLOCKED / NOT TESTED**. Detail: [DEPLOYMENT.md](DEPLOYMENT.md).

## 16. Known blockers

1. **No production deployment.** DirectAdmin cutover, production database, SMTP, HTTPS and post-deploy verification are BLOCKED. Nothing in this repository proves meetaj.ir runs the Laravel CMS.
2. **Interactive Users CRUD was not exercised** (create/delete accounts). Login and navigation for Admin and Editor **were** tested.
3. **No performance measurement.** No Lighthouse, WebPageTest, or query profiling run exists. All performance claims are code-level only.
4. **PWA install / offline behaviour** has never been exercised in a browser.

## 17. Known limitations

1. **`hreflang` is not implemented.** EN and FA share canonical URLs.
2. **German is draft-only.** No German content exists; `/de` is 404 by design.
3. **Scheduled publishing is query-based.** A future `published_at` simply stays invisible; there is no queue or cron to flip it.
4. **Authenticated Filament visual QA is PASS** after the 2026-09-18 contrast fix (login labels, sidebar, tables, filters). Evidence: [../qa/ADMIN-QA.md](../qa/ADMIN-QA.md). Users resource CRUD remains NOT TESTED.
5. **Standalone service detail pages** were removed on 2026-09-20. Service records remain for the homepage catalog, while `resources/legacy/services/` remains an importer source. See [PROJECT-STRUCTURE.md](PROJECT-STRUCTURE.md).
6. **Imported `featured_image` values still point at `/assets/...`** rather than Filament storage unless an editor uploads a replacement.
7. **Source-content leftovers** (not CMS defects): a generic overlay category label on some cards.
8. **No local admin account is provisioned.** Run `php artisan cms:create-user` to choose personal credentials. Debug is OFF and `APP_TIMEZONE=Asia/Tehran` is honored.

## 18. Git

A `.git` directory is present and `.git/HEAD` points at `refs/heads/feature/laravel-migration`. `git.exe` is **not** on PATH on this workstation and **no git command was executed** in this documentation pass — no init, no add, no commit, no push. Remote configuration was not re-verified in this pass: **UNKNOWN / NOT VERIFIED**.
