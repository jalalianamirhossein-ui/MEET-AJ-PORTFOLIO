> **HISTORICAL / SUPERSEDED.**
> **Original date:** 2026-09-16.
> **Original purpose:** catalog and classify every Markdown file after the first documentation rebuild, when all documents lived flat in `docs/`.
> **Superseded by:** [README.md](../README.md) — the current documentation index for the `current/ qa/ decisions/ phases/ historical/` structure.
> Current status: [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md). The classifications below describe the **old** layout; paths have been repointed so the links still resolve, but the "Current" labels in this file are historical.

# Documentation index — Meet AJ

**Authority:** superseded catalog of Markdown in this repository after the 2026-09-16 documentation rebuild.  
**Source of truth for current state:** [README.md](../../README.md), [DEPLOYMENT.md](../current/DEPLOYMENT.md), [PROJECT-STATUS.md](../current/PROJECT-STATUS.md), [QA-MATRIX.md](../qa/QA-MATRIX.md).

Classifications: **AUTHORITATIVE** | **REFERENCE** | **HISTORICAL** | **DEPLOYMENT** | **VENDOR** (not project docs).

---

## Current project documentation

| File | Purpose | Status | Authority |
|------|---------|--------|-----------|
| [README.md](../../README.md) | GitHub onboarding, stack, install, commands | Current | AUTHORITATIVE |
| [DEPLOYMENT.md](../current/DEPLOYMENT.md) | DirectAdmin production procedure | Current procedure; cutover not executed | DEPLOYMENT |
| [PROJECT-STATUS.md](../current/PROJECT-STATUS.md) | Live implementation/test/blocker status | Current | AUTHORITATIVE |
| [ARCHITECTURE.md](../current/ARCHITECTURE.md) | Runtime paths, routes, services | Current | AUTHORITATIVE |
| [DATABASE.md](../current/DATABASE.md) | Tables, FKs, engines | Current | AUTHORITATIVE |
| [ADMIN.md](../current/ADMIN.md) | Filament resources and gaps | Current | AUTHORITATIVE |
| [MULTILINGUAL.md](../current/MULTILINGUAL.md) | EN / FA / DE | Current | AUTHORITATIVE |
| [SERVICES.md](../current/SERVICES.md) | Service catalog, prices, URLs, admin | Current | AUTHORITATIVE |
| [SERVICE-CMS-IMPLEMENTATION.md](SERVICE-CMS-IMPLEMENTATION.md) | Implementation report for the service CMS | Current | REFERENCE |
| [SEO.md](../current/SEO.md) | Canonical, OG, sitemap, robots | Current | AUTHORITATIVE |
| [PWA.md](../current/PWA.md) | manifest, sw.js, cache, exclusions | Current | AUTHORITATIVE |
| [SECURITY.md](../current/SECURITY.md) | CSRF, honeypot, headers, env | Current | AUTHORITATIVE |
| [QA-MATRIX.md](../qa/QA-MATRIX.md) | Evidence-based QA | Current | AUTHORITATIVE |
| [DOCUMENTATION-INDEX.md](DOCUMENTATION-INDEX.md) | This catalog | Current | AUTHORITATIVE |
| [DOCUMENTATION-CLEANUP-REPORT.md](DOCUMENTATION-CLEANUP-REPORT.md) | What changed in the docs rebuild | Current | REFERENCE |
| [PROJECT-STRUCTURE-CLEANUP.md](PROJECT-STRUCTURE-CLEANUP.md) | Structure cleanup report | Current | REFERENCE |
| [get-to-know-me-ui.md](get-to-know-me-ui.md) | About / Get to Know Me UI report | Current | REFERENCE |
| [features.md](../current/FEATURES.md) | Search, tags, related, share, request pipeline | Current | AUTHORITATIVE |
| [admin-ui-qa.md](../qa/ADMIN-QA.md) | Filament UI QA | Current | REFERENCE |
| [design-system.md](../current/DESIGN-SYSTEM.md) | Public overlay tokens and components | Current | AUTHORITATIVE |
| [article-visual-dna.md](../current/ARTICLE-VISUAL-DNA.md) | Article detail pages as visual reference | Current | AUTHORITATIVE |
| [full-site-design-audit.md](../qa/DESIGN-AUDIT.md) | Pre-implementation visual audit (then implemented) | Current | REFERENCE |
| [full-site-visual-qa.md](../qa/VISUAL-QA.md) | Viewport matrix and screenshot notes | Current | REFERENCE |
| [service-detail-ui-qa.md](service-detail-ui-qa.md) | Six service landings + quote form | Current | REFERENCE |
| [final-project-qa-report.md](../qa/FINAL-QA-REPORT.md) | UI rebuild acceptance matrix | Current | AUTHORITATIVE |

## Historical / superseded (retained)

Moved to [historical/](README.md) so `docs/` current-state files stay uncluttered. Bodies were not rewritten.

| File | Purpose | Status | Authority |
|------|---------|--------|-----------|
| [historical/AUDIT_REPORT.md](AUDIT_REPORT.md) | Static-site audit 2026-09-09 | HISTORICAL | HISTORICAL |
| [architecture-decision-record.md](../decisions/ADR/ADR-001-laravel-13-filament-5-stack.md) | Why Laravel 13 / PHP 8.4 / Filament 5 | Decision still in force | HISTORICAL |
| [framework-version-decision.md](../decisions/ADR/ADR-002-framework-version-selection.md) | Pre-install version evidence | Decision still in force | HISTORICAL |
| [historical/laravel-implementation-plan.md](laravel-implementation-plan.md) | Original implementation plan (Laravel 11 era) | SUPERSEDED | HISTORICAL |
| [historical/laravel-migration-plan.md](laravel-migration-plan.md) | Original migration plan | SUPERSEDED | HISTORICAL |
| [historical/content-migration-analysis.md](content-migration-analysis.md) | Static content inventory | HISTORICAL | HISTORICAL |
| [historical/current-site-analysis.md](current-site-analysis.md) | Pre-CMS site analysis | HISTORICAL | HISTORICAL |
| [historical/cursor-review-report.md](cursor-review-report.md) | Read-only docs review before docs rebuild | HISTORICAL | HISTORICAL |
| [historical/design-audit.md](design-audit.md) | Visual audit before overlay | HISTORICAL | HISTORICAL |
| [historical/visual-upgrade-report.md](visual-upgrade-report.md) | Overlay implementation notes | HISTORICAL | HISTORICAL |
| [historical/content-integrity-fixes.md](content-integrity-fixes.md) | Compare/repair log | HISTORICAL | HISTORICAL |
| [historical/implementation-report.md](implementation-report.md) | Point-in-time CMS report | SUPERSEDED by PROJECT-STATUS | HISTORICAL |
| [historical/final-site-qa-report.md](final-site-qa-report.md) | CMS QA snapshot | SUPERSEDED by QA-MATRIX | HISTORICAL |
| [final-ui-qa.md](final-ui-qa.md) | Latest UI/UX browser QA | Keep as evidence | REFERENCE |
| [phases/phase-1-environment.md](../phases/phase-01-environment.md) | Phase 1 log | SUPERSEDED | HISTORICAL |
| [phases/phase-2-database.md](../phases/phase-02-database.md) | Phase 2 log | SUPERSEDED | HISTORICAL |
| [phases/phase-3-filament.md](../phases/phase-03-filament.md) | Phase 3 log | SUPERSEDED | HISTORICAL |
| [phases/phase-4-importer.md](../phases/phase-04-article-importer.md) | Phase 4 log | SUPERSEDED | HISTORICAL |
| [phases/phase-5-frontend.md](../phases/phase-05-frontend-seo-pwa.md) | Phase 5 log | SUPERSEDED | HISTORICAL |
| [phases/phase-6-deployment.md](../phases/phase-06-deployment.md) | Phase 6 log | SUPERSEDED | HISTORICAL |

## JSON inventories (not Markdown)

| File | Purpose | Authority |
|------|---------|-----------|
| `docs/current-site-inventory.json` | Pre-CMS HTML metadata dump | HISTORICAL REFERENCE |
| `docs/baseline-files.json` | SHA-256 baseline including vendor skill files | HISTORICAL REFERENCE |

## Vendor skill Markdown (not Meet AJ docs)

All files under `.claude/skills/ui-ux-pro-max/**/*.md` are a third-party UI/UX skill pack. They are **not** project documentation, were **not** rewritten in this audit, and must not be used as Laravel/CMS status.

---

No duplicate current-state report remains as an unmarked authority. Historical files keep their original measurements.
