# CMS login asset failure — 2026-10-10

Repository: `jalalianamirhossein-ui/MEET-AJ-PORTFOLIO`. Intended production installation: `/var/www/meetaj`.

## Confirmed root cause

The HTTPS login document embeds **HTTP CSS and JavaScript URLs**. These insecure subresources are mixed content on a secure document. Browser inspection reproduced the completely unstyled login. The asset files are available: republishing or rebuilding CSS alone cannot fix the generated scheme.

Read-only production evidence:

| Check | Result |
| --- | --- |
| HTTP and HTTPS `/admin/login` | Both return 200 HTML; both embed HTTP asset URLs |
| Browser navigation from the supplied HTTP URL | Ended at HTTPS with the unstyled page. The cause of that browser upgrade is not established; curl did not observe an HTTP redirect. |
| Styles in HTTPS HTML | `http://meetaj.ir/css/app/meet-aj-admin.css?v=5.8.3.0`, `http://meetaj.ir/css/filament/filament/app.css?v=5.8.3.0`, HTTP Inter CSS, HTTP late custom CSS |
| Scripts in HTTPS HTML | All nine use HTTP, including Filament and `/livewire-7e934fae/livewire.min.js?id=3dabe98a` |
| All 16 distinct linked login resources requested directly over HTTPS | 200, nonempty, appropriate MIME types; includes CSS, JS, preloaded font, logo, favicon |
| Representative core CSS/JS requested over HTTP | 200, appropriate MIME types |
| Response edge server | `ArvanCloud`, `X-Cache: BYPASS` |
| Response CSP | `base-uri 'self'; object-src 'none'; frame-ancestors 'self'`; no CSS/JS source restrictions |
| Public HTTPS homepage asset check | Passed: 15 CSS links and 14 scripts; relative or HTTPS resource URLs |
| New checker against current HTTPS production login | Failed correctly on 14 insecure CSS/JS/preloaded-font references |

No SSH connection was supplied or used. The actual production `.env`, cached configuration, forwarded headers reaching PHP, active Nginx configuration, file permissions, OS and PHP-FPM service remain **uninspected**. Incorrect proxy scheme recognition and/or deployed configuration explain the observed scheme mismatch; which server setting causes it is unconfirmed. Do not infer a particular production `APP_URL` or proxy IP from these observations.

No 404/403 was found in the linked login resources. No localhost/Vite development URL appeared. Missing npm output, Tailwind scanning and a stale build manifest do not explain the demonstrated failure. Static delivery works publicly; there is no evidence warranting a speculative Nginx rewrite.

## Architecture

| Requested inspection | Repository finding |
| --- | --- |
| Versions | Laravel **13.31.0**, Filament **5.8.3**, Livewire **4.4.5** in `composer.lock`; PHP requirement `^8.4` |
| Frontend | Blade + Filament/Livewire CMS; static vendor/site files for the public frontend |
| Vite/Mix/package scripts/build directory | No `package.json`, Vite/Mix config, `public/hot`, `public/build`, or Vite manifest. No Node build is required. |
| Login | Native `Filament\Auth\Pages\Login`; `vendor/filament/filament/resources/views/pages/simple.blade.php` |
| Layout | In that vendor package: `components/layout/simple.blade.php` → `components/layout/base.blade.php` |
| CSS/JS insertion | Native `@filamentStyles`, panel theme and `@filamentScripts`; Livewire injects its own endpoint-based script. Custom hooks are in `AdminPanelProvider`. |
| Admin CSS | Source `resources/css/filament-admin.css` → generated `public/css/app/meet-aj-admin.css` |
| Publication | `php artisan filament:assets`; public site uses `php artisan site:publish-assets --views` |
| Deployment | Existing helper already publishes both asset sets. Generated CSS/JS/fonts/site assets are Git-ignored and must be published on the server. Cache clearing previously followed publication. |
| URL configuration | `config/app.php` lacked `asset_url`, so Laravel did not honor `ASSET_URL`. Existing environment examples intentionally support HTTP hosting. |
| Nginx template | `deploy/nginx.conf.example`: root `/var/www/meetaj/public`, `try_files`, front-controller-only PHP, socket `/run/php/php8.4-fpm.sock`. Active production configuration is unknown. |
| `public/manifest.json` | PWA web manifest, not a Vite asset manifest |

## Files changed and behavior

- `app/Providers/AppServiceProvider.php`: force generated URLs to HTTPS when the configured canonical `APP_URL` uses HTTPS. Covers Filament CSS/JS, Livewire script/module/update URLs and routes when TLS terminates before PHP; HTTP local development stays HTTP.
- `app/Providers/Filament/AdminPanelProvider.php`: resolve logo/favicon URLs lazily so they honor request-time scheme configuration. Same files and design.
- `config/app.php`: wire optional `ASSET_URL` into Laravel. Leave it empty for same-origin assets; an HTTP override would reintroduce mixed content.
- `.env.example`, `.env.production.example`: expose/document the optional setting. No existing `.env` was edited.
- `app/Console/Commands/CheckSiteAssets.php`: new read-only `site:check-assets`. Fetches externally rendered pages, resolves relative links, checks explicit CSS/JS/preloaded-font references for mixed content, redirects, non-200 status, empty body and wrong MIME type. Defaults to login and homepage; supports repeatable `--path`; keeps TLS validation enabled and exits nonzero on failure.
- `deploy/update_project.sh`: check Composer platform requirements, clear stale caches before publication, run external asset checks before reporting deployment success. `MEETAJ_VERIFY_URL` overrides the checked origin.
- `tests/Feature/AdminAssetDeliveryTest.php`: secure URL generation, HTTP development, dashboard/article/editor URLs, native login validation/authentication/remember token, Livewire CSRF, and asset-check success/failure regression tests.
- `DEPLOYMENT.md` and this report: diagnosis, verification and recovery procedure.

No CSS design rules, form templates, credentials, database schema or security middleware changed. No production write, migration, reset, seeding, user creation or authentication attempt occurred.

## Commands and validation

Local runtime: PHP **8.5.11**, satisfying the application's PHP 8.4+ requirement. Relevant executed commands:

```powershell
git status --short
git diff --check
.runtime/php85/php.exe -l app/Console/Commands/CheckSiteAssets.php
.runtime/php85/php.exe -l app/Providers/AppServiceProvider.php
.runtime/php85/php.exe -l tests/Feature/AdminAssetDeliveryTest.php
.runtime/php85/php.exe artisan filament:assets
.runtime/php85/php.exe artisan site:check-assets --url=http://127.0.0.1:8089 --path=/admin/login
.runtime/php85/php.exe -d extension=gd vendor/phpunit/phpunit/phpunit --filter 'AdminAssetDeliveryTest|AdminThemeTest|AdminWorkspaceTest|SecurityRegressionTest|EnterpriseHardeningTest|FormCsrfAndAdminRequestsTest'
.runtime/php85/php.exe -d extension=gd vendor/phpunit/phpunit/phpunit --filter AdminAssetDeliveryTest
& 'C:/Program Files/Git/bin/bash.exe' -n deploy/update_project.sh
```

Production pages and each discovered login asset were fetched with curl GETs recording status/MIME/size. Windows curl required `--ssl-revoke-best-effort` because revocation checking was unavailable; certificate trust validation remained enabled. PHP initially lacked a CA bundle; a temporary PEM of trusted public Windows roots enabled verified HTTPS checks. No TLS bypass was added to application or deployment code.

The new command was also executed against HTTPS production login and homepage:

```bash
php artisan site:check-assets --url=https://meetaj.ir --path=/admin/login
php artisan site:check-assets --url=https://meetaj.ir --path=/
```

Results:

- PHP/Bash syntax and asset publication passed. Source/published admin CSS hashes match; CSS source remains unchanged.
- Local login: all 14 explicit CSS/JS/preloaded-font resources passed HTTP/MIME/content checks.
- A separate application process booted with `APP_URL=https://meetaj.ir`, session in memory, and an HTTP origin request: 200 login HTML, **zero HTTP `href`/`src` URLs**, including logo/favicon and Livewire. Existing environment/database untouched.
- Browser local login: original white/crimson design rendered; mobile 390×844 and desktop 1440×1000 viewport overrides checked after reload. Password visibility, Remember Me and native required-field validation worked. Console inspection returned no errors/warnings.
- Related feature suite passed: **42 tests, 293 assertions**. After adding the endpoint-level CSRF test, final asset regression suite passed: **7 tests, 53 assertions** (overlaps the related suite).
- Tests validate empty/incorrect credentials, successful authentication, remembered login token, and rejection of a real Livewire component request without CSRF / acceptance with CSRF. They use SQLite `:memory:` and do not alter operational users.
- Initial broad execution hit missing GD in the local PHP configuration; GD was enabled for the test process only, then tests passed.
- A combined local login/homepage check found a non-200 local homepage; that page is not reported as passing. Production homepage asset delivery and isolated tests are separate evidence.

The checker is not a full browser HAR, recursive CSS dependency scanner or authenticated editor interaction test. A clean browser Network panel for every request is not claimed. Production dashboard/admin/editor share the affected base assets; authenticated production inspection was unavailable. Their rendered URLs and role access were tested in isolation. Production login/logout and editor interactions must be rechecked after deployment.

## Exact production recovery procedure

The repository targets Ubuntu, Nginx and PHP 8.4-FPM, but actual origin versions were not remotely detected. Do not install Node, overwrite Nginx, or assume its PHP socket. Deploy this reviewed patch through the existing release process, then inspect the origin:

```bash
cd /var/www/meetaj
cat /etc/os-release
php -v
composer check-platform-reqs --no-dev
systemctl list-units --type=service --all 'php*-fpm.service'
sudo nginx -t
sudo nginx -T 2>/dev/null | grep -E '^[[:space:]]*(server_name|root|fastcgi_pass|proxy_set_header)[[:space:]]'
```

Review the block for `meetaj.ir`: root should be `/var/www/meetaj/public`; confirm the real FPM socket/service and MIME configuration. Keep full environment/server dumps private. Static requests already return 200, so avoid global permission changes. For a confirmed origin file problem, inspect targeted paths with `namei -l public/css/filament/filament/app.css` and check readability as the actual worker user.

Back up existing configuration outside the web root, preserving `.env`, `APP_KEY`, database and uploads. Edit only the relevant nonsecret entries in the existing environment using `sudoedit /var/www/meetaj/.env`:

```dotenv
APP_URL=https://meetaj.ir
ASSET_URL=
SESSION_SECURE_COOKIE=true
```

Configure `TRUSTED_PROXIES` with the **actual** proxy/CDN IPs/CIDRs and ensure the proxy sends the original `X-Forwarded-Proto`. No accurate IP can be given without the topology; do not use `*` or guessed sample values. Scheme forcing does not make `Request::secure()` true and does not replace proxy trust or cookie configuration. Preserve the existing enforcement choice; enable `FORCE_HTTPS=true` only after request scheme recognition works to avoid redirect loops. `ASSET_URL` alone cannot fix Livewire's `url()` calls.

Run as the normal deployment user; this patch needs no migration:

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
# This exact service name matches the repository template. Use the detected one if different.
sudo systemctl reload php8.4-fpm
php artisan site:check-assets --url=https://meetaj.ir
```

No Nginx reload is needed for this code/environment fix. If an independently confirmed Nginx correction is necessary, back up the active site file first, then `sudo nginx -t && sudo systemctl reload nginx`. Keep admin/Livewire/authenticated HTML out of CDN caches; purge stale login HTML if present. Current production login headers already advertise `no-store` and `BYPASS`.

For future deployments using the existing helper:

```bash
sudo env MEETAJ_VERIFY_URL=https://meetaj.ir bash deploy/update_project.sh
```

That helper already includes migrations and expects a clean `origin` checkout: review pending migrations and the release before using it. The manual asset-only procedure above performs no migrations or editorial seeding/import.

Finally hard reload HTTPS login with browser Network/Console open. Confirm no mixed content or 404/403, then test authorized login/logout, remember-me, dashboard, editor, validation and responsive layouts. Rollback uses the previous reviewed release and its published assets, preserving application data/key; restore changed nonsecret settings consistently with the actual hosting scheme.

## Remaining status and risks

Confirmed production diagnosis; repository fix prepared. **Not deployed; production is not claimed fixed.** Origin OS/configuration, actual trusted-proxy topology, production session/CSRF behavior and authenticated interactions remain operator checks. An HTTP `ASSET_URL` override, stale cached environment, or uncorrected proxy settings can reintroduce failures. The automated delivery check prevents an unhealthy asset result from being reported as successful deployment; it does not roll back code automatically.
