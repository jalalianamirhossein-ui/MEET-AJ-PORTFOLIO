# Project reorganization — 2026-09-19

The repository now follows the existing Laravel layout, with frontend sources under `resources/`, design documentation under `docs/`, diagnostics under `scripts/`, and `public/` as the web document root. See [the directory map](../current/PROJECT-STRUCTURE.md).

## Changes

- Moved 205 files across 23 source paths. SHA-256 hashes were captured before moving and matched immediately afterward.
- Moved assets to `resources/assets/`, original site content to `resources/legacy/`, manifest/preloaders/static partials to `resources/static/`, and the NetBox download to `resources/downloads/`.
- Moved six retired service Blade templates outside the active view directory. The dynamic `services/show.blade.php` remains active.
- Moved design notes to `docs/design-system/`, the historical baseline to `docs/qa/`, session diagnostics to `scripts/`, and the old temporary 404 response to a dated documentation archive.
- Updated publishers, importers, content comparison, sitemap source timestamps, asset-reading tests, and operating documentation for the new paths. Public URLs and stored article source identifiers are preserved.
- Fixed a pre-existing extra opening brace that made the historical baseline invalid JSON. The verifier now maps historical paths to current locations without replacing original hashes.

Two moved files intentionally changed after the byte-preserving move: the baseline JSON syntax repair and the session diagnostic's bootstrap paths. The other 203 moved files retain their original bytes.

## Verification

| Check | Result |
|-------|--------|
| PHP syntax checks on all eight changed PHP files | PASS |
| `php scripts/validate-environment.php` | PASS, all environment and writable-directory checks |
| `php artisan site:publish-assets --views` | PASS, 156 public files published and two views rebuilt |
| `php artisan filament:assets` | PASS |
| `php artisan site:compare-content` | PASS, `Failures: 0` |
| `php artisan route:list --except-vendor` | PASS, 29 routes listed |
| Full PHPUnit suite before changes | 58 tests, 1088 assertions, 3 failures, 1 skipped |
| Full PHPUnit suite after publishing from the new paths | 58 tests, 1123 assertions, 1 failure, 1 skipped; 56 passed |
| Historical originals verifier | Runs successfully as a verifier; exits 1 because 44 files differ from its historical baseline |

The remaining test failure existed before this change: `FormCsrfAndAdminRequestsTest::test_missing_csrf_token_returns_plain_419` expects HTTP 419 but receives 200. No application authentication, CSRF, or request-handling code was changed. The initial two homepage audit failures no longer reproduced after rebuilding and publishing. `MysqlSchemaTest` remains skipped without a MySQL test connection. No new test failures were observed.

Local test details are saved in ignored `storage/logs/reorganization-tests.log` and `storage/logs/reorganization-tests.xml`. The move manifest is in ignored `storage/app/reorganization-hashes.json`. These are local QA artifacts, not application inputs or deployment requirements.
