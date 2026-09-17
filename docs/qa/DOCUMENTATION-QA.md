# Documentation QA — reorganisation report

**Date:** 2026-09-17
**Scope:** documentation only. No application code, business logic, database behaviour or UI was changed, and no git command was executed.
**Result:** **PASS**
**Current source of truth:** [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md)

## 1. Documents reviewed

| Location (before) | Files |
|-------------------|-------|
| `docs/` root | 30 (27 Markdown, 2 JSON, 1 patch, 1 PDF — counted as 30 items) |
| `docs/historical/` | 12 Markdown |
| `docs/phases/` | 6 Markdown |
| `docs/qa/get-to-know-me/` | 7 PNG |
| Repository root | `README.md`, `DEPLOYMENT.md` |
| `design-system/meet-aj/` | 5 Markdown (read, left in place) |
| **Total reviewed** | **62** |

Vendor skill documentation under `.claude/skills/**` and `vendor/**` was excluded: it is not project documentation.

Application code was read before classifying anything: `app/`, `routes/web.php`, `config/`, `database/migrations/`, `resources/views/`, `tests/`, `scripts/`, `public/`.

## 2. Verification performed before writing

| Command | Result |
|---------|--------|
| `php artisan about` | Laravel 13.31.0, PHP 8.4.25, Filament v5.8.2, Livewire v4.4.5 |
| `php artisan migrate:status` | 9 migrations, all Ran (batches 1–3) |
| `php artisan optimize:clear` + `php artisan route:list` | 36 routes |
| `php artisan test` | 39 tests, 647 assertions, 1 skipped, 0 failures |
| `php artisan site:compare-content` | Failures: 0 |
| Live SQLite schema and row inspection | 11 tables; 23 articles, 23 redirects, 10 categories, 8 tags, 38 pivot rows, 6 services, 0 requests, 0 users |
| Asset measurement on disk | 147 published asset files; CSS/JS sizes recorded in `current/PERFORMANCE.md` |

No number in `docs/current/` was copied from an older report.

## 3. Classification

| Classification | Count | Examples |
|----------------|-------|----------|
| CURRENT | 18 | everything in `docs/current/` |
| QA (current evidence) | 9 | everything in `docs/qa/` |
| REFERENCE (decision, still in force) | 2 | ADR-001, ADR-002 |
| HISTORICAL | 12 | `AUDIT_REPORT.md`, `current-site-analysis.md`, `design-audit.md`, `implementation-report.md`, … |
| SUPERSEDED | 7 | `DOCUMENTATION-INDEX.md`, `DOCUMENTATION-CLEANUP-REPORT.md`, `PROJECT-STRUCTURE-CLEANUP.md`, `SERVICE-CMS-IMPLEMENTATION.md`, `final-ui-qa.md`, `service-detail-ui-qa.md`, `get-to-know-me-ui.md` |
| REFERENCE (data, must stay in place) | 2 | `docs/baseline-files.json`, `docs/netbox_installation_guide_v2.pdf` |
| REFERENCE (data snapshot) | 2 | `historical/current-site-inventory.json`, `historical/baseline-main-css.patch` |
| PHASE LOG | 10 | `docs/phases/phase-01…10` |
| TEMPORARY | 0 | none found |
| UNKNOWN | 0 | none left unclassified |

## 4. Documents moved

**Into `docs/current/` (13):** `PROJECT-STATUS.md`, `ARCHITECTURE.md`, `DATABASE.md`, `ADMIN.md`, `features.md` → `FEATURES.md`, `MULTILINGUAL.md`, `SERVICES.md`, `SEO.md`, `PWA.md`, `SECURITY.md`, `design-system.md` → `DESIGN-SYSTEM.md`, `article-visual-dna.md` → `ARTICLE-VISUAL-DNA.md`, and `DEPLOYMENT.md` from the repository root.

**Into `docs/qa/` (5):** `final-project-qa-report.md` → `FINAL-QA-REPORT.md`, `full-site-visual-qa.md` → `VISUAL-QA.md`, `admin-ui-qa.md` → `ADMIN-QA.md`, `QA-MATRIX.md`, `full-site-design-audit.md` → `DESIGN-AUDIT.md`.

**Into `docs/decisions/ADR/` (2):** `architecture-decision-record.md` → `ADR-001-laravel-13-filament-5-stack.md`, `framework-version-decision.md` → `ADR-002-framework-version-selection.md`.

**Into `docs/historical/` (9):** `DOCUMENTATION-INDEX.md`, `DOCUMENTATION-CLEANUP-REPORT.md`, `PROJECT-STRUCTURE-CLEANUP.md`, `SERVICE-CMS-IMPLEMENTATION.md`, `final-ui-qa.md`, `service-detail-ui-qa.md`, `get-to-know-me-ui.md`, `current-site-inventory.json`, `baseline-main-css.patch`.

**Renamed in place (6):** phase logs to zero-padded names, `phase-1-environment.md` → `phase-01-environment.md` through `phase-6-deployment.md` → `phase-06-deployment.md` (with `phase-4-importer` → `phase-04-article-importer` and `phase-5-frontend` → `phase-05-frontend-seo-pwa`).

**Screenshots (7):** `docs/qa/get-to-know-me/*.png` → `docs/historical/screenshots/get-to-know-me/`.

Total relocated or renamed files: **42**.

## 5. Documents created

| Path | Why |
|------|-----|
| `docs/README.md` | New documentation index |
| `docs/current/PROJECT-STATUS.md` | Rewritten from scratch as the single authoritative document |
| `docs/current/ARTICLES.md` | Article subsystem had no dedicated current document |
| `docs/current/REQUESTS.md` | Request pipeline was only described inside other files |
| `docs/current/TESTING.md` | Test facts were scattered across four documents |
| `docs/current/PERFORMANCE.md` | No performance document existed; now states what is measured and what is not |
| `docs/current/PROJECT-STRUCTURE.md` | No current directory map existed |
| `docs/qa/RESPONSIVE-QA.md` | Split out of the visual QA to end two competing matrices |
| `docs/qa/ACCESSIBILITY-QA.md` | A11y evidence was buried in the visual QA |
| `docs/qa/CONTENT-INTEGRITY.md` | `site:compare-content` result had no home of its own |
| `docs/qa/DOCUMENTATION-QA.md` | This report |
| `docs/decisions/ADR/README.md` | ADR index |
| `docs/phases/phase-07…10` | Service CMS, tags/request workflow, design audit and this reorganisation had no phase logs |

Root `README.md` was rewritten as the project entry point.

## 6. Documents deleted

**Zero.** Nothing was deleted. Every superseded document was moved to `docs/historical/` with a header giving its original date, its original purpose, and what supersedes it.

## 7. Duplication removed

Five documents previously competed to describe the current state. They now have one owner each:

| Former overlap | Resolution |
|----------------|------------|
| `PROJECT-STATUS.md`, `implementation-report.md`, `SERVICE-CMS-IMPLEMENTATION.md` all described "the current build" | `current/PROJECT-STATUS.md` is authoritative; the other two are historical snapshots |
| `final-project-qa-report.md`, `QA-MATRIX.md`, `final-site-qa-report.md`, `final-ui-qa.md` all carried PASS/FAIL claims | `qa/FINAL-QA-REPORT.md` + `qa/QA-MATRIX.md` are current; the other two are historical |
| Two different responsive matrices (`full-site-visual-qa.md` and `final-ui-qa.md`) | one matrix in `qa/RESPONSIVE-QA.md`; the visual QA links to it |
| `DOCUMENTATION-INDEX.md` vs no `docs/README.md` | `docs/README.md` is the index; the old catalog is historical |
| Service documentation split across `SERVICES.md` and `SERVICE-CMS-IMPLEMENTATION.md` | `current/SERVICES.md` is authoritative; the implementation report is historical |

## 8. Obsolete information corrected

| Claim found | Reality verified in code | Where fixed |
|-------------|--------------------------|-------------|
| “Users Filament resource is hidden from navigation (`shouldRegisterNavigation = false`)” | `UserResource::shouldRegisterNavigation()` returns `auth()->user()?->isAdmin()`, so admins see it | `current/PROJECT-STATUS.md`, `current/ADMIN.md`, `current/ARCHITECTURE.md`, `current/SECURITY.md` |
| “Users navigation / registered users index route” listed as not implemented, while the same file said the route exists | Both `/admin/users` and `/admin/cms-users` exist and resolve to `filament.admin.resources.users.index` | `current/ADMIN.md` |
| Overlay cache `v=1120` | Blade heads use `visual-upgrade.css?v=1314` | `qa/QA-MATRIX.md`, `current/PROJECT-STATUS.md` |
| “30 tests, 584 assertions” as the current result | 39 tests, 647 assertions, 1 skipped | `current/PROJECT-STATUS.md`, `current/TESTING.md` |
| Models list omitting `Tag` and `Service`; command list omitting `articles:sync-tags` | Seven models, seven commands | `current/ARCHITECTURE.md` |
| Article search described as title/excerpt/slug only | `scopeSearch()` also matches `content`, category name, and tag name/slug | `current/ARTICLES.md` |
| “Git: not initialized” | A `.git` directory exists on `feature/laravel-migration`; the accurate claim is that no git command was run | `qa/FINAL-QA-REPORT.md`, note added in `historical/README.md` |
| Phase 03 header claiming Users navigation was hidden because the index route was missing | Contradicted by the current code | `phases/phase-03-filament.md` |
| Phase 05 header quoting overlay `v=1108` as the later value | Current value is `v=1314` | `phases/phase-05-frontend-seo-pwa.md` |
| ADR-001 core schema list missing `services`, `tags`, `article_tag` | Those tables exist from phases 07 and 08 | note added to `decisions/ADR/ADR-001-…md` |

Searches for **Laravel 11**, **Laravel 12**, **PHP 8.2**, **Filament 3**, **React**, **Next.js**, **`contact_requests`**, and **`pages` table** in `docs/current/`, `docs/qa/` and `README.md` now return only explicit negative statements ("this is *not* …"). Every remaining positive mention lives in `docs/historical/` behind a HISTORICAL header.

## 9. Broken links fixed

| Item | Count |
|------|-------|
| Markdown files scanned | 60 |
| Links rewritten after the reorganisation | 84 |
| Legacy `historical/…` paths repaired inside the old index | 13 |
| Wrong-target `README.md` links repaired | 2 |
| **Unresolved internal links after the pass** | **0** |

Verification method: every Markdown link target in every file under `docs/` plus the root `README.md` was resolved against the filesystem. External `http(s)` links were not fetched.

## 10. Newly documented facts that were previously missing

- Six unused per-service Blade templates in `resources/views/services/` are dead code carrying stale asset versions.
- The `users` table is currently empty, which is the actual reason several admin checks are BLOCKED rather than merely untested.
- `docs/baseline-files.json` and `docs/netbox_installation_guide_v2.pdf` are referenced by `scripts/verify-originals.php` and `App\Services\LegacySitePublisher`, so they cannot be moved.
- No application version constant exists anywhere in the project.

## 11. Remaining uncertainties

| Item | Status |
|------|--------|
| Application version number | **UNKNOWN / NOT VERIFIED** — no constant, tag or config value exists |
| Git remote configuration | **UNKNOWN / NOT VERIFIED** — `git.exe` is not on PATH and no git command was run |
| MySQL integration result | Carried forward from 2026-09-16; not re-run on 2026-09-17 |
| Authenticated admin UI, responsive admin | **BLOCKED** — no CMS user exists locally |
| Performance, Lighthouse, Core Web Vitals | **NOT TESTED** |
| PWA install and offline behaviour | **NOT TESTED** |
| Production deployment of any kind | **BLOCKED** |
| Persian translation quality (as opposed to markup survival) | **NOT TESTED** |

No **NOT TESTED** or **BLOCKED** item was upgraded to PASS anywhere in this pass.

## 12. Verdict

**Documentation QA: PASS.** One authoritative current document, no competing status reports, no deleted history, zero broken internal links, and every figure traceable to a command run on 2026-09-17.
