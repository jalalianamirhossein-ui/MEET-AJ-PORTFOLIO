# Admin (Filament) — Meet AJ

**Authority:** AUTHORITATIVE Filament 5 description.  
**Verified:** 2026-09-16 against `app/Filament/**`, policies, `php artisan route:list`, and PHPUnit (`CmsOperationsTest`, `PublicSiteTest`, `ServiceCatalogTest`).

Panel URL: **`/admin`**. Guest hitting `/admin` is redirected to login. `/admin/login` is public (HTTP 200).

Authentication: Filament session auth against `users`. Roles: `admin`, `editor`. Password: hashed, minimum **12** characters. Create accounts with `php artisan cms:create-user` (see README). No default password is shipped.

Brand: `#2563eb`, logo from existing site assets. Navigation groups: **Content**, **Communications**, **Administration**.

```
Content
├── Articles
├── Categories
├── Tags
└── Services
Communications
└── Requests
Administration
└── Users (admins only)
```

Interactive browser login as an editor was **not** repeated in the documentation verification pass. PHPUnit covers login page visibility, panel protection, article CRUD, service CRUD authorization, and request authorization.

## Resources actually implemented

### Articles (`ArticleResource`) — Content

CRUD for `articles`. Admin and editor (`ArticlePolicy` / `canManageContent()`).

Form sections:

- **Identity:** title, slug, language (`en`/`fa`/`de`), category, tags (multi), excerpt
- **Image:** optional upload (JPEG/PNG/WebP, max 5 MB). Paths already under `/assets/` stay unless replaced
- **Body:** HTML or Filament RichEditor (`content` required)
- **SEO (collapsed):** `meta_title`, `meta_description`, `canonical_url` (blank → public article URL)
- **Publishing:** `status` draft/published, `published_at` in `config('cms.display_timezone')`. Helper text: German must remain draft. Future `published_at` is not visible on the public site without a queue (visibility is query-based)

Table: searchable/sortable title, language badge, category name, status, `published_at`, `updated_at` (toggleable). Filters and default sort exist on the resource. Slug changes write `article_redirects`.

Do not claim a public preview button or media library beyond this upload field.

### Tags (`TagResource`) — Content

CRUD for `tags`. Admin and editor (`TagPolicy` / `canManageContent()`). Unique `name` and `slug`. Table shows article count. Seed/repair with `php artisan articles:sync-tags` (catalog is derived from live article titles/categories; Docker is not included).

### Categories (`CategoryResource`) — Content

CRUD for `categories`. Unique `(language, slug)` and `(translation_key, language)`. Admin and editor.

### Services (`ServiceResource`) — Content

CRUD for `services`. **Admin only** (`ServicePolicy`). Editors receive authorization failure (PHPUnit).

Form sections: General, Content (repeaters for features / process / FAQ), Pricing, Media, SEO, Publishing.

Table: title, language, status, price (formatted), currency, sort order, published at, updated at. Filters: language, status, price type. Actions: preview (published EN), edit, replicate, publish, unpublish, delete (confirmed). Bulk publish skips German rows.

Prices are edited here only — never hardcoded in Blade. See [SERVICES.md](SERVICES.md).

### Requests (`RequestResource`) — Communications

Admin **only** (`RequestPolicy`). Editors receive authorization failure (PHPUnit).

- Inbound fields (`name`, `email`, `phone`, `subject`, `message`) are **read-only**
- Linked service title is shown when `service_id` is set; homepage contacts show “no service”
- Filter by service, status, and received date
- `status` workflow: `new`, `contacted`, `in_discussion`, `quoted`, `approved`, `completed`, `cancelled`
- `internal_notes` (admin only; `$hidden` on the model; never in contact mail or public JSON)
- Filament **create** of requests is denied (`create` policy false)
- No claim of reply-from-admin or SMTP from this screen

### Users (`Users\UserResource`) — Administration

The class exists (`app/Filament/Resources/Users/UserResource.php`) with form fields for name, email, role, password and `canViewAny` = admin.

**`shouldRegisterNavigation()` returns true for admins**, so Users appears in the Administration group.

After `php artisan optimize:clear` (2026-09-16), `route:list` **does** include:

- `GET /admin/users` → `filament.admin.resources.users.index`
- `GET /admin/cms-users` → the same route name (extra `authenticatedRoutes` registration in `AdminPanelProvider`)

Create users with:

```bash
php artisan cms:create-user
```

Interactive Filament Users CRUD in a browser remains **NOT TESTED**. Do not treat the duplicate index URL as a second product feature.

## Widgets

Registered in `AdminPanelProvider`: `CmsStatsOverview`, `RecentArticles`, `RecentRequests`.

## Authorization summary

| Action | Admin | Editor |
|--------|-------|--------|
| Articles / categories CRUD | yes | yes |
| Services CRUD / prices / publish | yes | no |
| View/update/delete requests | yes | no |
| Filament Users UI | intended admin-only; **UI not routed** | no |
| `cms:create-user` | CLI (any operator with shell) | CLI |

## Search, filters, sorting

Articles table: title search, language/status columns sortable, category shown. Do not claim full-text search of HTML bodies.

Services table: title search, language/status/price type filters, `sort_order` default.

## Image handling

Uploads go through Filament file upload into public storage. Imported `featured_image` values often remain `/assets/...`. Max 5 MB, JPEG/PNG/WebP. Service model rejects executable suffixes.

## What is not implemented

- Public `/de` admin preview
- Users navigation / registered users index route
- Filament-created contact rows
- Queue-based scheduled publishing (visibility is `published_at <= now()` in queries)
- Penetration-tested admin hardening
