# Project refactoring report — 2026-10-08

## Scope and result

Started from clean `main` at `af5c9da`; work is on `codex/security-hardening-2026-10-08`. Security and stable behavior took precedence over directory cosmetics. Laravel's `app/bootstrap/config/database/public/resources/routes/storage/tests` structure already existed. No migrations, article slugs, public download URLs, database records or unrelated local files were renamed/deleted. Private runtime/database/backups remain ignored; no production deployment occurred.

## Placement and naming review

| Original location | Final source/location | Reason and compatibility |
|---|---|---|
| Worker heredoc in `app/Services/LegacySitePublisher.php::writeServiceWorker` | `resources/static/sw.js` → published `public/sw.js` | Removes executable JS from PHP source; public URL unchanged; tests compare source/published bytes |
| Offline HTML heredoc in the same method | `resources/static/offline.html` → published `public/offline.html` | Editable static fallback source; existing public URL unchanged |
| `tests/Frontend/ServiceWorkerSecurityTest.cjs` | `tests/Frontend/service-worker-security.test.cjs` | Matches first-party Node test naming; git mv used; active/historical command references updated |

**Files renamed: 1. Existing files moved to another directory: 0. Embedded assets extracted into maintained resource files: 2.** This distinguishes extraction from filesystem moves. Existing `public/sw.js` and `public/offline.html` remain release artifacts at their original paths.

Laravel PHP classes remain PascalCase; methods camelCase; database fields snake_case; existing asset/article slugs mostly kebab-case. Python module `article_comparisons.py` intentionally remains snake_case because three builders import it by module name. Shell helper underscores and published download names were retained to avoid unneeded compatibility churn. Case-collision inventory reported none. Historical originals/reference assets remain outside the web root; upstream duplicate minified/unminified distributions were not pruned blindly.

## Removed/improved components

- Removed four private methods with no externally callable path: `writeHome`, `replaceServicesGrid`, `replaceTestimonials`, `injectCsrfTokens`. The active CMS-backed homepage is maintained directly and supplies its CSRF fields; article-list bootstrap/import tooling remains.
- Removed duplicate worker/offline heredoc generation, reused publisher copyFile logic, and preserved worker behavior with added Filament exclusion and cms-6 cache invalidation.
- Replaced source-repair migration eval with parsing the article's existing JSON localization metadata. Invalid/missing metadata throws before content writes. The repair CLI was syntax-reviewed rather than run on user content.
- Applied SafeImageUpload to the remaining service field; request logging now stores safe exception context; article sanitization boundary, literal search and related limits/projection were corrected.
- Added EnforceHttps middleware, explicit production flag, compatible browser headers and private signed-response handling without rewriting production `.env`.
- Added pinned GitHub verification workflow and a redacted high-confidence current-tree/history scanner. Broadened the documentation checker to include root operational Markdown and `scripts/README.md`.
- Added root operational entry points, maintained detailed current guides, regenerated the documentation index, and linked the new evidence reports. Corrected README's Filament version and nonexistent admin alias claim.

## Dependency changes

| Dependency | Before | After | Reason |
|---|---|---|---|
| Vendored Swiper JS/CSS/map | 11.1.9 | **12.1.2** | Upstream patched security release; advisory/provenance in vendor inventory |
| Composer dependencies/lock | Existing Laravel 13.31.0 / Filament 5.8.3 lock | Unchanged | Packagist audit reported no advisories/abandoned packages; no unnecessary upgrade |

Swiper crosses one major version because upstream's published fix is in 12.1.2. The official npm archive's SHA-512 integrity was verified; package scripts were not executed. MIT LICENSE was added with distributed assets. Existing navigation icons are preserved through `navigation.addIcons=false`. Prototype-pollution and fixture-based EN/FA/mobile slider checks passed. No package.json, Node build, npm installation tree or runtime requirement was introduced.

## Validation and compatibility

Final full PHP suite: **158 tests / 21,919 assertions / zero errors or failures / one MySQL skip**. New targeted hardening tests: 7 / 54. Frontend tests: 6 passed. PHP syntax: 225 files passed. Composer validate/platform/audit/strict optimized autoload passed. Laravel routes/views cache, asset publishing, Filament assets, 101-image/286-reference checks, Markdown targets, repository signatures and diff whitespace passed.

Chrome fixture QA covered home/library/article at desktop and mobile widths, EN/FA slider initialization and switching, with no public-page JS exceptions, missing local files or horizontal overflow. Live local HTTP connectivity was unavailable, so rendered fixture interception was used; login was render-only. MySQL/SMTP/live authenticated admin, offline PWA, Nginx server validation and hosted CI remain unexecuted. No failed/unexecuted check is described as passed. Final inventory counts are updated after documentation generation.

Deployment requires preserving `.env`, key, uploads/database; reviewing actual proxy addresses and HTTP redirects; publishing assets together; rebuilding caches and purging stale CDN assets/worker caches. [Deployment and rollback](../DEPLOYMENT.md) gives the `/var/www/meetaj/public` target, operator steps and rollback procedure. No database migration is added by this change.

## Remaining technical debt

Broader provenance/advisory coverage for unmanaged frontend libraries; full script/style CSP; imported/source content parity; optional missing historical PDF; final-admin concurrency; no external SMTP/MySQL/staging CI evidence; historical docs that retain earlier test counts/dates. The limited secret signatures do not guarantee absence of all credentials. Infrastructure article scripts were not executed against real devices/services. No unnecessary rewrite of article content, Laravel resources or existing deployment helpers was undertaken.

## Modified-file inventory

The complete inventory below is generated from the final tracked/untracked change set. Ignored test fixtures, screenshots, npm archives, local DBs, vendor output and generated admin/public asset trees are excluded. The renamed test is counted once at its new path. See [security findings](SECURITY-AUDIT-REPORT.md) for evidence and risk classification.

<!-- MODIFIED-FILES -->

**48 final paths modified or added**, including the renamed test at its current path. Documentation validation: **143 Markdown documents, zero broken local file links**.

- `.env.production.example`
- `.github/workflows/verify.yml`
- `ARCHITECTURE.md`
- `CHANGELOG.md`
- `DEPLOYMENT.md`
- `DEVELOPMENT.md`
- `README.md`
- `SECURITY.md`
- `app/Filament/Resources/ServiceResource.php`
- `app/Http/Controllers/ContactController.php`
- `app/Http/Middleware/EnforceHttps.php`
- `app/Http/Middleware/SecurityHeaders.php`
- `app/Models/Article.php`
- `app/Services/ArticleContentStandardizer.php`
- `app/Services/LegacySitePublisher.php`
- `bootstrap/app.php`
- `config/security.php`
- `deploy/README.md`
- `deploy/nginx.conf.example`
- `docs/DOCUMENTATION-INDEX.md`
- `docs/PROJECT-REFACTORING-REPORT.md`
- `docs/README.md`
- `docs/SECURITY-AUDIT-REPORT.md`
- `docs/current/ARCHITECTURE.md`
- `docs/current/DEPLOYMENT.md`
- `docs/current/PROJECT-STATUS.md`
- `docs/current/PROJECT-STRUCTURE.md`
- `docs/current/PWA.md`
- `docs/current/SECURITY.md`
- `docs/current/TESTING.md`
- `docs/qa/SECURITY-BUG-AUDIT-2026-10-08.md`
- `public/sw.js`
- `resources/README.md`
- `resources/assets/js/main.js`
- `resources/assets/vendor/DEPENDENCIES.md`
- `resources/assets/vendor/swiper/LICENSE`
- `resources/assets/vendor/swiper/swiper-bundle.min.css`
- `resources/assets/vendor/swiper/swiper-bundle.min.js`
- `resources/assets/vendor/swiper/swiper-bundle.min.js.map`
- `resources/static/offline.html`
- `resources/static/sw.js`
- `scripts/README.md`
- `scripts/check-documentation.cjs`
- `scripts/check-repository-security.cjs`
- `scripts/repair-mikrotik-firewall-source.php`
- `tests/Feature/EnterpriseHardeningTest.php`
- `tests/Frontend/service-worker-security.test.cjs`
- `tests/Frontend/swiper-security.test.cjs`
