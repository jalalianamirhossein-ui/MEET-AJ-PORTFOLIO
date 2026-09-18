# Final project QA report — Meet AJ production audit

**Date verified:** 2026-09-18  
**Overlay:** `visual-upgrade.css?v=1707` · `site-modules.css?v=1820` · `lang-toggle.css?v=1401`  
**Scripts:** `i18n.js?v=1402`, `main.js?v=1406`  
**Admin CSS:** `resources/css/filament-admin.css` only (`public/css/meet-aj-admin.css` retired)  
**Git:** no add, no commit, no push.  
**Current status:** [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md)  
**Superseded 2026-09-17 snapshot:** [../archive/2026-09-18/FINAL-QA-REPORT.md](../archive/2026-09-18/FINAL-QA-REPORT.md)  
**Superseded blue-admin QA:** [../archive/2026-09-18/FINAL-QA-REPORT-blue-admin.md](../archive/2026-09-18/FINAL-QA-REPORT-blue-admin.md)

**Later Filament contrast regression (login labels / sidebar nearly invisible) was found and fixed the same day.** Current admin evidence: [ADMIN-QA.md](ADMIN-QA.md). This file’s White/Red “PASS” predates that fix.

## Executive summary

Meet AJ remains Laravel 13 + PHP 8.4 + Filament 5 + Livewire 4 + Blade. This audit repaired the Contact → Requests inbox, Homepage first-load visibility, Persian encoding, the language switcher, English article titles, and Filament CSS cascade.

`php artisan site:compare-content` → **Failures: 0**.  
`php artisan test` → **55 tests, 1067 assertions, 1 skipped (`MysqlSchemaTest`), 0 failures** after the White/Red admin pass (includes `AdminThemeTest`).  
Live contact POST → SQLite `requests.id = 5`, HTTP 200 `OK`.

## Acceptance matrix

| Item | Status | Evidence |
|------|--------|----------|
| Filament admin CSS single source, White/Red | PASS (code + browser) | `AdminPanelProvider` loads `filament-admin.css`; published `public/css/app/meet-aj-admin.css`; primary `#be123c` |
| Communications → Requests in admin nav | PASS (PHPUnit) | `RequestResource` navigation registered; editor forbidden |
| Contact form → validation → DB → `/admin/requests` | PASS | PHPUnit + live POST id 5 |
| New/unread badge, read-only inbound fields, 7 statuses, notes, search, filters | PASS (code + PHPUnit) | `RequestResource` |
| Contact menu Homepage + Articles, EN/FA | PASS (browser) | `#contact` in viewport; `/articles` uses `/#contact` |
| Testimonials + Contact on first Homepage load | PASS (browser + PHPUnit) | opacity 1, `aos-animate`, non-zero height; AOS 2.3.4 vendored |
| FA encoding (no `????`) | PASS (browser + PHPUnit) | restored `data-fa`, typed FA roles; `i18n.js` Unicode-safe Arabic check |
| FA RTL / EN LTR | PASS (browser) | `dir=rtl` after switch; switcher blue `#2563eb` |
| Shared language switcher (not burgundy) | PASS (browser) | EN white/slate; FA filled blue; `site-modules.css` burgundy override removed |
| Article titles remain English in FA UI | PASS (browser + PHPUnit) | `data-i18n-lock`; 6 live DB titles repaired to English |
| Admin sees Articles, Categories, Tags, Services, Requests, Users | PASS (code + PHPUnit) | editors denied Services/Requests/Users |
| Viewports 1920 / 1440 / 1024 / 768 / 390 | PASS (browser CDP) | no horizontal overflow; sections remain visible at 390 |
| Authenticated Filament visual click-test | PASS (browser) | Admin + Editor 2026-09-18; White/Red; category chips; editor 403 on Requests |
| Production deploy / SMTP / Lighthouse | BLOCKED / NOT TESTED | unchanged |

## Root causes (this audit)

1. **First-load Testimonials/Contact:** AOS 2.3.4 CSS was referenced while `aos.js`/`aos.css` were missing or incomplete, leaving `[data-aos]` at opacity 0 until a later navigation re-inited scripts. Hash scroll waited on `window.load` only.
2. **FA `?????`:** `index.html` `data-fa` / `data-typed-items-fa` had been saved with CP1252 replacement question marks. `i18n.js` used `/\p{Arabic}/u`, which failed to parse in the embedded browser and prevented the switcher from mounting.
3. **Burgundy language button:** `site-modules.css` last-layer `!important` still painted `.lang-switcher-toggle` with `--menu-burgundy-*`.
4. **Requests inbox:** `RequestResource` had been hidden (`shouldRegisterNavigation false`) in favour of split Contact/Service inboxes.
5. **Dual admin CSS:** Filament asset + `HEAD_END` `meet-aj-admin.css` loaded the same overlay twice.
6. **Persian article titles:** `meta_title` / `og:title` came from legacy HTML `<title>` (often Persian) while `articles.title` was the English hero; FA UI also swapped H1 via `data-fa`.

## Remaining known issues

- Six unused per-service Blade files still ship stale asset versions; `services.show` is what Laravel renders.
- `hreflang` is not implemented. German stays draft-only.
- No production deployment, SMTP, Lighthouse, or PWA install test.
