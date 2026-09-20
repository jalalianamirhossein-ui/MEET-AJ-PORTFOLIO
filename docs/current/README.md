# Current documentation — Meet AJ

This directory contains the maintained operating documentation for the current Laravel application. When a current document conflicts with an archive, phase log or historical report, the current document wins. The top-level authority is [PROJECT-STATUS.md](PROJECT-STATUS.md).

## Start here

| Question | Document |
|---|---|
| What is true right now? | [PROJECT-STATUS.md](PROJECT-STATUS.md) |
| How is the repository organized? | [PROJECT-STRUCTURE.md](PROJECT-STRUCTURE.md) |
| How do I run and test it? | [TESTING.md](TESTING.md) and the root [README.md](../../README.md) |
| How does the application work? | [ARCHITECTURE.md](ARCHITECTURE.md) |

## Feature and operating guides

- [ADMIN.md](ADMIN.md) — Filament panel, roles and resource access.
- [ARTICLES.md](ARTICLES.md) — imports, redirects, tags and content integrity.
- [DATABASE.md](DATABASE.md) — tables, relationships, migrations and engines.
- [FEATURES.md](FEATURES.md) — user-facing feature inventory.
- [HOMEPAGE-CMS.md](HOMEPAGE-CMS.md) — editable homepage sections and sync rules.
- [MULTILINGUAL.md](MULTILINGUAL.md) — English/Persian runtime and German draft rules.
- [REQUESTS.md](REQUESTS.md) — contact form, service requests and workflow states.
- [SECURITY.md](SECURITY.md) — CSRF, validation, rate limiting, headers and auth.
- [SERVICES.md](SERVICES.md) — service catalog and editorial pricing.
- [SEO.md](SEO.md) — canonical URLs, sitemap, metadata and JSON-LD.
- [PWA.md](PWA.md) — manifest, service worker and offline limitations.
- [PERFORMANCE.md](PERFORMANCE.md) — measured and unverified performance areas.

## Visual and design references

- [DESIGN-SYSTEM.md](DESIGN-SYSTEM.md) — current public visual rules.
- [ARTICLE-VISUAL-DNA.md](ARTICLE-VISUAL-DNA.md) — article presentation rules.
- Detailed design references: [../design-system/meet-aj/MASTER.md](../design-system/meet-aj/MASTER.md).

## Evidence and history

- Live QA evidence is in [../qa/](../qa/), especially [QA-MATRIX.md](../qa/QA-MATRIX.md).
- Architecture decisions are in [../decisions/ADR/](../decisions/ADR/).
- Chronological implementation logs are in [../phases/](../phases/).
- Superseded reports are in [../historical/](../historical/) and [../archive/](../archive/).

## Update rule

When a feature changes, update its current guide and `PROJECT-STATUS.md` if counts, routes, versions, deployment state, tests or limitations change. Record major architectural choices as an ADR. Put dated evidence in `docs/qa/`; do not rewrite historical reports.
