# Architecture — Meet AJ

**Authority:** AUTHORITATIVE description of the running application.
**Verified:** 2026-09-20 against `app/`, `routes/web.php`, `config/`, `resources/views/`, `public/`, and `php artisan route:list` after `optimize:clear`.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md). Decision record: [../decisions/ADR/ADR-001-laravel-13-filament-5-stack.md](../decisions/ADR/ADR-001-laravel-13-filament-5-stack.md).

## Request flow

```
Browser
   │
   ▼
Apache / DirectAdmin  (document root = public/)
   │
   ▼
public/index.php → Laravel 13 HTTP kernel
   │
   ├── GET  /, /articles, /articles?q=, /articles?tag=, /articles/{slug}
   │         → controllers → Eloquent (scopes, relations) → Blade
   │
   ├── GET  /forms/get-csrf-token.php   POST /forms/contact.php
   │         → ContactController → requests table → optional mail
   │
   ├── GET  /sitemap.xml, /robots.txt
   │
   └── /admin/* → Filament 5 → Livewire 4 → models + policies → same database
```

No SPA, no Node build, no queue worker, no scheduler, no Redis. Cache and sessions use the **file** driver; `QUEUE_CONNECTION=sync`.

## Laravel layout

- Application root is the repository root (`artisan`, `app/`, `composer.json`).
- Front controller: `public/index.php`.
- Web routes: `routes/web.php`; console routes: `routes/console.php`.
- Config: `config/*.php`, with CMS-specific values in `config/cms.php` (`languages`, `public_languages`, `display_timezone`, `contact_email`, `mail_is_optional`).
- Local runtime for verification: `.runtime/php84/php.exe` (PHP 8.4.25), because `php` is not on PATH on this workstation.

## Routes

`php artisan route:list` reports the application routes (`/`, `/index.html`, three article routes, two `/forms/*.php` routes, `/sitemap.xml`, `/robots.txt`, `/manifest.json`), Filament `/admin` routes (dashboard, login/logout, Articles, Categories, Tags, Services, Requests, Users, plus hidden ContactRequest/ServiceRequest view URLs), and Livewire / Filament asset and export routes.

| Method | Path | Name | Handler |
|--------|------|------|---------|
| GET | `/` | `home` | `HomeController@index` |
| GET | `/index.html` | — | closure, 301 → `/` |
| GET | `/articles` | `articles.index` | `ArticleController@index` |
| GET | `/articles/{slug}` | `articles.show` | `ArticleController@show` |
| GET | `/articles/{slug}.html` | `articles.legacy` | `ArticleController@legacy` (301) |
| GET | `/forms/get-csrf-token.php` | `contact.token` | `ContactController@token` |
| POST | `/forms/contact.php` | `contact.store` | `ContactController@store` + `throttle:30,1` |
| GET | `/sitemap.xml` | `sitemap` | `SitemapController` |
| GET | `/robots.txt` | `robots` | `RobotsController` |

There is no `/de` route.

## Controllers

| Controller | Responsibility |
|------------|----------------|
| `HomeController` | Homepage content catalog, published services, articles, testimonials and filters |
| `ArticleController` | Library, search and tag filter, detail, legacy 301 |
| `ContactController` | CSRF token endpoint and contact submission |
| `SitemapController`, `RobotsController` | SEO endpoints |

## Models and policies

| Model | Table | Policy | Who may manage |
|-------|-------|--------|----------------|
| `Article` | `articles` | `ArticlePolicy` | admin + editor |
| `Category` | `categories` | `CategoryPolicy` | admin + editor. `accentColor()` = valid `accent_color` or slug palette |
| `Tag` | `tags` | `TagPolicy` | admin + editor |
| `Service` | `services` | `ServicePolicy` | admin only |
| `Request` | `requests` | `RequestPolicy` | admin only, create denied |
| `User` | `users` | `UserPolicy` | admin only |
| `ArticleRedirect` | `article_redirects` | — | managed by the importer and slug changes |

Policies are registered in `App\Providers\AppServiceProvider`.

## Domain services

| Class | Role |
|-------|------|
| `LegacyArticleImporter` | Parse `resources/legacy/articles/*.html` into `articles` + `article_redirects` |
| `LegacyServiceImporter` | Parse `resources/legacy/services/*.html` into `services` |
| `LegacySitePublisher` | Copy allowlisted assets into `public/` and rebuild Blade views from the original HTML |
| `ArticleSeo` | Canonical, Open Graph, Twitter and JSON-LD for article detail |
| `ArticleShareLinks` | Share URLs on the article page |
| `ArticleHtmlSanitizer` | Allowed HTML for stored article bodies |
| `ArticleTagAssigner` | Derive the tag vocabulary and article links |
| `HomepageContentCatalog` | Seed and load the seven editable homepage sections without overwriting admin changes |
| `HomepageServiceCatalog` | Define and import the homepage service catalog |

## Console commands

| Command | Class | Options |
|---------|-------|---------|
| `articles:import-legacy` | `ImportLegacyArticles` | `--dry-run`, `--refresh` |
| `services:import-legacy` | `ImportLegacyServices` | `--dry-run`, `--refresh` |
| `articles:sync-tags` | `SyncArticleTags` | — |
| `site:publish-assets` | `PublishLegacyAssets` | `--views` |
| `site:compare-content` | `CompareLegacyContent` | — |
| `cms:create-user` | `CreateCmsUser` | interactive |
| `test` | `RunTests` | forwards arguments to PHPUnit |

## Blade (public site)

| View | Origin |
|------|--------|
| `home.blade.php` | active CMS-backed homepage; legacy HTML is retained only as an article-library generation source |
| `articles/index.blade.php` | library chrome from the homepage portfolio section |
| `articles/show.blade.php` | renders database content imported from `resources/legacy/articles/{slug}.html` |
| `articles/partials/*` | breadcrumbs, library toolbar, related, search results, share, category filters |
| `components/article-card.blade.php`, `components/service-card.blade.php` | shared cards, English defaults with `data-fa` |
| `components/site-sidebar.blade.php`, `partials/site-sidebar-chrome.blade.php` | shared public sidebar |
| `partials/testimonials.blade.php` | shared EN/FA Swiper |
| `seo/sitemap.blade.php` | XML sitemap |
| `errors/{404,419,500}.blade.php` | error pages |

Rebuild the article listing with `php artisan site:publish-assets --views`. The CMS-backed homepage is maintained directly and is intentionally excluded from the legacy view writer.

The homepage service catalog is the only public service presentation. Homepage catalog definitions are stored in `HomepageServiceCatalog`; there are no standalone public service routes or detail views.

## Filament / Livewire

- Provider: `app/Providers/Filament/AdminPanelProvider.php`, panel path `/admin`
- Resources: `ArticleResource`, `CategoryResource`, `TagResource`, `HomepageContentResource`, `ServiceResource`, `TestimonialResource`, `RequestResource`, `Users/UserResource`
- Widgets: `CmsStatsOverview`, `RecentArticles`, `RecentRequests`
- Livewire 4 is a Filament dependency; the public pages use no Livewire components

Detail: [ADMIN.md](ADMIN.md).

## Middleware

| Middleware | Effect |
|------------|--------|
| `SecurityHeaders` | `nosniff`, `Referrer-Policy`, `SAMEORIGIN`, HSTS on HTTPS, `no-store` on `/admin`, `/livewire`, `/forms` and POST responses |
| `AcceptLegacyCsrfToken` | Maps the legacy `csrf_token` field onto Laravel's CSRF check |

## Assets and document root

Site CSS/JS/images live in `resources/assets/` and are copied into `public/assets/` by `php artisan site:publish-assets`. Public cascade: `main.css?v=1002` → `lang-toggle.css?v=1403` → `rtl.css?v=1405` → `visual-upgrade.css?v=1713` → **`site-modules.css?v=1853` last**. Scripts: `main.js?v=1414`, `i18n.js?v=1403`. Admin CSS is **not** in this public overlay — Filament loads `resources/css/filament-admin.css` (published as `public/css/app/meet-aj-admin.css`).

Only `public/` may be exposed by the web server. Frontend assets, static files, downloads, and original HTML live under `resources/`. Importers and the publisher read those sources; public URL paths are unchanged. See [PROJECT-STRUCTURE.md](PROJECT-STRUCTURE.md).

## Storage

- `storage/app/public` → Filament uploads, symlinked as `public/storage`
- Imported `featured_image` values usually stay under `/assets/...` unless replaced
- `storage/framework/{cache,sessions,views}` → file cache, sessions, compiled Blade
- `.runtime/` is a local PHP runtime and must never be uploaded to production

## What this architecture is not

Not Laravel 11 or 12, not PHP 8.2, not Filament 3, not React or Next.js, not a `pages`-table CMS, not a `contact_requests` table, not a public German site, and not a Node/Vite/Tailwind build.
