# Features — Meet AJ

**Authority:** AUTHORITATIVE description of public and CMS features after the 2026-09-17 master audit.  
**Overlay:** `visual-upgrade.css?v=1405`  
**Scripts:** `i18n.js?v=1201`, `main.js?v=1201`  
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md) · **Detail:** [ARTICLES.md](ARTICLES.md), [SERVICES.md](SERVICES.md), [REQUESTS.md](REQUESTS.md), [ADMIN.md](ADMIN.md)

## Public site

| Feature | Where | Notes |
|---------|--------|--------|
| Editorial homepage | `/` | Hero, Get to Know Me, expertise, resume, six service previews, article teasers, testimonials, contact |
| Article library | `/articles` | Default Isotope grid of all 23 published EN cards. Teaser titles are H3. Search/tag mode switches to paginated results and does **not** send article bodies to the browser |
| Article search | `GET /articles?q=` | `Article::scopeSearch` on title, excerpt, HTML content, category name, tag name/slug. LIKE queries; no Meilisearch/Algolia. Live status region (verified: 8 results for `linux`) |
| Tags | `GET /articles?tag=` | Unique `tags.slug`. Chips: DevOps, Linux, Microsoft, MikroTik, Networking, Security, VMware, Windows Server |
| Article detail | `/articles/{slug}` | Original HTML body preserved. Breadcrumbs, category, published date (only if `published_at` exists), reading time, tags, share links, related articles |
| Related articles | end of detail | Up to 3: matching tags → same category → same language published. Never the current row, never drafts. SSH guide showed 3 peers |
| Sharing | detail | LinkedIn / WhatsApp / Telegram / copy link. No tracking pixels |
| Breadcrumbs | detail | Visible `nav[aria-label=Breadcrumb]` + separate `BreadcrumbList` JSON-LD (`$seo['breadcrumb']`) beside Article schema |
| Services | `/services/{slug}` | Six landings from one Blade. Quote fields exist (8) but **0 visible** until Request a Quote |
| Languages | EN / FA UI | Persian placeholder applied (`عنوان، موضوع یا فناوری`). German remains draft-only. No fake DE articles or hreflang |
| Mobile menu | `<1200px` | Fullscreen `#header.header-show`. Escape closes. Background `inert` when open (`tabbableCount` 21 vs ~90). Closed header not in tab order |

## CMS (Filament 5)

| Resource | Who | What |
|----------|-----|------|
| Articles | admin, editor | Create/edit/delete, language, category, tags, status, slug, published date. Trusted HTML is not sanitized destructively |
| Categories | admin, editor | Unique `(language, slug)`. Editable `accent_color` ColorPicker; empty keeps the slug palette |
| Tags | admin, editor | Unique name and slug. `php artisan articles:sync-tags` seeds the catalog from real titles/categories |
| Services | admin | Catalog CMS; editors forbidden |
| Requests | admin | CRM-lite statuses: New, Contacted, In discussion, Quoted, Approved, Completed, Cancelled. Internal notes never leave Filament (`$hidden`, not in contact mail, not in the public `create()` payload) |
| Users | admin | Sidebar registered for admins. Password hashed; hashes never shown in the table |
| Dashboard | signed-in | Live counts: articles, published, drafts, categories, tags; admins also see total and new requests plus recent tables |

## Architecture notes

- Search lives on the `Article` model (`scopeSearch` / `scopeWithTag`) so a later engine can replace the query without rewriting Blade.
- Listing queries use `scopeForListing` and omit `content`.
- Policies: `TagPolicy` (content managers), `RequestPolicy` (admin only).
- PWA still excludes `/admin`, `/livewire`, `/forms`.
- Contact validation is `StoreContactRequest`. Status/internal notes cannot be posted from the public form.
