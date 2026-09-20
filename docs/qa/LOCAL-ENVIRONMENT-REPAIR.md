# Local environment repair — 2026-09-20

Scope: this Windows checkout. No production deployment or SMTP delivery was performed.

The checkout had changed since the earlier review. Filament 5.8.2 and Livewire 4.4.5 were already installed, public site assets/manifest/downloads were present, all fifteen migrations had run, and the local SQLite database was populated. Reinstalling dependencies or reimporting content was unnecessary.

## Repairs

- `config/app.php` now reads `APP_TIMEZONE`, retaining UTC as its default. Previously the hardcoded UTC value ignored the documented environment setting. The local value now resolves to `Asia/Tehran`.
- Ran `php artisan storage:link`. The Windows junction `public/storage` now points to `storage/app/public`; `artisan about` reports LINKED. The upload directory is currently empty.
- Fixed test environment isolation in `tests/TestCase.php`. PHPUnit's forced XML environment values do not replace inherited `$_SERVER` values. An Artisan-launched test run inherited `APP_ENV=local`, causing two Filament creation tests to reject filled fields. Synchronizing test values before application boot fixes this and prevents an inherited local database setting from overriding the test configuration.
- Corrected current documentation for database location, counts, debug mode, timezone and missing local admin accounts. Added the missing `filament:assets` step to the fresh-deployment procedure.

## Verification

- PHPUnit: **67 tests, 1102 assertions, zero failures, one skipped** (`MysqlSchemaTest`; no MySQL connection selected).
- `php artisan test`: the same **67 tests / 1102 assertions / one skipped / zero failures** after the isolation repair. Both failing creation tests also pass with deliberately inherited local/MySQL/file-cache/SMTP settings (2 tests, 16 assertions), without connecting to that database or mail transport.
- Composer validation and installed-package platform requirements pass. Composer's optional Git version discovery reported sandbox ownership warnings; no Git trust settings were changed.
- `site:compare-content`: **Failures: 0**, covering the homepage, article listing and all 24 articles.
- `migrate:status`: all fifteen migrations ran; no database migration or content import was needed in this repair.
- Browser: homepage renders in Persian/RTL and switches to English/LTR; no broken loaded images or warning/error console entries observed. A migrated DFS article renders correctly, and `/admin/login` renders the sign-in form without console errors.
- Initial local database snapshot: 24 articles, 24 redirects, 10 categories, 8 tags, 39 article/tag links, 13 published services (12 visible), 5 testimonials, 7 homepage sections, zero users and zero requests. At the final recheck an additional published article had been added concurrently (25 total, still 24 legacy redirects); this repair did not import or edit article records. The newly appearing `resources/assets/img/portfolio/oxidized-banner.png` was left untouched.

The PHPUnit suite uses SQLite `:memory:`. Existing editorial records were preserved. The local admin login requires a personal account created with `php artisan cms:create-user`; no credentials were generated as part of this repair. Authenticated browser CRUD, production MySQL/SMTP, PWA installation/offline behavior and Lighthouse remain outside this verification.
