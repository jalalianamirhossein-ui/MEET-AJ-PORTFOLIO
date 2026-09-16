> **HISTORICAL / SUPERSEDED phase log.** Authoritative Filament description: [ADMIN.md](../ADMIN.md). Users navigation was later hidden because the index route was missing.

# Phase 3 — Filament

Status: **installed and route-tested**. Interactive editorial CRUD in a browser session was not completed (no production admin user).

## Implemented work

- Filament 5.8.2 panel at `/admin` with login.
- Resources: Articles, Categories, Requests.
- Widget: NewRequests.
- `User` implements Filament access; `canViewAny` is role-gated.
- Requests are admin-only; editors may manage articles/categories.

## Files changed

- `app/Providers/Filament/AdminPanelProvider.php`
- `bootstrap/providers.php`
- `app/Filament/Resources/ArticleResource.php` (+ List/Create/Edit pages)
- `app/Filament/Resources/CategoryResource.php`
- `app/Filament/Resources/RequestResource.php`
- `app/Filament/Widgets/NewRequests.php`
- `composer.json` / `composer.lock`

## Commands executed

```text
composer require filament/filament:^5.0
php artisan route:list
php artisan test
```

`php artisan route:list` includes:

- `GET /admin` → Filament dashboard
- `GET /admin/login`
- `GET /admin/articles`, `/admin/articles/create`, `/admin/articles/{record}/edit`
- `GET /admin/categories`
- `GET /admin/requests`

## Tests executed

`test_admin_login_page_is_public_and_panel_is_protected`:

- `/admin/login` → 200
- `/admin` guest → redirect
- actingAs admin → 200

HTTP crawl: `/admin/login` 200; `/admin` 302 to `/admin/login`.

Browser: Filament login screen titled “Login - Meet AJ CMS” rendered.

## Real results

- Panel boots.
- No default password is shipped. Create an admin with `php artisan cms:create-user` on the host.

## Blockers

- `cms:create-user` is interactive and was not used to create a durable admin on this workstation.
- Browser CRUD (create/edit/delete article, view a request) was not exercised interactively.
- Filament file uploads write under `storage` / `public/storage`; `php artisan storage:link` is required on the host.
