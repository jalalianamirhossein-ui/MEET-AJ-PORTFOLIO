> **HISTORICAL phase log.** Figures below are from 2026-09-16 and were not rewritten. Authoritative schema: [../current/DATABASE.md](../current/DATABASE.md). Current status: [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md).

# Phase 02 — Database

**Phase:** 02
**Date:** 2026-09-15 to 2026-09-16
**Status:** **PASS** on local SQLite and local MariaDB · DirectAdmin production database **BLOCKED**

## Objective

Design and migrate the CMS schema, and prove it works on MySQL/MariaDB as well as SQLite.

## Changes

Six migrations creating: `users` + `password_reset_tokens`, `categories`, `articles`, `article_redirects`, `requests`, `sessions`, with InnoDB on MySQL.

- `categories`: unique `(language, slug)` and `(translation_key, language)`
- `articles`: the same two unique keys, FK `category_id`, indexes `(language, status, published_at)` and `(status, published_at)`
- `article_redirects`: unique `old_path`, FK `article_id` ON DELETE CASCADE

No `contact_requests` table. No `pages` table. (`services`, `tags` and `article_tag` arrive in phases 07 and 08.)

## Files changed

- `database/migrations/2026_09_15_000001` … `000006`
- `database/seeders/DatabaseSeeder.php` — rebuilds views, then imports articles
- `phpunit.mysql.xml` — MySQL/MariaDB PHPUnit configuration (port 3307, database `meetaj_test`)
- `app/Models/*`, `app/Policies/*`

## Commands executed

```text
mysql_install_db / mysqld --port=3307            (portable MariaDB 11.4.13 in ignored .runtime/)
CREATE DATABASE meetaj_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
php artisan config:clear
php artisan migrate:fresh --force --seed
php artisan db:show                              → MariaDB 11.4.13, meetaj_test, 8 tables
vendor/phpunit/phpunit/phpunit -c phpunit.mysql.xml
```

The local `.env` stayed on SQLite for `artisan serve`.

## Tests

- Default suite (SQLite in-memory): **OK, 23 tests, 510 assertions, 1 skipped** (`MysqlSchemaTest` skips unless the connection is MySQL).
- `phpunit.mysql.xml`: **OK, 23 tests, 517 assertions**, covering utf8mb4, unique slugs and foreign keys.

## Results

| Check | Result |
|-------|--------|
| Connection to MariaDB 11.4.13 | PASS |
| utf8mb4 default character set | PASS |
| `migrate:fresh --seed` | PASS (23 articles, 23 redirects) |
| `article_redirects_article_id_foreign` CASCADE | PASS |
| Unique `(language, slug)` rejects duplicates | PASS |
| Publication indexes present | PASS |
| `sessions` table created | PASS |
| DirectAdmin production database | **BLOCKED** |

Persian category names and a Persian request row stored correctly in utf8mb4; the Windows console renders them as `?`, which is a terminal limitation, not a data problem.

## Blockers

Production MySQL/MariaDB on DirectAdmin was never connected. There, use `migrate` (never `migrate:fresh`) after a backup, and never point this workstation's `.env` at production.
