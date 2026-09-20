> **HISTORICAL DECISION (still in force).** Record of why Laravel 13 / PHP 8.4 / Filament 5 / Blade / MySQL were chosen. Installed versions and live status: [PROJECT-STATUS.md](../../current/PROJECT-STATUS.md). Do not treat planning leftovers as current QA.

# ADR-001 — Laravel 13 + PHP 8.4 + Filament 5 + Blade + MySQL

Status: **Accepted**  
Date: 2026-09-16  
Supersedes: Laravel 11 / PHP 8.2 / Filament 3 / public `/de` routes / `pages` table / `contact_requests` table as described in older planning documents.

This is the canonical architecture for implementation and DirectAdmin production. Historical documents are retained. They are not the source of truth when they conflict with this record.

## Decision

| Layer | Choice | Notes |
| --- | --- | --- |
| Application | Laravel **13.31.0** | `laravel/framework:^13.0`; Composer resolved 13.31.0 |
| PHP | **8.4.25** locally; production **PHP 8.4.x** | Do not downgrade Laravel for PHP 8.2. Production host is verified for PHP 8.4 |
| Admin | Filament **5.8.2** | `filament/filament:^5.0`; Livewire 4.4.5 |
| Database (production) | MySQL / MariaDB, InnoDB, utf8mb4 | Local development/tests currently use SQLite. Engine-specific validation on the host is still required |
| Frontend | Blade | Original HTML converted to Blade; existing CSS/JS retained |
| Hosting | DirectAdmin shared hosting | Document root = Laravel `public/` only |
| Public languages | English + Persian | Same unprefixed URLs; `data-en` / `data-fa`; RTL via existing JS/CSS |
| German | Database-ready, unpublished | No `/de` routes, no `hreflang="de"`, no public switcher option |

## Rejected / obsolete options

| Obsolete item | Why it is obsolete |
| --- | --- |
| Laravel 11 | Outside current security support relative to Laravel 13; not required by PHP 8.4 |
| PHP 8.2 as the production floor | Production is PHP 8.4 |
| Filament 3 | Incompatible with the accepted Laravel 13 / Filament 5 stack |
| Public `/de` and `/de/articles/*` | No complete German content exists. German remains draft-only |
| `pages` table | Homepage and service pages are source-controlled Blade, not CMS pages |
| `contact_requests` table | Contact rows are stored in `requests` |
| Duplicate language-prefixed public article URLs | EN/FA stay on the original same-URL behaviour |

## Public request flow

Web server (Apache on DirectAdmin) → `public/index.php` → Laravel middleware/routes → controllers / Eloquent → Blade.

Static assets (`/assets/*`, `/manifest.json`, `/sw.js`, fonts, PDF) are files under `public/` copied from the original tree. They are not rewritten by Laravel.

Physical legacy `articles/*.html` and `services/*.html` remain **outside** `public/` so Apache cannot serve them and bypass Laravel.

## Core schema

As decided on 2026-09-16: `users`, `password_reset_tokens`, `sessions`, `categories`, `articles`, `article_redirects`, `requests`.

No `contact_requests`. No `pages`.

> Since this decision was recorded, three tables were added by later migrations under the same architecture: `services` (phase 07) and `tags` + `article_tag` (phase 08). The authoritative schema is [../../current/DATABASE.md](../../current/DATABASE.md).

## URL policy

| Source | Behaviour |
| --- | --- |
| `/` | Homepage |
| `/index.html` | 301 → `/` |
| `/services/{page}.html` | Unchanged service URLs |
| `/articles/{slug}.html` | 301 → `/articles/{slug}` (query string preserved, no chain) |
| `/articles/{slug}` | Article detail |
| Unknown article | 404 |
| `/de`, `/de/articles/*` | 404 |
| `/admin` | Filament; unauthenticated users redirect to `/admin/login` |
| GET `/forms/get-csrf-token.php` | JSON `{ token, success }` |
| POST `/forms/contact.php` | Literal `OK` on success; honeypot still returns `OK` |

## SEO policy

- Dynamic `/sitemap.xml` and `/robots.txt`.
- Sitemap excludes `/index.html`, legacy `.html` article URLs, redirects, drafts, `/admin`, and unpublished German.
- Canonical, OpenGraph, Twitter, and JSON-LD are preserved from source when present.
- Relative schema/image URLs are rewritten to absolute URLs.
- Publication dates are taken from original Article JSON-LD when present; otherwise CMS visibility uses the original HTML file mtime. Dates are not invented.

## PWA policy

`public/sw.js` replaces the unsafe placeholder worker. It caches the homepage, manifest, offline fallback, and static assets. It does not cache `/admin/*`, `/livewire*`, `/forms*`, or `.php` responses.

## Related documents

- Supporting version evidence: `docs/framework-version-decision.md`
- Implementation status (historical): `docs/historical/implementation-report.md`
- Hosting: `DEPLOYMENT.md`
- QA: `docs/QA-MATRIX.md` (historical snapshot: `docs/historical/final-site-qa-report.md`)
