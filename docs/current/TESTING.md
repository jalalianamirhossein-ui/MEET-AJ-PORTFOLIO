# Testing — Meet AJ

> Enterprise verification: 2026-10-08. Full SQLite suite: 158 tests, 21,919 assertions, zero failures/errors, one MySQL skip. Six Node tests pass; 225 PHP files pass syntax checks. Composer audit, asset publication, routes and view/route caches pass. See [current status](PROJECT-STATUS.md) and [the execution ledger](../SECURITY-AUDIT-REPORT.md).

Documentation reviewed **2026-10-08**. Older results (including the 2026-10-06 failing suite) remain historical in their dated reports. Current evidence: [enterprise security audit](../SECURITY-AUDIT-REPORT.md).

## Commands

```bash
php vendor/phpunit/phpunit/phpunit
php vendor/phpunit/phpunit/phpunit --filter BilingualEnterpriseArticleTest
php vendor/phpunit/phpunit/phpunit --filter SecurityRegressionTest
php vendor/phpunit/phpunit/phpunit --filter TagLocalizationTest
php vendor/phpunit/phpunit/phpunit -c phpunit.mysql.xml --filter MysqlSchemaTest
php artisan site:compare-content
php artisan migrate:status
php artisan route:list
python scripts/verify-enterprise-articles.py
```

On this Windows workstation PHP is `.runtime/php84/php.exe`. `composer test` also invokes PHPUnit. The custom `php artisan test` wrapper accepts PHPUnit options after `--`, for example `php artisan test -- --filter=EnterpriseHardeningTest`; direct PHPUnit invocation also supports filtering and reports.

## Isolation and coverage

`phpunit.xml` uses SQLite `:memory:`. `tests/TestCase.php` synchronizes configured environment values before Laravel bootstraps, preventing inherited local database/mail settings from leaking into tests. MySQL is opt-in through `phpunit.mysql.xml`; its schema test is the one skip in the default suite. Do not run destructive schema tests against an operational database.

The feature suite covers public pages and redirects; CMS roles/policies; contact validation, CSRF, honeypot and throttling; request workflow; homepage content; service catalog; article search/tags/related content; publication rules; image fallbacks; SQL backup package; FA/EN content and SEO; admin layout; and production-template handling.

New regression coverage checks that content comparison detects stale source hashes/missing localization even when markup counts match, skips custom CMS articles without crashing, and excludes drafts. Environment setup round-trips passwords with spaces, quotes, backslashes, dollar signs and comment characters, and refuses to overwrite an existing `.env`. Language checks use the plain preference cookie, verify English fallback and private caching, and require clean related/canonical URLs and one sitemap entry per article with its update date. Old language-query redirects preserve unrelated parameters.

Source-update regression coverage also verifies that importing changed HTML preserves existing draft/scheduled publication status, dates and translation keys.

## Local operational checks

All 25 local article rows were synchronized after a consistent SQLite backup. That dated run reported **Failures: 0**. The 2026-10-06 comparison reports **27 failures**, so source/database parity is currently unresolved. The command checks maintained source hashes, stored localization, body markers and rendered SEO. It skips public CMS articles with no corresponding legacy source. It is not a substitute for editorial review or browser testing.

The historical enterprise validator covers its 25-file inventory; the checkout now has 28 maintained HTML files, section IDs, FAQ/schema consistency, archive hashes and embedded Python syntax. Bash and PowerShell examples receive syntax-only checks; infrastructure commands are not executed.

Live browser checks cover the English menu name, local homepage/library navigation, English defaults, Persian article rendering, shared language preference, refresh and instant toggling at a clean URL. Evidence is in the dated audit. No real contact messages are sent by the browser checks.

## Limits

This pass does not validate remote deployment, SMTP delivery, real MySQL/MariaDB, authenticated admin interactions in a browser, PWA installation/offline behavior, Core Web Vitals, load capacity or infrastructure runbooks on their target equipment. Admin authorization and workflows are covered by isolated feature tests. Earlier dated QA files remain historical evidence.

## Optional frontend and documentation checks

```bash
node --test tests/Frontend/scroll-reveal.test.cjs tests/Frontend/service-worker-security.test.cjs tests/Frontend/swiper-security.test.cjs
node scripts/check-repository-security.cjs --history
node scripts/check-documentation.cjs
node scripts/check-documentation.cjs --write-index
```

Node is needed only for these checks, not to build or serve the site. The documentation check validates relative Markdown file links and root-relative website links under `public/`, and regenerates the complete inventory when requested. On this Windows sandbox, PHPUnit sees `vendor/autoload.php` as unreadable even though PHP can require it. The 2026-10-08 suite used the approved host runtime outside that filesystem restriction with unchanged tracked PHPUnit configuration and SQLite `:memory:`.

Image organization (2026-10-06): [banner, article-body and upload folder guide](IMAGES.md). Run `node scripts/check-images.cjs` after publishing images.
