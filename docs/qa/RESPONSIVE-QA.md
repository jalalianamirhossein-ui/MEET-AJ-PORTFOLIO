# Responsive QA — Meet AJ

**Date:** 2026-09-17
**Method:** Cursor browser with CDP. The overflow test is `document.documentElement.scrollWidth > clientWidth + 1`; a cropped screenshot is **not** evidence of overflow.
**Related:** [VISUAL-QA.md](VISUAL-QA.md) · [ACCESSIBILITY-QA.md](ACCESSIBILITY-QA.md) · [QA-MATRIX.md](QA-MATRIX.md)

Only the cells that were actually measured are marked PASS. Everything else is BLOCKED or NOT TESTED and must not be upgraded without a new run.

## Public pages

| Viewport | Homepage `/` | Articles index | Article detail | Service detail |
|----------|--------------|----------------|----------------|----------------|
| 320 × 800 | PASS (overflow 0) | NOT TESTED | NOT TESTED | NOT TESTED |
| 375 × 812 | NOT TESTED (412 measured instead) | PASS (overflow 0) | NOT TESTED | NOT TESTED |
| 412 (device emulation) | PASS (overflow 0) | NOT TESTED | NOT TESTED | NOT TESTED |
| 1280 × 800 | PASS (overflow 0) | NOT TESTED at this width | PASS (overflow 0) | PASS (overflow 0) |

Pages measured: `/`, `/articles`, `/articles/enable-ssh-linux-complete-guide`, `/services/network-design`.

## Widths not re-measured on 2026-09-17

390, 414, 768, 1024 × 768, 1366, 1440 and 1920: **BLOCKED this pass.** An earlier audit recorded overflow 0 on the homepage across that set, but that result is historical and is not claimed as current.

## Admin panel

| Viewport | Result |
|----------|--------|
| 414 / 768 / 1024 / 1280 | **BLOCKED** |

Reason: the `users` table is empty, so no authenticated Filament session could be opened. Admin responsive behaviour is unverified.

## Layout behaviour confirmed while measuring

- The mobile menu below 1200 px opens full screen: measured header height equals the viewport height.
- The articles library switches from the Isotope grid to paginated results when `?q=` or `?tag=` is present, at every width tested.
- The service quote form stays collapsed (8 fields present, 0 visible) until the CTA is used, at every width tested.

## Summary

| Item | Status |
|------|--------|
| Measured viewports show zero horizontal overflow | PASS |
| Full 10-width screenshot matrix | BLOCKED |
| Authenticated admin at any width | BLOCKED |
| Print or reduced-motion layout variants | NOT TESTED |
