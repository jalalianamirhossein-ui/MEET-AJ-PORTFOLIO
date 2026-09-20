# Meet AJ Portfolio — Laravel implementation plan

> **SUPERSEDED (HISTORICAL).** Planning document retained as history. Do not follow Laravel 11, PHP 8.2, Filament 3, `/de` routes, `pages`, or `contact_requests` from this file. Current status: [PROJECT-STATUS.md](../current/PROJECT-STATUS.md). Decision record: [architecture-decision-record.md](../decisions/ADR/ADR-001-laravel-13-filament-5-stack.md).

**Status: superseded.** Audit approved; implementation is paused under the final instruction in the latest request. This document proposes the implementation; it does not claim that installation, conversion, import or testing has been completed.

Prepared 2026-09-14 from `current-site-analysis.md`, `current-site-inventory.json`, `laravel-migration-plan.md`, the original files and the existing preliminary scaffold. This is the current planning document; older documents remain unchanged as records of earlier proposals.

## 1. Final proposed Laravel architecture

Laravel 11 on PHP 8.2+, MySQL/MariaDB with InnoDB/utf8mb4, Blade frontend and a compatible Filament admin at `/admin`. Production uses PHP and Composer-installed dependencies. No Docker, Redis, Supervisor, background workers or Node runtime dependency. Existing vendor CSS/JS remain local static assets; Filament assets are published during installation.

Public request flow: web server → `public/index.php` → Laravel middleware/routes → controllers and Eloquent → existing design rendered by Blade. Asset requests remain web-server-served files at their original URLs. Contact submission uses Laravel session/CSRF validation and stores a request before returning the existing success response.

The only production document root is `public/`. Keep original `index.html`, articles, services, forms, CSS, JavaScript, media, manifest and worker unchanged in their current locations. Build the Laravel frontend using new views and public copies; never relocate/delete originals. The original files remain rollback material outside the document root.

**Scope reconciliation for approval:** the request contains both `contact_requests`/Pages and a later exact core migration list using `requests`. This plan follows that later exact list: `users`, `categories`, `articles`, `article_redirects`, `requests`, `sessions`, `password_reset_tokens`, plus Laravel's migration ledger. Use `App\Models\Request` and `RequestResource`, labeled “Contact Requests” in admin. Do not create a duplicate `contact_requests` table. Homepage/services remain editable source-controlled Blade content in this core scope. A database `pages` table, PageResource, UserResource and database notification bell are separately identified extensions in section 3; they are not silently included in the exact core schema.

The provisional scaffold already in the workspace was created before the subsequent planning-only instruction. Leave it unchanged during this planning phase. It is incomplete and is not an installed/tested Laravel application. Its network-only public worker is unsuitable for the required offline behavior and must not be deployed; replace that newly added file with the compatible PWA implementation after approval, leaving original `sw.js` intact.

Branch: `feature/laravel-migration`, based on `main` commit `a9b23d5`. The existing user CSS edit is preserved in `docs/baseline-main-css.patch`; all 451 original files have SHA-256 baselines in `docs/baseline-files.json`.

## 2. Folder structure

Proposed new Laravel tree alongside existing source files:

```text
app/
  Console/Commands/
    ImportLegacyArticles.php
    CreateCmsUser.php
    PublishLegacyAssets.php
  Http/
    Controllers/
      HomeController.php
      ServiceController.php
      ArticleController.php
      ContactController.php
      SitemapController.php
      RobotsController.php
    Middleware/
      SetLanguage.php
      ValidateCsrfToken.php
      ResponseHeaders.php
    Requests/StoreContactRequest.php
  Models/{User,Article,Category,ArticleRedirect,Request}.php
  Policies/{Article,Category,Request}Policy.php
  Services/
    LegacyArticleImporter.php
    ArticleHtmlSanitizer.php
    ArticleSeo.php
  Filament/Resources/
    ArticleResource.php
    ArticleResource/Pages/
    CategoryResource.php
    CategoryResource/Pages/
    RequestResource.php
    RequestResource/Pages/
  Providers/
    AppServiceProvider.php
    Filament/AdminPanelProvider.php
bootstrap/{app.php,providers.php,cache/}
config/
database/{migrations/,seeders/}
lang/{en/,fa/,de/}
public/
  index.php
  .htaccess
  assets/{css/,js/,img/,vendor/}
  manifest.json
  sw.js
  offline.html
  docs/netbox_installation_guide_v2.pdf
  storage -> ../storage/app/public
resources/views/
  layouts/app.blade.php
  components/{header,footer,navbar,language-switcher}.blade.php
  components/{seo,article-card}.blade.php
  home.blade.php
  services/<existing-service-name>.blade.php
  articles/{index,show}.blade.php
  articles/presentation/<allowlisted-legacy-variant>.blade.php
  errors/{404,419,500}.blade.php
routes/{web.php,console.php}
storage/{app/,framework/,logs/}
tests/{Feature/,Unit/,fixtures/}
.env.example
.env.production.example
composer.json
composer.lock
artisan
phpunit.xml
DEPLOYMENT.md
implementation-report.md
```

Public asset publishing uses an explicit file allowlist and checksums, excluding legacy PHP, operational documents, `.git`, `.claude`, credentials and private uploads. Bootstrap/cache and storage subdirectories are prepared by installation. `public/sitemap.xml` and physical legacy article/service HTML must not shadow dynamic routes. Public compatibility copies for preloader/partial examples may be retained outside the sitemap.

## 3. Database schema

All core domain IDs: unsigned bigint primary keys. Text: utf8mb4; MySQL/MariaDB collation `utf8mb4_unicode_ci`. Application writes timestamps in UTC and displays them in `Asia/Tehran`. Use string values validated by application rules for role/language/status. Publication queries and import transactions must be tested on the production database engine.

### Core tables

| Table | Columns and constraints |
| --- | --- |
| `users` | `id`; `name` varchar(255); unique `email` varchar(255); nullable `email_verified_at`; `password` varchar(255), hashed; `role` varchar(20), admin/editor, default editor; nullable `remember_token`; timestamps |
| `categories` | `id`; `name` varchar(255); `slug` varchar(180); `language` varchar(2), en/fa/de; `translation_key` UUID; timestamps |
| `articles` | `id`; `title` varchar(255); `slug` varchar(180); `language` varchar(2); nullable `excerpt` text; `content` longText; nullable `featured_image` varchar(2048); nullable `category_id`; nullable `meta_title` varchar(255); nullable `meta_description` text; `status` varchar(20), draft/published, default draft; nullable `published_at`; timestamps |
| `articles` preservation fields | `translation_key` UUID; nullable `canonical_url` varchar(2048); nullable `seo_data` JSON; nullable `presentation` JSON; `sort_order` unsigned integer default 0. These preserve translation relationships, source social/schema metadata, layout/card context and existing order. Database metadata cannot select arbitrary view paths or execute code. |
| `article_redirects` | `id`; unique `old_path` varchar(255), site-relative path; `article_id` foreign key; timestamps. Store full old path, including locale and `.html` where applicable, rather than ambiguous bare slugs. |
| `requests` | `id`; `name` varchar(100); `email` varchar(255); nullable `phone` varchar(40); nullable `subject` varchar(255); `message` text; `status` varchar(20), new/in_progress/resolved/spam, default new; `created_at`, `updated_at` |
| `sessions` | string `id` primary key; nullable `user_id` FK; nullable IP/user-agent; longText payload; integer last_activity. Available for database sessions; default runtime uses files. |
| `password_reset_tokens` | `email` primary key; hashed token; nullable created_at. No public reset-email feature enabled without configured delivery. |
| `migrations` | Laravel-managed migration ledger |

Language-specific records use **unique `(language, slug)`**, not globally unique bare slugs. This permits English and Persian counterparts to share the established slug under distinct route namespaces. Categories and articles also have unique `(translation_key, language)` to prevent duplicate versions in the same translation group. These choices are explicit parts of the approval proposal.

Relationships and indexes:

- Category hasMany Articles; Article belongsTo Category. `category_id` references categories, SET NULL on deletion; enforce matching language in server-side validation.
- Article hasMany ArticleRedirects; each redirect belongsTo an Article. Redirects cascade when an article is deleted; deleted URLs return 404 unless a deliberate 410 policy is added.
- `articles`: `(language, status, published_at)` composite index and `(status, published_at)` index for cross-language publication/sitemap queries. New records default to draft. Published records require a date; future dates remain private until due.
- `requests`: `(status, created_at)` index; admin search over relevant contact fields. Preserve subject because all seven legacy forms submit it. `updated_at` records handling changes.
- `sessions`: index user_id and last_activity; user deletion sets session user_id null. No plaintext passwords or session tokens in reports/logs.
- Slug edits validate both current language/slug uniqueness and historical-path reservations. Save article edits and redirect history atomically to prevent ambiguous routes.

Migration order: users/password-reset tokens → categories → articles → article_redirects → requests → sessions. Rollback runs in reverse dependency order. Production migrations are additive; never run migrate:fresh against production.

`App\Models\Request` represents a stored inquiry. Alias `Illuminate\Http\Request` as `HttpRequest` wherever both types occur to avoid ambiguity.

### Separately proposed extensions from the broader brief

These are documented so the earlier requests are not lost. They require inclusion when approving scope, since the later instruction supplied an exact table/resource list:

| Extension | Required addition |
| --- | --- |
| Database-managed homepage/services/custom pages | `pages`: id, title, slug, language, content, meta_title, meta_description, canonical_url, seo_data, presentation, allowlisted template, translation_key, status, published_at, timestamps; composite locale uniqueness; Page model/policy/resource. Protect original route identity. |
| Admin account CRUD in Filament | UserResource/UserPolicy on the existing users table; admin-only access and last-admin safeguards. Core scope provides interactive CLI account creation. |
| Persistent admin notification bell | Laravel `notifications` table with UUID ID, type, notifiable type/ID, data, read_at and timestamps. Write synchronously with request storage; no worker. Core scope shows a new-request count/widget using the requests table. |

Do not create both `requests` and `contact_requests`. If the approved scope prefers the latter name, rename the proposed model mapping/resource consistently before generating migrations; never maintain duplicate submission stores.

## 4. Existing HTML to Blade migration strategy

Use new Blade files derived from the source HTML. Shared components retain exact markup, attribute ordering where practical, classes, IDs, ARIA properties, anchors and script/style ordering. Avoid broad HTML formatter or DOM reserialization over entire documents. Retain page-family differences; service pages do not acquire the homepage sidebar if they do not have it today.

| Original content | Proposed Blade/controller behavior |
| --- | --- |
| `index.html` | `home.blade.php` with existing static sections and database-driven portfolio cards |
| `services/network-design.html` | `services/network-design.blade.php`, original route/layout/content |
| `services/system-administration.html` | `services/system-administration.blade.php`, original route/layout/content |
| `services/devops-automation.html` | `services/devops-automation.blade.php`, original route/layout/content |
| `services/monitoring-security.html` | `services/monitoring-security.blade.php`, original route/layout/content |
| `services/virtualization-solutions.html` | `services/virtualization-solutions.blade.php`, original route/layout/content |
| `services/technical-consulting.html` | `services/technical-consulting.blade.php`, original route/layout/content |
| `articles/*.html` | Shared show view with preserved, allowlisted layout variants and database content |

Extract the original header/footer/navbar/language switcher, parameterizing only actual page differences. Preserve article TOCs and per-page scripts as source-controlled view fragments. Preserve optimized thumbnails, full-size gallery targets, image attributes and filter classes separately; one featured-image field must not erase distinct thumbnail/gallery paths.

Baseline assets are copied unchanged. Do not edit source CSS or JS to accommodate the backend. Dynamic cards retain the DOM contract expected by Isotope, galleries, load-more and language code. Any necessary CMS-only integration scripts are separate new files; verify they do not double-register event handlers or replace established interactions.

Pages and snippets stored in the database are output as validated/sanitized HTML, never passed to Blade::render/eval. Escaped examples, code blocks, nested markup and Persian text require round-trip checks. Keep trusted inline service/form logic in vetted Blade source, not editable rich-text fields.

### PWA preservation within the new public root

Copy `manifest.json` without changing its identity, start URL, icons or display behavior. Keep `/sw.js` and its scope. Retain public document network-first/offline fallback, static-asset caching, on-demand image caching and existing registration behavior. Do not deploy the preliminary network-only worker.

The existing worker excludes forms/PHP but can cache new `/admin` and `/livewire` GETs. In the **new public copy only**, add targeted private-route exclusions and response eligibility checks while preserving public offline behavior. Version the caches and migrate existing `meet-aj-*` caches safely. Never cache contact/token responses, authenticated pages, private uploads or `no-store` responses. Public documents must explicitly opt into cacheability. All PWA handlers present in the source are reviewed before reuse; retain safe behavior rather than silently dropping handlers.

Bound public document cache lifetime, evict entries after authoritative 404/410 responses, and revalidate on reconnect. Offline copies may remain stale until reconnect/expiry; document and test that limit. Never queue/store personal contact form submissions offline. PWA compatibility work is part of the proposed backend integration, not permission to change original files or remove offline capability.

## 5. Article import strategy

Proposed command: `php artisan articles:import-legacy --dry-run`, followed by the same command without dry-run after its report is reviewed. Command name/flags are planned, not yet implemented.

1. Use the audited manifest of exactly 23 original article files. Validate source SHA-256 hashes, local paths and encoding; fail/report a changed baseline instead of silently importing a different source.
2. Parse HTML to locate title/excerpt/body, images, TOC, bilingual attributes, category, publication metadata, canonical, OG, Twitter and schema. Use original source slices for complex content where parsing/reserialization would alter code/markup; DOM is used to identify and verify extraction, not to rewrite whole pages.
3. Preserve raw imported article body, original SEO extras and presentation context. Display through a sanitizer configured to retain needed safe classes, IDs and bilingual attributes. Save a source-content hash in presentation metadata so extraction can be verified. Report any sanitizer removal; unexplained loss is an import failure, not a successful migration.
4. Seed the 23 default article records with preserved bilingual content and original slugs. Identify them as legacy presentation variants so the original EN/FA toggle keeps working. Preserve metadata verbatim unless a canonical/image URL must be normalized to the approved target.
5. Build separate language records from explicit `data-fa`/`data-en` where completeness can be validated, sharing translation_key. The report distinguishes 23 source articles from additional translation rows. Incomplete locale variants remain draft; do not fabricate translations or duplicate English as published German content.
6. Preserve known datePublished/dateModified values. When publication date is absent, use a documented import publication date for visibility and do not pretend sitemap lastmod is historical publication evidence. Record date provenance; include accurate available dates in schema.
7. Create categories and initial `.html` redirects in the same database transaction. Validate row counts, source-to-record mapping, images and redirect uniqueness before commit. A failed import rolls back all new rows from that run.
8. Default repeat behavior skips existing `(language, slug)` and existing source mappings; it never overwrites CMS edits or resurrects renamed records. After a slug edit, use stored source identity/redirect history to recognize the article. A source path already reserved by a different record is an error.
9. Concurrent runs use an application lock plus database uniqueness constraints. No file-copy operation is treated as database-transactional: verify public assets before import, then write a report only after outcome is known.
10. No automatic reimport in Composer scripts or every deployment. Any explicit overwrite/reconciliation mode is a separate future feature, not part of this non-destructive importer.

## 6. URL redirect and SEO strategy

The previously approved article strategy keeps every old URL resolvable through a 301; it changes the canonical destination only for the explicitly requested `.html` → clean article migration. Service URLs stay unchanged. If preserving `.html` as the primary canonical rather than a redirect is intended, adjust this plan before approval.

| Current URL | Laravel URL / behavior |
| --- | --- |
| `/` | Same URL, rendered by Laravel |
| `/index.html` | 301 `/`, consistent with current homepage canonical |
| `/#portfolio` and existing section fragments | Same anchors/behavior |
| `/services/{existing-slug}.html` | Same URL, rendered by Laravel |
| `/articles/{existing-slug}.html` | 301 `/articles/{same-slug}` |
| `/articles/{slug}` | Published article detail; draft/future/unknown returns 404 |
| `/articles` | New article index using original portfolio design |
| `/forms/contact.php` | Same POST URL; Laravel persistence and plain-text `OK` |
| `/forms/get-csrf-token.php` | Same GET URL; JSON `{"token":"..."}` |
| `/assets/**`, `/manifest.json`, `/sw.js`, PDF download | Same URLs |
| `/sitemap.xml`, `/robots.txt` | Same URLs, generated by Laravel |

The complete 23-article old/new map is in `content-migration-analysis.md` and the existing audit. Register legacy redirect routes before clean-slug routes. Preserve query strings where valid; browser fragments are not sent to Laravel and depend on retained section IDs. Later slug changes add history resolving directly to the current published URL, with no redirect chains/loops or external destinations.

SEO implementation:

- Preserve original meta title/description, canonical intent, OpenGraph, Twitter, keywords and relevant schema fields. Separate page title from social title when the source differs.
- Generate absolute image and canonical URLs from a trusted configured origin. Nullable canonical_url defaults to the self route; validate overrides against same-site published canonical destinations.
- Preserve homepage Person/WebSite/BreadcrumbList and service Service JSON-LD. Generate safe Article JSON-LD for all public articles, including the 17 without it today; retain useful original fields from the other six. Escape JSON for HTML script context without executing content.
- Preserve robots public crawling intent and sitemap declaration. Exclude admin/form paths from crawling; authorization still protects them. Do not emit a physical sitemap/robots file that bypasses Laravel.
- Dynamic sitemap contains only published canonical URLs, including service routes and available language versions, using content modification dates. Exclude redirects, drafts, future articles, duplicate homepage URLs, examples and admin. Validate XML and escape URLs.
- Each explicit translated route has its own canonical and reciprocal hreflang only when the translation is published. x-default points to the stable legacy/default URL. No invented German alternates.

## 7. Multilingual implementation

The audit found working EN/FA client-side switching and bilingual HTML, not existing German article prose. Preserve that legacy interaction. The database supports en/fa/de, and new CMS content is queried by language.

On legacy unprefixed URLs, preserve the current client language preference/toggle behavior and bilingual attributes. Use stable default server metadata/canonical so cookies or Accept-Language do not make search-engine responses unpredictable. Middleware may use the existing language preference for UI context; it must not replace the default imported article with an unrelated/missing row.

For separately published language variants, use `/fa/articles/{slug}` and `/de/articles/{slug}`; English uses `/articles/{slug}`. Language middleware validates explicit route locale, sets Laravel locale and supplies RTL/LTR state. Route locale wins over preference for these explicit variants. Reject unsupported locale values. Category filters and related-content queries use the same language as the record.

German support needs a narrow new adapter because the audited JS only recognizes EN/FA. Original JS files remain unchanged. The proposal is to keep legacy EN/FA behavior on legacy pages and add a separate language-switcher integration on explicit localized CMS pages, using the same styling and controlling initialization order to prevent the old script resetting DE to EN. Extending the selector to DE is the requested language feature; do not claim three-language behavior can be achieved without any integration change.

Missing translation: show availability accurately and link to that language's listing where appropriate. German interface translations can be implemented; German articles/services require actual prose and remain drafts until provided/edited. Translation rows must be independently editable without overwritten legacy data attributes restoring older content.

Approval includes this additive locale integration. Preserve existing EN/FA public URLs and behavior; verify three-language behavior separately rather than replacing the two-language site wholesale.

## 8. Filament admin structure and contact handling

Install a Filament version compatible with Laravel 11/PHP 8.2; the current scaffold proposes Filament 3.3. Verify dependency resolution/advisories and commit composer.lock before calling it installed. No frontend build framework replacement.

| Resource | Behavior |
| --- | --- |
| ArticleResource | List/search/filter by language, category and status; create/edit/delete; title, slug, language, category, excerpt, content, featured image, SEO, publication status/date; preview and slug-history handling |
| CategoryResource | Localized category CRUD; article relation; prevent assigning a mismatched-language category |
| RequestResource | Admin-only read/detail/search/filter; change new/in_progress/resolved/spam status; show original subject/phone/message; new-request count/widget |

Roles: admin full core access; editor article/category content management only. Enforce policies on direct URLs and Livewire mutations, not just menu visibility. Protect request data, account management and role assignment from editors. Create initial admin and additional accounts via interactive CLI with hidden password input and Laravel hashing, no seeded default passwords or public registration. Pages/Users resources are the optional scope extensions listed above.

Article editing uses a rich editor for new ordinary content and a source-capable editor for imported complex markup, with sanitized preview. Validate image MIME/content/size; allow JPEG/PNG/WebP with generated filenames, reject SVG/executable uploads. Store uploads on public disk under storage and prohibit script execution there.

Contact handling preserves the seven existing form UIs and JavaScript behavior:

1. Token endpoint starts/uses Laravel session, returns `{token}` with no-store headers.
2. CSRF middleware accepts the legacy `csrf_token` field as an alias; never exempt the submission route from CSRF protection.
3. FormRequest validation covers name/email/optional phone/subject/message/honeypot; per-client throttling uses the file cache. Errors remain readable plain text compatible with the existing JavaScript.
4. Persist the request and return status 200 with literal `OK` only after commit. The core admin count/widget immediately reflects new requests.
5. Existing email delivery is a separate behavior to preserve with synchronous configured Laravel mail where the host supports it; no worker. Database storage is authoritative. Clearly report mail transport/setup and failure behavior, never return `OK` for a failed database save or log submitted personal text unnecessarily.

## 9. DirectAdmin deployment strategy

Prepare a staging release before changing the domain document root. Use PHP 8.2+ with required Laravel/Filament extensions; verify mbstring, intl, DOM/XML, fileinfo, PDO MySQL, ctype, curl, openssl, tokenizer and session support; confirm exact Composer platform requirements. PHP CLI and web/FPM versions must agree. Upload a tested composer.lock and use the same dependency set in production.

Preferred layout: `/home/ACCOUNT/domains/meetaj.ir/app/releases/RELEASE/` contains the application; DirectAdmin domain document root points to that release's `public/`. Keep `.env`, vendor, source HTML and application code out of the web root. If the hosting provider fixes public_html, ask it to point the domain to Laravel public (or link public_html to the approved public directory where supported); never point the domain at the repository root. Host support for the public-root setup is a deployment prerequisite.

Proposed installation sequence to document in DEPLOYMENT.md after implementation:

```sh
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan site:publish-assets
php artisan articles:import-legacy --dry-run
php artisan articles:import-legacy
php artisan cms:create-user
php artisan storage:link
php artisan optimize
composer check-platform-reqs --no-dev
```

The three project-specific commands are planned deliverables. Generate APP_KEY only on initial installation and preserve it on upgrades. Asset publish/import must refuse to overwrite original source files; import skips already managed content. Composer hooks/Filament asset generation and storage directories must be available before optimization.

Production .env: APP_ENV=production, APP_DEBUG=false, APP_URL=https://meetaj.ir, unique APP_KEY, host-specific DB_DATABASE/DB_USERNAME/DB_PASSWORD, secure HTTP-only session cookie, file sessions/cache and synchronous execution. Do not commit secrets. No required cron for publishing: visibility is evaluated using status/date at request time. Optional housekeeping/backups may use DirectAdmin cron; no long-running processes.

Storage link points to `storage/app/public`. Ensure correct account ownership, normally 755 directories/644 files, and grant only the PHP user the additional write access required for storage/bootstrap cache. No blanket 777. Restrict upload execution. Apache rewrite routes non-files to index.php; verify Nginx frontends do not bypass the rules.

Back up original deployment, database, storage/uploads and .env before cutover. Smoke-test staging routes, login, form persistence, assets, sitemap and worker upgrade. Switch document root only when verified. Roll back by restoring the previous release/document root, retaining a backup of all new requests/uploads; do not casually roll back the database after live submissions. PHP source, secrets and legacy forms must never become downloadable.

Lifecycle risk recorded in the audit: Laravel 11 security support ended 2026-03-12. Keep the explicitly requested major in this plan, but document the unsupported-major production risk and supported-version path before launch. Reference: [official Laravel release policy](https://laravel.com/framework/docs/11.x/releases). Do not silently change framework majors or disable dependency security checks.

## 10. Testing plan, risks and acceptance criteria

### Verification matrix

| Area | Required evidence |
| --- | --- |
| Source preservation | Every one of the 451 original file hashes matches baseline, including user's CSS edit; no legacy deletion; added-file report |
| Framework/database | PHP/Composer platform checks; migrations up/down on disposable MySQL/MariaDB; utf8mb4 Persian storage; FK/unique/index checks |
| Import | Exactly 23 source mappings; report translation row counts separately; unchanged code/structure/images/SEO; transactional failure rollback; repeat and renamed-record idempotence |
| Homepage/services | 200 homepage and all six service URLs; identical text/layout/classes/IDs/assets; homepage database cards update after publication |
| Articles/portfolio | Listing/detail/filter/gallery/load-more/code-copy behavior; unpublished/future/deleted visibility; category-language consistency |
| URLs | All 23 old article URLs resolve in one 301 to expected published clean URLs; unknown slugs 404; later slug edits preserve history; no reserved-path collisions or loops |
| Languages | Existing EN/FA preference/toggle and RTL unchanged on legacy pages; explicit en/fa/de content isolation, correct direction and missing-translation behavior |
| Forms | All seven forms; actual cookie/session token round-trip; missing/invalid CSRF rejected; validation/rate-limit errors readable; exactly one stored successful request; literal `OK`; optional phone/subject retained |
| Admin | Login/logout/session regeneration; article create/edit/delete/draft/publish/image upload; category CRUD; request filtering/status; direct editor access to requests/users denied |
| Security | Safe HTML/code round-trip plus XSS rejection; executable upload rejection; role escalation denied; no secrets/debug output exposed |
| SEO | Exact source title/description/OG/Twitter where preserved; valid absolute canonical/image URLs; escaped valid JSON-LD; reciprocal published hreflang; sitemap XML and robots |
| Desktop/mobile | Before/after screenshots at 320, 375/390, 768 and 1440px with matched data/language; no new overflow; mobile header/focus/keyboard behavior, responsive hero and carousel pause maintained |
| PWA | Existing installed worker upgrade and fresh install; public offline navigation/assets; on-demand image loading; reconnect; no cached admin/Livewire/token/form/private responses; no personal offline queue |
| Hosting | Actual DirectAdmin document-root/rewrite, PHP version/extensions, permissions, storage link, HTTPS secure cookies, optimized caches and synchronous form behavior |

Use meaningful Laravel feature/authorization tests, real HTTP CSRF tests (Laravel's testing bypass must not create false positives), import fixtures, and browser screenshots/interactions. SQLite can accelerate isolated tests; it cannot substitute for MySQL/MariaDB schema validation. No Node server or browser tooling is needed in production.

### Risks that must be resolved or reported

1. **Scope mismatch:** requests vs contact_requests and optional Pages/Users/notifications are expressly resolved for the core proposal above; approval must identify any extensions before schema creation.
2. **Content fidelity:** DOM rewriting and rich editors may strip bilingual attributes/code/classes. Preserve source fragments, configure sanitizer and fail import verification for unexplained loss.
3. **Legacy behavior vs DE:** original i18n handles only EN/FA. The proposed separate adapter needs regression testing; do not modify original JS or pretend DE already exists.
4. **Offline privacy/staleness:** original worker was written for a public static site. Targeted exclusions in the new public copy are required; public offline caching remains, with documented stale-data limits.
5. **SEO routing:** physical source HTML under public would bypass redirects. Public allowlist and one canonical route prevent this.
6. **Runtime availability:** audit found no PHP/Composer/MySQL on PATH; official runtime lookup attempts timed out. Installation/tests remain pending until a verified environment is available; no unrun test is a pass.
7. **Framework lifecycle:** the requested Laravel major is outside published security support. Record dependency advisories and production risk; do not silently override user choice.
8. **Rollback after live requests:** rolling back files is not permission to discard new database submissions or uploads. Preserve both before any recovery action.

### Approval and completion gates

- Audit: approved by the user.
- Planning: this document plus the complete content/URL mapping are prepared for review.
- Implementation: **awaiting approval of this plan**, including core schema/scope, article redirects, additive locale integration and cache-safe preservation of offline behavior. Earlier “proceed” text is superseded by the final “Do NOT start coding yet” instruction.
- After approval: complete framework → schema/models/policies → importer → Blade/SEO → Filament/forms → PWA/deployment → tests/reports.
- Delivery reports: `implementation-report.md` and `docs/final-report.md` with actual added/changed files, migration inventory, install/deployment commands, verification evidence and remaining blockers; detailed results in `docs/testing-report.md`. Do not create a false completion report during planning.

No installation, application code changes, migrations, import, frontend conversion or deployment are authorized by preparation of this document alone.
