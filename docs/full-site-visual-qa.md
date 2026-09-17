# Full-site visual QA — Meet AJ

**Date:** 2026-09-17  
**Overlay:** `assets/css/visual-upgrade.css?v=1310`  
**Method:** Cursor browser render + screenshots + CDP overflow. Not code-only.

Article detail pages were **not** redesigned. They are the visual DNA. Homepage and Services were aligned to that language.

## Stage loop

For each stage: render → screenshot → list problems → fix → re-render.

### Stage A — Homepage

Rendered: hero, Get to Know Me, Skills, Resume, Services preview, Articles preview, Testimonials, Contact, Footer. Desktop ~1280 and mobile 375.

Problems found (this pass):

1. Article H1 had inherited homepage white fill (fixed earlier: `body.index-page` scope + article restore).
2. Hero name sat on noisy stairs (fixed: left-weighted scrim + `.hero-bg` crop).
3. About H2 had no Article DNA bar (fixed).
4. About body used muted gray instead of `#1e293b` (fixed).
5. Skills jammed `name100%` with no domain headings (fixed: Technical / Professional; percentages hidden).
6. Screenshot panel crops the right column at 1280 (CDP `overflowX` is 0 — panel artifact).
7. Contact still uses icon tiles; form remains one grouping card (allowed).
8. Typed role line sits below the name; existing copy kept (`I'm a …`).
9. Mobile hero CTAs sit below the first screen (acceptable; name/role readable).
10. About-core map still uses a surface panel (grouping for the interactive visual, not a content card wall).

Re-render: About H2 bar + navy body; Skills lists; hero white name on scrim. Overflow 0 at 320, 375, 390, 414, 768, 1024, 1280, 1366, 1440, 1920 (homepage).

### Stage B — Services index

Homepage `#services` catalog: icon, title, short description, existing AED price, View Details / Request Service. Cards used for grouping six offerings. Third card cropped in screenshot panel; overflow 0.

### Stage C — Six service landings

All six `/services/{slug}` heroes rendered (1280). Network Design also: process, SLA, FAQ, quote CTA + revealed form; 375 stacked hero.

Problems:

1. Overlay loaded before `lang-toggle.css` on service/article show (fixed: overlay last).
2. Floating EN sat 5px from process `01` (fixed: block inset 6.5rem).
3. Form stayed hidden until Request a Quote (verified; Full Name focused after click).

### Stage D — Articles index

23 titles in the DOM. Filters: All / Microsoft / Linux / MikroTik / VMware / Other. Language listbox: EN + FA only (no DE).

### Stage E — Navigation / language

Desktop sidebar matches article chrome. Mobile: hamburger → fullscreen `header-show` (Close control, navy full-viewport menu). Switcher: EN/FA; DE not advertised.

### Stage F — Contact

Editorial heading + method links + one form surface. Send button uses primary blue.

### Stage G — Mobile

375 homepage: overflow 0, readable name, Available for Work, menu toggle. 375 service: stacked title/price/CTAs, overflow 0. 320 homepage overflow 0.

## Comparison (Article vs Homepage vs Service)

| Token | Article | Homepage | Service |
|-------|---------|----------|---------|
| Primary | `#2563eb` (topic themes local) | `#2563eb` | `#2563eb` |
| H1 | navy `#1e293b` | white on photo only | navy |
| H2 | bar + 2px rule | same | same |
| Buttons | 12px, 2.95rem | same | same |
| Cards | one reading surface | catalog / teasers / form | quote form only |
| Canvas | `#f4f7fb` | `#f4f7fb` | `#f4f7fb` |

## Console / network

No `__qaErrors` collector on the page. Snapshots did not surface JS exceptions. Dedicated DevTools console export: **not captured** this pass.

## Automated

- `php artisan optimize:clear` — done
- `php artisan site:compare-content` — Failures: 0 (23 articles + 6 services + home)
- `php artisan test` — 31 tests, 593 assertions, 1 skipped, 0 failures
- `php artisan route:list` — `/`, `/articles`, `/articles/{slug}`, `/services/{slug}`, contact endpoints, sitemap, robots. `manifest.json` / `sw.js` are public static files, not Laravel routes.
