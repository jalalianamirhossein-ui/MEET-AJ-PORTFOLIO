# Meet AJ — documentation

Navigation map for every document in this repository. Last updated **2026-09-19** (Hero soft blue About transition + mobile face-safe composition; asset versions `site-modules?v=1840` / `visual-upgrade?v=1711` / `main.js?v=1412`).

**Start here:** [current/PROJECT-STATUS.md](current/PROJECT-STATUS.md) — the single authoritative statement of what exists today.

Status vocabulary used throughout: **PASS**, **FAIL**, **BLOCKED**, **NOT TESTED**, plus **UNKNOWN / NOT VERIFIED** where code cannot prove a claim.

Documentation sync evidence: [qa/DOCUMENTATION-QA.md](qa/DOCUMENTATION-QA.md).

## Current documentation

Describes the application as it is now. When one of these disagrees with `PROJECT-STATUS.md`, `PROJECT-STATUS.md` wins.

| Document | Covers |
|----------|--------|
| [current/PROJECT-STATUS.md](current/PROJECT-STATUS.md) | Versions, database, counts, blockers, limitations |
| [current/ARCHITECTURE.md](current/ARCHITECTURE.md) | Request flow, routes, controllers, models, commands |
| [current/DATABASE.md](current/DATABASE.md) | Every table, column, index, foreign key, delete behaviour |
| [current/ADMIN.md](current/ADMIN.md) | Filament panel, resources, authorization |
| [current/FEATURES.md](current/FEATURES.md) | Public and CMS feature inventory |
| [current/ARTICLES.md](current/ARTICLES.md) | 23 articles, import, slugs, redirects, search, tags |
| [current/SERVICES.md](current/SERVICES.md) | Six services, pricing, publication, requests |
| [current/REQUESTS.md](current/REQUESTS.md) | Contact and quote pipeline, statuses, internal notes |
| [current/MULTILINGUAL.md](current/MULTILINGUAL.md) | EN / FA / draft-only DE, RTL, hreflang status |
| [current/SEO.md](current/SEO.md) | Canonicals, OG, JSON-LD, sitemap, robots, redirects |
| [current/PWA.md](current/PWA.md) | Manifest, service worker, offline, exclusions |
| [current/SECURITY.md](current/SECURITY.md) | CSRF, honeypot, rate limits, policies, headers |
| [current/TESTING.md](current/TESTING.md) | Suites, commands, results, what is untested |
| [current/PERFORMANCE.md](current/PERFORMANCE.md) | Measured asset sizes, query behaviour, unmeasured areas |
| [current/DEPLOYMENT.md](current/DEPLOYMENT.md) | DirectAdmin procedure, LOCAL / TEST / PRODUCTION |
| [current/DESIGN-SYSTEM.md](current/DESIGN-SYSTEM.md) | Tokens, typography, components, motion |
| [current/ARTICLE-VISUAL-DNA.md](current/ARTICLE-VISUAL-DNA.md) | Reference styling of article detail pages |
| [current/PROJECT-STRUCTURE.md](current/PROJECT-STRUCTURE.md) | Real directory tree and what each folder is for |

## QA

Evidence, not intentions. Each file names its method and marks BLOCKED work honestly.

| Document | Covers |
|----------|--------|
| [qa/FINAL-QA-REPORT.md](qa/FINAL-QA-REPORT.md) | Master audit acceptance matrix, fixes, open items |
| [qa/QA-MATRIX.md](qa/QA-MATRIX.md) | Per-URL expected vs actual, test method, status |
| [qa/VISUAL-QA.md](qa/VISUAL-QA.md) | Browser and CDP visual verification |
| [qa/RESPONSIVE-QA.md](qa/RESPONSIVE-QA.md) | Viewport overflow measurements |
| [qa/ACCESSIBILITY-QA.md](qa/ACCESSIBILITY-QA.md) | Manual a11y audit, WARN items, untested areas |
| [qa/ADMIN-QA.md](qa/ADMIN-QA.md) | Filament QA, including what is blocked |
| [qa/CONTENT-INTEGRITY.md](qa/CONTENT-INTEGRITY.md) | `site:compare-content` result across 23 articles |
| [qa/DESIGN-AUDIT.md](qa/DESIGN-AUDIT.md) | Full-site design audit findings |
| [qa/DOCUMENTATION-QA.md](qa/DOCUMENTATION-QA.md) | Doc reorganisation (2026-09-17) + evening sync to match live app (2026-09-18) |

## Archive

Superseded QA, admin, status and visual reports. **Not current.**

| Location | Contents |
|----------|----------|
| [archive/2026-09-18/README.md](archive/2026-09-18/README.md) | 2026-09-17 snapshots plus same-day blue-admin reports moved aside by the White/Red Filament pass |

## Architecture decisions

| Document | Decision |
|----------|----------|
| [decisions/ADR/README.md](decisions/ADR/README.md) | ADR index |
| [decisions/ADR/ADR-001-laravel-13-filament-5-stack.md](decisions/ADR/ADR-001-laravel-13-filament-5-stack.md) | Laravel 13 + PHP 8.4 + Filament 5 + Blade + MySQL; rejects Laravel 11, PHP 8.2, Filament 3, `pages`, `contact_requests`, public `/de` |
| [decisions/ADR/ADR-002-framework-version-selection.md](decisions/ADR/ADR-002-framework-version-selection.md) | Why Laravel 13 over 11 / 12, pre-install evidence |

## Implementation phases

Chronological history. Phase logs are records, not current status.

| Phase | Document |
|-------|----------|
| 01 | [phases/phase-01-environment.md](phases/phase-01-environment.md) |
| 02 | [phases/phase-02-database.md](phases/phase-02-database.md) |
| 03 | [phases/phase-03-filament.md](phases/phase-03-filament.md) |
| 04 | [phases/phase-04-article-importer.md](phases/phase-04-article-importer.md) |
| 05 | [phases/phase-05-frontend-seo-pwa.md](phases/phase-05-frontend-seo-pwa.md) |
| 06 | [phases/phase-06-deployment.md](phases/phase-06-deployment.md) |
| 07 | [phases/phase-07-service-cms.md](phases/phase-07-service-cms.md) |
| 08 | [phases/phase-08-tags-and-request-workflow.md](phases/phase-08-tags-and-request-workflow.md) |
| 09 | [phases/phase-09-design-audit.md](phases/phase-09-design-audit.md) |
| 10 | [phases/phase-10-documentation-reorganisation.md](phases/phase-10-documentation-reorganisation.md) |

## Historical documentation

Superseded snapshots, plans and audits. Kept on purpose: they hold measurements, decisions and bug history that the current documents summarise but do not reproduce. **None of these describe the current state.**

| Document | What it records |
|----------|-----------------|
| [historical/README.md](historical/README.md) | Notice for the historical folder |
| [historical/AUDIT_REPORT.md](historical/AUDIT_REPORT.md) | Pre-Laravel static site audit (2026-09-09) |
| [historical/current-site-analysis.md](historical/current-site-analysis.md) | Pre-implementation inventory (2026-09-14) |
| [historical/content-migration-analysis.md](historical/content-migration-analysis.md) | Content inventory and migration risk review |
| [historical/laravel-migration-plan.md](historical/laravel-migration-plan.md) | Superseded plan (Laravel 11, `/de`, `pages`) |
| [historical/laravel-implementation-plan.md](historical/laravel-implementation-plan.md) | Superseded implementation plan |
| [historical/implementation-report.md](historical/implementation-report.md) | Point-in-time implementation report (2026-09-16) |
| [historical/SERVICE-CMS-IMPLEMENTATION.md](historical/SERVICE-CMS-IMPLEMENTATION.md) | Service CMS build report (2026-09-16) |
| [historical/content-integrity-fixes.md](historical/content-integrity-fixes.md) | Log of content repairs vs original HTML |
| [historical/design-audit.md](historical/design-audit.md) | Visual audit before the overlay upgrade |
| [historical/visual-upgrade-report.md](historical/visual-upgrade-report.md) | Overlay implementation report |
| [historical/final-ui-qa.md](historical/final-ui-qa.md) | UI QA snapshot (2026-09-16) with the older viewport matrix |
| [historical/final-site-qa-report.md](historical/final-site-qa-report.md) | Site QA snapshot (2026-09-16) |
| [historical/service-detail-ui-qa.md](historical/service-detail-ui-qa.md) | Service landing QA at overlay v1310 |
| [historical/get-to-know-me-ui.md](historical/get-to-know-me-ui.md) | Homepage About section UI report + screenshots |
| [historical/cursor-review-report.md](historical/cursor-review-report.md) | Read-only `/docs` review (2026-09-16) |
| [historical/DOCUMENTATION-INDEX.md](historical/DOCUMENTATION-INDEX.md) | Previous documentation index, replaced by this file |
| [historical/DOCUMENTATION-CLEANUP-REPORT.md](historical/DOCUMENTATION-CLEANUP-REPORT.md) | Earlier documentation cleanup (2026-09-16) |
| [historical/PROJECT-STRUCTURE-CLEANUP.md](historical/PROJECT-STRUCTURE-CLEANUP.md) | Earlier file-structure cleanup (2026-09-16) |
| [historical/current-site-inventory.json](historical/current-site-inventory.json) | Pre-CMS HTML metadata dump |
| [historical/baseline-main-css.patch](historical/baseline-main-css.patch) | Preserved pre-migration CSS diff |
| historical/screenshots/get-to-know-me/ | PNG evidence for the About section report |

## Files that must stay at `docs/` root

| File | Why |
|------|-----|
| `baseline-files.json` | `scripts/verify-originals.php` reads this exact path for SHA-256 baselines |
| `netbox_installation_guide_v2.pdf` | `App\Services\LegacySitePublisher` copies it to `public/docs/` to keep the public download URL alive |

Do not move or rename either file without changing the code that points at it.
