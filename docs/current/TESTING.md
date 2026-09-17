# Testing — Meet AJ

**Authority:** AUTHORITATIVE testing document.
**Verified:** 2026-09-17 by running the suite and reading `phpunit.xml`, `phpunit.mysql.xml`, `tests/TestCase.php` and every file in `tests/Feature/`.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md). Per-URL evidence: [../qa/QA-MATRIX.md](../qa/QA-MATRIX.md).

## Latest run

```
PHPUnit 11.5.56 by Sebastian Bergmann and contributors.
Runtime:       PHP 8.4.25
Configuration: phpunit.xml

..................S....................                           39 / 39 (100%)

OK, but some tests were skipped!
Tests: 39, Assertions: 647, Skipped: 1.
```

**Status: PASS.** The single skip is `MysqlSchemaTest`, which only runs when a MySQL/MariaDB connection is bound.

## Suites and configuration

| Configuration | Database | Purpose |
|---------------|----------|---------|
| `phpunit.xml` (default) | SQLite `:memory:` | Full feature suite |
| `phpunit.mysql.xml` | MySQL / MariaDB `127.0.0.1:3307`, database `meetaj_test` | Schema compatibility only |

`tests/TestCase.php` together with `.env.testing` keeps the default suite away from the live `database/database.sqlite` file, because PHPUnit 11 does not always honour forced environment variables from XML.

## Test files

| File | Tests | Covers |
|------|-------|--------|
| `PublicSiteTest.php` | 9 | Homepage, `/index.html` 301, all 23 articles and their legacy redirects, 404 on unknown slugs, contact endpoints, honeypot, validation, rate limit, sitemap, robots, German routes returning 404 |
| `CmsOperationsTest.php` | 8 | Filament access control, article create/update, slug-change redirects, importer behaviour |
| `ServiceCatalogTest.php` | 8 | Service catalog rendering, detail pages, `.html` 301, pricing output, editor authorization failure |
| `ArticleLibraryTest.php` | 5 | Search, tag filter, pagination, related articles, share links |
| `ContentRulesTest.php` | 5 | Language rules, German publishing rejection, publication gates |
| `RequestWorkflowTest.php` | 3 | Request statuses, admin-only access, hidden internal notes |
| `MysqlSchemaTest.php` | 1 | Schema creation on MySQL/MariaDB (7 assertions; skipped on SQLite) |

Total: **39 tests, 647 assertions**.

## Commands

```bash
php artisan test                                              # default SQLite suite
vendor/bin/phpunit -c phpunit.mysql.xml --filter MysqlSchemaTest   # MySQL schema check
php artisan site:compare-content                              # content integrity vs original HTML
php artisan migrate:status                                    # migration ledger
php artisan route:list                                        # route inventory
```

`php artisan test` is a project command (`App\Console\Commands\RunTests`) that forwards arguments to PHPUnit. On this workstation every command runs through `.runtime/php84/php.exe` because `php` is not on PATH.

## Content integrity

`php artisan site:compare-content` renders each article through Laravel and compares it against the original HTML file for complete body, bilingual attributes, headings and SEO tokens.

Result on 2026-09-17: **Failures: 0** across 23 articles. Evidence: [../qa/CONTENT-INTEGRITY.md](../qa/CONTENT-INTEGRITY.md).

## MySQL integration

`MysqlSchemaTest` last ran green on 2026-09-16 against MariaDB on `127.0.0.1:3307` (1 test, 7 assertions). It was **not** re-run on 2026-09-17, so its status is carried forward from that date rather than re-verified today.

## What is not tested

| Area | Status | Reason |
|------|--------|--------|
| Interactive Filament CRUD in a browser | BLOCKED | `users` table is empty; no account to log in with |
| Admin responsive layout | NOT TESTED | depends on an authenticated session |
| PWA install, offline browsing | NOT TESTED | never exercised in a browser |
| Lighthouse or any performance budget | NOT TESTED | no run exists |
| Production smoke tests on meetaj.ir | BLOCKED | no deployment |
| SMTP delivery | BLOCKED | no production mail account |
| Penetration testing, dependency CVE gate | NOT TESTED | out of scope so far |
| Unit tests (`tests/Unit`) | none exist | the suite is feature-level only |

Do not upgrade any of these to PASS without new evidence.
