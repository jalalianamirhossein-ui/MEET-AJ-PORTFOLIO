# Project structure — Meet AJ

**Authority:** AUTHORITATIVE directory map.
**Verified:** 2026-09-17 by listing the repository from disk. Only directories that actually exist are described.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md).

## Top level

```
MEET AJ PORTFOLIO/
├── app/                 Laravel application code
├── articles/            23 original article HTML files (import source)
├── assets/              Original CSS / JS / images / fonts (publish source)
├── bootstrap/           Laravel bootstrap and cached framework files
├── config/              Laravel configuration, including cms.php
├── database/            Migrations, seeders, local SQLite file
├── design-system/       Page-level design notes for the Meet AJ look
├── docs/                Project documentation (this tree)
├── forms/               Original static PHP endpoints (source only, not served)
├── partials/            Original shared HTML fragment (lang-toggle.html)
├── public/              Web document root
├── resources/           Blade views and admin CSS
├── routes/              web.php, console.php
├── scripts/             Standalone maintenance PHP scripts
├── services/            6 original service HTML files (import source)
├── storage/             Framework storage, logs, uploads
├── tests/               PHPUnit feature tests
├── vendor/              Composer dependencies
├── .runtime/            Local PHP 8.4 runtime (workstation only, never deployed)
├── artisan              Laravel CLI entry point
├── composer.json / composer.lock
├── phpunit.xml / phpunit.mysql.xml
├── index.html           Original homepage (import and rebuild source)
├── manifest.json, sw.js, robots.txt, sitemap.xml, preloader.*  Original static files
└── README.md            Project entry point
```

`.env`, `.env.example`, `.env.production.example` and `.env.testing` live at the top level. Only the example files belong in version control.

## `app/`

```
app/
├── Console/Commands/    ImportLegacyArticles, ImportLegacyServices, SyncArticleTags,
│                        PublishLegacyAssets, CompareLegacyContent, CreateCmsUser, RunTests
├── Filament/
│   ├── Pages/           Dashboard
│   ├── Resources/       ArticleResource, CategoryResource, TagResource,
│   │                    ServiceResource, RequestResource, Users/UserResource
│   └── Widgets/         CmsStatsOverview, RecentArticles, RecentRequests
├── Http/
│   ├── Controllers/     Home, Article, Service, Contact, Sitemap, Robots
│   ├── Middleware/      SecurityHeaders, AcceptLegacyCsrfToken
│   └── Requests/        StoreContactRequest
├── Mail/                ContactReceivedMail
├── Models/              Article, ArticleRedirect, Category, Request, Service, Tag, User
├── Policies/            Article, Category, Tag, Service, Request, User
├── Providers/           AppServiceProvider, Filament/AdminPanelProvider
└── Services/            LegacyArticleImporter, LegacyServiceImporter, LegacySitePublisher,
                         ArticleSeo, ArticleShareLinks, ArticleHtmlSanitizer, ArticleTagAssigner
```

## `database/`

```
database/
├── migrations/          10 migrations (users → tags/request workflow → `categories.accent_color`)
├── seeders/             DatabaseSeeder: rebuild views, then import articles and services
└── database.sqlite      Local development database (not for production)
```

Migration list and schema: [DATABASE.md](DATABASE.md).

## `resources/`

```
resources/
├── css/                 filament-admin.css (White/Red Filament overlay; published via filament:assets)
└── views/
    ├── home.blade.php   (includes Expertise / تخصص‌ها)
    ├── articles/        index, show, partials/(breadcrumbs, library-toolbar, related,
    │                    search-results, share, category-filters)
    ├── services/        show.blade.php (routed) + six unused per-service templates
    ├── components/      article-card, service-card, site-sidebar
    ├── partials/        testimonials, site-sidebar-chrome, service-drawer
    ├── errors/          404, 419, 500
    └── seo/             sitemap.blade.php
```

## `routes/`

`web.php` holds the 11 application routes; `console.php` holds console route definitions. Filament and Livewire register their own routes at runtime.

## `public/`

```
public/
├── index.php            Front controller
├── .htaccess            Laravel rewrite rules
├── assets/              Published copies of css, js, img, fonts, vendor (147 files)
├── css/, js/, fonts/    Additional published static assets
├── docs/                Published copy of the NetBox PDF
├── partials/            Published copy of the language-toggle fragment
├── storage/             Symlink to storage/app/public
├── manifest.json, sw.js, offline.html   PWA files
└── preloader.css, preloader.html
```

This directory is the only one a web server should expose.

## `articles/`, `services/`, `assets/`, `forms/`, `partials/`, `index.html`

These are the **original static sources** kept for import, rebuild and rollback:

- `articles/*.html` — 23 files, the content source of truth for `site:compare-content`
- `services/*.html` — 6 files, the source of the service catalog and its AED prices
- `assets/` — `css/`, `js/`, `img/`, `scss/`, `vendor/`; the publisher copies an allowlist into `public/assets/`
- `forms/contact.php`, `forms/get-csrf-token.php`, `forms/security.php` — the original PHP endpoints, replaced by `ContactController` but kept as reference
- `partials/lang-toggle.html` — original language switcher fragment
- `index.html` — original homepage, the source for `home.blade.php`

None of these are inside `public/`, so Apache cannot serve them directly.

## `tests/`

```
tests/
├── TestCase.php
└── Feature/
    ├── AdminThemeTest.php               6 tests
    ├── ArticleLibraryTest.php           5 tests
    ├── CmsOperationsTest.php            8 tests
    ├── ContentRulesTest.php             5 tests
    ├── FormCsrfAndAdminRequestsTest.php 6 tests
    ├── MysqlSchemaTest.php              1 test (skipped unless MySQL is bound)
    ├── ProductionAuditTest.php          6 tests
    ├── PublicSiteTest.php              10 tests
    ├── RequestWorkflowTest.php          3 tests
    └── ServiceCatalogTest.php           8 tests
```

**58 tests** in total (1124 assertions on the last green run). Detail: [TESTING.md](TESTING.md).

## `scripts/`

- `validate-environment.php` — checks the local PHP environment
- `verify-originals.php` — compares original files against the SHA-256 baseline in `docs/baseline-files.json`

Both are standalone PHP scripts, not artisan commands.

## `design-system/`

`design-system/meet-aj/MASTER.md` plus page notes for `homepage`, `articles`, `services` and `admin`. These describe the intended visual language; the implemented tokens are documented in [DESIGN-SYSTEM.md](DESIGN-SYSTEM.md).

## `docs/`

```
docs/
├── README.md                     Documentation index
├── baseline-files.json           SHA-256 baseline read by scripts/verify-originals.php
├── netbox_installation_guide_v2.pdf   Published to public/docs/ by LegacySitePublisher
├── current/                      Current-state documentation (this directory)
├── qa/                           QA evidence and reports
├── decisions/ADR/                Architecture decision records
├── phases/                       Chronological implementation history
└── historical/                   Superseded snapshots, plans and screenshots
```

`baseline-files.json` and `netbox_installation_guide_v2.pdf` must stay at `docs/` root: application code and a maintenance script reference those exact paths.

## Directories deliberately not deployed

`.runtime/` (local PHP), `tests/`, `docs/`, `design-system/`, `.phpunit.cache/`, and the development `database/database.sqlite`.
