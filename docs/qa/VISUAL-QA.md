# Visual QA — Meet AJ

**Date verified:** 2026-09-18  
**Method:** Cursor browser on `http://127.0.0.1:8000/` (lock → navigate → CDP `Runtime.evaluate` + a11y snapshots). Not a Lighthouse run.  
**Assets:** `main.css?v=1002`, `lang-toggle.css?v=1403`, `rtl.css?v=1405`, `visual-upgrade.css?v=1711`, `site-modules.css?v=1840`, `main.js?v=1412`, `i18n.js?v=1403`  
**Superseded:** [../archive/2026-09-18/VISUAL-QA.md](../archive/2026-09-18/VISUAL-QA.md)  
**Current status:** [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md)

## First Homepage load (direct URL, no prior in-app navigation)

| Check | Result |
|-------|--------|
| `#testimonials` present, `opacity: 1`, `aos-init aos-animate` | PASS |
| `#contact` present, `opacity: 1`, form `.php-email-form` | PASS |
| `window.AOS` is an object; `aos.js` + `aos.css` linked | PASS |

## Language switcher

| State | Background | Notes |
|-------|------------|--------|
| EN | white / slate type | Not burgundy |
| FA | `rgb(37, 99, 235)` / white type | Shared `#lang-toggle` on Home, Articles, services |

## Persian Homepage

`dir=rtl`, `lang=fa`, nav fully Persian, typed line in Arabic script, **0** visible `????` text nodes.

## Article titles in FA UI

Listing H3s remain English. `data-i18n-lock` on H1/cards.

## Contact hash

Homepage nav → `#contact` in viewport. From `/articles` (`href="/#contact"`) → in viewport.

## Expertise / تخصص‌ها (2026-09-18 redesign)

| Check | Result |
|-------|--------|
| Desktop 5 equal columns | PASS (`grid-template-columns` five tracks) |
| Pastel title pills + Bootstrap Icons | PASS (Infrastructure green, Networking blue, DevOps purple, Monitoring teal, Security orange) |
| Skill row 3px `border-inline-start` | PASS LTR = left; FA RTL = **right** (`border-right` carries accent) |
| Titles FA: تخصص‌ها / زیرساخت / شبکه / دواپس / مانیتورینگ / امنیت | PASS |
| `is-inview` after scroll | PASS (`initExpertiseReveal` in `main.js`) |
| Not heavy cards (no filled column backgrounds) | PASS |

## Mobile menu / sidebar

Icy-blue fullscreen header `<1200px`. Hamburger `inset-inline-start`, language `inset-inline-end`. Shared `site-sidebar` component.

## Remaining visual notes

- Authenticated Filament contrast: [ADMIN-QA.md](ADMIN-QA.md).
- Responsive overflow matrix: [RESPONSIVE-QA.md](RESPONSIVE-QA.md).
- About diagram whitespace above Expertise is intentional layout, not a regression of this redesign.
