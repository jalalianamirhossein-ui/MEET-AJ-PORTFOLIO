# Architecture — Meet AJ Laravel CMS

**Authority:** AUTHORITATIVE description of the running application.  
**Verified:** 2026-09-17 against `app/`, `routes/`, `config/`, `public/`, `composer.lock`.

## Request flow

```
Browser
   │
   ▼
Apache / DirectAdmin (document root = public/)
   │
   ▼
public/index.php  →  Laravel 13 HTTP kernel
   │
   ├── GET  / , /articles , /articles?q= , /articles?tag= , /articles/{slug} , /services/{slug} (and .html 301)
   │         Blade views
   │         App\Services (SEO, share links, tag assigner, importer, publisher)
   │         Eloquent models (`scopeSearch`, `relatedArticles`)
   │         SQLite or MySQL/MariaDB
   │
   ├── GET/POST  /forms/*.php
   │         ContactController  →  requests table  →  optional mail
   │
   └── /admin  →  Filament 5  →  Livewire 4  →  models / policies  →  database
```

There is no separate SPA, no Node build, and no queue worker. Cache and sessions are **file** drivers in the intended production config.

## Laravel

- Application root: repository root (contains `artisan`, `app/`, `composer.json`).
- Front controller: `public/index.php`.
- Web routes: `routes/web.php`. After `optimize:clear` on 2026-09-17, `php artisan route:list` showed **36** routes including `/admin/tags`.
- Console: `app/Console/Commands/`.
- Config: `config/*.php`; CMS-specific: `config/cms.php`.
- Local runtime used for verification: `.runtime/php84/php.exe` (PHP 8.4.25) because `php` is not on PATH.

## Blade (public site)

Views live in `resources/views/`:

| View | Source |
|------|--------|
| `home.blade.php` | rebuilt from `index.html` |
| `articles/index.blade.php` | listing chrome from homepage `#portfolio` |
| `articles/show.blade.php` | `articles/{slug}.html` |
| `services/show.blade.php` | dynamic catalog detail from `services` table |
| `components/article-card.blade.php` | shared article card (EN defaults + `data-fa`) |
| `components/service-card.blade.php` | homepage catalog card |
| `seo/sitemap.blade.php` | XML sitemap |
| `vendor/filament/**` | admin UI (vendor + panel provider) |

Rebuild command: `php artisan site:publish-assets --views` (`App\Services\LegacySitePublisher`).

Public copies of CSS/JS/images: `public/assets/` (copied from `assets/`). Overlay stylesheet `assets/css/visual-upgrade.css` is loaded after `rtl.css`.

## Filament / Livewire

- Provider: `app/Providers/Filament/AdminPanelProvider.php`
- Path: `/admin` (login `/admin/login`)
- Resources: `app/Filament/Resources/{Article,Category,Service,Request}Resource.php` plus `Users/UserResource.php` (navigation disabled)
- Widgets: `CmsStatsOverview`, `RecentArticles`, `RecentRequests`
- Livewire 4 is a Filament 5 dependency; public pages do not use Livewire components

## Database

See [DATABASE.md](DATABASE.md). Eloquent models: `app/Models/{User,Article,Category,Request,ArticleRedirect}.php`.

## Public document root

Production and `php artisan serve` must expose **`public/`** only. Original `index.html`, `articles/`, `services/`, `assets/`, `forms/` at the repository root are **sources**, not the live document root. Copying them into `public/` would bypass Laravel.

## Storage

- `storage/app/public` — Filament image uploads (symlink `public/storage`)
- Imported featured images often remain under `/assets/...` and are not rewritten unless an editor uploads a replacement
- `storage/framework/{cache,sessions,views}` — file cache/sessions/compiled Blade
- Do not upload `.runtime/` to production

## Routes (`routes/web.php`)

| Method | Path | Name | Controller |
|--------|------|------|------------|
| GET | `/` | `home` | `HomeController@index` |
| GET | `/index.html` | (closure) | 301 → `/` |
| GET | `/services/{slug}.html` | `services.legacy` | `ServiceController@legacy` (301) |
| GET | `/services/{slug}` | `services.show` | `ServiceController@show` |
| GET | `/articles/{slug}.html` | `articles.legacy` | `ArticleController@legacy` |
| GET | `/articles/{slug}` | `articles.show` | `ArticleController@show` |
| GET | `/articles` | `articles.index` | `ArticleController@index` |
| GET | `/forms/get-csrf-token.php` | `contact.token` | `ContactController@token` |
| POST | `/forms/contact.php` | `contact.store` | `ContactController@store` + `throttle:30,1` |
| GET | `/sitemap.xml` | `sitemap` | `SitemapController` |
| GET | `/robots.txt` | `robots` | `RobotsController` |

Filament registers `/admin/*` and Livewire routes. There is **no** `/de` route.

## Services

| Class | Role |
|-------|------|
| `App\Services\LegacyArticleImporter` | Parse `articles/*.html` into `articles` + `article_redirects` |
| `App\Services\LegacyServiceImporter` | Parse `services/*.html` into `services` |
| `App\Services\LegacySitePublisher` | Copy allowlisted assets; rebuild Blade from original HTML |
| `App\Services\ArticleSeo` | Canonical, OG, Twitter, JSON-LD for article detail |
| `App\Services\ArticleHtmlSanitizer` | Allowed HTML for stored article bodies |

## Models and policies

| Model | Policy | Ability |
|-------|--------|---------|
| `Article` | `ArticlePolicy` | admin + editor (`canManageContent`) |
| `Category` | `CategoryPolicy` | admin + editor |
| `Request` (table `requests`) | `RequestPolicy` | admin only; create from Filament is false |
| `Service` | `ServicePolicy` | admin only |
| `User` | `UserPolicy` | admin only |

Registered in `AppServiceProvider`.

## Commands

| Command | Class |
|---------|-------|
| `articles:import-legacy` | `ImportLegacyArticles` |
| `services:import-legacy` | `ImportLegacyServices` |
| `site:publish-assets` | `PublishLegacyAssets` |
| `site:compare-content` | `CompareLegacyContent` |
| `cms:create-user` | `CreateCmsUser` |
| `test` | `RunTests` (wraps PHPUnit; **does not** accept `--filter`) |

## PWA

Static files under `public/`: `manifest.json`, `sw.js`, `offline.html`. Registered from homepage markup. See [PWA.md](PWA.md).

## What this architecture is not

- Not Laravel 11 / PHP 8.2 / Filament 3
- Not a `pages` table CMS
- Not a `contact_requests` table
- Not a public German site (`/de`)
- Not a Node/Vite/Tailwind app
