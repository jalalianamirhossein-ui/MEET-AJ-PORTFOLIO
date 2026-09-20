> **HISTORICAL phase log.** The overlay cache version recorded here has moved on several times (now `v=1314`). Current public, SEO and PWA status: [../current/SEO.md](../current/SEO.md), [../current/PWA.md](../current/PWA.md), [../qa/QA-MATRIX.md](../qa/QA-MATRIX.md).

# Phase 05 — Frontend, SEO, PWA, contact

**Phase:** 05
**Date:** 2026-09-16
**Status:** **PASS** for rendering, SEO endpoints and contact · PWA install/offline and a full viewport matrix **NOT TESTED** in this phase

## Objective

Serve the original site from Blade through Laravel without redesigning it, keeping every existing URL, CSS hook and JavaScript contract.

## Changes

- `php artisan site:publish-assets --views` copies original assets into `public/` and rebuilds Blade from the original HTML.
- Homepage, six service pages, article index and article detail rendered from Blade.
- Contact endpoints keep the legacy `.php` URLs and the `OK` / CSRF-JSON contracts.
- Dynamic `/sitemap.xml` and `/robots.txt`.
- Replacement `public/sw.js` and `public/offline.html`.
- `SecurityHeaders` middleware and the legacy `csrf_token` field name.
- Converted HTML wrapped in `@verbatim` so email addresses and JSON-LD `@type` survive Blade compilation.

Reusable Blade introduced: `components/article-card.blade.php` and `articles/show.blade.php`. Header, footer, navigation, language switcher and contact forms kept the original markup inside the converted page views.

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
curl.exe -sS -D - against the public URLs
php artisan site:compare-content
php artisan test
```

## Tests

`PublicSiteTest` covered the homepage, services, articles, redirects, 404s, sitemap and robots exclusions, contact persistence, honeypot, validation, the admin gate, and the absence of `/de`. Browser checks covered the homepage on desktop and ~320 px, an article in EN and FA, a service page, the articles index at desktop and ~375 px, and the Filament login screen.

## Results

- No `public/articles/`, `public/services/`, `public/index.html`, `public/sitemap.xml`, `public/robots.txt` or `public/forms/` directory: Laravel owns those routes.
- After the `@verbatim` fix, the homepage HTML contained the real `mailto:` address and JSON-LD `"@type"` keys.
- Service URLs still answered at their original `.html` paths with 200 at this point; the canonical clean `/services/{slug}` URLs with 301 from `.html` arrived in phase 07.

## Blockers

- PWA install prompt, cache contents and offline navigation were not verified in DevTools.
- Keyboard, reduced-motion and every breakpoint on every page were not completed.
- Contact was exercised in PHPUnit, not submitted from a real browser form.
