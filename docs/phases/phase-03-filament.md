> **HISTORICAL phase log.** This phase installed three resources and a `NewRequests` widget; both the resource set and the widgets changed later. Authoritative admin description: [../current/ADMIN.md](../current/ADMIN.md). Current status: [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md).

# Phase 03 — Filament admin

**Phase:** 03
**Date:** 2026-09-15 to 2026-09-16
**Status:** **PASS** for installation and routing · interactive browser CRUD **BLOCKED**

## Objective

Install Filament 5 as the only admin UI, with role-gated access and no shipped default password.

## Changes

- Filament 5.8.2 panel at `/admin` with login.
- Resources at the time: Articles, Categories, Requests.
- Widget at the time: `NewRequests` (later replaced by `CmsStatsOverview`, `RecentArticles`, `RecentRequests`).
- `User` implements Filament panel access; `canViewAny` is role-gated.
- Requests admin-only; editors may manage articles and categories.

## Files changed

- `app/Providers/Filament/AdminPanelProvider.php`
- `bootstrap/providers.php`
- `app/Filament/Resources/ArticleResource.php` (+ List / Create / Edit pages)
- `app/Filament/Resources/CategoryResource.php`
- `app/Filament/Resources/RequestResource.php`
- `app/Filament/Widgets/NewRequests.php`
- `composer.json`, `composer.lock`

## Commands executed

```text
composer require filament/filament:^5.0
php artisan route:list
php artisan test
```

`route:list` at the time included `/admin`, `/admin/login`, `/admin/articles`, `/admin/articles/create`, `/admin/articles/{record}/edit`, `/admin/categories`, `/admin/requests`.

## Tests

`test_admin_login_page_is_public_and_panel_is_protected`: `/admin/login` → 200, `/admin` as guest → redirect, `actingAs(admin)` → 200. HTTP crawl confirmed `/admin/login` 200 and `/admin` 302. The Filament login screen rendered with the title “Login - Meet AJ CMS”.

## Results

The panel boots and no default password is shipped; accounts are created with `php artisan cms:create-user`.

## Blockers

- `cms:create-user` is interactive and was not used to create a durable admin on this workstation.
- Browser CRUD (create/edit/delete an article, open a request) was never exercised interactively. This remains BLOCKED today.
- Filament uploads need `php artisan storage:link` on the host.
