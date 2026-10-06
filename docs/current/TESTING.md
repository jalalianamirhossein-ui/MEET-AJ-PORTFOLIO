# Testing — Meet AJ

> Maintenance review: 2026-10-06. Current suite outcome: 96 tests, 1,369 assertions, 2 errors, 16 failures, 1 skipped using the sandbox bootstrap workaround. This is not a passing release gate. Four frontend tests pass. See [current status](PROJECT-STATUS.md) and [the dated audit](../qa/STRUCTURE-DOCUMENTATION-AUDIT-2026-10-06.md).

Documentation reviewed **2026-10-06**. The 2026-10-01 result (85 tests, 5,453 assertions, 1 skipped) is historical. Current evidence: [structure/documentation audit](../qa/STRUCTURE-DOCUMENTATION-AUDIT-2026-10-06.md).

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

All 25 local article rows were synchronized after a consistent SQLite backup. That dated run reported **Failures: 0**. The 2026-10-06 comparison reports **27 failures**, so source/database parity is currently unresolved. The command checks maintained source hashes, stored localization, body markers and rendered SEO. It skips public CMS articles with no corresponding legacy source. It is not a substitute for editorial review or browser testing.

The historical enterprise validator covers its 25-file inventory; the checkout now has 28 maintained HTML files, section IDs, FAQ/schema consistency, archive hashes and embedded Python syntax. Bash and PowerShell examples receive syntax-only checks; infrastructure commands are not executed.

Live browser checks cover the English menu name, local homepage/library navigation, English defaults, Persian article rendering, shared language preference, refresh and instant toggling at a clean URL. Evidence is in the dated audit. No real contact messages are sent by the browser checks.

## Limits

This pass does not validate remote deployment, SMTP delivery, real MySQL/MariaDB, authenticated admin interactions in a browser, PWA installation/offline behavior, Core Web Vitals, load capacity or infrastructure runbooks on their target equipment. Admin authorization and workflows are covered by isolated feature tests. Earlier dated QA files remain historical evidence.

## Optional frontend and documentation checks

```bash
node --test tests/Frontend/scroll-reveal.test.cjs
node scripts/check-documentation.cjs
node scripts/check-documentation.cjs --write-index
```

Node is needed only for these checks, not to build or serve the site. The documentation check validates local Markdown file links and regenerates the complete inventory when requested. On this Windows sandbox, PHPUnit sees `vendor/autoload.php` as unreadable even though PHP can require it; see the audit for the temporary wrapper used to run the suite without changing tracked PHPUnit configuration.
