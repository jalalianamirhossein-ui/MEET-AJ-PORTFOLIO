# Project status — Meet AJ Laravel CMS

**Authority:** AUTHORITATIVE current-state document.  
**Verified:** 2026-09-17 against application code, `composer.lock`, migrations, routes, and local test runs.  
**Not production-validated.** DirectAdmin cutover has not been executed from this environment.

Status vocabulary: **IMPLEMENTED** | **TESTED** | **NOT TESTED** | **BLOCKED** | **PLANNED**.  
Test scope: **LOCAL TESTED** | **INTEGRATION TESTED** | **PRODUCTION TESTED**.

---

## Project overview

Meet AJ is the public portfolio of AmirHossein Jalalian. The original static EN/FA PWA (homepage, six service quote pages, 23 HTML articles, contact `.php` endpoints, SEO URLs, PWA) now runs as a **Laravel 13 + Blade + Filament 5** application. Original HTML in the repository root remains the import/source of truth. Public document root is **`public/`**.

| Item | Classification |
|------|----------------|
| Laravel public site | IMPLEMENTED · LOCAL TESTED |
| Filament `/admin` | IMPLEMENTED · LOCAL TESTED (HTTP login page + PHPUnit CRUD; interactive editor login **NOT TESTED** in this pass) |
| Production on meetaj.ir | PLANNED · BLOCKED (no DirectAdmin deploy from this environment) |

## Current architecture

```
Browser → Laravel 13 (public/) → Blade → services → SQLite (dev) or MySQL/MariaDB (intended production)
Admin   → Filament 5 → Livewire 4 → models / policies → same database
```

Details: [ARCHITECTURE.md](ARCHITECTURE.md).

## Technology versions

From `composer.lock` and `.runtime/php84/php.exe -v` / `php artisan about` on this workstation:

| Component | Constraint / lock | Runtime (local) | Status |
|-----------|-------------------|-----------------|--------|
| PHP | `^8.4` | 8.4.25 | IMPLEMENTED · LOCAL TESTED |
| Laravel | `laravel/framework` **v13.31.0** | 13.31.0 | IMPLEMENTED · LOCAL TESTED |
| Filament | `filament/filament` **v5.8.2** | 5.8.2 | IMPLEMENTED · LOCAL TESTED |
| Livewire | `livewire/livewire` **v4.4.5** | 4.4.5 | IMPLEMENTED |
| PHPUnit | **11.5.56** | 11.5.56 | IMPLEMENTED · LOCAL TESTED |
| Node / npm | none (`package.json` absent) | — | not required |

Local `php artisan about` also showed: environment **local**, debug **ENABLED**, timezone **UTC**, DB **sqlite**, URL `http://127.0.0.1:8000`, events/routes/views **CACHED**, Filament views **NOT PUBLISHED**. `.env.example` intends `APP_TIMEZONE=Asia/Tehran` and `APP_DEBUG=false`. Production must use `.env.production.example` values. This mismatch is a **local environment** fact, not a code defect.

## Database status

Tables from migrations (no `pages`, no `contact_requests`):

`users`, `password_reset_tokens`, `sessions`, `categories`, `articles`, `article_redirects`, `tags`, `article_tag`, `requests`, `services`.

| Environment | Engine | Status |
|-------------|--------|--------|
| Default PHPUnit | SQLite `:memory:` | LOCAL TESTED (39 tests, 647 assertions, 1 skipped, 0 failures) |
| Local artisan | SQLite `database/database.sqlite` | LOCAL TESTED |
| MariaDB via `phpunit.mysql.xml` | `127.0.0.1:3307` / `meetaj_test` | INTEGRATION TESTED (`MysqlSchemaTest` 1 test, 7 assertions, OK) |
| DirectAdmin production DB | — | BLOCKED · NOT TESTED |

See [DATABASE.md](DATABASE.md).

## Article migration

- 23 source files in `articles/*.html`
- Importer: `php artisan articles:import-legacy` (`--dry-run`, `--refresh`)
- Public query: published **English** rows; Persian in `data-fa` inside HTML
- 23 `article_redirects` for `*.html` → clean slug
- `php artisan site:compare-content`: **Failures: 0** (this workstation)

| Item | Status |
|------|--------|
| Import + listing + detail + 301s | IMPLEMENTED · LOCAL TESTED |
| Search / tags / related / share / breadcrumbs | IMPLEMENTED · LOCAL TESTED (`ArticleLibraryTest` + browser search + SSH article) |
| German published articles | Not allowed (model throws `ValidationException`) |

## Service catalog

- Six source files in `services/*.html` (AED prices from original JSON-LD)
- Importer: `php artisan services:import-legacy` (`--dry-run`, `--refresh`)
- Public query: published **English** rows ordered by `sort_order`
- Canonical `/services/{slug}`; `/services/{slug}.html` 301 once
- Filament `ServiceResource` admin-only; prices not hardcoded in Blade

| Item | Status |
|------|--------|
| Import + homepage catalog + detail + 301s + requests.service_id | IMPLEMENTED |
| German published services | Not allowed (model throws `ValidationException`) |

## Frontend migration

Blade views rebuilt from original HTML via `php artisan site:publish-assets --views`. Overlay `assets/css/visual-upgrade.css` (cache `v=1120`) after `rtl.css`. No Tailwind. Identity (photo hero, white sidebar, blue footer, `#2563eb`, Poppins / Vazirmatn) retained. Service details render `services/show.blade.php` as landings (form hidden until CTA).

| Item | Status |
|------|--------|
| Homepage section IDs | IMPLEMENTED · LOCAL TESTED |
| Homepage service catalog (`sort_order`, prices from DB) | IMPLEMENTED |
| Six `/services/{slug}` + `.html` 301 | IMPLEMENTED |
| `/articles` listing + filters | IMPLEMENTED · LOCAL TESTED (browser QA 2026-09-16) |
| EN/FA switcher | IMPLEMENTED · LOCAL TESTED |
| Production visual on meetaj.ir | NOT TESTED |

## Filament status

Panel `/admin`, brand colour `#2563eb`, groups Content | Communications | Administration.

| Resource | Navigation | Status |
|----------|------------|--------|
| Articles | yes | IMPLEMENTED · LOCAL TESTED (PHPUnit CRUD) |
| Categories | yes | IMPLEMENTED · LOCAL TESTED |
| Services | yes, admin-only | IMPLEMENTED |
| Requests | yes, admin-only | IMPLEMENTED · LOCAL TESTED (editor denied; service column/filter) |
| Users | **hidden** (`shouldRegisterNavigation = false`) | Class + routes exist after cache clear (`/admin/users`, `/admin/cms-users`). Sidebar hidden. Create users with `cms:create-user`. Browser CRUD **NOT TESTED** |

Widgets registered: `CmsStatsOverview`, `RecentArticles`, `RecentRequests`. There is no `NewRequests` widget on disk.

Interactive Filament login + article edit in a real browser: **NOT TESTED** in the documentation verification pass.

## Contact system

`GET /forms/get-csrf-token.php` JSON `{ token, success }`.  
`POST /forms/contact.php` CSRF, honeypot `website`, validation, 5 posts/IP/hour, persist `requests` (optional `service` slug → `service_id`), plain `OK`. Mail optional.

| Item | Status |
|------|--------|
| Token + persist + honeypot + 400 + 429 | IMPLEMENTED · LOCAL TESTED |
| SMTP notification on production | NOT TESTED · optional |
| DirectAdmin mail | BLOCKED · NOT TESTED |

## SEO

Canonical, Open Graph, Twitter, JSON-LD (home, services, articles), `/sitemap.xml`, `/robots.txt`, article 301s, `/index.html` → `/`. **hreflang is not implemented** (EN and FA share URLs). See [SEO.md](SEO.md).

Status: IMPLEMENTED · LOCAL TESTED (PHPUnit). Production crawler behaviour: NOT TESTED.

## PWA

`public/manifest.json`, `public/sw.js` (`CACHE_NAME` = `meet-aj-v2.0.0-cms-3`), `public/offline.html`. Private prefixes `/admin`, `/livewire`, `/forms`, `/storage/livewire-tmp`. See [PWA.md](PWA.md).

| Item | Status |
|------|--------|
| Files + install/fetch logic | IMPLEMENTED |
| HTTP 200 for manifest/sw/offline (PHPUnit/local) | LOCAL TESTED (assets published) |
| Offline / installability / Lighthouse | NOT TESTED |
| Production PWA | NOT TESTED |

## Security

CSRF, honeypot, rate limit, FormRequest validation, hashed passwords (min 12), Filament auth, policies, `SecurityHeaders` (nosniff, referrer, SAMEORIGIN, HSTS on HTTPS), `APP_DEBUG` must be false in production. **No penetration test.** See [SECURITY.md](SECURITY.md).

Local debug ENABLED: expected for development; **must not ship**.

## Multilingual status

| Language | Role | Status |
|----------|------|--------|
| EN | Production public (default markup) | IMPLEMENTED · LOCAL TESTED |
| FA | Production public (client `data-fa` + RTL) | IMPLEMENTED · LOCAL TESTED |
| DE | CMS language enum / draft-only | IMPLEMENTED restriction · `/de` 404 LOCAL TESTED · **no German content** |

See [MULTILINGUAL.md](MULTILINGUAL.md).

## Testing

| Command | Latest result (this workstation, 2026-09-16) |
|---------|-----------------------------------------------|
| `php artisan test` | 30 tests, 584 assertions, 1 skipped (`MysqlSchemaTest` in default sqlite suite), 0 failures |
| `vendor/bin/phpunit -c phpunit.mysql.xml --filter MysqlSchemaTest` | 1 test, 7 assertions, OK |
| `php artisan site:compare-content` | Failures: 0 |

Lighthouse: NOT TESTED. DirectAdmin smoke: NOT TESTED.

Authoritative matrix: [QA-MATRIX.md](QA-MATRIX.md).

## DirectAdmin status

Procedure: [DEPLOYMENT.md](../DEPLOYMENT.md).  
Cutover, PHP 8.4 selector confirmation, production MySQL, SMTP, SSL, document-root switch: **BLOCKED / NOT TESTED**.

## Known problems

1. **Users Filament resource** is hidden from navigation. Routes exist at `/admin/users` and `/admin/cms-users` (same route name). CLI `cms:create-user` remains the documented provisioning path. Browser Users CRUD is **NOT TESTED**.
2. **Local `.env`** may show timezone UTC and debug ON; `.env.example` documents Tehran / debug false.
3. **Production** stack, SMTP, PWA offline, and Lighthouse are unproven.
4. Source leftovers (not CMS bugs): generic overlay category «مقاله» / “Article” on some cards; service “Back to Services” stays English in FA.

## Production readiness

**NOT READY / BLOCKED** for meetaj.ir until DirectAdmin PHP 8.4, `public/` document root, MySQL migrations, `APP_DEBUG=false`, HTTPS, and post-deploy checks in DEPLOYMENT.md are executed and verified.

Local CMS: **READY for further local work** (tests green).

## Git status

A `.git` directory is present. HEAD is **`feature/laravel-migration`**. Remote **`AJ`** points at `https://github.com/jalalianamirhossein-ui/MEET-AJ-PORTFOLIO.git`. `git.exe` is not on PATH on this workstation, so the requested `laravel` branch, commit, and push were **not** executed. See [SERVICE-CMS-IMPLEMENTATION.md](SERVICE-CMS-IMPLEMENTATION.md).
