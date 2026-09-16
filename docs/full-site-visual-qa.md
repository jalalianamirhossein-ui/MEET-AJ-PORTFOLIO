# Full-site visual QA — Meet AJ

**Date:** 2026-09-16  
**Environment:** `http://127.0.0.1:8000` · overlay `visual-upgrade.css?v=1120`  
**Language during browser pass:** Persian (`localStorage` `lang=fa`). English markup is the default; FA strings come from `data-fa`.  
**Screenshot canvas** is wider than the emulated viewport; overflow is measured with `scrollWidth - clientWidth`, not the white remainder.

## Viewports actually tested

| Width | Height | Surfaces | Overflow | Visual screenshot |
|-------|--------|----------|----------|-------------------|
| 320 | 800 | Homepage | 0 | overflow only |
| 375 | 812 | Home, mobile menu, articles index, article detail, service | 0 | yes (home, menu, articles, service) |
| 390 | 844 | Article detail | 0 | overflow only |
| 414 | 896 | Article detail | 0 | overflow only |
| 768 | 1024 | Homepage | 0 | overflow only |
| 1024 | 768 | Homepage | 0 | overflow only |
| 1280 | 800 | Homepage | 0 | overflow only |
| 1366 | 768 | Service | 0 | overflow only |
| 1440 | 900 | Home, network-design, form open | 0 | yes |
| 1920 | 1080 | devops-automation | 0 | yes |

## Surfaces

| Surface | Result | Notes |
|---------|--------|-------|
| Homepage hero | PASS | Static role line visible; name/role/typed/CTAs. Typed.js cursor can overlap FA typed string (`\|\|VMwar…`) — remaining. |
| Get to Know Me | PASS | Existing premium layout kept; token-aligned. |
| Stats / Skills / Resume | PASS | Skill bars `rgb(37, 99, 235)` both columns. Resume wraps; no page overflow. |
| Services index | PASS | Six catalog cards; CTAs 44px. |
| Service landings (all six HTML) | PASS | Fetch+DOM: h1, hidden form, CSRF, JSON-LD, canonical. Network + DevOps screenshotted. |
| Quote form | PASS | Hidden until CTA; click revealed form, focused `#contact-name`, overflow 0. |
| Articles index | PASS | 23 cards, 6 `<button>` filters, 375 overflow 0. |
| Article detail | PASS | Chrome only; `pre` scrolls internally (`maxPreOverflow` 697 at 375); page overflow 0. |
| Testimonials | PASS | Existing pause on focus / `document.hidden` / reduced motion (code + interactive bullets present). Autoplay not re-timed in this pass. |
| Contact / Footer | PASS | Two-column desktop already in `main.css`; footer aliased to primary. Map `max-height: 200px`. |
| Navigation | PASS | Desktop sidebar; mobile fullscreen `opacity:1`, `navTop` 213 / `socialTop` 692 at 375. |
| Language switcher | PASS | Options `en`, `fa` only. No `[data-de]`. |
| Reduced motion | PASS | CSS `@media (prefers-reduced-motion: reduce)` on overlay + AOS `disable` on service pages. OS setting not toggled in browser. |

## Failures / remaining

1. Typed.js FA overlap on the hero subtitle (source animation, not a layout overflow).
2. Exhaustive Chromium console/ARIA audit across every URL: **not captured** this pass (see final report BROWSER QA).
3. Filament `/admin` interactive login: **not opened** (no credentials in this pass).
