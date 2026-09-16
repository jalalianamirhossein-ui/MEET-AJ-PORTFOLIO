> **HISTORICAL.** Read-only `/docs` review snapshot (2026-09-16) taken before this documentation rebuild. Current status: [PROJECT-STATUS.md](../PROJECT-STATUS.md). Index: [DOCUMENTATION-INDEX.md](../DOCUMENTATION-INDEX.md).

# Meet AJ Portfolio — Cursor Migration Documentation Review

**Review type:** Read-only audit of `/docs` plus cross-check against the current workspace (no code or documentation fixes).  
**Review date:** 2026-09-16  
**Scope:** All files under `/docs`, including phase reports, inventories, and the baseline hash set.

Documents reviewed:

- `docs/framework-version-decision.md`
- `docs/content-migration-analysis.md`
- `docs/laravel-implementation-plan.md`
- `docs/laravel-migration-plan.md`
- `docs/current-site-analysis.md`
- `docs/current-site-inventory.json`
- `docs/baseline-files.json`
- `docs/baseline-main-css.patch`
- `docs/phases/phase-1-environment.md`
- `docs/phases/phase-2-database.md`

Cross-checked (not modified): `composer.json`, `composer.lock`, migrations, models, policies, Filament stubs, `public/`, `.env*.example`, `sitemap.xml`, `robots.txt`, legacy forms, and original `sw.js`.

---

# Executive Summary

The migration goal is sound: convert a static bilingual portfolio into a Laravel CMS without redesigning the public site, while preserving URLs, assets, forms, and SEO. The **current technical target** in `framework-version-decision.md` is correct for September 2026: **Laravel 13**, **PHP 8.4**, **Filament 5**, **MySQL/MariaDB utf8mb4**, Blade, and DirectAdmin-friendly file sessions/cache with no Redis, queues, Docker, or Node in production.

The project is **not ready for Git commit or cutover**. Planning documents disagree with each other and with the code already in the tree. Phase 1 has not actually booted Laravel (`vendor/` is absent). Phase 2 schema code exists but has never been executed against MySQL/MariaDB. Filament PHP files exist without a Filament Composer dependency. The workspace is **not a Git repository**, despite plans that name branch `feature/laravel-migration` and commit `a9b23d5`.

The strongest parts of the plan are the 23-article URL map, the core InnoDB schema (composite locale uniqueness, redirect history, publication indexes), and the importer safety rules (dry-run, source hashes, transactions, skip-existing). The highest-risk gaps are document-root/host verification, SEO cutover of `.html` article URLs, bilingual vs `/fa` duplicate content, the placeholder `public/sw.js`, and unresolved DirectAdmin constraints (PHP 8.4, `storage:link`, Composer).

**Verdict:** Keep this as a planning-plus-partial-implementation snapshot. Reconcile documents, finish a bootable Phase 1, prove Phase 2 on MariaDB, then initialize Git. Do not treat the current tree as a committable migration milestone.

---

# Current Migration Status

## Current website architecture

The live/source site is a **static HTML/CSS/JS portfolio** with:

- Homepage `index.html`, **23 article HTML files**, **6 service HTML files**, plus preloader/partial examples.
- Client-side English/Persian switching (`data-en` / `data-fa`, `i18n.js`, RTL stylesheet, localStorage). **No German article prose.**
- Local Bootstrap/vendor assets, PWA (`manifest.json`, `sw.js`), sitemap, robots.
- Seven contact/quote forms posting to `/forms/contact.php` with CSRF token JSON from `/forms/get-csrf-token.php`. Success contract is literal `OK`. Current handler **emails only**; it does not persist inquiries.
- No package manifest, database, authentication, or CMS.

`docs/current-site-analysis.md` and the inventory agree on this picture. The inventory also embeds `.claude` skill files and other non-page artifacts, so it is a whole-tree dump rather than a clean public-page catalog.

## Migration goal

Replace the static site with Laravel while:

1. Keeping original files untouched outside `public/` as rollback/source.
2. Rendering the same design from Blade + database-backed article cards.
3. Permanently redirecting `/articles/{slug}.html` → `/articles/{slug}`; keeping all six `/services/*.html` URLs.
4. Adding a Filament admin at `/admin` for articles, categories, and contact requests.
5. Preserving metadata, images, code blocks, TOCs, and EN/FA behavior.
6. Running on DirectAdmin shared hosting without workers, Redis, Docker, or a Node production runtime.

## Current completed phases

| Phase | Documented status | Actual workspace status |
| --- | --- | --- |
| Pre-migration audit | Complete (2026-09-14) | Complete. Baseline hashes and CSS patch exist. |
| Planning | Written; older plans still “awaiting approval” | Complete as writing, **not internally consistent**. |
| Framework decision | Laravel 13 / PHP 8.4 / Filament 5 | Decision is technically correct. Older plans still say Laravel 11 / PHP 8.2 / Filament 3. |
| Phase 1 environment | In progress; runtime pending | **Partial.** `composer.json` and `composer.lock` (Laravel **v13.31.0**) exist. `vendor/` is missing. App cannot boot. No MySQL/MariaDB. DirectAdmin not inspected. |
| Phase 2 database | Code written; tests pending | **Partial.** Six migrations, models, and policies exist and match the *core* schema. PHPUnit tests are written but cannot run without Composer install. No engine proof on MariaDB. |

## Pending phases

Not present as phase reports, and not implemented as a runnable application:

- Filament package installation and panel registration (PHP stubs already exist ahead of the plan).
- Legacy article importer (`articles:import-legacy`) and asset publisher.
- Blade conversion of homepage, six services, article index/show, SEO components.
- Public routes, language middleware, CSRF alias, contact persistence, sitemap/robots controllers.
- Cache-safe PWA replacement (current `public/sw.js` is the forbidden network-only worker).
- MySQL/MariaDB migrate + feature tests + DirectAdmin staging.
- `DEPLOYMENT.md`, `implementation-report.md`, `docs/testing-report.md`.

There is **no Git repository** in this workspace, so the documented branch/commit baseline cannot be verified here.

---

# Architecture Assessment

## Target stack validation

| Decision | Source of truth | Technically correct? |
| --- | --- | --- |
| Laravel **13** (`^13.0`, lockfile `v13.31.0`) | `framework-version-decision.md`, `composer.json` | **Yes.** Released 2026-03-17; security support through **2028-03-17**. PHP 8.3–8.5. Laravel 11 security ended 2026-03-12, so remaining on 11 would be unsupported. |
| PHP **8.4** | Decision + `composer.json` `^8.4` / platform `8.4.0` | **Yes** for Laravel 13. Stricter than Laravel’s 8.3 floor. Hosts with only 8.3 cannot install this lockfile. |
| MySQL/MariaDB InnoDB utf8mb4 | Implementation plan + `config/database.php` | **Yes** for shared hosting. SQLite is correctly limited to tests. |
| Filament **5** (`^5.0`) | Decision document | **Yes, with caveats.** PHP 8.2+, Laravel 11.28+, Livewire 4. Recent 5.x added Laravel 13 support; early 5.0–5.3.x did not. Must resolve a **current** 5.x (for example 5.8.x), not a pinned 5.0.0. Windows PowerShell can strip `^` from Composer constraints. |
| Blade + existing vendor CSS/JS | All plans | **Yes.** No frontend rebuild is required for the public site. |
| File sessions/cache, sync queue, no Redis/workers | Implementation plan | **Yes** for DirectAdmin. |
| No Node in **production** | Decision | **Mostly yes** if the default Filament compiled assets are used. Filament 5 lists Tailwind 4.1+ as a requirement; that applies to custom theming, not necessarily to a stock panel. Production Node is still correctly avoided. |
| DirectAdmin / `public/` document root | All plans | **Correct design.** Host compatibility is **unverified**. |

Storage vs display timezones are implemented correctly in config: `config/app.php` hard-codes **UTC**; `config/cms.php` display timezone defaults to `Asia/Tehran`.

## Architecture problems

1. **Two incompatible plans are still marked current.** `laravel-migration-plan.md` and `laravel-implementation-plan.md` still specify Laravel 11, PHP 8.2+, Filament 3, and (in the older plan) `pages`, `contact_requests`, Users resource, and `/de` public routes. `framework-version-decision.md` and Phase 1 supersede that stack. Anyone implementing from the wrong file will build the wrong system.
2. **Implementation started while plans still say “do not start coding.”** That is a governance failure, not just a wording issue.
3. **Filament code is ahead of Composer.** `app/Filament/**` and `AdminPanelProvider` exist, but `filament/filament` is **not** in `composer.json` / `composer.lock`, and `bootstrap/providers.php` registers only `AppServiceProvider`. Those classes cannot autoload.
4. **Laravel 13 CSRF middleware is `PreventRequestForgery`**, not `ValidateCsrfToken` as named in the implementation plan. The Filament stub already uses the new class; public forms still have no middleware/alias for legacy `csrf_token`.
5. **Health route `/up`** is enabled in `bootstrap/app.php` with no documented robots/auth policy.
6. **Frontend approach is coherent** only if original HTML is *copied* into Blade and originals stay outside `public/`. No controllers or views exist yet, so this remains design-only.

**Overall:** The *selected* stack is the right one. The *documented* stack is not a single source of truth. Hosting compatibility is assumed, not proven.

---

# SEO Assessment

## Existing URL inventory

The 23 article mappings are **identical** across `current-site-analysis.md`, `content-migration-analysis.md`, and `laravel-migration-plan.md`. Service URLs are preserved exactly. `/index.html` → `/` matches the current homepage canonical intent.

Current `sitemap.xml` contains:

- `https://meetaj.ir/` **and** `https://meetaj.ir/index.html` (duplicate)
- All 23 article **`.html`** URLs
- All 6 service `.html` URLs

Current `robots.txt` allows everything and only declares the sitemap. Admin/form disallow rules do not exist yet.

## Article redirects

The planned **301** from `/articles/{slug}.html` to `/articles/{slug}` is the right mechanism if clean URLs become canonical. Requirements that are specified and correct:

- Register legacy redirect routes before clean-slug routes.
- One hop; no chains; no homepage catch-all.
- Query strings retained; fragments are browser-only.
- Later slug edits write `article_redirects.old_path` and resolve directly to the current URL.
- Unpublished/future/unknown slugs return 404.

## Canonical strategy

Plan: nullable `canonical_url`; blank means self-canonical; overrides must be same-origin published routes. Implementation currently **rejects any canonical that is not exactly `publicUrl()`**, so the column cannot store a true override. Imported source canonicals all point at `.html` URLs and **must be rewritten** during import or the model will throw.

Unprefixed URLs must not vary by cookie/`Accept-Language`. That part is correct and necessary.

## Sitemap plan

Dynamic `/sitemap.xml` with published canonicals only, content modification dates, no redirects/drafts/admin/examples, is correct. It must **stop listing `/index.html` and `.html` article URLs** at cutover.

## Metadata preservation

Inventory supports the documented counts:

- 23/23 articles have canonical, description, and OpenGraph title.
- **6** have Article JSON-LD (`enable-ssh-linux-complete-guide`, `mikrotik-block-website`, `mikrotik-openvpn-setup-v7`, `mikrotik-unequal-dual-wan-load-balancing-ecmp`, `nginx-installation-configuration-ubuntu`, `windows-cmd-common-network-commands`).
- **17** need generated Article schema.
- Homepage: Person / WebSite / BreadcrumbList.
- Services: Service schema.
- Several OG/schema images are relative (`../assets/img/...`) and must become absolute.
- Do not invent `datePublished` from sitemap `lastmod` (several lastmods are 2026-09-01).

Twitter cards in source are `summary`, not `summary_large_image`. Preserving them is faithful; changing them is a separate SEO choice.

## SEO risks

1. **Equity transfer depends on a clean 301 + canonical + sitemap swap on the same day.** If physical `.html` files remain under `public/`, Apache/Nginx will serve them as files and **bypass Laravel redirects** (`!-f` in `public/.htaccess`). This is the single most important cutover risk and is already called out in the plans.
2. **Internal links still target `.html`.** Until Blade rewrites them, every in-site click is an extra 301. Crawlers will follow, but it is sloppy and can create short chains with the trailing-slash rule in `public/.htaccess`.
3. **Bilingual page vs `/fa/articles/{slug}`.** Today Persian lives on the same URL via `data-fa`. Publishing a second FA row on a prefixed URL without leaving the unprefixed body bilingual (or vice versa) can create **duplicate Persian content** and conflicting hreflang.
4. **German hreflang / `/de` routes** appear in older plans; the approved decision forbids public DE until real translations exist. Emitting `hreflang=de` for drafts would be a quality error.
5. **Deleted articles cascade-delete redirects**, so old URLs become 404. There is no 410 policy. Accidental delete is a ranking loss.
6. **New `/articles` index** is a new crawlable URL. It needs canonical, nav, and sitemap inclusion, and must not duplicate `/#portfolio` as a second homepage-like result.
7. **PWA cache** can keep stale canonical HTML after unpublished/URL changes. The replacement worker is required before cutover.
8. No documented Search Console URL mapping, coverage monitoring, or HTTP→HTTPS / www vs apex policy.

---

# Database Assessment

## Planned vs implemented core tables

Phase 2 and the implementation-plan *core* list match the migrations on disk:

| Table | Present | Notes |
| --- | --- | --- |
| `users` | Yes | `role` varchar, default `editor`; unique email; hashed password. No DB check constraint on role. |
| `password_reset_tokens` | Yes | Bundled with users migration. No public reset feature. |
| `categories` | Yes | Unique `(language, slug)` and `(translation_key, language)`. |
| `articles` | Yes | Unique locale/slug and translation pair; `seo_data` / `presentation` JSON; publication indexes. |
| `article_redirects` | Yes | Globally unique `old_path`; FK cascade on article delete. |
| `requests` | Yes | Subject + phone nullable; `(status, created_at)` index. |
| `sessions` | Yes | FK `user_id` nullOnDelete; `last_activity` indexed. Default runtime remains **file** sessions. |
| `pages` | No | Older plan requires it; core plan defers it. **Correct omission** if core scope is approved. |
| `notifications` | No | Optional extension. Core uses a request-count widget instead. |
| `contact_requests` | No | Older plan rename; **must not** also be created. |

## Relationships

- Category hasMany Articles; Article belongsTo Category; `nullOnDelete` — correct; articles survive category deletion.
- Article hasMany Redirects; cascade delete — consistent with code, weak for SEO.
- Matching category/article language is enforced in model validation, **not** in the database. Filament category select is not locale-filtered in the form stub.
- German publish is blocked in `Article::saving`. Public scope is EN/FA, published, and due. This matches the language decision.

## Indexes and unique constraints

Sound for this dataset:

- `(language, slug)` uniqueness allows the same slug in EN and FA.
- `(translation_key, language)` prevents duplicate translations.
- `(language, status, published_at)` and `(status, published_at)` support listing/sitemap.
- Redirect `old_path` uniqueness is the right collision key (full path, not bare slug).

Gaps:

- No fulltext indexes on `requests` (acceptable at low volume; admin `LIKE` search will degrade later).
- No unique constraint / trigger preventing a second `admin` from being removed (last-admin safeguard is only a future UserResource note).
- `translation_key` is not independently indexed beyond the unique pair (fine at this scale).
- `articles.featured_image` is one column; thumbnail vs gallery must live in `presentation` JSON or they will collapse.

## Future scalability

For tens of articles and modest contact volume this schema is adequate. It will not scale into a large multilingual publishing platform without:

- Soft deletes or a dedicated 410 table
- Media library (not a single featured-image path)
- Optional `pages` if homepage/services must be CMS-edited
- Search indexes
- Clear split between legacy bilingual HTML and true per-locale rows

`App\Models\Request` vs `Illuminate\Http\Request` is a real collision. The plan to alias HTTP requests is necessary and not yet implemented in controllers (there are no controllers).

### Importer vs schema safety

The **designed** importer is safe: 23-file manifest, SHA-256, dry-run, DOM identify / source-slice extract, sanitizer fail-on-loss, transactional redirects, skip-existing, application lock. It is **not implemented**.

The **Article model is not importer-safe as written**: `$fillable` omits `seo_data`, `presentation`, and `sort_order`. `Article::create()` will drop the fields the migration exists to preserve. `canonical_url` validation also fights raw `.html` source canonicals.

---

# Deployment Assessment

DirectAdmin shared hosting is a valid target for this architecture **if** the host can satisfy Laravel 13.

## Environment compatibility

| Requirement | Plan | Risk |
| --- | --- | --- |
| PHP 8.4 + extensions (mbstring, intl, DOM/XML, fileinfo, PDO MySQL, ctype, curl, openssl, tokenizer, session) | Documented | **Unverified on the host.** `composer.json` will refuse PHP 8.3. |
| CLI PHP = FPM PHP | Documented | Common DirectAdmin failure mode. |
| Composer | `composer install --no-dev` on server **or** upload vendor | Many shared hosts have no Composer, low memory, or disabled `proc_open`. No vendor-commit/upload fallback is specified as the primary path. |
| Document root = `public/` | Documented as a prerequisite | DirectAdmin often hard-locks `public_html`. If the domain points at the repo root, `.env`, `app/`, original `forms/*.php`, and HTML will be web-reachable and will **shadow** Laravel routes. |
| Storage / bootstrap cache writable | 755/644, no 777 | Correct. Must be proven per account user. |
| `php artisan storage:link` | Required for Filament public uploads | **Symlinks are frequently disabled** on DirectAdmin. No documented copy/fallback (`php artisan storage:link` failure plan). |
| Cron | Not required for publish/forms | Correct. Optional housekeeping only. |
| HTTPS secure cookies | `.env.production.example` sets `SESSION_SECURE_COOKIE=true` | Correct, assuming HTTPS is forced at the vhost. |
| PWA `public/sw.js` | Must preserve offline with private-route exclusions | Current `public/sw.js` **deletes `meet-aj-*` caches and caches nothing**. Deploying it as document root **removes offline support**. |

`vendor/` is not in the workspace. Phase 1 download attempts already saw TLS/timeout issues. That is a local environment problem; it also means **no verified `composer check-platform-reqs`**.

`.gitignore` excludes `/public/assets/`, `/public/docs/`, `/public/manifest.json`. Production therefore **depends on an unpublished Artisan publish command**. Deploying Git contents alone would ship a CSS-less site.

## Likely deployment problems

1. PHP version selector not on 8.4.
2. `public_html` cannot be remapped; operators copy the repo into it.
3. `storage:link` fails; uploaded images 404.
4. Composer not available; lockfile unused; ad-hoc vendor upload with wrong PHP platform.
5. Physical `sitemap.xml` / article HTML accidentally copied into `public/`.
6. File session/cache directories not writable; forms and admin login fail.
7. Mail transport unset; current site emails, Laravel plan stores DB first. Behavior change must be explicit.
8. Preliminary `public/sw.js` goes live.

---

# Security Assessment

## Planned controls that are appropriate

- No public registration; first user via interactive `cms:create-user`; min 12-char password; hashed via Laravel.
- Admin vs editor policies; contacts admin-only.
- CSRF on contact routes; planned `csrf_token` **alias**, not exemption.
- Honeypot `website`.
- Per-client throttle (current production limit is **5 POSTs / hour / IP** after validation).
- HTML sanitizer; never `Blade::render` database HTML.
- Uploads: JPEG/PNG/WebP only, no SVG/executables, generated names, public disk with execution disabled.
- `APP_DEBUG=false` in production example; secrets not committed (`.env` gitignored).
- `Options -Indexes`; deny dotfiles in `public/.htaccess`.

## Gaps and weaknesses

1. **Filament `User` does not implement `FilamentUser` / `canAccessPanel()`.** Any future authenticated user could be panel-eligible depending on Filament defaults. Role is not in `$fillable` (good) but also has no last-admin lock.
2. **Policies are not proven.** `AppServiceProvider` is empty. Tests that would check Gate behavior cannot run.
3. **RequestResource displays full message/email/phone.** Editors must be denied at policy *and* resource registration. The stub has no `canViewAny` override.
4. **Legacy CSRF consume-on-use vs Laravel persistent token** is a behavior change. Existing JS should still work if the session token is stable, but this needs a real HTTP test (Laravel’s test CSRF bypass is correctly forbidden as evidence).
5. **Contact validation in Laravel is unspecified** relative to the live handler: name 2–50, email ≤100, subject **required** 5–100, message 10–1000, fake `OK` on honeypot without persistence. Column sizes are looser (`name` 100, `email` 255, `subject` nullable). Drift will either break the JS or accept data the old site rejected.
6. **FileUpload uses `disk('public')` while `.env.example` sets `FILESYSTEM_DISK=local`.** Local/private vs public mix-up can place uploads outside the web path or, worse, under a writable web path with execution enabled.
7. **Featured-image regex** allows `articles/...jpg` but not all storage layouts; it is a good allowlist if enforced on every write, including Filament mutators.
8. **`/up` health endpoint** discloses stack presence.
9. **No security headers middleware yet** (`ResponseHeaders` is only planned). Current PHP forms send nosniff / referrer-policy / HSTS; Laravel should not regress that.
10. **PWA** must never cache `/admin`, `/livewire`, tokens, or form responses. The original `sw.js` caches general GETs except forms/PHP — unsafe once `/admin` exists.
11. **Rate-limit storage** today uses `sys_get_temp_dir()`. Laravel file cache under `storage/framework/cache` is better if permissions are correct; if not, throttling fails open or closed. Current PHP fails closed (503). The plan should keep fail-closed.
12. **No 2FA**, no login throttling documented for Filament, no backup-code story. Acceptable for a one-operator CMS only if Filament’s built-in login throttle is verified.

Overall planned security is **directionally right** for a small CMS and **not implemented enough to trust**.

---

# Issues Found

## 1. Workspace is not a Git repository

**Severity:** Critical  

**Description:** Plans repeatedly cite branch `feature/laravel-migration` and `main` commit `a9b23d5`. This workspace has no `.git` directory. User environment metadata also reports the directory is not a Git repo.  

**Impact:** No verifiable baseline, no branch protection, no way to commit, diff, or roll back. SHA-256 file baselines cannot be tied to a commit.  

**Recommendation:** After reconciling docs and making Phase 1 bootable, `git init` (or clone the real remote), recreate `main` from the original static site if it still exists elsewhere, then branch. Do not invent `a9b23d5` in this tree.

## 2. Planning documents contradict the approved stack and still forbid implementation

**Severity:** Critical  

**Description:** `laravel-migration-plan.md` and `laravel-implementation-plan.md` still say Laravel 11 / PHP 8.2 / Filament 3, “awaiting approval”, and “do not start coding”. `framework-version-decision.md` and Phase 1/2 say Laravel 13 / PHP 8.4 / Filament 5 and that implementation has started. Table names (`requests` vs `contact_requests`), `pages`, Users, notifications, and public `/de` routes also disagree.  

**Impact:** Implementers, reviewers, and future Git history will not know which schema or framework is approved. Duplicate tables or an unsupported Laravel 11 deploy are plausible.  

**Recommendation:** Mark one document as the living plan. Stamp the others “superseded”. Explicitly approve: Laravel 13, PHP 8.4, Filament 5, `requests` (not `contact_requests`), no `pages` in core, DE draft-only / no public DE routes.

## 3. Phase 1 is not actually complete — Laravel cannot boot

**Severity:** Critical  

**Description:** `vendor/` is absent. `routes/web.php` has no routes. Phase 1 correctly says PHP execution, Composer audit, and application boot are pending, yet `composer.lock` already pins `laravel/framework` v13.31.0. That lockfile is not evidence of a running app.  

**Impact:** Migrations, Filament, importer, and tests cannot be executed. Any “phase complete” claim would be false.  

**Recommendation:** Install PHP 8.4 locally, `composer install`, run `php artisan --version`, `composer audit`, and `composer check-platform-reqs`. Record exact versions in the Phase 1 report as the decision document already requires.

## 4. DirectAdmin PHP 8.4, public root, and Composer are unverified

**Severity:** Critical  

**Description:** Production is specified as DirectAdmin + PHP 8.4 + document root `public/` + Composer. None of this has been inspected on `meetaj.ir` hosting. Composer platform is 8.4.0; Laravel 13 would allow 8.3, but this project would not.  

**Impact:** Cutover can fail completely (wrong PHP), or succeed dangerously (repo served from `public_html`, credentials and legacy PHP exposed, `.html` files bypassing 301s).  

**Recommendation:** Before more application work, collect host facts: PHP selector versions, CLI vs FPM, Composer presence, symlink permission, ability to point the domain at `public/`. If 8.4 is unavailable, either upgrade the host or consciously lower the platform to 8.3 (still valid for Laravel 13).

## 5. Physical files in `public/` can bypass SEO redirects

**Severity:** High  

**Description:** Plans correctly require that original article/service HTML and `sitemap.xml` never sit in the Laravel public directory. `public/.htaccess` routes only when the request is not an existing file.  

**Impact:** Google keeps indexing `.html` 200s; clean URLs never become canonical; ranking splits or drops.  

**Recommendation:** Public allowlist + publish command + a release checklist that fails if `public/articles/*.html` or `public/sitemap.xml` exist.

## 6. Bilingual unprefixed pages plus `/fa` records can duplicate Persian content

**Severity:** High  

**Description:** The live site has one URL per article with `data-en`/`data-fa`. The importer intends both to preserve bilingual HTML and to create separate language rows with `/fa/articles/{slug}`.  

**Impact:** Duplicate content, mixed canonicals, and hreflang pointing at near-copies. Client JS can also overwrite server-rendered FA/DE.  

**Recommendation:** Choose one public model before import: (A) keep unprefixed bilingual pages and do not publish FA-prefixed duplicates, or (B) split locales and strip opposite-language bodies from each URL. Document the choice in the living plan. Keep DE unpublished and out of hreflang.

## 7. `public/sw.js` is the network-only worker the plan forbids deploying

**Severity:** High  

**Description:** Original `sw.js` provides offline caching. `public/sw.js` uninstalls `meet-aj-*` caches and does not recache public documents. The implementation plan says this worker is unsuitable and must be replaced after approval. It is already in `public/`.  

**Impact:** The moment `public/` becomes the document root, installed PWAs lose offline behavior. Conversely, shipping the *original* worker unchanged would cache `/admin` and Livewire.  

**Recommendation:** Do not point production at `public/` until a reviewed replacement worker exists: public network-first + offline fallback, explicit exclusions for `/admin`, `/livewire`, tokens, forms, and `no-store`.

## 8. Filament sources exist without the Filament package or panel registration

**Severity:** High  

**Description:** Article/Category/Request resources and `AdminPanelProvider` are in `app/`, but `composer.json` does not require `filament/filament`, and the provider is not listed in `bootstrap/providers.php`. Phase 1 said Filament waits until phase 3.  

**Impact:** `php artisan` after a future naive provider registration will fatal. Code review cannot validate admin auth. Windows `composer require filament/filament:"^5.0"` must keep the caret so Laravel 13-capable 5.x is resolved.  

**Recommendation:** Either remove the stubs until Phase 3, or add Filament 5, register the panel, implement `FilamentUser`, and install with a quoted `^5.0` constraint.

## 9. Article preservation fields cannot be mass-assigned

**Severity:** High  

**Description:** Migration includes `seo_data`, `presentation`, `sort_order`. `Article::$fillable` does not. Featured image is a single field; 24 PNG + 23 JPEG thumbnail paths need presentation metadata. Canonical validation rejects anything except the clean self-URL.  

**Impact:** A normal importer `create()` will silently drop SEO JSON, layout/TOC/card context, and sort order. CMS edits can destroy thumbnail vs gallery distinction.  

**Recommendation:** Add the preservation fields to fillable or forceFill in a dedicated importer API. Store thumbnail and gallery paths separately in `presentation`. Normalize canonicals in the importer, not by losing `seo_data`.

## 10. Contact contract and rate limit are not specified to match production

**Severity:** High  

**Description:** Live `forms/contact.php` requires subject, tight length limits, honeypot fake-`OK` without storage, CSRF consume, and **5 requests/hour/IP** with fail-closed 503. Laravel plan says “throttling” and nullable subject without numbers. Mail is currently the only persistence.  

**Impact:** Front-end JS can break; spam capacity can increase; users can see `OK` when mail fails if DB save succeeds (or the reverse).  

**Recommendation:** Copy the live validation table into the living plan. Keep honeypot fake-OK. Persist first, then send mail; define whether mail failure still returns `OK`. Keep fail-closed throttling at 5/hour unless a change is approved.

## 11. Shared-host `storage:link` has no fallback

**Severity:** High  

**Description:** Filament uploads target the `public` disk and URLs under `/storage`. DirectAdmin often disables symlinks.  

**Impact:** Admin image uploads appear to work and then 404 publicly.  

**Recommendation:** Document a non-symlink fallback (alias/rewrite from `/storage` to `storage/app/public`, or deploy-time copy). Test it on staging.

## 12. CSRF alias and Laravel 13 forgery middleware are unimplemented

**Severity:** High  

**Description:** Forms post `csrf_token`, not `_token`. Plans mention `ValidateCsrfToken`; Laravel 13 uses `PreventRequestForgery`. Public middleware is empty.  

**Impact:** All seven forms will 419 after cutover unless the legacy field is accepted. Exempting the route would be worse.  

**Recommendation:** Implement the alias without exemption. Add feature tests that use real HTTP sessions.

## 13. Redirect cascade on article delete with no 410

**Severity:** Medium  

**Description:** Deleting an article deletes `article_redirects`. Old `.html` and prior slugs 404. Tests even assert redirect count goes to 0.  

**Impact:** Accidental delete drops all equity for that URL set.  

**Recommendation:** Soft delete, or keep redirects pointing at a 410 response. Never redirect deleted articles to `/`.

## 14. HTML sanitizer / rich editor can strip code, TOC, and `data-fa`

**Severity:** Medium  

**Description:** Articles contain escaped code, copy buttons, section IDs, and bilingual attributes. The plan is aware; sanitizer config and source-slice extraction are not yet code. Filament uses a plain Textarea for imported `presentation` records (good) and RichEditor for new ones.  

**Impact:** Silent content loss, broken TOCs, Persian attribute loss, XSS if the allowlist is too wide.  

**Recommendation:** Fail import on unexplained sanitizer removals, as planned. Round-trip fixtures for a code-heavy article (for example Linux CLI) and a schema-rich article.

## 15. Phase reports omit required evidence and miss later files

**Severity:** Medium  

**Description:** Phase 1 does not record the lockfile version even though `v13.31.0` exists. Phase 2 claims syntax checks on “35 PHP files” while Filament stubs already exist. No `DEPLOYMENT.md`. Inventory mixes site pages with `.claude` skills.  

**Impact:** False confidence; reviewers cannot tell pass from pending.  

**Recommendation:** Update phase reports with executed commands only. Keep a separate public-URL inventory from the whole-tree hash list.

## 16. Admin authorization holes in the stubs

**Severity:** Medium  

**Description:** No `canAccessPanel()`, no last-admin protection, Request policy untested, category select not language-filtered, `RequestResource` searches full message bodies.  

**Impact:** Editor privilege escalation or contact-data exposure once Filament is wired.  

**Recommendation:** Implement FilamentUser, register policies, hide RequestResource from editors, filter categories by article language, add last-admin guards before any User UI.

## 17. `/up` health route and missing security headers

**Severity:** Medium  

**Description:** Laravel health is enabled. Planned `ResponseHeaders` middleware is absent. Robots still allow all paths.  

**Impact:** Fingerprinting; admin URL discovery; missing HSTS/nosniff on the HTML site after PHP forms are retired.  

**Recommendation:** Disallow `/up`, `/admin`, `/livewire`, and `/forms/` in robots; protect `/up` if it must stay; restore the current form security headers on Laravel responses.

## 18. Trailing-slash 301 plus article 301 can chain

**Severity:** Low  

**Description:** `public/.htaccess` 301-strips trailing slashes before Laravel. A request to `/articles/slug.html/` becomes `/articles/slug.html` then another 301 to `/articles/slug`.  

**Impact:** Minor crawl waste.  

**Recommendation:** Serve the `.html/` case in one hop if logs show it; otherwise accept as low.

## 19. `requests` model/table naming

**Severity:** Low  

**Description:** Table `requests` and model `Request` collide with HTTP Request. Documented, not yet coded around.  

**Impact:** Import bugs and confusing stack traces.  

**Recommendation:** Keep the approved table name if required, but alias `HttpRequest` everywhere. Do not also create `contact_requests`.

## 20. Collation and search limits

**Severity:** Low  

**Description:** `utf8mb4_unicode_ci` stores Persian correctly but is not the best Persian sort/search collation. No FULLTEXT on contacts/articles.  

**Impact:** Irrelevant at 23 articles; minor admin search weakness later.  

**Recommendation:** Stay on unicode_ci unless Persian sorting bugs appear. Revisit FULLTEXT when contact volume grows.

---

# Git Sync Readiness

**Is this project ready for Git commit?** **NO**

**Reason:**

1. This workspace is **not a Git repository**, so there is nothing to commit to and no way to preserve `a9b23d5`.
2. The documentation set is **internally contradictory** (Laravel 11 vs 13, `requests` vs `contact_requests`, DE routes vs DE forbidden, “do not code” vs coded phases). Committing it would freeze confusion as history.
3. Phase 1/2 are **not executable**: no `vendor/`, no MySQL proof, no passing tests, no importer, no Blade, no public routes.
4. `public/sw.js` and unregistered Filament stubs are **unsafe or unbootable artifacts** to treat as a milestone.
5. `.gitignore` is mostly sensible (`.env`, `vendor/`, `.runtime/`), but a commit now would still not represent a reviewable, bootable Laravel app.

**Recommended next action:**

1. Reconcile docs into one living plan (Laravel 13 / PHP 8.4 / Filament 5 / core tables / no public German).
2. Verify DirectAdmin PHP 8.4, document root, Composer, and symlink policy.
3. Finish a real Phase 1: PHP 8.4, `composer install`, artisan boot, `composer audit`.
4. Run Phase 2 migrations on a disposable MariaDB and execute PHPUnit.
5. Restore or clone Git from the original `main` if it exists; otherwise `git init` after the tree is consistent.
6. Then make a **checkpoint commit** of planning + verified Phase 1–2 only — not a cutover commit.

This audit did not modify any project files except creating this report at `docs/cursor-review-report.md`.
