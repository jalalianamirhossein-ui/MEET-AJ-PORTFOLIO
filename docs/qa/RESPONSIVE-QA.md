# Responsive QA — Meet AJ

**Date verified:** 2026-09-18  
**Method:** Chrome DevTools `Emulation.setDeviceMetricsOverride` on the live Homepage, then `document.documentElement.scrollWidth` vs `innerWidth`.  
**Superseded:** [../archive/2026-09-18/RESPONSIVE-QA.md](../archive/2026-09-18/RESPONSIVE-QA.md)

| Viewport | Overflow-x | Testimonials / Contact | Language switcher |
|----------|------------|------------------------|-------------------|
| 1920×1080 | none (`scrollWidth` 1905) | visible | FA blue |
| 1440×900 | none (1425) | visible | — |
| 1024×768 | none (1009) | visible | menu toggle present |
| 768×1024 (mobile flag) | none (768) | visible | — |
| 390×844 (mobile, DPR 2) | none (390) | opacity 1, heights 556 / 1718 | 44×66px, visible, `#2563eb` |

No horizontal overflow at the five required widths. Authenticated Admin breakpoints remain **NOT TESTED**.
