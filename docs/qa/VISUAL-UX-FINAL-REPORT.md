# Visual UX + functional QA — final report

**Status:** CURRENT  
**Date:** 2026-09-17  
**Overlay:** `visual-upgrade.css?v=1402` · `main.js?v=1201` · `i18n.js?v=1201` · `lang-toggle.css?v=1201`  
**Baseline before this pass:** [pre-visual-upgrade-baseline.md](pre-visual-upgrade-baseline.md)  
**Tests this pass:** 40 tests, 708 assertions, 1 skipped. `site:compare-content` Failures: 0.

This is not a from-scratch redesign. The existing identity (Poppins + Vazirmatn, `#2563eb`, iPortfolio sidebar, article DNA) is preserved. Changes restore regressions and make listing, search, skills, language and article images match the current product.

## Problems found

| Problem | Evidence | Fix |
|---------|----------|-----|
| Article search results were a text-only `<ol>` with no thumbnails | `articles/partials/search-results.blade.php` used `article-result-list` links | Search now includes `components.article-card` |
| Overlay hid skill bars and percentages | `body.index-page .skills .progress-bar-wrap { display: none }` | Bars and values restored; IntersectionObserver fills 0 → `aria-valuenow` |
| Overlay killed filter motion | `position: static !important; transform: none !important; display: none !important` | CSS grid kept; custom FLIP (380ms, transform + opacity) |
| Language control was a dropdown listbox | `aria-haspopup="listbox"` + `#lang-menu` | Single `[ EN ⇄ ]` / `[ FA ⇄ ]` button |
| Language sat in the logo row, breaking brand centering | `.logo-section .lang-switcher { margin-inline-start: auto }` | Brand is logo+“Meet AJ” as one unit; language under it (`#lang-mount`) |
| Article detail banner image was `display: none` | `articles.css` `.article-page .article-banner { display: none }` | Overlay shows the per-article `galleryUrl()` image |
| Search empty/clear layout collided with cards | Missing `.article-search-head` layout | Flex header; reset only when `q` or tag is active |
| Cards were one uniform blue | Category badge used `--color-primary` only | Topic tokens on badge, 3px edge, CTA, filter active |

Homepage `#portfolio` / `#contact` were **already unique** in the live DOM (1 each). A regression test now locks that. The “duplicated articles/contact” look was empty vertical rhythm plus search listing vs homepage catalog, not a second Blade include.

## Results

| Item | Status | Evidence |
|------|--------|----------|
| Articles section renders once (`id="portfolio"`) | PASS | CDP counts + `PublicSiteTest::test_homepage_renders_each_primary_section_once` |
| Contact renders once | PASS | same |
| Services / skills / resume / testimonials unique | PASS | same |
| Article search shows thumbnails | PASS | `/articles?q=linux` cards + PHPUnit asserts `article-teaser-media` and `thumbnailUrl()` |
| Article detail shows that article’s image | PASS | Nginx detail `src=/assets/img/portfolio/linux-1.png`, box 1125×448 after unhide |
| Article filter layout + FLIP implementation | PASS | Linux filter pressed; only Linux cards remain; motion is Web Animations API 380ms. Frame-by-frame filmstrip not recorded |
| Category color accents | PASS | Linux green, Microsoft blue, MikroTik orange on badges/borders/CTAs; card fill stays white |
| Sidebar logo + “Meet AJ” as one unit | PASS | CDP: logo 40px then name, composition ~148px, centered in sidebar |
| Language is one clickable button, not a dropdown | PASS | No `#lang-menu`, no `aria-haspopup`; label “Switch language to فارسی” |
| Language keeps the current route | PASS | `/services/network-design` stayed on that URL in FA |
| Skills bars + existing percentages | PASS | Screenshot + CDP widths 100/92/90/88/77 matching `aria-valuenow` |
| Skills IO animation | PASS | Bars start at 0, fill once in view; `prefers-reduced-motion` skips WAAPI |
| Service landing + quote form | PASS | Form opens, labelled fields, CSRF/honeypot unchanged; RTL screenshot |
| Mobile menu Escape / inert / scroll lock | PASS | Existing `main.js` behaviour unchanged this pass |
| RTL (FA) | PASS | Service form + chrome mirrored; language control `FA ⇄` |
| LTR (EN) | PASS | Homepage, listing, article detail |
| Horizontal overflow at ~1425px | PASS | CDP `overflow: false` on home and service |
| Viewports 320 / 390 / 414 / 768 / 1024 / 1366 / 1440 / 1920 | NOT TESTED | Emulation 375 attempted; screenshot canvas stayed desktop. No dedicated resize tool |
| Automated axe / Lighthouse | NOT TESTED | Not installed |
| Authenticated Filament admin | BLOCKED | `users` table empty; login page still public |
| PWA `/admin` `/livewire` `/forms` exclusion | PASS | unchanged `sw.js` `PRIVATE_PREFIXES` |
| Article HTML body integrity | PASS | `site:compare-content` Failures: 0 across 23 articles |
| Console errors | NOT TESTED | No DevTools console export this pass |

## Design system (tokens)

Global tokens live in `:root` of `assets/css/visual-upgrade.css`. Category accents (badge, top edge, CTA, active filter only):

| Topic | Token | Used for |
|-------|-------|----------|
| Microsoft / Windows | `--topic-microsoft: #2563eb` | `filter-microsoft` |
| Linux | `--topic-linux: #15803d` | `filter-linux` |
| MikroTik / Networking | `--topic-mikrotik: #c2410c` | `filter-mikrotik` |
| VMware | `--topic-vmware: #6d28d9` | `filter-vmware` |
| Security | `--topic-security: #be123c` | tag accent only (no article category) |
| DevOps | `--topic-devops: #0e7490` | tag accent only |
| Other | `--topic-other: #a16207` | `filter-others` |

There are still no Networking / Security / DevOps **categories** in the database. Those remain tags.

## Remaining issues

- Homepage articles section id is still `portfolio` (legacy iPortfolio). Nav hashes use `#portfolio`.
- Related-article titles stay English in FA chrome (source rows are EN). WARN.
- Homepage skill group headings still skip H2→H4 in source HTML. WARN.
- Service quote “Subject” is an English CMS default string even in FA.
- Authenticated admin visual QA remains BLOCKED until a local CMS user exists.
- Ten named viewports were not all screenshotted; do not treat the responsive matrix as complete.

## Git

No git init, commit, or push was performed.
