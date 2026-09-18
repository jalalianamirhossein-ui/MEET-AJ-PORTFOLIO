# Features — Meet AJ

**Authority:** AUTHORITATIVE description of public and CMS features.  
**Verified:** 2026-09-18 against Blade heads, `Category` / Filament resources, homepage Expertise markup, and PHPUnit.  
**Assets in live Blade heads:** `main.css?v=1002`, `lang-toggle.css?v=1403`, `rtl.css?v=1405`, `visual-upgrade.css?v=1710`, `site-modules.css?v=1832`, `main.js?v=1412`, `i18n.js?v=1403`, `contact-form.js?v=1403`, `service-catalog.js?v=1813`.  
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md) · **Detail:** [ARTICLES.md](ARTICLES.md), [SERVICES.md](SERVICES.md), [REQUESTS.md](REQUESTS.md), [ADMIN.md](ADMIN.md), [DESIGN-SYSTEM.md](DESIGN-SYSTEM.md)

## Public site

| Feature | Where | Notes |
|---------|--------|--------|
| Editorial homepage | `/` | Hero, Get to Know Me, Expertise / تخصص‌ها, resume, six service previews, article teasers, testimonials, contact |
| Expertise / تخصص‌ها | Homepage About | Five columns (Infrastructure, Networking, DevOps, Monitoring, Security). Pastel title pills + icons. Skill rows use 3px `border-inline-start` (left LTR, right RTL). Data unchanged. Reveal via `initExpertiseReveal()` in `main.js` |
| Shared sidebar | All public layouts | `resources/views/components/site-sidebar.blade.php` + `partials/site-sidebar-chrome.blade.php` |
| Mobile menu | `<1200px` | Fullscreen `#header.header-show`, icy-blue header (not burgundy). Hamburger `inset-inline-start`, language `inset-inline-end`. Escape closes. Background `inert` when open. Closed header not in tab order |
| Testimonials | `#testimonials` | One Swiper for EN and FA. RTL follows `html[dir]`. Previous/Next stay correct in Persian. Not a second FA carousel |
| Article library | `/articles` | Default Isotope grid of all 23 published EN cards. Teaser titles are H3. Search/tag mode switches to paginated results and does **not** send article bodies to the browser |
| Category accents | Cards, badges, filters, related | `Category::accentColor()` — stored `categories.accent_color` when valid `#RRGGBB`, else slug palette. Inline `--topic` / `--article-primary` |
| Article search | `GET /articles?q=` | `Article::scopeSearch` on title, excerpt, HTML content, category name, tag name/slug. LIKE queries; no Meilisearch/Algolia. Live status region (verified: 8 results for `linux`) |
| Tags | `GET /articles?tag=` | Unique `tags.slug`. Chips: DevOps, Linux, Microsoft, MikroTik, Networking, Security, VMware, Windows Server |
| Article detail | `/articles/{slug}` | Original HTML body preserved. Breadcrumbs, category, published date (only if `published_at` exists), reading time, tags, share links, related articles |
| Related articles | end of detail | Up to 3: matching tags → same category → same language published. Never the current row, never drafts |
| Sharing | detail | LinkedIn / WhatsApp / Telegram / copy link. No tracking pixels |
| Breadcrumbs | detail | Visible `nav[aria-label=Breadcrumb]` + separate `BreadcrumbList` JSON-LD (`$seo['breadcrumb']`) beside Article schema |
| Services | `/services/{slug}` | Six landings from one Blade. Quote fields exist (8) but **0 visible** until Request a Quote |
| Languages | EN / FA UI | Persian placeholder applied (`عنوان، موضوع یا فناوری`). German remains draft-only. No fake DE articles or hreflang |
| Contact | `/forms/*.php` | CSRF + honeypot + throttle; rows land in Filament Requests |

## CMS (Filament 5)

White + Red admin (`#be123c` / `#fff1f2` / `#7f1d1d`). Public site stays blue.

| Resource | Who | What |
|----------|-----|------|
| Articles | admin, editor | Create/edit/delete, language, category, tags, status, slug, published date. Category chips use `accentColor()`. Trusted HTML is not sanitized destructively |
| Categories | admin, editor | Unique `(language, slug)`. Editable `accent_color` ColorPicker (live preview, hex, Reset). Empty keeps the slug palette. Invalid hex rejected |
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
- Public CSS cascade: `main.css` → `lang-toggle.css` → `rtl.css` → `visual-upgrade.css` → `site-modules.css` (last overlay).
