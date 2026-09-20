# Deployment — Meet AJ

**Authority:** AUTHORITATIVE deployment procedure.
**Verified:** 2026-09-17 against `.env.example`, `.env.production.example`, `composer.json`, `config/`, and the console commands that exist.
**Cutover status:** documented, **NOT executed** from this environment. Every production claim below is BLOCKED / NOT TESTED.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md)

Stack: **Laravel 13.31.0**, **PHP 8.4**, **Filament 5.8.2**, **Blade**, **MySQL/MariaDB in production**.
Do not select PHP 8.2. Do not install Redis, Supervisor, Node, or a queue worker: this application uses file cache, file sessions and `QUEUE_CONNECTION=sync`.

## Environments

| | LOCAL | TEST | PRODUCTION |
|---|-------|------|------------|
| Where | This workstation | PHPUnit suites | DirectAdmin on meetaj.ir |
| PHP | `.runtime/php84/php.exe` 8.4.25 | same runtime | PHP 8.4 selector (**unverified**) |
| Database | SQLite `.runtime/cms.sqlite` (local `DB_DATABASE`) | SQLite `:memory:` (default) or MariaDB `127.0.0.1:3307` via `phpunit.mysql.xml` | MySQL / MariaDB (**not created**) |
| `APP_ENV` / `APP_DEBUG` | `local` / **false** | `testing` | `production` / **false** |
| Mail | `log` | none | SMTP (**not configured**) |
| Document root | `php artisan serve` on `public/` | n/a | must be `.../laravel/public` |
| Status | PASS | PASS (67 tests / 1127 assertions, 1 skipped) | BLOCKED · NOT TESTED |

The sections below describe the PRODUCTION procedure only. Nothing in them has been executed.

This file does not assume DirectAdmin features that a given shared plan may lack (SSH, Composer CLI, `cron`, `nodejs`). Use the path that the account actually provides.

## 1. PHP 8.4

In DirectAdmin → Domain → PHP version: select **8.4**.

Confirm over SSH if available:

```bash
php -v
# PHP 8.4.x
```

Required extensions (enable in PHP settings / Select PHP Version → Extensions):

`bcmath`, `ctype`, `curl`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `zip`

`sodium` is recommended when the host provides it (Laravel encryption).

## 2. Composer

If SSH + Composer exist:

```bash
cd /home/ACCOUNT/domains/meetaj.ir/laravel   # adjust to the real path
php -d memory_limit=512M composer install --no-dev --optimize-autoloader
```

If Composer is not installed, upload the already-built `vendor/` from a trusted build machine that used PHP 8.4 and the project `composer.lock`. Do not copy `.env` from a workstation.

## 3. MySQL / MariaDB

In DirectAdmin → MySQL Management:

1. Create a database (example name `ACCOUNT_meetaj`).
2. Create a user and grant all privileges on that database.
3. Record host (`localhost` on most DirectAdmin stacks), name, user, password.

Do not run migrations against an existing production database without a backup. Use a new database for the first CMS cutover.

## 4. Files on disk

Upload the Laravel project **above** the web root, for example:

```text
/home/ACCOUNT/domains/meetaj.ir/laravel/     ← application (artisan, app, vendor)
/home/ACCOUNT/domains/meetaj.ir/laravel/public/  ← document root
```

Include `resources/` in the release: `resources/legacy/` supplies imports and generated views, `resources/assets/` and `resources/static/` supply public assets, and `resources/downloads/` supplies visitor downloads. Keep these sources outside `public/`. Do not copy original article, service, or form files into the web root.

Do not upload `.env` from git. Do not upload `.runtime/`.

## 5. Document root

DirectAdmin → Domain Setup → document root:

**`/home/ACCOUNT/domains/meetaj.ir/laravel/public`**

(or the equivalent subdomain path). The web root must be Laravel `public/`, not the repository root.

`public/.htaccess` must remain in place (Laravel front controller).

## 6. Environment

```bash
cp .env.production.example .env
php artisan key:generate
```

Edit `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://meetaj.ir
APP_TIMEZONE=Asia/Tehran
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ACCOUNT_meetaj
DB_USERNAME=ACCOUNT_meetaj
DB_PASSWORD=...
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
QUEUE_CONNECTION=sync
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@meetaj.ir
MAIL_FROM_NAME="Meet AJ"
CONTACT_NOTIFICATION_EMAIL=
```

DirectAdmin typically exposes local SMTP as `localhost` on port 25 or 587, or a remote host such as `mail.meetaj.ir`. Use the values from the DirectAdmin email account. Leave `CONTACT_NOTIFICATION_EMAIL` empty to skip notification mail; contact rows are still stored. If SMTP is misconfigured, submissions still return `OK` and remain in `requests`.

Never commit `.env`.

## 7. Permissions

```bash
chmod -R ug+rwx storage bootstrap/cache
```

The PHP/Apache user must own or be able to write `storage/` and `bootstrap/cache`.

```bash
php artisan storage:link
```

If `storage:link` is blocked on shared hosting, create the `public/storage` symlink in DirectAdmin File Manager equivalent, pointing at `storage/app/public`.

## 8. Publish public copies and migrate

From the application root:

```bash
php artisan site:publish-assets --views
php artisan filament:assets
php artisan migrate --force
php artisan articles:import-legacy
php artisan services:import-legacy
php artisan cms:create-user
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Do not run `articles:import-legacy --refresh` or `services:import-legacy --refresh` on a database that already has editorial changes.

Confirm `php artisan about` shows production, debug OFF, mysql, Laravel 13, PHP 8.4, the configured timezone, and linked public storage. Publish both site and Filament assets on every fresh deployment; generated public assets are excluded from Git.

## 9. Cron

Not required for contact storage, articles, or Filament. Laravel’s scheduler is unused.

If the host later needs `schedule:run`, add a DirectAdmin cron only then:

```text
* * * * * php /home/ACCOUNT/domains/meetaj.ir/laravel/artisan schedule:run >> /dev/null 2>&1
```

## 10. SSL

Use DirectAdmin Let’s Encrypt / SSL for `meetaj.ir` (and `www` if used). Set `APP_URL` to the canonical `https://` origin. `SESSION_SECURE_COOKIE=true` requires HTTPS.

## 11. Post-deploy checks

- `https://meetaj.ir/` → 200
- `https://meetaj.ir/index.html` → 301 `/`
- One removed service URL → 404 (`/services/{slug}` and `.html`)
- One article `.html` URL → 301 to the clean URL
- `/sitemap.xml`, `/robots.txt`, `/manifest.json`, `/sw.js`
- `/admin/login` → 200; `/admin` guest → redirect
- POST `/forms/contact.php` still returns `OK` for a valid form

## 12. Rollback

1. Point the DirectAdmin document root back to a separately preserved, complete previous static release. The reorganized `resources/legacy/` directory is an import source, not a standalone web root; assets now live separately under `resources/assets/`.
2. Or restore a filesystem + MySQL backup taken before cutover.
3. Leave the Laravel tree in place until the static site is confirmed.

Original article HTML is never deleted by the CMS importer.

## 13. Secrets

Do not place database passwords, `APP_KEY`, or `.env` in git, tickets, or world-readable directories.
