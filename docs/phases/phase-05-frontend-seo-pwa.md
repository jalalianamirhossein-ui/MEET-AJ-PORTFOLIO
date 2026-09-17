> **HISTORICAL / SUPERSEDED phase log.** Current public/SEO/PWA/contact status: [SEO.md](../current/SEO.md), [PWA.md](../current/PWA.md), [QA-MATRIX.md](../qa/QA-MATRIX.md). Overlay cache was later `v=1108`.

# Phase 5 — frontend, SEO, PWA, contact

Status: **public pages render from Blade**. Original CSS/JS preserved. Full viewport matrix and PWA install/offline were not fully tested.

## Implemented work

- `php artisan site:publish-assets --views` copies original assets into `public/` and rebuilds Blade from original HTML.
- Homepage, six service pages, article index, article detail.
- Contact endpoints keep legacy URLs and `OK` / CSRF JSON contracts.
- Dynamic sitemap and robots.
- Replacement `public/sw.js` + `offline.html`.
- Security headers middleware; legacy CSRF field name `csrf_token`.
- Converted HTML is wrapped in `@verbatim` so emails and JSON-LD `@type` survive Blade.

Reusable Blade:

- `resources/views/components/article-card.blade.php` (CMS article grid)
- `resources/views/articles/show.blade.php` (article layout + SEO from the database)
- Header, footer, navigation, language switcher, and contact forms remain the original markup inside the converted page views so CSS/IDs/JS hooks are not redesigned.

## Files changed

- `app/Services/LegacySitePublisher.php`
- `app/Http/Controllers/{Home,Service,Article,Contact,Sitemap,Robots}Controller.php`
- `app/Http/Requests/StoreContactRequest.php`
- `app/Http/Middleware/{AcceptLegacyCsrfToken,SecurityHeaders}.php`
- `app/Services/ArticleSeo.php`
- `routes/web.php`
- `resources/views/home.blade.php`, `articles/*`, `services/*`, `seo/sitemap.blade.php`, `errors/*`
- `public/sw.js`, `public/offline.html`
- `tests/Feature/PublicSiteTest.php`

## Commands executed

```text
php artisan site:publish-assets --views
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize
curl.exe -sS -D - against public URLs (see QA report)
php artisan site:compare-content
php artisan test
```

`php artisan optimize` succeeded (config, events, routes, views, filament).

## Tests executed

PublicSiteTest: homepage, services, articles, redirects, 404, sitemap/robots exclusions, contact persist/honeypot/validation, admin gate, no `/de`.

Browser (Cursor): homepage desktop, Ubuntu article EN + FA a11y tree, DevOps service EN visual, articles index desktop + ~375 layout, Filament login, homepage ~320 layout.

## Real results

- No `public/articles/`, `public/services/`, `public/index.html`, `public/sitemap.xml`, `public/robots.txt`, or `public/forms/` directory. Laravel owns those routes.
- After the `@verbatim` fix, homepage HTML contains `mailto:jalalian.amirhossein@gmail.com` and JSON-LD `"@type"`.
- Service URLs remain `*.html` and return 200.

## Blockers

- PWA install prompt, cache contents, and offline navigation were not interactively verified in DevTools Application.
- Keyboard / reduced-motion / every breakpoint for every page were not completed.
- Contact was tested in PHPUnit, not submitted from the browser form.
