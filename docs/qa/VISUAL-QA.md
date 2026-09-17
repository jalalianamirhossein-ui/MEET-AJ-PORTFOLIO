# Visual QA — Meet AJ

**Date:** 2026-09-17  
**Current status:** [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md) · **Accessibility:** [ACCESSIBILITY-QA.md](ACCESSIBILITY-QA.md) · **Responsive:** [RESPONSIVE-QA.md](RESPONSIVE-QA.md)  
**Overlay:** `assets/css/visual-upgrade.css?v=1404`  
**Scripts:** `main.js?v=1201`, `i18n.js?v=1201`, `lang-toggle.css?v=1202`  
**Latest visual pass:** [VISUAL-UX-FINAL-REPORT.md](VISUAL-UX-FINAL-REPORT.md)  
**Method:** Cursor browser snapshots + CDP overflow/computed styles. Screenshots are often **stale vs URL**; CDP and the accessibility tree are the visual/layout truth this pass.

Skills followed: visual-qa-testing (navigate, snapshot, screenshot, CDP resources/overflow), responsive-testing (320 / 375 / 412 / 1280 + HTTP), accessibility-auditing (aria tree, labels, headings, keyboard Escape, inert).

Article detail pages were **not** redesigned.

## Evidence this pass

| Surface | What was actually inspected | Result |
|---------|-----------------------------|--------|
| Homepage `/` | Snapshot FA hero/nav; CDP overlay 1314; H2 “بهتر منو بشناس” weight **700**; overflow **false** at **320** and **1280** and **412** | PASS (CDP). Screenshot panel cropped |
| Articles index | 23 H3 teasers (0 H4); skip link; labelled search; 8 tags; FA placeholder `عنوان، موضوع یا فناوری` | PASS |
| Search `?q=linux` | Status “8 نتیجه برای «linux»”; value `linux`; labelled searchbox | PASS |
| Article DNA `/articles/enable-ssh-linux-complete-guide` | H1 color `rgb(30, 41, 59)`; Breadcrumb nav; 2 JSON-LD scripts; share LinkedIn/WhatsApp/Telegram/copy; 3 related; overflow false at 1280 | PASS chrome. Body HTML untouched |
| Service Network Design | Full landing structure in a11y tree; AED 4,900; FAQ collapsed; **8 form fields, 0 visible** until quote; overflow false | PASS |
| Services 2–6 | HTTP **200** each; same `services/show.blade.php`. Independent screenshots this pass | **PARTIAL** — not separately snapshotted this turn |
| Mobile menu | Fullscreen `headerH === vh`; Close expanded; Escape closed; after `main.js?v=1119` background `main`/`footer` **inert**; tabbable **21** (menu + lang) | PASS |
| Admin login | Snapshot: labelled Email/Password, Sign in, Remember me. Title “Login - Meet AJ CMS” | PASS login a11y. Screenshot stale |
| Authenticated Filament | Not logged in (no production credentials; throwaway QA user deleted) | **BLOCKED** |
| Console / network | No failed overlay/i18n in CDP; dedicated DevTools console export not captured | WARN |

## Responsive matrix

Viewport measurements live in one place to avoid two competing matrices: [RESPONSIVE-QA.md](RESPONSIVE-QA.md).

Summary: overflow 0 confirmed at 320, 375, 412 and 1280 on the pages named there; all other widths and the whole authenticated admin are BLOCKED.

## Visual quality notes

- RTL FA homepage and article library read as one brand: navy text, primary buttons, H2 bar.
- Search button sits at inline-start in RTL (expected).
- Related-article **titles stay English** while chrome is Persian (source rows are EN). WARN, not a fake-translation FAIL.
- Homepage skill/value/cert headings remain `h4` in source (H2→H4 skip). WARN.
- Cursor screenshot panel often shows a previous page; do not treat those PNGs as URL proof.

## Automated

- `php artisan optimize:clear` — done
- `php artisan site:compare-content` — Failures: **0** (23 articles + 6 services + home). Compare command now calls `$kernel->terminate()` so the first article is not a false FAIL.
- `php artisan test` — **40 tests, 708 assertions, 1 skipped, 0 failures**
- HTTP HEAD/GET: 68 URLs, **0 unexpected** (home 200, `/index.html` 301, 23 articles 200, 23 legacy 301, 6 services 200 + 6 `.html` 301, csrf/sitemap/robots/manifest/sw/offline/admin login 200)
