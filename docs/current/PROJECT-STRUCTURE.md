# Project structure — Meet AJ

**Verified:** 2026-09-20. This is the current directory map. Earlier layouts in `docs/historical/`, `docs/archive/`, and `docs/phases/` describe their original point in time.

## Repository layout

```text
MEET AJ PORTFOLIO/
├── app/                       Laravel application code
│   ├── Console/Commands/      Imports, publishing, content comparison, CMS tools
│   ├── Filament/              Admin resources, pages, widgets
│   ├── Http/                  Controllers, middleware, form requests
│   ├── Mail/                  Notification mail
│   ├── Models/                Eloquent models
│   ├── Policies/              Authorization
│   ├── Providers/             Application and admin panel setup
│   └── Services/              Content processing and application services
├── bootstrap/                 Application bootstrap and generated cache
├── config/                    Laravel and CMS configuration
├── database/                  Migrations, seeders, ignored local SQLite database
├── docs/                      Documentation and design references
│   ├── current/               Current implementation and operating guides
│   ├── design-system/meet-aj/  MASTER.md and page design notes
│   ├── qa/                    QA reports and historical baseline-files.json
│   ├── decisions/ADR/         Architecture decisions
│   ├── phases/                Implementation history
│   ├── historical/            Superseded documents
│   └── archive/               Dated snapshots and debugging evidence
├── public/                    The only web document root
├── resources/                 Versioned frontend and content sources
│   ├── assets/                Site CSS, JS, images, SCSS, third-party assets
│   ├── css/                   Filament admin CSS source
│   ├── downloads/             Public downloadable documents
│   ├── legacy/                Static content used by importers and article view generation
│   │   ├── index.html         Homepage and article-library generation source
│   │   ├── articles/          24 original articles
│   │   ├── services/          Six original service sources
│   │   ├── forms/             Former PHP endpoints, reference only
│   │   ├── views/services/    Six retired Blade templates, reference only
│   │   └── robots.txt, sitemap.xml, sw.js  Former static versions
│   ├── static/                Manifest, preloaders, language-toggle fragment
│   └── views/                 Active Blade views and reusable partials
├── routes/                    Web and console routes
├── scripts/                   Standalone PHP maintenance and diagnostics
├── storage/                   Ignored uploads, runtime files, logs, caches
├── tests/                     PHPUnit feature suite and TestCase
├── vendor/                    Ignored Composer dependencies
├── .runtime/                  Ignored local PHP/Composer tools
├── artisan                    Laravel CLI entry point
├── composer.json / composer.lock
├── phpunit.xml / phpunit.mysql.xml
├── .env.example / .env.production.example
└── README.md
```

## Source and output ownership

| Edit here | Published or consumed here | Command |
|-----------|---------------------------|---------|
| `resources/assets/` | `public/assets/` | `php artisan site:publish-assets` |
| `resources/static/` | `public/manifest.json`, `public/preloader.*`, `public/partials/lang-toggle.html` | `php artisan site:publish-assets` |
| `resources/downloads/netbox_installation_guide_v2.pdf` | `public/docs/netbox_installation_guide_v2.pdf` | `php artisan site:publish-assets` |
| `resources/css/filament-admin.css` | `public/css/app/meet-aj-admin.css` | `php artisan filament:assets` |
| `resources/legacy/index.html` and `LegacySitePublisher` | `resources/views/articles/index.blade.php` | `php artisan site:publish-assets --views` |
| `resources/legacy/articles/` | Article and redirect records | `php artisan articles:import-legacy` |
| `HomepageServiceCatalog` | Homepage service records and ordering | `php artisan services:import-legacy` |
| `HomepageContentCatalog` + `homepage_contents` | `resources/views/home.blade.php` | Homepage request; admin sync |
| `LegacySitePublisher::writeServiceWorker()` | `public/sw.js`, `public/offline.html` | `php artisan site:publish-assets` |

Public URLs remain `/assets/...`, `/docs/netbox_installation_guide_v2.pdf`, `/manifest.json`, `/sw.js`, and the existing page routes. Filesystem moves do not change stored article source identifiers (`articles/*.html`) or legacy redirect URLs.

Keep `resources/` in deployment packages: asset publishing, imports, content comparison, Blade rendering, and sitemap source timestamps depend on it. Never expose the repository root or `resources/` as the web document root. For a static rollback, use a separate complete static release; `resources/legacy/` alone is not a standalone website.

The active homepage is `resources/views/home.blade.php`. Its copy is database-backed by `homepage_contents`; `resources/legacy/index.html` remains a source for the article-library publisher and must not overwrite the CMS view. `public/` contains published web output only. Do not edit generated public assets when the source exists under `resources/`.

## Placement rules

- Follow Laravel's existing `app/`, `config/`, `database/`, `routes/`, and `tests/` conventions for new backend work.
- Put active templates under `resources/views/`; service presentation lives in the homepage catalog and drawer. Service catalog definitions live in `HomepageServiceCatalog`.
- Edit asset sources in `resources/`, then publish them. Some generated public outputs are tracked for deployment compatibility; do not edit those copies directly.
- Keep developer documentation in `docs/`, and downloadable visitor content in `resources/downloads/`.
- Keep standalone diagnostics in `scripts/`. Use `storage/app/` or `storage/logs/` for temporary output; these are already ignored by Git.
- Keep credentials in the ignored root `.env`; commit only environment examples. Never deploy `.runtime/`, test databases, or local scratch output.
- Design notes now live in `docs/design-system/meet-aj/`. Tools that generate a `design-system/` directory should use `docs/` as their output directory.

## Maintenance scripts

Run these from the repository root with PHP 8.4:

```bash
php scripts/validate-environment.php
php scripts/dump-session-config.php
php scripts/verify-originals.php
```

The originals verifier reads `docs/qa/baseline-files.json`, maps historical paths to the current layout, and preserves original SHA-256 values. That historical baseline already differs from current content; its nonzero exit is not a standalone regression test for this reorganization. Current content parity is checked with `php artisan site:compare-content`.
