# Admin UI QA — Meet AJ Filament 5

**Date verified:** 2026-09-18 (contrast regression re-test)  
**Panel:** `/admin` (Filament 5.8.2, Livewire 4.4.5)  
**Brand:** White surfaces, slate type, Meet AJ crimson `#be123c` accent, danger `#7f1d1d`.  
**Superseded (pre-contrast-fix White/Red):** [../archive/2026-09-18/ADMIN-QA-white-red-pre-contrast-fix.md](../archive/2026-09-18/ADMIN-QA-white-red-pre-contrast-fix.md)  
**Superseded (blue admin):** [../archive/2026-09-18/ADMIN-QA-blue-theme.md](../archive/2026-09-18/ADMIN-QA-blue-theme.md)

Local QA accounts exist. Passwords are not recorded here.

## Contrast regression (fixed)

White/Red chrome forced `#ffffff` surfaces while Filament 5.8 `app.css` (loaded **after** the custom asset) still painted **dark-variant** type:

| Filament rule | Effect on white surfaces |
|---------------|--------------------------|
| `.fi-fo-field .fi-fo-field-label-content:where(.dark, .dark *) { color: var(--color-white); }` | Email / Password / Remember me labels invisible; only the danger `*` stayed visible |
| `.fi-sidebar-item-label:where(.dark, .dark *) { color: var(--gray-200); }` | Articles, Categories, Tags, Services, Requests, Users nearly invisible |

A stale custom selector `.fi-fo-field-wrp-label` never matches Filament 5.8 (live class is `.fi-fo-field-label-content`). An earlier contrast-lock comment that pasted those Filament selectors could also close the CSS comment at `*` and drop every rule after it.

**Fix (source `resources/css/filament-admin.css`, then `php artisan filament:assets`):** scoped `html.fi` / `html.dark` locks to slate type (`#1e293b`) and `-webkit-text-fill-color`, no `:where()` in our override lists, and a `PanelsRenderHook::STYLES_AFTER` link so the published sheet also loads after `filament/app.css`. Red stays an accent. White stays a surface. Contact Requests PHP was not changed.

## Browser this pass

Computed colours via Cursor CDP `getComputedStyle`. Screenshots: `admin-login-contrast-fix.png`, `admin-login-dark-class-fixed.png`, `admin-login-after-submit.png`, `admin-dashboard-sidebar-contrast.png`, `admin-articles-contrast.png`, `admin-articles-filters-contrast.png`, `admin-categories-contrast.png`, `admin-services-contrast.png`, `admin-requests-contrast.png`, `admin-users-contrast.png`.

| Surface | Result | Evidence |
|---------|--------|----------|
| `/admin/login` labels | PASS | Email / Password / Remember me `rgb(30, 41, 59)` (`#1e293b`); required `*` `rgb(190, 18, 60)`; heading `rgb(15, 23, 42)` |
| `/admin/login` + forced `html.dark` | PASS | Same slate labels (no longer white-on-white) |
| `/admin/login` validation | PASS | “These credentials do not match our records.” `rgb(127, 29, 29)` (`#7f1d1d`); input text `#0f172a` on white |
| `/admin/login` Sign in | PASS | Button `background rgb(190, 18, 60)`, type `rgb(255, 255, 255)` |
| `/admin` sidebar | PASS | Inactive Articles…Users `rgb(30, 41, 59)`; groups `rgb(71, 85, 105)`; active Dashboard `rgb(159, 18, 57)` on `#fff1f2` |
| Sidebar + forced `html.dark` | PASS | Inactive items remain `#1e293b`; active remains `#9f1239` |
| `/admin/articles` | PASS | Active Articles crimson; table rows `#1e293b`; pagination overview `#1e293b`; “Per page” `#475569`; New article crimson |
| Articles filters dropdown | PASS | Language/Status/Category/Tags labels `#1e293b`; Apply filters crimson; Reset crimson accent |
| `/admin/categories` | PASS | Active Categories crimson; name chips + accent hex readable; Accent color ColorPicker with preview/reset |
| `/admin/services` | PASS | Active Services crimson; title/price/status readable; New service crimson |
| `/admin/requests` | PASS | Active Requests crimson; unread rose rows; View / Update status still present; badge `5` white-on-`#7f1d1d`. **Requests PHP unchanged.** |
| `/admin/users` | PASS | Active Users crimson; names/emails/`Per page` readable |

## Category accent colours (2026-09-18)

`categories.accent_color` is nullable `#RRGGBB`. Unedited rows keep the previous slug palette via `Category::accentColor()`. Public cards, filters, badges and related teasers read that method (inline `--topic`), not a second CSS map. Article form category chips use the same value.

Browser check on `/admin/categories`: Linux edit modal shows **Accent color** ColorPicker, helper “Used for article cards, badges and category accents on the public site.”, live Preview chip + hex, and Reset. Empty Linux stays `#15803d` (Slug fallback). Table Accent column shows the effective hex.

## WCAG (normal text on `#ffffff`)

| Token | Hex | Approx. contrast |
|-------|-----|------------------|
| Primary text | `#0f172a` / `#1e293b` | ≥ 15:1 |
| Secondary | `#475569` | ~7.0:1 |
| Muted | `#64748b` | ~4.6:1 |
| Danger | `#7f1d1d` | ~9.5:1 |
| Active nav | `#9f1239` | ≥ 4.5:1 on white / `#fff1f2` |

## What was checked in code + PHPUnit

| Surface | Result | Notes |
|---------|--------|--------|
| Contrast lock selectors | PASS | `AdminThemeTest` asserts `html.dark .fi-fo-field-label-content`, `html.fi .fi-sidebar-item-label`, `html.fi .fi-checkbox-label`, late `data-meetaj="admin-contrast-late"` |
| `filament:assets` + `optimize:clear` | PASS | Published `public/css/app/meet-aj-admin.css` |
| Contact → Requests | unchanged | No edits to `RequestResource` or request workflow |

## Failures

None remaining for this contrast pass. Editor 403 and mobile breakpoints were verified in the earlier White/Red session (archived report) and were not re-run here.
