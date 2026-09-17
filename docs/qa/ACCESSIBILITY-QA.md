# Accessibility QA — Meet AJ

**Date:** 2026-09-17
**Method:** Cursor browser accessibility tree snapshots, CDP computed styles and tabbable-element counts, plus keyboard interaction on the mobile menu.
**Related:** [VISUAL-QA.md](VISUAL-QA.md) · [RESPONSIVE-QA.md](RESPONSIVE-QA.md) · [FINAL-QA-REPORT.md](FINAL-QA-REPORT.md)

No automated accessibility engine was run. `axe`, Lighthouse and Pa11y are all **BLOCKED / NOT TESTED**, so this is a manual audit with named evidence.

## Passing checks

| Check | Evidence |
|-------|----------|
| Skip link present on the article library | Accessibility tree shows the skip link as the first focusable element |
| Landmarks | `banner`, `navigation`, `main`, `contentinfo` present in the tree |
| Search field is labelled | Searchbox exposes an accessible name; Persian placeholder `عنوان، موضوع یا فناوری` applied after the `i18n.js` fix |
| Search results announced | Status region reports the result count, for example “8 نتیجه برای «linux»” |
| Heading hierarchy on the article library | Section H2 followed by 23 teaser H3 headings, 0 H4 (previously H4, corrected in `components/article-card.blade.php`) |
| Article detail breadcrumbs | `nav[aria-label="Breadcrumb"]` plus a `BreadcrumbList` JSON-LD block |
| Mobile menu closes on Escape | Keyboard Escape closes the fullscreen menu |
| Background is inert while the menu is open | After `main.js?v=1119`, tabbable elements drop to 21 (menu plus language switcher) from roughly 90 |
| Closed header is out of the tab order | Verified in the tabbable count with the menu closed |
| Admin login form | Textboxes named “Email address” and “Password”, both required, with show-password and remember-me controls |
| Article H1 contrast colour | Computed `rgb(30, 41, 59)` on a light background |

## Known issues (WARN)

| Issue | Where | Note |
|-------|-------|------|
| Heading level skip H2 → H4 | Homepage skill, value and certification titles in `index.html` | Source markup; not corrected in this pass |
| Related-article titles stay English in the Persian UI | Article detail | The database rows are English; translating them would mean inventing content |
| “Back to Services” stays English in the Persian UI | Service detail | Source-content leftover |

These are recorded as WARN, not FAIL: they are real imperfections with no proven functional break.

## Not tested

| Check | Status | Reason |
|-------|--------|--------|
| `axe` or any automated a11y engine | BLOCKED | Not installed / not run |
| Full Tab tour of every page | BLOCKED | Only the mobile menu and library were traversed |
| Screen reader pass (NVDA / VoiceOver) | NOT TESTED | No assistive technology session |
| Colour contrast audit across all components | NOT TESTED | Only the article H1 colour was measured |
| `prefers-reduced-motion` behaviour | NOT TESTED | Declared in CSS but never toggled at the OS level |
| Authenticated admin accessibility | BLOCKED | No CMS user exists locally |
