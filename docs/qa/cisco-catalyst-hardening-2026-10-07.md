# Cisco Catalyst hardening article — 2026-10-07

The PHP builder `scripts/build-cisco-hardening-article.php` owns the bilingual source in `resources/legacy/articles/cisco-catalyst-layer-2-layer-3-switch-hardening.html` and two independent downloadable `.cfg` templates in `public/downloads/cisco-catalyst-layer-2-layer-3-switch-hardening/`.

The article contains architecture, all 24 requested topics, official Cisco references, localized metadata, LTR CLI blocks, comparison/checklist tables and 12 warning callouts. The existing Cisco banner is reused when present. No remote deployment is performed.

Technical review explicitly covers VTY/platform differences, Type 8/9 password syntax, SHA-1 versus SHA-2, legacy NTP MD5 limitations, Option 82 compatibility, binding persistence, mixed static/DHCP hosts, stateless SVI ACL return traffic, ingress trust boundaries, and existing Catalyst CoPP. L3 uses a routed upstream interface and a separate downstream trunk; DHCP and DAI trust decisions are intentionally separate. IPv6 requires a separate deployment review.

Verification: PHP syntax checks pass. The focused feature test passes with 37 assertions covering both locales, section/callout retention after sanitization, identical CLI blocks, template downloads matching the article, L2/L3 routing separation, authPriv monitoring, search, sitemap and legacy redirect. The existing ignored PHPUnit audit wrapper is used because Windows sandbox readability checks reject the normal Composer bootstrap even though PHP can require it. Set `APP_CONFIG_CACHE` to the nonexistent `storage/app/phpunit-config-not-cached.php` before using that older wrapper; it lacks this setting itself. No switch hardware or IOS/IOS XE emulator was available; command availability must be checked against the actual Catalyst PID and image before deployment.

Migration `2026_10_07_000031_add_cisco_catalyst_hardening_article.php` imports only this article and preserves existing CMS edits on reruns. Local database changes are preceded by a SQLite backup.

## Local recovery incident

The initial use of the older audit wrapper loaded cached local database settings and RefreshDatabase rebuilt the local CMS database. This was an execution error. The damaged database is preserved in `storage/app/private/cisco-hardening-recovery/after-test.sqlite`. The latest available full backup, `storage/app/private/image-reorganization/before-20261006-131724-0901f2.sqlite`, was restored through the SQLite backup API and pending migrations reapplied. Integrity check returned `ok`; the backup contains 28 articles, 7 homepage content rows, 13 services and 9 testimonials. Tests were rerun with an explicit uncached configuration path and passed. Later CMS edits after that backup cannot be guaranteed. The remote site database was not accessed.
