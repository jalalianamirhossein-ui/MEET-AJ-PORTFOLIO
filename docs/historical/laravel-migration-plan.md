# Meet AJ Laravel migration plan

> **SUPERSEDED (HISTORICAL).** Planning document retained as history. Obsolete targets in this file: Laravel 11, PHP 8.2, Filament 3, public `/de`, `pages`, `contact_requests`. Current status: [PROJECT-STATUS.md](../current/PROJECT-STATUS.md). Decision record: [architecture-decision-record.md](../decisions/ADR/ADR-001-laravel-13-filament-5-stack.md).

Date: 2026-09-14. Status: **Superseded.**

This plan incorporates the expanded requirements for Pages, Users, separate EN/FA/DE content, `contact_requests`, admin notifications and offline operation. It supersedes the preliminary target-schema and network-only service-worker proposals in the earlier audit. The source audit remains a record of the original website.

## 1. Baseline, constraints and approval gate

- Branch: `feature/laravel-migration`, created from `main` at `a9b23d5`.
- Audit: `docs/current-site-analysis.md`; full inventory: `docs/current-site-inventory.json`.
- All 451 original files are protected by SHA-256 records in `docs/baseline-files.json`. The pre-existing 19-line CSS change is separately saved in `docs/baseline-main-css.patch`.
- Preserve all existing accessibility, responsive, navigation, image-loading, form and SEO improvements. No redesign, replacement template or CSS rewrite.
- Add Laravel 11, PHP 8.2+, MySQL/MariaDB, Blade and Filament. File-backed cache/sessions and synchronous execution; no Docker, Redis, Supervisor, background workers or queue daemon.
- Only Laravel `public/` is web-accessible. Keep original files in place outside that directory.
- Before the latest schema-approval instruction arrived, an additive, incomplete Laravel scaffold was created under the earlier authorization. It contains configuration, an entry point and a provisional worker; no models, database migrations, import, frontend conversion or database writes have been performed. It is not a runnable CMS. Its provisional worker will be replaced with the offline design below after approval.
- **Do not proceed with application implementation, database migrations or import until the user approves the proposed schema in section 7.** This is the user's explicit gate, not an inferred approval requirement.

## 2. Existing routes and HTML-to-Laravel mapping

The primary, unprefixed URLs are stable English/default routes. Current mixed EN/FA source text and SEO values must be preserved during extraction; unprefixed routes must not change language based on cookies or crawler headers. Dedicated `/fa` and `/de` routes provide deterministic, crawlable language variants.

| Existing file / URL | Laravel route and controller | Blade mapping / response |
| --- | --- | --- |
| `index.html`; `/` | GET `/` → `PageController::home` | `pages/home.blade.php`; original sections and database article grid |
| `/index.html` | GET `/index.html` | 301 `/`; browser retains fragment identifiers |
| `/#hero`, `/#about`, `/#stats`, `/#skills`, `/#resume`, `/#services`, `/#portfolio`, `/#testimonials`, `/#contact` | Same homepage | Preserve every ID; no server-side fragment routes |
| `services/network-design.html` | GET `/services/network-design.html` → `PageController::service` | `services/network-design.blade.php` + matching page row |
| `services/system-administration.html` | GET `/services/system-administration.html` → `PageController::service` | `services/system-administration.blade.php` + matching page row |
| `services/devops-automation.html` | GET `/services/devops-automation.html` → `PageController::service` | `services/devops-automation.blade.php` + matching page row |
| `services/monitoring-security.html` | GET `/services/monitoring-security.html` → `PageController::service` | `services/monitoring-security.blade.php` + matching page row |
| `services/virtualization-solutions.html` | GET `/services/virtualization-solutions.html` → `PageController::service` | `services/virtualization-solutions.blade.php` + matching page row |
| `services/technical-consulting.html` | GET `/services/technical-consulting.html` → `PageController::service` | `services/technical-consulting.blade.php` + matching page row |
| 23 `articles/*.html` files | GET `/articles/{slug}.html` → legacy redirect resolver | 301 to the corresponding published article; exact map below |
| No standalone listing; current `/#portfolio` | GET `/articles` → `ArticleController::index` | `articles/index.blade.php`; reuse portfolio cards/filter styles with pagination |
| Article destinations | GET `/articles/{slug}` → `ArticleController::show` | `articles/show.blade.php`; DB content with preserved presentation |
| POST `/forms/contact.php` | `ContactRequestController::store` | Save `contact_requests`, return literal `OK` on success |
| GET `/forms/get-csrf-token.php` | `ContactRequestController::token` | JSON `{ "token": "..." }`, session-backed Laravel CSRF, no-store |
| `/sitemap.xml` | `SitemapController` | Dynamic XML; no physical public sitemap shadowing Laravel |
| `/robots.txt` | `RobotsController` | Preserve public indexing; add admin/form restrictions and sitemap URL |
| `/manifest.json`, `/sw.js` | Public files | Preserve PWA identity; replace unsafe cache policy |
| `/assets/**` | Public static assets | Same paths, copied byte-for-byte initially |
| `/docs/netbox_installation_guide_v2.pdf` | Public PDF copy | Existing download URL preserved |
| `/preloader.html`, `/preloader.css`, `/partials/lang-toggle.html` | Public copies for compatibility | Retain demo/partial URLs, excluded from sitemap; do not expose PHP/source configuration |
| No existing CMS | GET `/admin`, `/admin/login` | Filament panel with Laravel session authentication |
| New custom pages | GET `/pages/{slug}` | `pages/show.blade.php`; constrained CMS page namespace |

Locale equivalents of public content routes: `/fa`, `/de`, `/{locale}/articles`, `/{locale}/articles/{slug}`, `/{locale}/services/{slug}.html`, `/{locale}/pages/{slug}`. Locale is constrained to `fa|de`; English uses the established unprefixed URLs. Explicit `/en/...` aliases may 301 to the corresponding unprefixed URL. Unknown locales and unknown content return 404, never arbitrary view/file lookup. Contact endpoints and admin paths remain unprefixed.

Preserve original root helper/example files in the repository. Never publish the three legacy PHP form scripts: Laravel compatibility routes replace their execution.

### Complete article route map

Every row is a permanent redirect, with article visibility checked before serving content. Query parameters may be retained; fragments are handled by the browser. Existing content section IDs must survive conversion.

| Original URL | Canonical article URL |
| --- | --- |
| `/articles/creating-a-bootable-usb.html` | `/articles/creating-a-bootable-usb` |
| `/articles/downgrade-mikrotik-routeros-firmware-safely.html` | `/articles/downgrade-mikrotik-routeros-firmware-safely` |
| `/articles/enable-ssh-linux-complete-guide.html` | `/articles/enable-ssh-linux-complete-guide` |
| `/articles/http-vs-https-ssl-certificate-impact.html` | `/articles/http-vs-https-ssl-certificate-impact` |
| `/articles/imap-vs-pop3-email-protocol-comparison.html` | `/articles/imap-vs-pop3-email-protocol-comparison` |
| `/articles/install-dfs-server-windows-server.html` | `/articles/install-dfs-server-windows-server` |
| `/articles/install-mikrotik-chr-vmware-workstation.html` | `/articles/install-mikrotik-chr-vmware-workstation` |
| `/articles/install-vmware-esxi-vmware-workstation-vmcisr.html` | `/articles/install-vmware-esxi-vmware-workstation-vmcisr` |
| `/articles/linux-cli-common-commands.html` | `/articles/linux-cli-common-commands` |
| `/articles/linux-security-account-access-management.html` | `/articles/linux-security-account-access-management` |
| `/articles/mikrotik-block-port-scanners.html` | `/articles/mikrotik-block-port-scanners` |
| `/articles/mikrotik-block-website.html` | `/articles/mikrotik-block-website` |
| `/articles/mikrotik-openvpn-setup-v7.html` | `/articles/mikrotik-openvpn-setup-v7` |
| `/articles/mikrotik-unequal-dual-wan-load-balancing-ecmp.html` | `/articles/mikrotik-unequal-dual-wan-load-balancing-ecmp` |
| `/articles/nginx-installation-configuration-ubuntu.html` | `/articles/nginx-installation-configuration-ubuntu` |
| `/articles/set-static-ip-ubuntu-server-netplan.html` | `/articles/set-static-ip-ubuntu-server-netplan` |
| `/articles/sql-server-automatic-backup-job.html` | `/articles/sql-server-automatic-backup-job` |
| `/articles/ubuntu-date-time-settings.html` | `/articles/ubuntu-date-time-settings` |
| `/articles/vmware-esxi-8-installation-basic-configuration.html` | `/articles/vmware-esxi-8-installation-basic-configuration` |
| `/articles/vsphere-standard-switch-vs-distributed-switch.html` | `/articles/vsphere-standard-switch-vs-distributed-switch` |
| `/articles/windows-cmd-common-network-commands.html` | `/articles/windows-cmd-common-network-commands` |
| `/articles/windows-hardware-info-cmd-vs-dxdiag.html` | `/articles/windows-hardware-info-cmd-vs-dxdiag` |
| `/articles/windows-password-reset-secure-access-recovery.html` | `/articles/windows-password-reset-secure-access-recovery` |

## 3. Frontend and article migration strategy

1. Save baseline screenshots at desktop and mobile sizes for homepage, representative short/long articles and all six services before conversion. Compare against Laravel renders with matching locale and viewport.
2. Use `resources/views/layouts/app.blade.php` with explicit slots/sections for existing page-family head tags, body classes, styles and scripts. Extract `components/header.blade.php`, `footer.blade.php`, `navbar.blade.php` and `language-switcher.blade.php` from the actual source markup. Preserve article-specific navigation/TOCs and service-specific wrappers.
3. Copy allowlisted assets into `public/assets` without altering originals. Keep stylesheet order, version parameters, class names, responsive rules, breakpoints, IDs, animation attributes and image dimensions. Reuse existing JavaScript; add a narrow CMS locale adapter where three-language server routing requires it.
4. Write an explicit Artisan import command with dry-run mode and a transaction. Parse HTML using a DOM parser; extract article title, excerpt, content, image, category, known publication date, all SEO metadata and presentation context. Never execute or compile imported HTML as Blade/PHP.
5. Extract translated strings from `data-en` and `data-fa` into separate article/page rows. Keep original code blocks and layout attributes. Keep English original visible text/SEO where no reliable translated source is provided; record missing translations in an import report.
6. Seed the 23 original articles, their available Persian counterparts, homepage and all service content. Articles with incomplete translations remain draft in that locale; do not publish a page in one language under another language's URL. Original English/default URLs remain available. No existing German article text is present, so German content starts as editable drafts rather than invented translations.
7. Use rich editing for ordinary new content; preserve an HTML/source editor for imported complex article/page markup, with a preview and server-side HTML sanitization. Allow required classes, IDs, code blocks, data translation attributes and safe links, while removing scripts, event handlers and unsafe protocols from editable HTML. Keep vetted page JavaScript in source-controlled views/assets.
8. Preserve existing card text, optimized thumbnails, filter categories, related links and article ordering using `presentation` metadata and `sort_order`. All displayed cards query the database, including homepage `/#portfolio`; newly published content becomes visible without editing HTML.
9. Import is idempotent, keyed by language/slug; existing CMS records are skipped by default. Keep original-path redirects and reject slug collisions with historical routes. Never reimport over edited content during normal deployment.
10. New publication, editing, deletion and language filtering must affect homepage/listings/details/sitemap consistently. Article publication requires `status=published` and `published_at <= now()`; scheduled visibility uses request-time queries, with no worker or required scheduler.

## 4. Multilingual strategy

- Allowed language values: `en`, `fa`, `de`. Laravel language middleware derives language from the explicit route, sets application locale, and supplies `lang`/`dir` to Blade. Persian is RTL; English and German are LTR.
- Stable unprefixed URLs always use the default content record. Never vary canonical page language on cookies, localStorage or `Accept-Language`. This prevents cache and SEO inconsistencies.
- Separate content rows by `language`, with a shared `translation_key` UUID connecting versions of the same article, page or category. Unique `(language, slug)` permits the same slug in different languages; unique `(translation_key, language)` prevents duplicate translations.
- Language switching navigates to the published matching translation. Preserve the existing toggle styling and placement; extend its behavior to three languages without redesigning navigation. Where an article translation does not exist, display unavailable status and offer that language's article index, rather than silently serving another language under its URL.
- Preserve existing bilingual source attributes during extraction and import. The CMS adapter must prevent the old two-language initialization from replacing the explicitly selected German language or a server-rendered record with stale browser preference.
- German UI chrome/validation messages can be supplied as application translations. German article/service prose requires actual content and starts as drafts. No `hreflang=de` entry until corresponding content is published.
- Locale-aware links, pagination, canonical tags and sitemap entries are generated consistently. Content language and category language must match, enforced by server validation; translation groups must retain one content family.

## 5. SEO preservation strategy

- Keep every original public page reachable. Preserve six service `.html` URLs exactly; permanently redirect the 23 article `.html` URLs to clean slugs. `/index.html` redirects to the existing `/` canonical.
- Preserve original title, description, OpenGraph/Twitter text, keywords and known structured data in the import. Replace only obsolete canonical/article URLs and image paths with correct absolute public URLs.
- Store nullable `canonical_url`; blank derives the self-canonical from the route. Validate explicit canonicals as same-origin, locale-correct canonical content routes without fragments/query strings; prevent accidental canonicalization to drafts or redirect paths. Off-site syndication is outside this plan.
- Generate Article JSON-LD from real article data, retaining useful original schema fields in `seo_data`. Escape JSON safely for embedding in HTML. Preserve Person/WebSite/BreadcrumbList and Service schema on corresponding pages.
- Six existing articles have Article JSON-LD; generate schema for the remaining 17. Normalize relative OG/Twitter/schema image URLs. Preserve known publication dates; document fallback dates where source contains none.
- Add reciprocal `hreflang` only for published translations, with `x-default` pointing to the default version where it exists.
- Dynamic `/sitemap.xml` contains only published, self-canonical pages/articles, home, services and language variants. Omit admin, drafts, future content, redirects, example pages and duplicate URLs. Use modification timestamps from content, not each request's time.
- Preserve robots allow/index intent and sitemap declaration; disallow admin, Livewire and submission endpoints. Authentication protects private data; robots directives are not access controls.
- On later article slug changes, save previous complete paths and resolve all historical paths directly to the current article URL. Deleted content returns 404/410; do not redirect unrelated deleted pages to the homepage.

## 6. Contact, authorization and PWA behavior

### Contact and admin

- Replace the legacy PHP endpoint with Laravel controller, FormRequest validation, CSRF, honeypot and per-IP/session throttling. Accept the existing `csrf_token` field through Laravel CSRF middleware; do not exempt these routes.
- Preserve seven form UIs and their fetch contracts. Persist name/email/phone/message plus existing subject (including service quote context), then return `OK`. Validation failures return readable messages compatible with existing handlers.
- Save request and admin database notifications synchronously in one transaction. Filament's notification bell/polling provides the admin notification panel without a queue worker. Submitted personal details remain in the protected request record; notification text can be limited to a request reference.
- Filament modules: Articles, Categories, Pages, Contact Requests and Users. Admin has full access; editor has content management access to Articles/Categories/Pages only. Policies protect direct resource/Livewire operations as well as navigation. Editors cannot read contacts/notifications, administer accounts or elevate their own role.
- No public registration. Create the first admin through an interactive Artisan command with a hashed password; never seed a default password. Protect against deleting/demoting the last administrator.
- Restrict uploads to verified JPEG/PNG/WebP, size limits and generated filenames. Do not allow executable or SVG uploads. Keep temporary uploads private and disable script execution in public upload storage.

### PWA and offline capability

- Preserve manifest identity, icons, start URL, display mode and the `/sw.js` registration scope. Do not retire offline capability.
- Version the replacement worker and clear only legacy `meet-aj-*` caches during activation, then claim clients. Cache a small offline shell/fallback and allowlisted same-origin static design assets; load larger media on demand with bounded caches.
- Public, anonymous article/service/home navigations use network-first caching with an offline fallback. Cache only successful HTML responses explicitly marked cacheable by Laravel; distinguish locale and full URL. Never cache authenticated pages, `/admin`, `/livewire`, CSRF endpoints, contact submissions, personal data or private uploads. A `no-store` response is never cached.
- When the network returns 404/410 or a page is unpublished, evict the previous cache entry and honor that response. A short bounded document TTL and cache version changes limit stale offline articles. A disconnected device cannot instantly learn that content was unpublished; show a clear offline/stale indicator and revalidate when online.
- Forms are online-only: show an offline message and retain in-page input for retry. Do not queue or store personal submissions for background sync.
- Limit scope of exclusions/caching by path and response headers; never treat all GET responses as safe. Test a previously installed legacy worker upgrade as well as a fresh install.

## 7. Proposed database schema — approval requested

All tables use MySQL/MariaDB InnoDB and utf8mb4. IDs are unsigned big integers unless stated. Dates are stored consistently and displayed with the configured `Asia/Tehran` timezone. Use string fields with explicit application validation for status/language/role, keeping SQLite feature tests portable. Default content status is `draft`.

### users

| Column | Type / constraints |
| --- | --- |
| id | Primary key |
| name | varchar(255), required |
| email | varchar(255), unique, required |
| email_verified_at | nullable timestamp |
| password | varchar(255), hashed only |
| role | varchar(20), `admin` or `editor`; default `editor` |
| remember_token | nullable varchar(100) |
| created_at, updated_at | timestamps |

### articles

| Column | Type / constraints |
| --- | --- |
| id | Primary key |
| translation_key | UUID; same across language variants |
| title | varchar(255), required |
| slug | varchar(180), required; unique with language |
| language | varchar(2), `en`, `fa`, `de`; indexed |
| excerpt | nullable text |
| content | longText, sanitized HTML |
| featured_image | nullable varchar(2048), local asset/upload path |
| category_id | nullable FK → categories; SET NULL on category deletion |
| meta_title | nullable varchar(255) |
| meta_description | nullable text, preserving source descriptions |
| canonical_url | nullable varchar(2048), derived if blank |
| seo_data | nullable JSON, original OG/Twitter/schema extras |
| presentation | nullable JSON, layout variant, translations and legacy card/source context; not executable code |
| sort_order | unsigned integer, default 0; preserves current grid order |
| status | varchar(20), `draft` or `published` |
| published_at | nullable timestamp |
| created_at, updated_at | timestamps |

Constraints/indexes: unique `(language, slug)`; unique `(translation_key, language)`; index `(language, status, published_at)`; category FK. A published article requires a publication date. Validate category language against article language. Reserve colliding historical slugs/paths before save.

### categories

| Column | Type / constraints |
| --- | --- |
| id | Primary key |
| translation_key | UUID, connects language variants |
| name | varchar(255), required |
| slug | varchar(180), required |
| language | varchar(2), required |
| created_at, updated_at | timestamps |

Unique `(language, slug)` and `(translation_key, language)`. Preserve existing grid filters: Microsoft, Linux, MikroTik, VMware and Others. Category deletion does not delete articles.

### pages

| Column | Type / constraints |
| --- | --- |
| id | Primary key |
| translation_key | UUID, connects translations |
| title | varchar(255), required |
| slug | varchar(180), required; `home`, six service slugs, or custom page slug |
| language | varchar(2), required |
| content | longText, sanitized editable page content |
| template | varchar(50), allowlisted `home`, `service`, `page`; never an arbitrary file path |
| meta_title | nullable varchar(255) |
| meta_description | nullable text |
| canonical_url | nullable varchar(2048) |
| seo_data | nullable JSON for original metadata/schema |
| presentation | nullable JSON for preserved page-family layout/context |
| status | varchar(20), `draft` or `published` |
| published_at | nullable timestamp |
| created_at, updated_at | timestamps |

Unique `(language, slug)` and `(translation_key, language)`; publication index. Template/route identity of the original homepage and six service pages is protected from accidental slug changes/deletion in the admin UI. Custom pages use `/pages/{slug}` to avoid reserved application routes. Never compile database content as Blade.

### contact_requests

| Column | Type / constraints |
| --- | --- |
| id | Primary key |
| name | varchar(100), required |
| email | varchar(255), required |
| phone | nullable varchar(40) |
| subject | nullable varchar(255), preserves existing form/quote context |
| message | text, required |
| status | varchar(20), `new`, `in_progress`, `resolved`, `spam`; default `new` |
| created_at, updated_at | timestamps |

Index `(status, created_at)`; searchable name/email/phone/subject/message in admin. `updated_at` records status changes. This replaces the earlier proposed `requests` table; no duplicate table will be created.

### article_redirects

| Column | Type / constraints |
| --- | --- |
| id | Primary key |
| old_path | varchar(255), globally unique site-relative path |
| article_id | FK → articles; cascade on deletion |
| created_at, updated_at | timestamps |

Stores both original `.html` paths and later changed clean/localized paths. Resolves directly to the current published record; cannot contain external redirect targets.

### notifications and framework support

- `notifications`: Laravel UUID primary key, notification type, polymorphic notifiable type/ID with index, text JSON data, nullable read_at, timestamps. Only administrators receive contact notifications; no queues required.
- `password_reset_tokens`: email primary key, hashed token, nullable created_at. Supports account recovery if enabled later; no automatic promise of email delivery without configured mail transport.
- `sessions`: string primary key, nullable indexed user_id, nullable IP/user-agent, longText payload, indexed last_activity. Available for database sessions if the host chooses; default uses files.
- `migrations`: Laravel-managed migration ledger.
- No Redis/cache/jobs/failed_jobs tables or queue workers required by the chosen runtime configuration.

### Decisions included in approval

Approval covers the five requested domain tables plus: translation UUIDs, composite language/slug uniqueness, nullable SEO/presentation JSON, stable ordering, subject and updated_at for requests, article redirect history, and synchronous admin database notifications. These additions preserve existing content, SEO and contact behavior. Defaults and relations above are part of the proposal.

## 8. Migration risks and mitigations

| Risk | Mitigation / verification |
| --- | --- |
| Original files or user CSS edit lost | Additive development; baseline hash check and saved diff; no overwrites |
| Static files bypass Laravel routes | `public/` document root, no legacy article/service HTML in public, dynamic sitemap only |
| Appearance/accessibility regression | Shared original markup/CSS, preserve family differences; compare mobile/desktop screenshots and interactions |
| Translation loss or false-language pages | DOM extraction and separate records; report missing strings; incomplete/German content stays draft |
| Old JS overrides server language | Narrow locale adapter and explicit route precedence; test all three languages and browser preferences |
| SEO duplication/redirect chains | Deterministic locale URLs, one canonical, direct historical redirects, published-only sitemap/hreflang |
| Rich editor alters code/complex layout | Source editing option, output sanitizer configured for safe layout attributes; import round-trip tests |
| Import overwrites later CMS edits | Transactional dry-run/import and skip-existing default; report record counts and source hashes |
| Editors access contacts or users | Server policies and direct Livewire authorization tests; no role changes through content forms |
| Stored XSS or executable uploads | HTML sanitization, escaped SEO/JSON, restrictive image validation and server upload rules |
| CSRF/form contract regression | Legacy-token-name support without exemption; real HTTP/session CSRF tests; preserve literal `OK` |
| Notification write fails after partial contact save | Synchronous transaction and explicit failure reporting; no phantom success |
| PWA leaks private pages or serves stale publications | Explicit public-cache eligibility, private-path exclusions, bounded TTL, offline indication and worker-upgrade tests |
| Shared-host permissions/configuration fail | File-backed runtime, explicit storage/cache permissions, public-root guide, no persistent processes |
| Framework support lifecycle | Laravel 11 security support ended 2026-03-12 per official releases documentation; keep requested major but record as a production risk requiring a supported-version plan before launch |
| Runtime unavailable locally | PHP/Composer/MySQL not on PATH at audit; prepare verified runtime or report tests blocked honestly |

Official lifecycle reference: https://laravel.com/framework/docs/11.x/releases . Filament compatibility reference: https://filamentphp.com/docs/3.x/panels/installation . Use a compatible, dependency-audited Filament release; do not disable Composer security checks to force an unsafe install.

## 9. Implementation sequence after approval

1. Complete compatible Laravel configuration and dependency installation, record lockfile/platform requirements; retain originals untouched.
2. Implement the approved migrations/models, authentication, policies and language middleware.
3. Extract Blade layouts/components and create dry-run importer; compare imported counts, translations, URLs and source content.
4. Implement dynamic pages/articles, redirects, sanitized rendering, SEO and sitemap.
5. Implement Filament content/categories/pages/users, request management and synchronous notifications.
6. Replace PHP execution with Laravel form routes and implement cache-safe offline PWA.
7. Run the checks below; document actual passes/failures and remaining hosting-only checks.
8. Write `DEPLOYMENT.md`, `docs/testing-report.md` and `docs/final-migration-report.md`, including installation, schema, changed-file list, rollback instructions and constraints.

## 10. Validation and DirectAdmin preparation

Automated feature tests: homepage/service routes; 23 legacy redirects; database-driven listings/details; draft/future visibility; slug-history collision handling; locale isolation/translation linking; SEO/canonical/hreflang/schema and XML escaping; sitemap; import idempotence; contact validation/rate limits/notification persistence; admin login and CRUD; editor access restrictions; stored XSS and upload rejection. Validate against MySQL/MariaDB before production, even if most isolated feature tests use SQLite.

Browser checks: desktop/mobile layouts (including 320px width), navigation focus/keyboard behavior, filters, gallery, responsive hero, code-copy, service forms, EN/FA/DE switching and direction, admin login/create/edit/delete/upload, offline fallback, reconnect behavior, private-path exclusions and legacy service-worker upgrade. Record screenshots and precise limitations instead of claiming unrun checks passed.

`DEPLOYMENT.md` will cover: verified PHP extensions; upload outside public_html; document root pointed at public or a correctly adjusted public entry point; `.env` with `APP_ENV=production`, `APP_DEBUG=false`, HTTPS and secure cookies; database creation; `composer install --no-dev --optimize-autoloader`; key generation; `php artisan migrate --force`; dry-run/import command; initial admin creation; `php artisan storage:link`; `php artisan optimize`; writable storage/bootstrap cache directories without 777; synchronous notification behavior; backups and release rollback. No required cron or worker for normal CMS operation. Optional housekeeping may run via DirectAdmin cron, never as a prerequisite for publication or request delivery.

## 11. Current gate status

- [x] Current architecture audited and original file baseline recorded.
- [x] Current routes and all 23 article redirects mapped.
- [x] Proposed database design documented.
- [x] Migration risks and mitigations documented.
- [ ] User approval of section 7 database schema.
- [ ] Application implementation, import, executable tests and deployment preparation after approval.

This document is a plan, not a claim that migration or testing is complete.
