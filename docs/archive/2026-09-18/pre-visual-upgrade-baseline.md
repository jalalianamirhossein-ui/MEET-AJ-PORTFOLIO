# Pre visual-upgrade baseline

**Status:** CURRENT (baseline snapshot)
**Date:** 2026-09-17
**Purpose:** Record the verified state of the application immediately **before** the visual UX / responsive / accessibility upgrade, so every later claim can be compared against it.
**Current state document:** [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md)
**Result of the upgrade:** [VISUAL-UX-FINAL-REPORT.md](VISUAL-UX-FINAL-REPORT.md)

No application behaviour was changed before this snapshot was taken.

## 1. Commands executed

| Command | Result |
|---------|--------|
| `php artisan optimize:clear` | PASS (caches cleared) |
| `php artisan test` | PASS — Tests: 39, Assertions: 647, Skipped: 1, Time 00:32 |
| `php artisan route:list --json` | PASS — 36 routes registered |
| `php artisan site:compare-content` | PASS — 23 rows checked, **Failures: 0** |

Runtime reported by PHPUnit: PHP 8.4.25, PHPUnit 11.5.56, configuration `phpunit.xml` (SQLite).

## 2. Database counts (live development SQLite database)

| Table | Rows |
|-------|------|
| `articles` | 23 (all `published`) |
| `article_redirects` | 23 |
| `services` | 6 (all `published`) |
| `categories` | 10 |
| `tags` | 8 |
| `article_tag` | 38 |
| `requests` | 0 |
| `users` | 0 |

## 3. Article inventory used by the listing UI

Filter class distribution (from `Article::filterClass()`, used by the library filter buttons):

| Filter class | Articles | Category label rendered on the card |
|--------------|----------|-------------------------------------|
| `filter-linux` | 6 | Linux |
| `filter-microsoft` | 5 | Microsoft |
| `filter-mikrotik` | 6 | Mikrotik |
| `filter-vmware` | 3 | Vmware |
| `filter-others` | 3 | Other |

There are **no** article categories for Networking, Security or DevOps. Those three exist only as **tags**. No category was invented for this upgrade.

Thumbnail availability: **0 missing**. Every published article resolves `thumbnailUrl()` to a file that exists on disk, so the listing has no legitimate reason to render an empty media area.

## 4. Verified front-end baseline (measured in the browser at 1605px viewport)

| Item | Baseline state |
|------|----------------|
| Article listing grid | `.isotope-container` is forced to `display: grid` by `visual-upgrade.css`; Isotope's absolute positioning is neutralised, so Isotope contributes **no** layout animation |
| Article filter behaviour | `.is-filtered-out { display: none !important }` — cards disappear and the grid re-flows in a single frame (no FLIP, no fade) |
| Article card content | thumbnail, category badge, title, excerpt, lightbox icon, CTA. **No tags, no metadata (date / reading time)** |
| Category badges | identical primary-blue pill for every category (no category accent) |
| Article search results | rendered as a plain numbered `<ol class="article-result-list">` of links — **no thumbnail, no card, no CTA** |
| Filter presentation | two stacked filter systems (server-side tag links **and** client-side category buttons) with different pill styling |
| Homepage Skills | `.progress-bar-wrap { display: none }` and `.val { display: none }` in `visual-upgrade.css` — **progress bars and percentages are not rendered at all**; values exist in markup (`aria-valuenow` 66–100) |
| Skills animation | depends on the `Waypoint` vendor library (loaded) writing inline `width`, but the bar is `display:none`, so nothing is visible |
| Language control | JS-injected **dropdown** (`button[aria-haspopup="listbox"]` + `#lang-menu` listbox) showing `EN ⌄` |
| Sidebar brand | profile photo, then a separate `.logo-section` row containing logo + "Meet AJ" **plus the language dropdown**, so the brand is not one centred composition |
| Mobile menu | full-screen shell with Escape, focus trap, `inert` background, scroll lock already implemented in `main.js` |
| Page `h1` on `/articles` | not present — first heading is the `h2` section title |

## 5. Public HTTP behaviour assumed unchanged by this upgrade

Verified in earlier phases and re-verified after the upgrade (see final report): 23 article URLs return 200, 23 legacy `.html` paths return 301, 6 service URLs return 200, `/admin` redirects to login, `/forms/contact.php` enforces CSRF + honeypot + rate limiting.

## 6. Known blockers at baseline

| Blocker | Impact |
|---------|--------|
| `users` table empty | Filament admin UI cannot be opened interactively without first creating a local development account |
| No Python on the workstation | `ui-ux-pro-max/scripts/search.py` cannot be executed; its CSV catalogues are read directly instead |
| No Lighthouse / axe available | performance and automated accessibility scores cannot be produced |
