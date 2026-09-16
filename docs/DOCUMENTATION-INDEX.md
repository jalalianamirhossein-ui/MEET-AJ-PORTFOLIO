# Documentation index — Meet AJ

**Authority:** AUTHORITATIVE catalog of Markdown in this repository after the 2026-09-16 documentation rebuild.  
**Source of truth for current state:** [README.md](../README.md), [DEPLOYMENT.md](../DEPLOYMENT.md), [PROJECT-STATUS.md](PROJECT-STATUS.md), [QA-MATRIX.md](QA-MATRIX.md).

Classifications: **AUTHORITATIVE** | **REFERENCE** | **HISTORICAL** | **DEPLOYMENT** | **VENDOR** (not project docs).

---

## Current project documentation

| File | Purpose | Status | Authority |
|------|---------|--------|-----------|
| [README.md](../README.md) | GitHub onboarding, stack, install, commands | Current | AUTHORITATIVE |
| [DEPLOYMENT.md](../DEPLOYMENT.md) | DirectAdmin production procedure | Current procedure; cutover not executed | DEPLOYMENT |
| [PROJECT-STATUS.md](PROJECT-STATUS.md) | Live implementation/test/blocker status | Current | AUTHORITATIVE |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Runtime paths, routes, services | Current | AUTHORITATIVE |
| [DATABASE.md](DATABASE.md) | Tables, FKs, engines | Current | AUTHORITATIVE |
| [ADMIN.md](ADMIN.md) | Filament resources and gaps | Current | AUTHORITATIVE |
| [MULTILINGUAL.md](MULTILINGUAL.md) | EN / FA / DE | Current | AUTHORITATIVE |
| [SERVICES.md](SERVICES.md) | Service catalog, prices, URLs, admin | Current | AUTHORITATIVE |
| [SERVICE-CMS-IMPLEMENTATION.md](SERVICE-CMS-IMPLEMENTATION.md) | Implementation report for the service CMS | Current | REFERENCE |
| [SEO.md](SEO.md) | Canonical, OG, sitemap, robots | Current | AUTHORITATIVE |
| [PWA.md](PWA.md) | manifest, sw.js, cache, exclusions | Current | AUTHORITATIVE |
| [SECURITY.md](SECURITY.md) | CSRF, honeypot, headers, env | Current | AUTHORITATIVE |
| [QA-MATRIX.md](QA-MATRIX.md) | Evidence-based QA | Current | AUTHORITATIVE |
| [DOCUMENTATION-INDEX.md](DOCUMENTATION-INDEX.md) | This catalog | Current | AUTHORITATIVE |
| [DOCUMENTATION-CLEANUP-REPORT.md](DOCUMENTATION-CLEANUP-REPORT.md) | What changed in the docs rebuild | Current | REFERENCE |
| [PROJECT-STRUCTURE-CLEANUP.md](PROJECT-STRUCTURE-CLEANUP.md) | Structure cleanup report | Current | REFERENCE |
| [get-to-know-me-ui.md](get-to-know-me-ui.md) | About / Get to Know Me UI report | Current | REFERENCE |
| [design-system.md](design-system.md) | Public overlay tokens and components | Current | AUTHORITATIVE |
| [full-site-design-audit.md](full-site-design-audit.md) | Pre-implementation visual audit (then implemented) | Current | REFERENCE |
| [full-site-visual-qa.md](full-site-visual-qa.md) | Viewport matrix and screenshot notes | Current | REFERENCE |
| [service-detail-ui-qa.md](service-detail-ui-qa.md) | Six service landings + quote form | Current | REFERENCE |
| [final-project-qa-report.md](final-project-qa-report.md) | UI rebuild acceptance matrix | Current | AUTHORITATIVE |

## Historical / superseded (retained)

Moved to [historical/](historical/README.md) so `docs/` current-state files stay uncluttered. Bodies were not rewritten.

| File | Purpose | Status | Authority |
|------|---------|--------|-----------|
| [historical/AUDIT_REPORT.md](historical/AUDIT_REPORT.md) | Static-site audit 2026-09-09 | HISTORICAL | HISTORICAL |
| [architecture-decision-record.md](architecture-decision-record.md) | Why Laravel 13 / PHP 8.4 / Filament 5 | Decision still in force | HISTORICAL |
| [framework-version-decision.md](framework-version-decision.md) | Pre-install version evidence | Decision still in force | HISTORICAL |
| [historical/laravel-implementation-plan.md](historical/laravel-implementation-plan.md) | Original implementation plan (Laravel 11 era) | SUPERSEDED | HISTORICAL |
| [historical/laravel-migration-plan.md](historical/laravel-migration-plan.md) | Original migration plan | SUPERSEDED | HISTORICAL |
| [historical/content-migration-analysis.md](historical/content-migration-analysis.md) | Static content inventory | HISTORICAL | HISTORICAL |
| [historical/current-site-analysis.md](historical/current-site-analysis.md) | Pre-CMS site analysis | HISTORICAL | HISTORICAL |
| [historical/cursor-review-report.md](historical/cursor-review-report.md) | Read-only docs review before docs rebuild | HISTORICAL | HISTORICAL |
| [historical/design-audit.md](historical/design-audit.md) | Visual audit before overlay | HISTORICAL | HISTORICAL |
| [historical/visual-upgrade-report.md](historical/visual-upgrade-report.md) | Overlay implementation notes | HISTORICAL | HISTORICAL |
| [historical/content-integrity-fixes.md](historical/content-integrity-fixes.md) | Compare/repair log | HISTORICAL | HISTORICAL |
| [historical/implementation-report.md](historical/implementation-report.md) | Point-in-time CMS report | SUPERSEDED by PROJECT-STATUS | HISTORICAL |
| [historical/final-site-qa-report.md](historical/final-site-qa-report.md) | CMS QA snapshot | SUPERSEDED by QA-MATRIX | HISTORICAL |
| [final-ui-qa.md](final-ui-qa.md) | Latest UI/UX browser QA | Keep as evidence | REFERENCE |
| [phases/phase-1-environment.md](phases/phase-1-environment.md) | Phase 1 log | SUPERSEDED | HISTORICAL |
| [phases/phase-2-database.md](phases/phase-2-database.md) | Phase 2 log | SUPERSEDED | HISTORICAL |
| [phases/phase-3-filament.md](phases/phase-3-filament.md) | Phase 3 log | SUPERSEDED | HISTORICAL |
| [phases/phase-4-importer.md](phases/phase-4-importer.md) | Phase 4 log | SUPERSEDED | HISTORICAL |
| [phases/phase-5-frontend.md](phases/phase-5-frontend.md) | Phase 5 log | SUPERSEDED | HISTORICAL |
| [phases/phase-6-deployment.md](phases/phase-6-deployment.md) | Phase 6 log | SUPERSEDED | HISTORICAL |

## JSON inventories (not Markdown)

| File | Purpose | Authority |
|------|---------|-----------|
| `docs/current-site-inventory.json` | Pre-CMS HTML metadata dump | HISTORICAL REFERENCE |
| `docs/baseline-files.json` | SHA-256 baseline including vendor skill files | HISTORICAL REFERENCE |

## Vendor skill Markdown (not Meet AJ docs)

All files under `.claude/skills/ui-ux-pro-max/**/*.md` are a third-party UI/UX skill pack. They are **not** project documentation, were **not** rewritten in this audit, and must not be used as Laravel/CMS status.

---

No duplicate current-state report remains as an unmarked authority. Historical files keep their original measurements.
