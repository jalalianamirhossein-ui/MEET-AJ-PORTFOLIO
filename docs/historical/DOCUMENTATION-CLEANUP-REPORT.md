> **HISTORICAL / SUPERSEDED.**
> **Original date:** 2026-09-16.
> **Original purpose:** report the first documentation cleanup pass against the running Laravel CMS.
> **Superseded by:** [../qa/DOCUMENTATION-QA.md](../qa/DOCUMENTATION-QA.md) (2026-09-17 reorganisation) and [../README.md](../README.md).
> Current status: [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md).

# Documentation cleanup report

Date: 2026-09-16  
Scope: every project Markdown file vs the running Laravel CMS. Application logic was **not** redesigned.

## 1. Markdown files reviewed

**Project documentation (rewritten, updated, or bannered):** 33 files plus this report.

Root: `README.md`, `DEPLOYMENT.md`, `AUDIT_REPORT.md`.

`docs/`: architecture-decision-record, framework-version-decision, laravel-implementation-plan, laravel-migration-plan, content-migration-analysis, current-site-analysis, cursor-review-report, design-audit, visual-upgrade-report, content-integrity-fixes, implementation-report, final-site-qa-report, final-ui-qa, plus the new authoritative set listed in section 3.

`docs/phases/`: phase-1-environment through phase-6-deployment.

**JSON (inspected, not rewritten):** `docs/current-site-inventory.json`, `docs/baseline-files.json`.

**Vendor (inspected as inventory only, not rewritten):** `.claude/skills/ui-ux-pro-max/**/*.md` (~50 skill files). These are not Meet AJ CMS docs.

`vendor/` Markdown was not treated as project documentation.

## 2. Files updated

- `README.md` — full rebuild (was the December 2024 static PWA readme)
- `DEPLOYMENT.md` — authority banner; procedure already matched Laravel 13 / PHP 8.4 / Filament 5
- Historical/plan/QA snapshots — **HISTORICAL / SUPERSEDED / REFERENCE** banners and pointers to PROJECT-STATUS / QA-MATRIX (bodies kept)

## 3. Files created

| File | Role |
|------|------|
| `docs/PROJECT-STATUS.md` | Authoritative current status |
| `docs/ARCHITECTURE.md` | Runtime architecture |
| `docs/DATABASE.md` | Schema |
| `docs/ADMIN.md` | Filament |
| `docs/MULTILINGUAL.md` | EN / FA / DE |
| `docs/SEO.md` | SEO |
| `docs/PWA.md` | PWA |
| `docs/SECURITY.md` | Security controls |
| `docs/QA-MATRIX.md` | Authoritative QA |
| `docs/DOCUMENTATION-INDEX.md` | Catalog |
| `docs/DOCUMENTATION-CLEANUP-REPORT.md` | This report |

## 4. Files deleted

**None.** Duplicate *current-state* reports were marked SUPERSEDED instead of deleted so unique measurements (MariaDB 11.4.13 host, overlay viewport notes, phase logs) are not lost.

## 5. Historical files retained

`AUDIT_REPORT.md`, ADR, framework-version-decision, both Laravel plans, content/current-site analyses, cursor-review-report, design-audit, visual-upgrade-report, content-integrity-fixes, implementation-report, final-site-qa-report, all six phase logs.

`final-ui-qa.md` retained as **REFERENCE** evidence for the UI polish pass.

## 6. Duplicate documentation removed

No files removed. Functional duplicates of “current status”:

- `implementation-report.md` → SUPERSEDED by PROJECT-STATUS
- `final-site-qa-report.md` → SUPERSEDED by QA-MATRIX
- Phase 1–6 logs → SUPERSEDED phase history

## 7. Obsolete references fixed

Active docs now state Laravel **13.31.0**, PHP **8.4.25**, Filament **5.8.2**, Livewire **4.4.5**, table **`requests`**, no `pages`, no public `/de`.

Laravel 11 / PHP 8.2 / Filament 3 / `contact_requests` remain **only** inside HISTORICAL/SUPERSEDED plans (and as “do not use” warnings). Those historical facts were not rewritten.

No “Production Ready” claim exists in current documentation.

## 8. README changes

Replaced static-site 1.0.4 documentation with Laravel CMS onboarding: overview, actual features, lockfile versions, ASCII architecture, public URLs, Filament (including Users gap), importer, contact contract, EN/FA/DE, install without Node, `php artisan serve`, test commands, `cms:create-user`, pointer to DEPLOYMENT.md, Git **not initialized**.

## 9. Broken links fixed

New docs link only to files that exist. Historical banners point at PROJECT-STATUS, QA-MATRIX, README, DEPLOYMENT, ADMIN, DATABASE, SEO, PWA.

`.claude` skill Markdown is indexed as VENDOR, not linked as CMS status.

## 10. Command verification

Executed or listed 2026-09-16 with `.runtime/php84/php.exe`:

| Command | Result |
|---------|--------|
| `php artisan about` | Laravel 13.31.0, PHP 8.4.25, Filament v5.8.2, Livewire v4.4.5, sqlite, debug ENABLED, timezone UTC |
| `php artisan route:list` | 29 routes; Articles/Categories/Requests admin routes present; **no** users resource route |
| `php artisan list` | `articles:import-legacy`, `site:publish-assets`, `site:compare-content`, `cms:create-user`, `test` |
| `php artisan test` | 23 tests, 510 assertions, 1 skipped, **0 failures** |
| `vendor/bin/phpunit -c phpunit.mysql.xml --filter MysqlSchemaTest` | 1 test, 7 assertions, OK |

Documented options match signatures: `--dry-run` / `--refresh`; `--views`; `--name` / `--email` / `--role` / `--password`.

`php artisan test --filter` is **not** supported (custom `RunTests` command). Not documented.

## 11. Remaining documentation problems

- Historical plans still contain Laravel 11 instructions **by design** (bannered SUPERSEDED).
- Vendor skill Markdown under `.claude/` is unrelated and unmaintained as CMS docs.
- JSON inventories are large historical dumps, not human docs.
- Interactive Filament login was not re-run in this documentation pass (PHPUnit covers login page + CRUD).
- PWA offline / Lighthouse / production crawlers remain NOT TESTED (stated in QA-MATRIX).

## 12. Application problems discovered but NOT changed

1. **Filament Users:** `UserResource` exists; `shouldRegisterNavigation()` is false; `filament.admin.resources.users.index` is absent from `route:list`. User provisioning is CLI-only.
2. Filament Users index route is missing from `route:list` (CLI user create only).
3. **Local `.env`:** `php artisan about` shows debug ENABLED and timezone UTC vs `.env.example` (`APP_DEBUG=false`, `Asia/Tehran`).
4. **hreflang** is absent (EN/FA share URLs; by architecture, not a silent omission in docs).
5. Source leftovers (not CMS bugs): generic card category labels; service “Back to Services” stays English in FA.
6. Production DirectAdmin, SMTP, and live PWA: **BLOCKED / NOT TESTED**.

---

## Documentation status

**CLEAN** for current-state docs (README, DEPLOYMENT, `docs/PROJECT-STATUS.md`, `docs/QA-MATRIX.md`, and the other new AUTHORITATIVE files). Historical files are bannered. **NEEDS REVIEW** only in the sense that production evidence does not exist yet.

## Project status

**BLOCKED** for production cutover. **NOT READY** to call meetaj.ir migrated. Local automated tests: **PASS**.

## Git status

**NOT INITIALIZED** (no `.git` directory; Git CLI not on PATH). No `git init`, commit, or push was performed.
