# Deployment — Meet AJ

> Maintenance review: 2026-10-06. Include `resources/content/articles/` and the maintained script paths in releases. The Linux package path now matches its slug; selected downloads retain their public URLs. No remote release was inspected. See [current status](PROJECT-STATUS.md) and [the dated audit](../qa/STRUCTURE-DOCUMENTATION-AUDIT-2026-10-06.md).

**Authority:** AUTHORITATIVE deployment procedure.
**Verified:** 2026-10-01 against `.env.example`, `.env.production.example`, `composer.json`, `config/`, and the console commands that exist.
**Deployment status:** the owner reports a deployed server. This local audit did not inspect or modify that server; remote configuration remains unverified.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md)

Stack: **Laravel 13.31.0**, **PHP 8.4**, **Filament 5.8.3**, **Blade**, **MySQL/MariaDB in production**.
Do not select PHP 8.2. Do not install Redis, Supervisor, Node, or a queue worker: this application uses file cache, file sessions and `QUEUE_CONNECTION=sync`.

## Environments

| | LOCAL | TEST | PRODUCTION |
|---|-------|------|------------|
| Where | This workstation | PHPUnit suites | DirectAdmin on meetaj.ir |
| PHP | `.runtime/php84/php.exe` 8.4.25 | same runtime | PHP 8.4 selector (**unverified**) |
| Database | SQLite `.runtime/cms.sqlite` (local `DB_DATABASE`) | SQLite `:memory:` (default) or MariaDB `127.0.0.1:3307` via `phpunit.mysql.xml` | MySQL / MariaDB (**remote state unverified**) |
| `APP_ENV` / `APP_DEBUG` | `local` / **false** | `testing` | `production` / **false** |
| Mail | `log` | none | SMTP (**remote state unverified**) |
| Document root | `php artisan serve` on `public/` | n/a | must be `.../laravel/public` |
| Status | PASS | PASS (84 tests, 0 failures, 1 skipped) | NOT TESTED remotely |

The sections below describe the production procedure. They are not a record of actions performed on the remote server.

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
COMPOSER_MEMORY_LIMIT=512M composer install --no-dev --optimize-autoloader
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

Security update (2026-10-08): retain the current lockfile (CommonMark 2.10.2 / Filament 5.8.3), publish patched Swiper 12.1.2 and `public/sw.js` with cache version `cms-6`, and regenerate Filament assets. Set `FORCE_HTTPS=true` and an HTTPS `APP_URL`. If a reverse proxy terminates HTTPS, set `TRUSTED_PROXIES` to its actual comma-separated IPs/CIDRs before caching configuration; direct hosting keeps it empty. Forwarded host headers are not trusted. Incorrect proxy configuration can cause redirect loops, so verify staging first. Limited production HEAD checks found HTTP 200 without redirection and inconsistent admin security headers; no server/environment changes were made. See [findings](../SECURITY-AUDIT-REPORT.md) and [the `/var/www/meetaj` deployment checklist](../../DEPLOYMENT.md).

For a **new installation only**, copy the template if no environment exists and generate the initial key:

```bash
if [ ! -e .env ] && [ ! -L .env ]; then
    cp .env.production.example .env
    php artisan config:clear
    php artisan key:generate --force
fi
```

For an existing installation, preserve its `.env` and original `APP_KEY`. If only `.env.production.example` was deleted, restore the tracked template from the release; Laravel normally reads `.env`, not the example. If the real `.env` was lost, recover it from a protected backup or the hosting secret store before clearing cached configuration. A cached configuration may be the remaining source of the original key; do not print it into logs or chat. Generating a new key is not recovery for encrypted data.

If initial key generation fails, run from the directory containing `artisan`, verify PHP 8.4 and installed `vendor/`, confirm `.env` contains exactly one `APP_KEY=` entry and is writable by the deployment account, and inspect the actual error. Quote passwords containing spaces or comment characters. Never fix permissions with `chmod 777`.

The Ubuntu VPS `deploy/setup_env.sh` is a fresh-install helper, expects `www-data`, and refuses an existing `.env`. Its PHP writer quotes and validates values before exclusive creation. DirectAdmin users should follow the hosting account permissions below rather than assuming that service user exists.

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

### Updating an existing deployment with bilingual article sources

Pulling new HTML files does not replace article content already stored in the database. A plain `articles:import-legacy` imports missing rows and skips existing content. Both editions require the body translations and `presentation.localizations` together. Deploy the updated bootstrap cookie configuration, middleware, controllers, localization service, Blade views and assets as well as the HTML sources. The clean article URL now follows the homepage's saved language preference.

From the Laravel application root, preview the affected rows, then apply the reviewed source replacements and publish the language-switch asset:

```bash
php artisan articles:import-legacy --update-existing --dry-run
php artisan articles:import-legacy --update-existing
php artisan site:publish-assets --views
php artisan optimize:clear
php artisan optimize
php artisan site:compare-content
```

`--update-existing` replaces changed source articles, including CMS edits to those rows; back up the database before replacing them. It preserves existing IDs and translation keys. `--refresh` deletes article rows and is unnecessary for this update. If using `scripts/refresh-project.sh`, its ordinary seeding step also skips existing article bodies. `deploy/update_project.sh` also leaves editorial replacements explicit; run the reviewed article update above when deploying changed sources.

On the homepage, select Persian and open an article: it should stay Persian at `/articles/{slug}`, including after refresh. Switch to English and repeat. With no preference cookie, page source defaults to `data-article-language="en"`; with cookie `lang=fa`, it contains `data-article-language="fa"`. Both have the same clean canonical. Confirm all public pages load `i18n.js?v=1407` and `glass-system.css?v=11`; switching should update text and metadata without another document request or preloader. Old article `?lang=…` links redirect to the clean path. These URLs must reach Laravel rather than a static HTML copy. Ensure the CDN/proxy forwards the `lang` cookie, respects article `Cache-Control: private`, and does not force public caching of article HTML. Purge stale article responses and assets after deployment. If PHP output remains stale, reload PHP OPcache through the host's PHP service control.

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

1. Retain the previous complete application release, public assets, original `.env` and database backup before updating.
2. If rollback is needed, return the document root/release pointer to that application release and rebuild its caches with the same application key.
3. Restore database state only when required for schema/content compatibility, with an explicit plan for data received since the backup. Do not run `migrate:fresh` or blindly roll back migrations on production.
4. Test the homepage, both article languages, authentication, contact storage and asset delivery before reopening traffic.

`resources/legacy/` is an import source, not a deployable static web root. Original article snapshots remain in `docs/enterprise-articles/originals.zip`.

## 13. Secrets

Do not place database passwords, `APP_KEY`, or `.env` in git, tickets, or world-readable directories.

Image organization (2026-10-06): [banner, article-body and upload folder guide](IMAGES.md). Run `node scripts/check-images.cjs` after publishing images.
