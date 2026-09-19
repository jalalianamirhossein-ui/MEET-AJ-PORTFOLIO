# Admin panel — Meet AJ

**Authority:** AUTHORITATIVE Filament description.
**Verified:** 2026-09-18 against `app/Filament/**`, `app/Policies/**`, `app/Providers/Filament/AdminPanelProvider.php`, `resources/css/filament-admin.css`, `public/css/app/meet-aj-admin.css`, `php artisan route:list` (after `optimize:clear`), PHPUnit (`CmsOperationsTest`, `ServiceCatalogTest`, `RequestWorkflowTest`, `PublicSiteTest`, `ProductionAuditTest`, `FormCsrfAndAdminRequestsTest`, `AdminThemeTest`), a live contact POST that appears at `/admin/requests`, and authenticated Cursor-browser sessions for Admin and Editor.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md).

Panel: **Filament v5.8.2** on **Livewire v4.4.5**, mounted at `/admin`, brand name “Meet AJ CMS”, **White + Red** identity (canvas `#ffffff`, primary `#be123c`, gray palette Slate, danger `#7f1d1d`), collapsible sidebar, collapsible navigation groups, unsaved-changes alerts, and global search enabled.

A guest hitting `/admin` is redirected to `/admin/login`; the login page itself returns HTTP 200.

## Authentication

Filament session authentication on the `web` guard against the `users` table. Roles are `admin` and `editor` (`users.role`). Passwords are hashed with the Laravel hasher and must be at least 12 characters (enforced by both `cms:create-user` and the Users form). No default account ships with the repository.

```bash
php artisan cms:create-user
```

**The local `users` table currently has 3 rows** (admin / editor QA accounts plus a throwaway contrast-QA admin created 2026-09-18). Passwords are not stored in documentation.

## Navigation

```
Content (document icon)
├── Articles        newspaper
├── Categories      squares
├── Tags            hashtag
└── Services        briefcase (admins only)
Communications (inbox icon)
└── Requests        inbox; badge = count of `status=new` (white on `#7f1d1d`)
Administration (cog icon)
└── Users           users (admins only)

Active item: light crimson `#fff1f2` fill, 3px `#be123c` inset bar, crimson label. Hover uses the same soft red, not a saturated fill.
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

Form sections: Identity (title, slug, language `en`/`fa`/`de`, category, multi-select tags, excerpt); Image (optional upload, JPEG/PNG/WebP, max 5 MB); Body (`content`, required); SEO, collapsed (`meta_title`, `meta_description`, `canonical_url`); Publishing (`sort_order` with lower numbers displayed first, `status`, `published_at` in `config('cms.display_timezone')`, with helper text that German rows must stay draft). The article table defaults to this same `sort_order`.

Table: searchable and sortable title, gray language badge, **category colour chip** (dot + tinted pill + readable name from the public topic palette), gray tag badges, status, `published_at`, toggleable `updated_at`, plus filters and a default sort. Preview/Edit are gray; Delete stays danger. Changing a slug writes a new `article_redirects` row.

The category select on create/edit uses `allowHtml()` chips from `Category::accentChipHtml()`. The chip uses `Category::accentColor()` (stored `accent_color` when valid, otherwise the slug palette).

Public preview is the “Preview” / “View public page” action on published rows. There is no media library beyond the single upload field.

## Categories (`CategoryResource`) — Content

Simple CRUD on `categories` for admins and editors, respecting the unique `(language, slug)` and `(translation_key, language)` constraints. `sort_order` controls the public article filter order; lower numbers appear first.

The form includes an **Accent color** `ColorPicker` (`#RRGGBB`, nullable) with a live chip preview, hex readout, and a reset action that clears the field. Helper text: “Used for article cards, badges and category accents on the public site.” Empty values keep the slug fallback. Invalid values are rejected.

The table shows a colour chip on **Name**, an **Accent** hex badge of the **effective** colour (`Category::accentColor()`), and the filter `sort_order`.

`Category::accentColor()` returns stored `accent_color` when it is valid `#RRGGBB`; otherwise it maps the public topic slug:

| Topic key (from slug) | Fallback hex |
|-----------------------|--------------|
| microsoft / windows-server | `#2563eb` |
| linux | `#15803d` |
| mikrotik / networking | `#c2410c` |
| vmware | `#6d28d9` |
| security | `#be123c` |
| devops | `#0e7490` |
| others (default) | `#a16207` |

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

Interactive browser CRUD on this screen was exercised for login/navigation only; creating or deleting users was **not** part of this pass.

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

Admin branding is loaded **once as source**: `AdminPanelProvider` registers `Css::make('meet-aj-admin', resource_path('css/filament-admin.css'))`, which Filament publishes to `public/css/app/meet-aj-admin.css`. After CSS edits run `php artisan filament:assets`. The same published file is linked again on `PanelsRenderHook::STYLES_AFTER` so it wins against `filament/app.css` (which otherwise loads later and paints Filament 5.8 dark-variant white/`--gray-200` type onto our white surfaces). Meet AJ **admin** tokens are white canvas, slate type `#0f172a`/`#1e293b`, secondary `#475569`, muted `#64748b`, borders `#e2e8f0`, crimson `#be123c`, soft selected `#fff1f2`, danger `#7f1d1d`. Contrast locks are scoped as `html.fi` / `html.dark` on real Filament 5.8 classes (`.fi-fo-field-label-content`, `.fi-sidebar-item-label`, `.fi-fo-field-label` for Remember me) — not unscoped `.text-gray-*` utilities. The public site remains blue. `public/css/meet-aj-admin.css` is retired (comment-only, no rules). Sidebar, topbar, navigation groups, widgets, tables, forms, badges, buttons, unread request rows (`.meetaj-request-new`), category chips (`.meetaj-category-chip`), focus rings and compact breakpoints live in that single source file.

Filament `Color::hex('#be123c')` generates a light 400 swatch; `filament-admin.css` flattens primary buttons to solid crimson so Sign in / New article are not candy-pink. White is a **surface**; crimson is an **accent**. A 2026-09-18 contrast regression (invisible login labels and sidebar items) is documented and closed in [../qa/ADMIN-QA.md](../qa/ADMIN-QA.md).

## Testing status

| Check | Method | Status |
|-------|--------|--------|
| `/admin` guest redirect, `/admin/login` 200 | PHPUnit + HTTP | PASS |
| Article create / update / slug redirect | PHPUnit `CmsOperationsTest` | PASS |
| Editor denied on services | PHPUnit `ServiceCatalogTest` | PASS |
| Editor denied on requests, status workflow, internal notes hidden | PHPUnit `RequestWorkflowTest` + `ProductionAuditTest` + browser 403 | PASS |
| Contact form creates a Request visible at `/admin/requests` | PHPUnit `ProductionAuditTest` + live POST 2026-09-18 | PASS |
| White/Red theme + contrast lock + category chips + `accent_color` | PHPUnit `AdminThemeTest` + browser ColorPicker | PASS |
| Interactive login (Admin) | Cursor browser 2026-09-18 | PASS — labels/sidebar readable including forced `html.dark` |
| Admin responsive layout 1024 / 768 / 390 | Cursor browser | PASS |

Evidence: [../qa/ADMIN-QA.md](../qa/ADMIN-QA.md).
