> **HISTORICAL phase log.** Full evidence: [../qa/FINAL-QA-REPORT.md](../qa/FINAL-QA-REPORT.md), [../qa/DESIGN-AUDIT.md](../qa/DESIGN-AUDIT.md), [../qa/VISUAL-QA.md](../qa/VISUAL-QA.md), [../qa/ACCESSIBILITY-QA.md](../qa/ACCESSIBILITY-QA.md).

# Phase 09 — Master design audit and accessibility fixes

**Phase:** 09
**Date:** 2026-09-17
**Status:** **PASS** for the fixes applied · several checks **BLOCKED** and recorded as such

## Objective

Audit the whole site against the design system and accessibility expectations, then fix what the audit proved wrong — without redesigning article detail pages.

## Changes

1. `assets/js/i18n.js` now applies `data-*-placeholder` attributes, so the Persian search placeholder actually appears (`عنوان، موضوع یا فناوری`).
2. Article teaser titles changed from `h4` to `h3` in `components/article-card.blade.php`, with matching overlay selectors, removing an H2 → H4 heading skip in the library.
3. `assets/js/main.js` gained `setBackgroundInert()`, so opening the fullscreen mobile menu makes the rest of the page `inert`: tabbable elements drop from roughly 90 to 21.
4. Cache versions bumped in every publish point: `visual-upgrade.css?v=1314`, `main.js?v=1119`, `i18n.js?v=1116`.

## Files changed

`assets/js/i18n.js`, `assets/js/main.js`, `assets/css/visual-upgrade.css`, `resources/views/components/article-card.blade.php`, `index.html`, `app/Services/LegacySitePublisher.php`, `resources/views/{home,articles/index,articles/show,services/show}.blade.php`, plus the design and QA documents.

## Commands executed

```text
php artisan optimize:clear
php artisan site:publish-assets --views
php artisan site:compare-content
php artisan test
HTTP checks across 68 URLs
```

## Tests

- `php artisan test` → **39 tests, 647 assertions, 1 skipped, 0 failures**
- `php artisan site:compare-content` → **Failures: 0**
- HTTP: 68 URLs, **0 unexpected** statuses
- Browser: accessibility-tree snapshots and CDP measurements on the homepage, article library, one article, one service page, the mobile menu and the admin login page

## Results

Verified by measurement: article H1 colour `rgb(30, 41, 59)`, homepage H2 weight 700, zero horizontal overflow at 320 / 375 / 412 / 1280 on the pages tested, 23 H3 teasers and 0 H4 in the library, 8 form fields present but 0 visible on a service page until the CTA, and Escape closing the mobile menu.

Recorded as WARN, not fixed: homepage skill, value and certification titles still use `h4` in the source markup; related-article titles stay English in the Persian UI; “Back to Services” stays English in Persian.

## Blockers

- Authenticated Filament visual and responsive QA (no CMS user existed).
- Independent screenshots of five of the six service landings.
- Automated accessibility tooling (`axe`) and Lighthouse.
- Offline PWA behaviour.
- The Cursor screenshot panel frequently returned stale frames, so CDP and accessibility trees were used as the source of truth.
