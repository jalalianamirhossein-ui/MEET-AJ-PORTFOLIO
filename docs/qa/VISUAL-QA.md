# Visual QA — Meet AJ

**Date verified:** 2026-09-18  
**Method:** Cursor browser tab `http://127.0.0.1:8000/` (lock → navigate → CDP `Runtime.evaluate` + a11y snapshots). Not a Lighthouse run.  
**Assets:** `visual-upgrade.css?v=1707`, `site-modules.css?v=1820`, `lang-toggle.css?v=1401`, `main.js?v=1406`, `i18n.js?v=1402`  
**Superseded:** [../archive/2026-09-18/VISUAL-QA.md](../archive/2026-09-18/VISUAL-QA.md)

## First Homepage load (direct URL, no prior in-app navigation)

| Check | Result |
|-------|--------|
| `#testimonials` present, `opacity: 1`, height ~593px, `aos-init aos-animate` | PASS |
| `#contact` present, `opacity: 1`, height ~1008px, form `.php-email-form` | PASS |
| `window.AOS` is an object; `aos.js` + `aos.css` linked | PASS |

## Language switcher

| State | Background | Notes |
|-------|------------|--------|
| EN | `rgba(255, 255, 255, 0.92)` / slate type | Not burgundy |
| FA | `rgb(37, 99, 235)` / white type | Shared `#lang-toggle` on Home, Articles, services |

## Persian Homepage

`dir=rtl`, `lang=fa`, nav fully Persian (`صفحه اصلی` … `تماس با من`), typed line e.g. `مدیر VMware`, **0** visible `????` text nodes after restoring `data-fa` and `data-typed-items-fa`.

## Article titles in FA UI

All listing H3s remained English (e.g. `Nginx Installation & Configuration Ubuntu`). `data-i18n-lock` on H1/cards.

## Contact hash

Homepage nav `تماس با من` → URL `#contact`, section `top ≈ 96px`, in viewport. From `/articles` (`href="/#contact"`) → `http://127.0.0.1:8000/#contact`, in viewport.

## Remaining visual issues

Authenticated Filament screens not photographed (no CMS user). Unused per-service Blade files are not in the public route.
