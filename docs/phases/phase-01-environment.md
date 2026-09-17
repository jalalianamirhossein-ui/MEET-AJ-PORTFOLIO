> **HISTORICAL / SUPERSEDED phase log.** Current status: [PROJECT-STATUS.md](../current/PROJECT-STATUS.md). Environment requirements: [README.md](../../README.md).

# Phase 1 — environment

Status: **local environment boots**. Production DirectAdmin PHP 8.4 was specified by the user; this workstation used a verified local PHP 8.4.25 runtime because `php` / `composer` were not on PATH.

## Implemented work

- Laravel 13 application tree with PHP `^8.4` and Composer platform `8.4.0`.
- Private ignored `.runtime/` containing PHP 8.4.25 NTS (`43a8f67ed2e5223fafb21293c85976361808855405278cef2cf3037c3ae2529c`) and Composer 2.10.3.
- `vendor/` installed from `composer.lock`.
- Laravel boots; `php artisan about` reports Laravel 13.31.0 / PHP 8.4.25.

## Files changed

- `composer.json`, `composer.lock`
- `bootstrap/app.php`, `bootstrap/providers.php`
- `config/*`, `.env.example`, `.env.production.example`
- `.gitignore` (`.env`, `.runtime/`, `vendor/`, published public copies)
- `scripts/validate-environment.php`, `scripts/verify-originals.php` (retained)

## Commands executed (this machine)

```text
.php runtime: PHP 8.4.25 (NTS Visual C++ 2022 x64)
Composer version 2.10.3
composer install  (completed earlier in this migration)
php artisan about
```

`php artisan about` (2026-09-16):

- Application Name: Meet AJ
- Laravel Version: 13.31.0
- PHP Version: 8.4.25
- Environment: local
- Debug Mode: ENABLED (local `.env` only; production example is `APP_DEBUG=false`)
- Database driver (local): sqlite
- Cache/session: file
- Queue: sync

Required extensions present locally: `bcmath`, `ctype`, `curl`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `PDO`, `pdo_mysql`, `pdo_sqlite`, `session`, `tokenizer`, `xml`, `zip`.

## Tests executed

Environment validation is by executing the binaries above. PHPUnit is recorded in later phases.

## Real results

- Laravel boots.
- `vendor/` exists.
- Local `.env` is gitignored and uses SQLite. It is not a production configuration.

## Blockers

- No MySQL/MariaDB server on this workstation. `pdo_mysql` is loaded; a live engine was not migrated against.
- DirectAdmin PHP selector / document-root change was not performed on the live host.
- Local `APP_DEBUG=true` is expected for development and must not be copied to production.
