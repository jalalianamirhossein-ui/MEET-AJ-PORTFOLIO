# Testing — Meet AJ

**Authority:** AUTHORITATIVE testing document.
**Verified:** 2026-09-18 by running the suite and reading `phpunit.xml`, `phpunit.mysql.xml`, `tests/TestCase.php` and every file in `tests/Feature/`.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md). Per-URL evidence: [../qa/QA-MATRIX.md](../qa/QA-MATRIX.md).

## Latest run

```
PHPUnit 11.5.56 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.4.25
Configuration: phpunit.xml

........................S.........................                50 / 50 (100%)

OK, but some tests were skipped!
Tests: 50, Assertions: 1008, Skipped: 1.
```

**Status: PASS.** The single skip is `MysqlSchemaTest`, which only runs when a MySQL/MariaDB connection is bound.

Also run on 2026-09-18: `php artisan optimize:clear`, `php artisan route:list` (42 routes), `php artisan site:compare-content` (**Failures: 0**).

## Suites and configuration

| Configuration | Database | Purpose |
|---------------|----------|---------|
| `phpunit.xml` (default) | SQLite `:memory:` | Full feature suite |
| `phpunit.mysql.xml` | MySQL / MariaDB `127.0.0.1:3307`, database `meetaj_test` | Schema compatibility only |

`tests/TestCase.php` together with `.env.testing` keeps the default suite away from the live `database/database.sqlite` file, because PHPUnit 11 does not always honour forced environment variables from XML.

## Test files

| File | Covers |
|------|--------|
| `PublicSiteTest.php` | Homepage, `/index.html` 301, all 23 articles and their legacy redirects, 404 on unknown slugs, contact endpoints, honeypot, validation, rate limit, sitemap, robots, German routes returning 404 |
| `CmsOperationsTest.php` | Filament access control, article create/update, slug-change redirects, importer behaviour |
| `ServiceCatalogTest.php` | Service catalog rendering, detail pages, `.html` 301, pricing output, editor authorization failure |
| `ArticleLibraryTest.php` | Search, tag filter, pagination, related articles, share links |
| `ContentRulesTest.php` | Language rules, German publishing rejection, publication gates |
| `RequestWorkflowTest.php` | Request statuses, admin-only access, hidden internal notes |
| `FormCsrfAndAdminRequestsTest.php` | CSRF contracts, homepage + service quote persistence, `/admin/requests` inbox |
| `ProductionAuditTest.php` | Contact → Request → admin inbox; editor 403; first-load Testimonials + Contact; FA encoding; English article titles + importer repair |
| `MysqlSchemaTest.php` | Schema creation on MySQL/MariaDB (skipped on SQLite) |

## Commands

```bash
php artisan test                                              # default SQLite suite
vendor/bin/phpunit -c phpunit.mysql.xml --filter MysqlSchemaTest   # MySQL schema check
php artisan site:compare-content                              # content integrity vs original HTML
php artisan migrate:status                                    # migration ledger
php artisan route:list                                        # route inventory
```

`php artisan test` is a project command (`App\Console\Commands\RunTests`) that forwards arguments to PHPUnit. On this workstation every command runs through `.runtime/php84/php.exe` because `php` is not on PATH. That wrapper does **not** accept `--filter`; use `php vendor/phpunit/phpunit/phpunit --filter …` for a subset.

## Content integrity

`php artisan site:compare-content` on 2026-09-18: **Failures: 0** across home, 6 services, articles index, and 23 articles. Evidence: [../qa/CONTENT-INTEGRITY.md](../qa/CONTENT-INTEGRITY.md).

## Live HTTP (not PHPUnit)

| Check | Result |
|-------|--------|
| `GET /forms/get-csrf-token.php` then `POST /forms/contact.php` | 200 `OK`; SQLite `requests.id = 5` |
| Direct Homepage first load | `#testimonials` and `#contact` opacity 1, `aos-animate`, non-zero height |
| FA switch | `dir=rtl`, Persian nav, typed roles in Arabic script, 0 visible `????` nodes |
| Contact from Homepage and from `/articles` | `#contact` in viewport |
| Viewports 1920 / 1440 / 1024 / 768 / 390 | no horizontal overflow |

## MySQL integration

`MysqlSchemaTest` last ran green on 2026-09-16 against MariaDB on `127.0.0.1:3307`. It was **not** re-run on 2026-09-18.

## What is not tested

| Area | Status | Reason |
|------|--------|--------|
| Interactive Filament CRUD in a browser | BLOCKED | `users` table is empty; no account to log in with |
| Admin responsive layout (authenticated) | NOT TESTED | depends on an authenticated session |
| PWA install, offline browsing | NOT TESTED | never exercised as an install |
| Lighthouse or any performance budget | NOT TESTED | no run exists |
| Production smoke tests on meetaj.ir | BLOCKED | no deployment |
| SMTP delivery | BLOCKED | no production mail account |
| Penetration testing, dependency CVE gate | NOT TESTED | out of scope so far |
| Unit tests (`tests/Unit`) | none exist | the suite is feature-level only |

Do not upgrade any of these to PASS without new evidence.
