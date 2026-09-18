# Documentation QA — sync report

**Date:** 2026-09-18 (evening documentation sync)  
**Scope:** documentation only. No application behaviour change in this pass. No git commit.  
**Result:** **PASS**  
**Current source of truth:** [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md)

Earlier reorganisation history (2026-09-17 moves, deletes = 0, link repair) remains valid background; this file now states **what is current after the 2026-09-18 product work**.

## 1. What this sync corrected

| Stale claim in docs | Reality | Files updated |
|---------------------|---------|---------------|
| Suite “39 / 647” or “55 / 1067” as current | **58 tests, 1124 assertions, 1 skipped** | `PROJECT-STATUS`, `TESTING`, `PROJECT-STRUCTURE`, `DEPLOYMENT`, `QA-MATRIX`, `FINAL-QA-REPORT`, `historical/README` |
| 9 migrations / no `accent_color` | **10** app migrations; `categories.accent_color` nullable | `DATABASE` (already), `PROJECT-STRUCTURE`, `QA-MATRIX` |
| Overlay `site-modules?v=1820` / `main.js?v=1406` / `visual-upgrade?v=1707` | `site-modules?v=1832`, `main.js?v=1412`, `visual-upgrade?v=1710`, `lang-toggle?v=1403`, `i18n?v=1403` | `FINAL-QA-REPORT`, `VISUAL-QA`, `FEATURES`, `ARCHITECTURE`, `DESIGN-SYSTEM`, `PROJECT-STATUS` |
| Admin authenticated BLOCKED / 0 users | Local QA users exist; White/Red + contrast PASS | `ADMIN-QA`, `PROJECT-STATUS`, `ADMIN`, `FINAL-QA-REPORT` |
| Expertise described as plain blue uppercase columns | Pastel pills, icons, LTR/RTL markers, reveal JS | `FEATURES`, `DESIGN-SYSTEM`, `VISUAL-QA`, `PROJECT-STATUS` |
| Duplicate “Article library” row in status table | Removed | `PROJECT-STATUS` |
| Test file inventory missing `AdminThemeTest` / `ProductionAuditTest` / `FormCsrf…` | Full Feature list | `PROJECT-STRUCTURE`, `TESTING` |

Archive under `docs/archive/2026-09-18/` was **left as historical** (blue admin, pre-contrast, pre-audit). Not rewritten.

## 2. Document map (current)

| Folder | Role |
|--------|------|
| `docs/current/` | Authoritative product docs; `PROJECT-STATUS.md` wins on conflict |
| `docs/qa/` | Living evidence (FINAL, MATRIX, VISUAL, ADMIN, …) |
| `docs/archive/2026-09-18/` | Superseded same-day / prior snapshots |
| `docs/historical/` | Pre-Laravel and early migration records |
| `docs/phases/` | Chronological build logs (not current status) |
| `docs/decisions/ADR/` | Decisions still in force |

## 3. Verification used for the sync

| Check | Result |
|-------|--------|
| Live Blade cache strings | `site-modules.css?v=1832`, `main.js?v=1412` in home/articles/services + publisher |
| Feature test inventory | 58 `test_*` methods across 10 Feature files |
| Migrations | `2026_09_18_000010_add_accent_color_to_categories_table` present |
| Expertise markup | `data-expertise` on five columns in `home.blade.php` / `index.html` |
| Category accents | `Category::accentColor()`, Filament ColorPicker, `AdminThemeTest` |

## 4. Remaining documentation uncertainties

| Item | Status |
|------|--------|
| Application version constant | UNKNOWN / NOT VERIFIED |
| Git remote | UNKNOWN / NOT VERIFIED (no git commands in this pass) |
| MySQL `MysqlSchemaTest` | Last green 2026-09-16; not re-run tonight |
| Production / Lighthouse / PWA install | BLOCKED / NOT TESTED |
| Users CRUD create/delete | NOT TESTED |

## 5. Verdict

**Documentation sync: PASS.** Current and QA docs match the running app’s asset versions, migration count, test suite size, Filament White/Red + accents, and Homepage Expertise redesign. Historical and archive files remain labelled non-authoritative.
