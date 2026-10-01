# Testing — Meet AJ

Verified **2026-10-01**. Latest full suite: **85 tests, 5,453 assertions, 0 failures, 1 skipped**. Browser findings are in [FULL-AUDIT-2026-10-01.md](../qa/FULL-AUDIT-2026-10-01.md).

## Commands

```bash
php vendor/phpunit/phpunit/phpunit
php vendor/phpunit/phpunit/phpunit --filter BilingualEnterpriseArticleTest
php vendor/phpunit/phpunit/phpunit -c phpunit.mysql.xml --filter MysqlSchemaTest
php artisan site:compare-content
php artisan migrate:status
php artisan route:list
python scripts/verify-enterprise-articles.py
```

On this Windows workstation PHP is `.runtime/php84/php.exe`. `composer test` also invokes PHPUnit. The custom `php artisan test` wrapper exists, but does not accept PHPUnit options such as `--filter`; invoke PHPUnit directly for filtering or reports.

## Isolation and coverage

`phpunit.xml` uses SQLite `:memory:`. `tests/TestCase.php` synchronizes configured environment values before Laravel bootstraps, preventing inherited local database/mail settings from leaking into tests. MySQL is opt-in through `phpunit.mysql.xml`; its schema test is the one skip in the default suite. Do not run destructive schema tests against an operational database.

The feature suite covers public pages and redirects; CMS roles/policies; contact validation, CSRF, honeypot and throttling; request workflow; homepage content; service catalog; article search/tags/related content; publication rules; image fallbacks; SQL backup package; FA/EN content and SEO; admin layout; and production-template handling.

New regression coverage checks that content comparison detects stale source hashes/missing localization even when markup counts match, skips custom CMS articles without crashing, and excludes drafts. Environment setup round-trips passwords with spaces, quotes, backslashes, dollar signs and comment characters, and refuses to overwrite an existing `.env`. Language checks use the plain preference cookie, verify English fallback and private caching, and require clean related/canonical URLs and one sitemap entry per article with its update date. Old language-query redirects preserve unrelated parameters.

Source-update regression coverage also verifies that importing changed HTML preserves existing draft/scheduled publication status, dates and translation keys.

## Local operational checks

All 25 local article rows were synchronized after a consistent SQLite backup. `site:compare-content` reports **Failures: 0**. The command checks maintained source hashes, stored localization, body markers and rendered SEO. It skips public CMS articles with no corresponding legacy source. It is not a substitute for editorial review or browser testing.

The article validator checks 25 maintained HTML files, section IDs, FAQ/schema consistency, archive hashes and embedded Python syntax. Bash and PowerShell examples receive syntax-only checks; infrastructure commands are not executed.

Live browser checks cover the English menu name, local homepage/library navigation, English defaults, Persian article rendering, shared language preference, refresh and instant toggling at a clean URL. Evidence is in the dated audit. No real contact messages are sent by the browser checks.

## Limits

This pass does not validate remote deployment, SMTP delivery, real MySQL/MariaDB, authenticated admin interactions in a browser, PWA installation/offline behavior, Core Web Vitals, load capacity or infrastructure runbooks on their target equipment. Admin authorization and workflows are covered by isolated feature tests. Earlier dated QA files remain historical evidence.
