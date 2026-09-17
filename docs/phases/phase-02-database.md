> **HISTORICAL / SUPERSEDED phase log.** Authoritative schema: [DATABASE.md](../DATABASE.md). Current status: [PROJECT-STATUS.md](../PROJECT-STATUS.md). DirectAdmin production DB remains NOT TESTED.

# Phase 2 — database

Status: **MariaDB 11.4.13 integration validated locally** on `127.0.0.1:3307`, database `meetaj_test`, charset `utf8mb4` / `utf8mb4_unicode_ci`. DirectAdmin production MySQL was **not** connected.

## Implemented work

Tables (InnoDB):

1. `users` + `password_reset_tokens`
2. `categories` — unique `(language, slug)` and `(translation_key, language)`
3. `articles` — unique `(language, slug)` and `(translation_key, language)`; FK `category_id`; indexes `(language, status, published_at)` and `(status, published_at)`
4. `article_redirects` — unique `old_path`; FK `article_id` ON DELETE CASCADE
5. `requests`
6. `sessions`

No `contact_requests`. No `pages`.

`php artisan migrate:fresh --seed` on MariaDB imported 23 articles and 23 redirects. Persian category names and a Persian `requests` row stored in utf8mb4 (Windows console may print `?`; the database charset is utf8mb4).

## Files changed

- `database/migrations/2026_09_15_00000{1-6}_*.php`
- `database/seeders/DatabaseSeeder.php` — rebuilds views and runs `articles:import-legacy`
- `phpunit.mysql.xml` — dedicated MySQL/MariaDB PHPUnit config (port 3307, database `meetaj_test`)
- `app/Models/*`, `app/Policies/*`

## Commands executed

```text
mysql_install_db / mysqld --port=3307   (portable MariaDB 11.4.13 in ignored .runtime/)
CREATE DATABASE meetaj_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
php artisan config:clear
php artisan migrate:fresh --force --seed
php artisan db:show     → MariaDB 11.4.13, meetaj_test, 8 tables
vendor/phpunit/phpunit/phpunit -c phpunit.mysql.xml
  OK (23 tests, 517 assertions)
```

Local `.env` remains SQLite for the artisan serve process (not rewritten to MySQL).

## Tests executed

- Default `php artisan test` (SQLite in-memory via `phpunit.xml`): **OK, 23 tests, 510 assertions, 1 skipped** (`MysqlSchemaTest` skips unless the connection is mysql).
- `phpunit.mysql.xml`: **OK (23 tests, 517 assertions)** including utf8mb4, unique slug, FK presence.

## Real results

| Check | Result |
| --- | --- |
| Connection | PASS (127.0.0.1:3307, user `meetaj`) |
| utf8mb4 | PASS (`DEFAULT_CHARACTER_SET_NAME=utf8mb4`) |
| migrate:fresh --seed | PASS (23 articles, 23 redirects) |
| Foreign keys | PASS (`article_redirects_article_id_foreign` CASCADE) |
| Unique `(language, slug)` | PASS (index present; duplicate insert raises QueryException) |
| Publication indexes | PASS |
| Sessions table | PASS |
| DirectAdmin production DB | **BLOCKED** — no hosting credentials in this environment |

## Blockers

Production DirectAdmin MySQL/MariaDB remains **BLOCKED**. Repeat `migrate` (not `migrate:fresh`) there after backup. Do not point this workstation `.env` at production.
