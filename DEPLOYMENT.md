# Production deployment and rollback

Target installation root: `/var/www/meetaj`; document root: **`/var/www/meetaj/public`**. This task did not deploy, change production databases, rotate credentials or rewrite any production environment. [Existing hosting guidance](docs/current/DEPLOYMENT.md) also covers DirectAdmin; adapt its example paths to the actual host.

## Before release

Back up the existing database, public uploads, `.env` and application key into restricted storage outside the web root. Record the current Git revision and server configuration. Verify tested code, locked dependencies, PHP 8.4/FPM extensions, and writable `storage/` and `bootstrap/cache/`. Keep application source read-only to the web process; do not use `chmod 777`. Review migrations before deployment; this hardening change adds none.

Environment variable names requiring review: `APP_ENV`, `APP_DEBUG`, `APP_URL`, `APP_KEY`, `FORCE_HTTPS`, `TRUSTED_PROXIES`, `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `SESSION_DRIVER`, `SESSION_SECURE_COOKIE`, `SESSION_SAME_SITE`, `SESSION_LIFETIME`, `SESSION_DOMAIN`, `CACHE_STORE`, `LOG_CHANNEL`, `LOG_LEVEL`, `MAIL_MAILER`, `MAIL_SCHEME`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `CONTACT_NOTIFICATION_EMAIL`. Never copy a workstation `.env` or generate a new key for an existing installation.

Production requires production environment and disabled debug. HTTP hosting is supported: set `APP_URL=http://meetaj.ir`, `FORCE_HTTPS=false` and `SESSION_SECURE_COOKIE=false` so login/contact sessions work over HTTP. Disable forced HTTPS redirects and HSTS at the CDN/web server as well. HTTPS responses with enforcement disabled send `Strict-Transport-Security: max-age=0` to clear a previously stored host policy; affected browsers must receive that response over HTTPS first.

For HTTPS-only hosting, explicitly set an HTTPS `APP_URL`, `FORCE_HTTPS=true` and `SESSION_SECURE_COOKIE=true`. Only this mode redirects safe HTTP requests and rejects unsafe HTTP submissions. HTTPS enforcement defaults off, including in production. Keep login/admin traffic on HTTPS whenever available because HTTP carries credentials and sessions without encryption.

With TLS terminated at a proxy, set `TRUSTED_PROXIES` to the **actual** proxy IPs/CIDRs and forward the original scheme/client IP. Empty is correct for direct hosting. Never use `*`. An incorrect proxy allowlist can cause redirect loops; fix proxy trust/forwarding before enabling the new production release. Forwarded host is intentionally not trusted.

## Release steps (operator executed)

Use the tested release in a clean checkout/release directory; preserve shared configuration/uploads and editorial data. Inspect local changes before switching code. For the in-place Ubuntu helper, see `deploy/update_project.sh`; it refuses dirty working trees and assumes the clone's `origin` remote. This workstation's remote is named `VSCode`, so use its actual remote if manually pulling locally.

```bash
cd /var/www/meetaj
composer install --no-dev --prefer-dist --optimize-autoloader
composer check-platform-reqs --no-dev
php artisan optimize:clear
php artisan site:publish-assets --views
php artisan filament:assets
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run reviewed pending migrations with a backup only if the chosen release needs them. Ensure `public/storage` links only to `storage/app/public`; do not expose all `storage/`. Disable script execution in uploads, deny dotfiles, and serve PHP only through the front controller. A reviewable Nginx TLS/redirect template is in [deploy/nginx.conf.example](deploy/nginx.conf.example); validate it with `nginx -t` on staging before installation. That server validation has not been executed here.

Reload the actual PHP-FPM service after deploying. Purge stale CDN HTML/private caches and the old JS/CSS assets. Publish **Swiper 12.1.2** JS/CSS/map together and **service-worker `cms-6`**; activation replaces older worker caches. HSTS now covers the current host only. Previously remembered subdomain policies may remain until their existing expiry; removing the directive does not instantly undo all browsers' earlier policy.

Verify HTTP redirects, HTTPS headers, login, EN/FA articles, language switching, sliders, image uploads and a controlled contact submission. Confirm CDN honors private/no-store and does not strip security headers on login/error routes. The new CSP preserves current inline scripts/styles/maps and is intentionally limited; a full script policy needs staging/report-only evaluation before enforcement. Do not add HSTS includeSubDomains or preload without a separate domain assessment.

## Rollback

Keep the previous release and protected configuration/upload/database backups. Switch back to the recorded release, install its lockfile, republish **that release's** assets, rebuild its configuration/routes/views, reload FPM, and purge CDN/browser-worker caches as appropriate. Do not reset the database or replace `.env`/APP_KEY for this code-only rollback. Restore a database backup only for a reviewed database incident/migration rollback, with a plan for submissions received since backup. Revalidate TLS/proxy settings and public functions afterward. Rolling back the application does not necessarily revert an installed CDN/Nginx rule or a browser's remembered HSTS policy.

## Maintenance

Run Composer and vendored-advisory reviews before releases; keep private backups and verify restore procedures. Review logs for safe failure classes, rotate/retain logs according to the site's data policy, patch PHP/web server/OS, and verify certificate renewal externally. Queue workers and scheduled cron jobs are unnecessary for the current implementation. Remote MySQL, SMTP delivery, server permissions, full TLS chain/revocation and actual CI execution remain operational checks for the owner.
