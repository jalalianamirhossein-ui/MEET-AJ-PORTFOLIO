# Full-site design audit — Meet AJ

**Date:** 2026-09-17  
**Source of truth:** live overlay `visual-upgrade.css?v=1314`, Article DNA, Filament branding.  
**Skills used:** ui-ux-pro-max catalogs (read directly), frontend-design (Article DNA as the distinctive language; avoid SaaS-card kit / glass everywhere / emoji CTAs).

## Verdict of the current visual system

The public site is **one product**, not six coexisting templates.

| Surface | Visual language | Status |
|---------|-----------------|--------|
| Article detail | Reading document: canvas `#f4f7fb`, H1 `#1e293b`, H2 bar, 12px buttons | REFERENCE — not redesigned. Verified H1 `rgb(30, 41, 59)` |
| Homepage | Editorial portfolio on the same tokens | Implemented. About H2 weight **700**. Hero is photo + typography, not a SaaS dashboard |
| Services | Shared landing (`services/show.blade.php`) | Implemented. Price is typography, not a SaaS table. Form gated |
| Articles index | Library + search/tags | Implemented. H2 → H3 teasers. FA search placeholder applied |
| Contact / Footer | Primary `#2563eb` footer, labelled form | Implemented |
| Nav / i18n | Solid compact listbox + fullscreen mobile | Implemented (`main.js?v=1119` background inert) |
| Admin | Native Filament 5 + `#2563eb` | Login labelled. Authenticated chrome **not visually inspected** this pass |

## Hierarchy

Article DNA: H1 display, H2 bar, H3 subsections. Homepage section titles use the same H2 bar. Service landings: category/kicker → H1 → description → price → CTAs in whitespace, not one giant card.

Remaining skip: homepage skill/value/cert blocks still use `h4` after section `h2` in `index.html`.

## Typography

- LTR: Poppins 400/600/700. RTL: Vazirmatn 400–700.
- Body 16px. Article H1 clamp unchanged.
- Overlay last, so service pages load the same families.

## Color

Brand primary `#2563eb`. Text `#1e293b`. Canvas `#f4f7fb`. Green = success/availability only. Topic greens/oranges stay inside `.article-page.theme-*`.

## Spacing / grid

Scale 4 / 8 / 12 / 16 / 24 / 32 / 48 / 64 / 80 / 96. Reading column 56rem. Catalog ~70rem. Control height 2.95rem.

## Components

Buttons 12px / 700 / ≥44px. Pills for tags/filters/kickers only. Cards only for grouping (service preview, article teaser, forms, quotes).

## Do not regress

- Article body HTML
- 23 articles / 23 redirects / 6 services
- Contact CSRF + honeypot + rate limit
- No fake German
- No git this pass
