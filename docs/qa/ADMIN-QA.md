# Admin UI QA — Meet AJ Filament 5

**Date:** 2026-09-17  
**Panel:** `/admin` (Filament 5.8.2, Livewire 4.4.5)  
**Brand:** `#2563eb`, existing logo, groups Content / Communications / Administration.

## Browser this pass

| Surface | Result | Evidence |
|---------|--------|----------|
| `/admin/login` | PASS (a11y tree) | Title “Login - Meet AJ CMS”. Textboxes named Email address / Password (required). Show password. Remember me. Sign in. HTTP 200 |
| Login visual PNG | BLOCKED | Screenshot tool returned a stale articles-index frame |
| Dashboard / Articles / Categories / Tags / Requests / Users (authenticated) | **BLOCKED** | No production credentials used. Throwaway local QA user was created earlier then **deleted**. Auto-review previously blocked password fill |
| Admin responsive 414 / 768 / 1024 / 1280 | **BLOCKED** | Requires authenticated session |

## What was checked in code + PHPUnit (PASS)

| Surface | Result | Notes |
|---------|--------|--------|
| Dashboard widgets | PASS in code | `CmsStatsOverview` (Articles, Published, Drafts, Categories, Tags, Requests, New requests), `RecentArticles`, `RecentRequests`. Counts query the live database |
| Articles | PASS PHPUnit | Search, filters (language, status, category, tags), HTML body preserved |
| Categories | PASS PHPUnit | Unique constraints in the model |
| Tags | PASS PHPUnit | `TagResource` create/edit/delete/search, unique name+slug |
| Requests | PASS PHPUnit | Search, filters, seven workflow statuses, internal notes, editor forbidden |
| Users | PASS in code | `shouldRegisterNavigation()` true for admins. Table shows name/email/role, not password. Password dehydrated only when filled |
| Authorization | PASS PHPUnit | Public contact cannot set `status` or `internal_notes` |
| Native Filament | PASS | Light branding only (`filament-admin.css` / `meet-aj-admin.css`). Not a consumer landing page |

## Failures

None identified in code or PHPUnit. **Visual PASS is not claimed** for authenticated Admin.
