# Project structure cleanup

Date: 2026-09-16  
Rule used: delete only when references were verified. If uncertain, keep.

Application logic was not redesigned. Git was not initialized, committed, or pushed.

## Files reviewed

Application (`app/`), Blade (`resources/views/`), public site sources (`index.html`, `articles/`, `services/`, `assets/`), `public/`, `config/`, `database/migrations/`, `tests/`, `scripts/`, `forms/`, `partials/`, Composer files, env examples, `.gitignore`, PHPUnit configs, `DEPLOYMENT.md`, `README.md`, all project Markdown, `docs/*.json`.

Also inspected: no `package.json`, no Docker files, no CI/CD workflows.

## Directories reviewed

Root, `app/`, `articles/`, `assets/`, `bootstrap/`, `config/`, `database/`, `docs/`, `forms/`, `partials/`, `public/`, `resources/`, `routes/`, `scripts/`, `services/`, `storage/`, `tests/`, `vendor/` (not modified), `.claude/` (not modified), `.runtime/` (local PHP, gitignored, kept).

## Files deleted

Verified unused or generated:

| File | Classification | Why |
|------|----------------|-----|
| `Preloader.jsx` | UNUSED | React demo. No `package.json`. Only listed in historical JSON inventories. |
| `PreloaderExample.jsx` | UNUSED | Same. Only imported `Preloader.jsx`. |
| `storage/logs/laravel.log` | TEMPORARY | Generated log (gitignored). Directory + `.gitignore` kept. |

Relocated (copied to `docs/historical/`, then the **old path** removed):

- `AUDIT_REPORT.md` (was at repo root)
- `docs/laravel-implementation-plan.md`
- `docs/laravel-migration-plan.md`
- `docs/content-migration-analysis.md`
- `docs/current-site-analysis.md`
- `docs/cursor-review-report.md`
- `docs/design-audit.md`
- `docs/visual-upgrade-report.md`
- `docs/content-integrity-fixes.md`
- `docs/implementation-report.md`
- `docs/final-site-qa-report.md`

`docs/baseline-files.json` no longer hashes the deleted JSX files so `scripts/verify-originals.php` does not fail on them.

`php artisan optimize:clear` removed **generated** bootstrap/view/route/config caches. Those are not source files.

## Directories deleted

**None.** Empty leftover `app/Filament/Resources/UserResource/Pages` (no PHP files; the live resource is `Users/UserResource.php`) was **kept** because directory removal was not applied.

## Duplicate files removed

No duplicate runtime code was deleted. Duplicate *current-state documentation* was consolidated by moving superseded reports into `docs/historical/` rather than destroying them.

`public/assets/`, `public/preloader.html`, `public/manifest.json`, `public/sw.js` remain generated **copies** of sources. They are required at runtime. `.gitignore` already ignores several of them.

## Obsolete code removed

- Unused React preloader demos (above).
- `NewRequests.php` was **already absent** from `app/Filament/Widgets/` (only `CmsStatsOverview`, `RecentArticles`, `RecentRequests` remain). Docs that still mentioned it were updated.

## Documentation consolidated

| Current (`docs/`) | Historical (`docs/historical/`) |
|-------------------|----------------------------------|
| PROJECT-STATUS, ARCHITECTURE, DATABASE, ADMIN, MULTILINGUAL, SEO, PWA, SECURITY, QA-MATRIX, DOCUMENTATION-INDEX, ADR, framework-version-decision, final-ui-qa, phases | superseded plans, audits, implementation/QA snapshots, root `AUDIT_REPORT.md` |

`docs/historical/README.md` states they are not current status.

## Files intentionally retained

- Original site sources: `index.html`, `articles/*.html`, `services/*.html`, `assets/`, `manifest.json`, `sw.js`, `robots.txt`, `sitemap.xml`
- Rollback/demo: `forms/*.php`, `preloader.html`, `preloader.css`, `partials/lang-toggle.html`
- Laravel app, migrations, tests, `scripts/validate-environment.php`, `scripts/verify-originals.php`
- Local data: `database/database.sqlite` (now gitignored)
- Tooling: `.claude/skills/`, `.runtime/php84/`
- Vendor: `vendor/`
- Env examples: `.env.example`, `.env.production.example`
- PDF: `docs/netbox_installation_guide_v2.pdf` (published to `public/docs/`)

## Uncertain files retained

| Item | Reason kept |
|------|-------------|
| Root `sitemap.xml` / `robots.txt` / `sw.js` | Original static copies. Laravel serves dynamic/public versions from `public/`. Useful rollback/source. |
| `preloader.html` / `preloader.css` | Explicitly copied by `LegacySitePublisher`. Demo URLs, not in sitemap. |
| `forms/*.php` | Not executed when document root is `public/`. Kept for rollback (DEPLOYMENT.md). |
| Empty `app/Filament/Resources/UserResource/` | Leftover empty folders after the class moved to `Users/`. No PHP inside. |
| `.env` / `.env.testing` | Local secrets/test env; gitignored. |
| Large `docs/current-site-inventory.json` | Historical inventory used as a snapshot, not a live gate. |

## Tests executed

```
php artisan optimize:clear   OK
php artisan about            Laravel 13.31.0, PHP 8.4.25, Filament v5.8.2, Livewire v4.4.5
php artisan route:list       31 routes
php artisan test             23 tests, 510 assertions, 1 skipped, 0 failures
```

No Node/frontend build exists (`package.json` absent).

**Note:** Before `optimize:clear`, a stale route cache listed 29 routes and omitted Users. After clear, `GET /admin/users` and `GET /admin/cms-users` both exist. Sidebar Users nav remains hidden. Docs were updated to match. Application code was not changed.

## Counts

Comparable filter: exclude `vendor/`, `node_modules/`, `.runtime/` (includes `.git` objects).

| | All files | All dirs | Excl. vendor/runtime files | Excl. vendor/runtime dirs |
|--|-----------|----------|----------------------------|---------------------------|
| **BEFORE** | 18643 | 4154 | 2324 | 288 |
| **AFTER** | 18604 | 4153 | 2101 | 443 |

The drop in “all files” is mostly `optimize:clear` (compiled views / bootstrap cache under gitignored paths) plus the three explicit deletes. Directory count under the excl. filter rose because `docs/historical/` was added and `.git` packing is not a stable source-tree metric.

Explicit source deletes: **3 files**. Relocated docs: **11 files**. New: `docs/historical/README.md`, `docs/PROJECT-STRUCTURE-CLEANUP.md`.

## Deleted files (explicit)

```
Preloader.jsx
PreloaderExample.jsx
storage/logs/laravel.log
```

## Deleted directories

```
(none)
```

## Final project tree (source, not vendor)

```
MEET AJ PORTFOLIO/
  .env.example
  .env.production.example
  .gitignore
  README.md
  DEPLOYMENT.md
  artisan
  composer.json
  composer.lock
  phpunit.xml
  phpunit.mysql.xml
  index.html                 # original homepage (import/source)
  manifest.json
  sw.js                      # original worker (public/sw.js is generated)
  robots.txt                 # original; Laravel serves /robots.txt
  sitemap.xml                # original; Laravel serves /sitemap.xml
  preloader.html / preloader.css
  app/                       # Laravel + Filament
  articles/                  # 23 original HTML articles
  assets/                    # original CSS/JS/images
  bootstrap/
  config/
  database/migrations/       # six migrations kept
  docs/                      # current + historical/ + phases/
  forms/                     # original PHP (rollback; not public docroot)
  partials/
  public/                    # document root
  resources/views/
  routes/
  scripts/
  services/                  # six original service HTML files
  storage/
  tests/
```

## Git

A `.git` directory exists. This cleanup did **not** run `git init`, commit, or push.
