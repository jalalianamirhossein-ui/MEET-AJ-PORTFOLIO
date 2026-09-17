# Visual UX + functional QA — final report

**Status:** CURRENT  
**Date:** 2026-09-17  
**Overlay:** `visual-upgrade.css?v=1404` · `main.js?v=1201` · `i18n.js?v=1201` · `lang-toggle.css?v=1202`  
**Baseline before this pass:** [pre-visual-upgrade-baseline.md](pre-visual-upgrade-baseline.md)  
**Tests this pass:** 40 tests, 708 assertions, 1 skipped. `site:compare-content` Failures: 0.

This is not a from-scratch redesign. The existing identity (Poppins + Vazirmatn, `#2563eb`, iPortfolio sidebar, article DNA) is preserved. Changes restore regressions and make listing, search, skills, language and article images match the current product.

Skills followed: ui-ux-pro-max (portfolio/editorial, 44px targets, FLIP spatial continuity, reduced-motion), frontend-design (restrained category accents, not rainbow cards), visual-qa-testing, responsive-testing, accessibility-auditing.

## Problems found

| Problem | Evidence | Fix |
|---------|----------|-----|
| Article search results were a text-only `<ol>` with no thumbnails | `articles/partials/search-results.blade.php` used `article-result-list` links | Search now includes `components.article-card` |
| Overlay hid skill bars and percentages | `body.index-page .skills .progress-bar-wrap { display: none }` | Bars and values restored; IntersectionObserver fills 0 → `aria-valuenow` |
| Overlay killed filter motion | `position: static !important; transform: none !important; display: none !important` | CSS grid kept; custom FLIP (380ms, transform + opacity) |
| Language control was a dropdown listbox | `aria-haspopup="listbox"` + `#lang-menu` | Single `[ EN ⇄ ]` / `[ FA ⇄ ]` button |
| Language sat in the logo row, breaking brand centering | `.logo-section .lang-switcher { margin-inline-start: auto }` | Brand is logo+“Meet AJ” as one unit; language under it (`#lang-mount`) |
| RTL reversed the brand to “Meet AJ” then logo | flex `row` follows `dir=rtl` | Brand lock `direction: ltr` so order is always [logo] [Meet AJ] |
| Article detail banner image was `display: none` | `articles.css` `.article-page .article-banner { display: none }` | Overlay shows the per-article `galleryUrl()` image |
| Article detail header had no `#lang-mount` | `articles/show.blade.php` | Mount added; switch stays on the article URL |
| `site:compare-content` false FAIL on first article | HTTP kernel reused without `terminate()` | Command now terminates each request. Failures: 0 |

Homepage `#portfolio` / `#contact` were **already unique** in the live DOM (1 each). A regression test now locks that. The “duplicated articles/contact” look was empty vertical rhythm plus search listing vs homepage catalog, not a second Blade include.

## Results

| Item | Status | Evidence |
|------|--------|----------|
| Articles section renders once (`id="portfolio"`) | PASS | CDP counts + `PublicSiteTest::test_homepage_renders_each_primary_section_once` |
| Contact renders once | PASS | same |
| Services / skills / resume / testimonials unique | PASS | same |
| Article search shows thumbnails | PASS | `/articles?q=linux` a11y tree: Preview image links + PHPUnit `article-teaser-media` and `thumbnailUrl()`. CDP: search-head bottom 407px, first card top 431px (no overlap). Screenshot compositor sometimes paints a stale frame |
| Article detail shows that article’s image | PASS | Nginx detail `src=/assets/img/portfolio/linux-1.png`, banner `display:block`, box ~1605×448 |
| Article filter layout + FLIP implementation | PASS | Linux filter pressed earlier this pass; only Linux cards remain; motion is Web Animations API 380ms. Frame-by-frame filmstrip not recorded |
| Category color accents | PASS | Linux green, Microsoft blue, MikroTik orange on badges/borders/CTAs; card fill stays white |
| Sidebar logo + “Meet AJ” as one unit | PASS | CDP LTR: img left 80px, name left 129px, unit width ~139px, `direction:ltr`. Screenshot: wolf mark immediately before “Meet AJ”, centered in the sidebar |
| Language is one clickable button, not a dropdown | PASS | `#lang-toggle` button, no `#lang-menu`, no `aria-haspopup`; labels “Switch language to فارسی” / “تغییر زبان به English” |
| Language keeps the current route | PASS | `/services/network-design` stayed on that URL in FA; article detail keeps its slug |
| Skills bars + existing percentages | PASS | Screenshot + earlier CDP widths 100/92/90/88/77 matching `aria-valuenow` |
| Skills IO animation | PASS | Bars start at 0, fill once in view; `prefers-reduced-motion` skips WAAPI |
| Service landing + quote form | PASS | Form opens, labelled fields, CSRF/honeypot unchanged; RTL screenshot earlier this pass |
| Mobile menu Escape / inert / scroll lock | PASS | Existing `main.js` behaviour unchanged this pass |
| RTL (FA) | PASS | Brand stays [logo][Meet AJ]; language `FA ⇄`; chrome mirrored |
| LTR (EN) | PASS | Homepage, listing, article detail, admin login tree |
| Horizontal overflow at current desktop (~1425 and login at 320) | PASS | CDP `overflow: false` on home, articles search, admin login at 320 (`scrollWidth === 320`) |
| Viewports 375 / 390 / 414 / 768 / 1024 / 1366 / 1440 / 1920 | NOT TESTED | 320 measured this pass (admin login overflow 0; articles search visible in emulation). Named screenshot matrix incomplete |
| Automated axe / Lighthouse | NOT TESTED | Not installed |
| Authenticated Filament admin | BLOCKED | `users` table empty; login page a11y tree PASS |
| PWA `/admin` `/livewire` `/forms` exclusion | PASS | unchanged `sw.js` `PRIVATE_PREFIXES` |
| Article HTML body integrity | PASS | `site:compare-content` Failures: 0 across 23 articles (after kernel terminate fix) |
| Console errors | NOT TESTED | No DevTools console export this pass. CDP: 0 broken images on the Nginx detail page |

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
- Cursor screenshot frames are often stale versus the live URL; CDP and the accessibility tree are the layout truth when they disagree.

## Git

No git init, commit, or push was performed.
