# Admin UI QA — Meet AJ Filament 5

**Date verified:** 2026-09-18  
**Panel:** `/admin` (Filament 5.8.2, Livewire 4.4.5)  
**Brand:** White surfaces, slate type, Meet AJ crimson `#be123c` accent, danger `#7f1d1d`.  
**Superseded (blue admin):** [../archive/2026-09-18/ADMIN-QA-blue-theme.md](../archive/2026-09-18/ADMIN-QA-blue-theme.md)

Local QA accounts exist (`qa-admin@meetaj.local` admin, `qa-editor@meetaj.local` editor). Passwords are not recorded here.

## Browser this pass

| Surface | Result | Evidence |
|---------|--------|----------|
| `/admin/login` | PASS | White card, slate labels, Sign in solid `#be123c` (not candy-pink 400) |
| Dashboard (admin) | PASS | White chrome; crimson active Dashboard; stats cards with 3px accent bar; Recent articles category chips |
| Articles table | PASS | Category chips (dot + tint + label); gray language/tags; gray Preview/Edit; brick Delete; crimson New article |
| Categories table | PASS | Name chips + Accent hex badges using public topic colours |
| Article edit form | PASS | Category select selected chip + dropdown chips (Microsoft `#2563eb`, Linux `#15803d`, MikroTik `#c2410c`, …). Save/Delete not submitted |
| Requests inbox | PASS | 5 rows, unread rose + crimson inset; View / Update status unchanged; sidebar badge `5` white-on-`#7f1d1d` |
| 1024×768 | PASS | Sidebar + Requests badge visible; table scrolls inside card; page `scrollWidth` 1009 ≤ 1024 |
| 768×1024 | PASS | Hamburger; no page overflow (`scrollWidth` 768) |
| 390×844 | PASS | Hamburger; open sidebar shows Requests `5` on active light-red item |
| Editor session | PASS | Nav = Dashboard + Articles/Categories/Tags only. `/admin/requests` → HTTP 403 |

## What was checked in code + PHPUnit (PASS)

| Surface | Result | Notes |
|---------|--------|--------|
| Single admin stylesheet | PASS | Source `resources/css/filament-admin.css`; published `public/css/app/meet-aj-admin.css` (HTTP 200). `public/css/meet-aj-admin.css` remains comment-only |
| White/Red tokens | PASS | `AdminThemeTest`; primary `#be123c`, danger `#7f1d1d`, canvas `#ffffff` |
| Category accents | PASS | Reuse public topic hex via `Category::accentColor()` (no DB column) |
| Communications → Requests | PASS PHPUnit | Nav + `/admin/requests`; editor forbidden |
| New-message badge / unread rows | PASS | Badge = count of `status=new`; `.meetaj-request-new` |
| Authorization | PASS PHPUnit + browser | Editor forbidden on Requests, Services, Users |
| Contact → inbox | PASS | PHPUnit + live row id 5 still listed |

## Failures

None. Authenticated White/Red visual QA is **PASS** for Admin and Editor on this workstation.
