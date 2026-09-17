# Historical documentation

**Nothing in this folder describes the current state of the project.**

These files are preserved records: earlier audits, superseded plans, point-in-time implementation reports, QA snapshots, bug and content-integrity logs, and screenshot evidence. They are kept because they contain measurements, decisions and failure history that the current documents summarise but do not reproduce.

When a file here disagrees with `docs/current/`, the current document wins.

- Current status: [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md)
- Current QA: [../qa/FINAL-QA-REPORT.md](../qa/FINAL-QA-REPORT.md), [../qa/QA-MATRIX.md](../qa/QA-MATRIX.md)
- Documentation index: [../README.md](../README.md)
- Decisions still in force: [../decisions/ADR/README.md](../decisions/ADR/README.md)

## What is here

| Category | Files |
|----------|-------|
| Pre-Laravel audits | `AUDIT_REPORT.md`, `current-site-analysis.md`, `content-migration-analysis.md` |
| Superseded plans | `laravel-migration-plan.md`, `laravel-implementation-plan.md` |
| Implementation reports | `implementation-report.md`, `SERVICE-CMS-IMPLEMENTATION.md` |
| Bug and integrity history | `content-integrity-fixes.md` |
| Design and UI history | `design-audit.md`, `visual-upgrade-report.md`, `final-ui-qa.md`, `service-detail-ui-qa.md`, `get-to-know-me-ui.md` |
| QA snapshots | `final-site-qa-report.md` |
| Documentation history | `DOCUMENTATION-INDEX.md`, `DOCUMENTATION-CLEANUP-REPORT.md`, `PROJECT-STRUCTURE-CLEANUP.md`, `cursor-review-report.md` |
| Data snapshots | `current-site-inventory.json`, `baseline-main-css.patch` |
| Screenshots | `screenshots/get-to-know-me/` |

## Obsolete claims you will find here

Several files describe **Laravel 11**, **PHP 8.2**, **Filament 3**, a `pages` table, a `contact_requests` table, or public `/de` routes. None of those exist. They were rejected in [ADR-001](../decisions/ADR/ADR-001-laravel-13-filament-5-stack.md) and [ADR-002](../decisions/ADR/ADR-002-framework-version-selection.md). Older test counts (for example 23 or 30 tests) are also historical; the current suite is 39 tests with 647 assertions.

Do not delete files here because they are old. Do not cite them as current.
