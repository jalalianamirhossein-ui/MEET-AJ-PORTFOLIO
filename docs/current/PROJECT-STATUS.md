# Project status — Meet AJ

**Current local verification: 2026-10-01.** This is the current status reference. Dated reports under `docs/qa/` and historical folders describe their own review dates. Evidence: [FULL-AUDIT-2026-10-01.md](../qa/FULL-AUDIT-2026-10-01.md).

## Application

Laravel 13.31.0, Filament 5.8.2, Livewire 4.4.5 and PHPUnit 11.5.56 are locked dependencies. Local verification uses PHP 8.4.25. Blade and maintained CSS/JavaScript serve the public portfolio; Filament serves `/admin`. No frontend build, Redis, worker or scheduler is required. Local database: SQLite; deployment documentation targets MySQL/MariaDB with PHP 8.4.

The owner reports a deployed server. This review did not connect to that server, alter its environment, or validate its database/SMTP/TLS settings.

## Local data

| Item | Count |
|------|-------|
| Published articles | 25 |
| Articles with complete FA/EN localization | 25 |
| Legacy article redirects | 25 |
| Categories | 19 |
| Tags / article-tag links | 24 / 44 |
| Published services / visible catalog services | 13 / 12 |
| Testimonials | 9 |
| Homepage content sections | 7 |
| Users / contact requests | 0 / 0 |

All 18 migration files report Ran. The local migration ledger includes 19 historical records; the number of rows is not the number of migration files in the current checkout. No migration reset was used.

Before synchronizing articles, a consistent local SQLite backup was saved under ignored `storage/app/full-review/local-before-article-sync-20261001.sqlite`. `articles:import-legacy --update-existing` updated 25 rows, preserving IDs and translation keys. Normal import skips existing article bodies. `site:compare-content` now reports zero failures.

## Public behavior

- Homepage sections, service catalog, testimonials and article data are CMS-backed.
- The library supports search, tag filters, pagination and related articles. Cards keep English titles.
- English article content and SEO use the clean URL; Persian uses `?lang=fa`. Both are complete server responses with localized schemas, self-canonicals and reciprocal alternate links.
- The floating language toggle updates the current DOM/metadata and URL without reloading the document. Code is identical in both editions.
- The extra inline FA/EN row and historical-edition panels are removed. Exact original articles remain in the documentation archive.
- vSS/vDS and the other comparison topics retain their comparison tables and selection criteria. DFS initial synchronization now gates activation of the second namespace target.
- The English sidebar name stays on one line. Glass CSS is `?v=11`; main JavaScript is `?v=1415`; article i18n is `?v=1406` (homepage/library `?v=1403`).
- Contact endpoints retain CSRF, honeypot, validation, throttling and request storage. Services appear in the homepage catalog; removed detail routes return 404. German content remains draft-only.

## Verification

Full isolated feature suite: **84 tests, 0 failures, 1 skipped** (MySQL-only schema test). Article structural validation: **25 files, zero errors**. Live local content comparison: **Failures: 0**. Browser and syntax-check details are recorded in the dated audit.

Deployment setup now refuses an existing `.env`, safely quotes new values and validates them with the installed dotenv parser. The local `.env` and existing `APP_KEY` were preserved.

## Boundaries

Remote production, SMTP delivery and real MySQL were not tested here. No operational infrastructure examples were executed on RouterOS, ESXi, SQL Server or Windows Server. No Lighthouse, load test or PWA install/offline test was run. There are no local CMS users, so admin browser login was not exercised; authorization/workflow tests use isolated fixtures.

## Documentation

Start with the [root README](../../README.md), [architecture](ARCHITECTURE.md), [articles](ARTICLES.md), [language behavior](MULTILINGUAL.md), [SEO](SEO.md), [testing](TESTING.md) and [deployment/recovery](DEPLOYMENT.md). The [FA/EN article report](../enterprise-articles/bilingual-report.md) lists every filename, both titles, translation status, missing sections and SEO status.
