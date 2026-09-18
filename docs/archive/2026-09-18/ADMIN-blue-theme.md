# Admin panel — Meet AJ

**Authority:** AUTHORITATIVE Filament description.
**Verified:** 2026-09-18 against `app/Filament/**`, `app/Policies/**`, `app/Providers/Filament/AdminPanelProvider.php`, `resources/css/filament-admin.css`, `php artisan route:list` (after `optimize:clear`), PHPUnit (`CmsOperationsTest`, `ServiceCatalogTest`, `RequestWorkflowTest`, `PublicSiteTest`, `ProductionAuditTest`, `FormCsrfAndAdminRequestsTest`), and a live contact POST that appears at `/admin/requests`.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md).

Panel: **Filament v5.8.2** on **Livewire v4.4.5**, mounted at `/admin`, brand name “Meet AJ CMS”, primary colour `#2563eb`, gray palette Slate, collapsible sidebar, collapsible navigation groups, unsaved-changes alerts, and global search enabled.

A guest hitting `/admin` is redirected to `/admin/login`; the login page itself returns HTTP 200.

## Authentication

Filament session authentication on the `web` guard against the `users` table. Roles are `admin` and `editor` (`users.role`). Passwords are hashed with the Laravel hasher and must be at least 12 characters (enforced by both `cms:create-user` and the Users form). No default account ships with the repository.

```bash
php artisan cms:create-user
```

**The local `users` table currently has 0 rows**, so there is no account to log in with until that command is run. Every check below that needs an authenticated browser session is therefore marked BLOCKED.

## Navigation

```
Content
├── Articles
├── Categories
├── Tags
└── Services        (admins only)
Communications
└── Requests        (admins only, badge = count of new requests)
Administration
└── Users           (admins only)
```

`ServiceResource`, `RequestResource` and `UserResource` gate themselves through `canViewAny()`; `UserResource::shouldRegisterNavigation()` returns true for admins, so Users **is** in the sidebar for an admin.

## Routes

`php artisan route:list` reports 14 `/admin` routes:

| Route | Name |
|-------|------|
| `GET /admin` | `filament.admin.pages.dashboard` |
| `GET /admin/login`, `POST /admin/logout` | `filament.admin.auth.login`, `filament.admin.auth.logout` |
| `GET /admin/articles`, `/admin/articles/create`, `/admin/articles/{record}/edit` | `filament.admin.resources.articles.*` |
| `GET /admin/categories` | `filament.admin.resources.categories.index` |
| `GET /admin/tags` | `filament.admin.resources.tags.index` |
| `GET /admin/services`, `/admin/services/create`, `/admin/services/{record}/edit` | `filament.admin.resources.services.*` |
| `GET /admin/requests` | `filament.admin.resources.requests.index` |
| `GET /admin/users` | `filament.admin.resources.users.index` |

## Dashboard

`app/Filament/Pages/Dashboard.php` with three widgets registered in the panel provider: `CmsStatsOverview`, `RecentArticles`, `RecentRequests`. There is no `NewRequests` widget on disk.

## Articles (`ArticleResource`) — Content

CRUD on `articles` for admins and editors (`ArticlePolicy` → `User::canManageContent()`).

Form sections: Identity (title, slug, language `en`/`fa`/`de`, category, multi-select tags, excerpt); Image (optional upload, JPEG/PNG/WebP, max 5 MB); Body (`content`, required); SEO, collapsed (`meta_title`, `meta_description`, `canonical_url`); Publishing (`status`, `published_at` in `config('cms.display_timezone')`, with helper text that German rows must stay draft).

Table: searchable and sortable title, language badge, category, status, `published_at`, toggleable `updated_at`, plus filters and a default sort. Changing a slug writes a new `article_redirects` row.

There is no public preview button and no media library beyond the single upload field.

## Categories (`CategoryResource`) — Content

Simple CRUD on `categories` for admins and editors, respecting the unique `(language, slug)` and `(translation_key, language)` constraints.

## Tags (`TagResource`) — Content

CRUD on `tags` for admins and editors (`TagPolicy`). `name` and `slug` are unique; the table shows an article count. The vocabulary can be rebuilt from article content with `php artisan articles:sync-tags`.

## Services (`ServiceResource`) — Content

**Admin only** (`ServicePolicy`). Editors receive an authorization failure, asserted in PHPUnit.

Form sections: General, Content (repeaters for features, process, FAQ), Pricing, Media, SEO, Publishing.
Table: title, language, status, formatted price, currency, sort order, published at, updated at. Filters: language, status, price type. Actions: preview, edit, replicate as draft, publish, unpublish, delete with confirmation; bulk publish skips German rows. Prices live here and nowhere else ([SERVICES.md](SERVICES.md)).

## Requests (`RequestResource`) — Communications

**Admin only** (`RequestPolicy`); `create` is denied, so requests can only arrive from the public form.

- The **Inbound message** section (`name`, `email`, `phone`, `subject`, `message`, and the linked service title) is disabled and not dehydrated — inbound data cannot be edited. Homepage submissions show “Homepage contact (no service)”.
- The **Handling** section exposes `status` and `internal_notes` only.
- Status options come from `Request::STATUSES`: New, Contacted, In discussion, Quoted, Approved, Completed, Cancelled, rendered as coloured badges.
- Table columns: name, email, service, phone (hidden by default), subject, message (hidden by default), status, received date, updated (hidden by default). Default sort is newest first, and rows with status `new` get the `meetaj-request-new` CSS class.
- Filters: status, service relationship, and a received-date range (from / until).
- Bulk actions: mark completed, mark cancelled, both with confirmation.
- The sidebar badge shows the number of `new` requests in red.

Detail: [REQUESTS.md](REQUESTS.md).

## Users (`Users\UserResource`) — Administration

**Admin only**: both `canViewAny()` and `shouldRegisterNavigation()` return `auth()->user()?->isAdmin()`.

Form: name, email (unique), role (Admin / Editor, default Editor), password with `Password::min(12)->max(72)`, dehydrated only when filled so editing without a new password keeps the old one.
Table: name, email, role badge, created-at (relative), role filter, edit action, and a delete action hidden for your own account. The empty state suggests `php artisan cms:create-user`.

Interactive browser CRUD on this screen is **BLOCKED / NOT TESTED** (no CMS user exists locally).

## Authorization summary

| Capability | Admin | Editor |
|------------|-------|--------|
| Articles, Categories, Tags CRUD | yes | yes |
| Services CRUD, pricing, publishing | yes | no |
| Requests view, status, internal notes | yes | no |
| Users CRUD | yes | no |
| `php artisan cms:create-user` | any operator with shell access | any operator with shell access |

Policies are registered in `AppServiceProvider`: `ArticlePolicy`, `CategoryPolicy`, `TagPolicy`, `ServicePolicy`, `RequestPolicy`, `UserPolicy`.

## Search, filters, pagination

Filament global search is enabled panel-wide. Table search is column-scoped (article titles, request name/email/subject, user name/email) — it is not full-text search over article HTML. Filters are listed per resource above. Pagination uses Filament table defaults; no custom page size is configured.

## Styling

Admin branding is loaded **once**: `AdminPanelProvider` registers `Css::make('meet-aj-admin', resource_path('css/filament-admin.css'))`. Meet AJ tokens are blue `#2563eb`, cyan `#0ea5e9`, slate gray. `public/css/meet-aj-admin.css` is retired (comment-only, no rules) so a second cascade cannot fight Filament/Livewire. Sidebar, topbar, navigation groups, widgets, tables, forms, badges, buttons, unread request rows (`.meetaj-request-new`), focus rings and compact breakpoints live in that single file.

## Testing status

| Check | Method | Status |
|-------|--------|--------|
| `/admin` guest redirect, `/admin/login` 200 | PHPUnit + HTTP | PASS |
| Article create / update / slug redirect | PHPUnit `CmsOperationsTest` | PASS |
| Editor denied on services | PHPUnit `ServiceCatalogTest` | PASS |
| Editor denied on requests, status workflow, internal notes hidden | PHPUnit `RequestWorkflowTest` + `ProductionAuditTest` | PASS |
| Contact form creates a Request visible at `/admin/requests` | PHPUnit `ProductionAuditTest` + live POST 2026-09-18 | PASS |
| Interactive login and editing in a browser | — | BLOCKED (no CMS user) |
| Admin responsive layout on small screens | code + overlay CSS; no authenticated session | NOT TESTED |

Evidence: [../qa/ADMIN-QA.md](../qa/ADMIN-QA.md).
