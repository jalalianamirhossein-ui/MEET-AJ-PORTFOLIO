# Performance — Meet AJ

**Authority:** AUTHORITATIVE statement of what is known about performance.
**Verified:** 2026-09-17 by reading query scopes, view code, the service worker, and measuring published asset sizes on disk.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md).

> **No performance measurement has ever been run on this project.** There is no Lighthouse report, no WebPageTest run, no load test, and no query profiling. Everything below is either a code-level fact or a file measurement. Treat every timing claim elsewhere as **UNKNOWN / NOT VERIFIED**.

## Measured facts

Published asset sizes in `public/assets/` (147 files, measured on disk 2026-09-17):

| File | Size |
|------|------|
| `css/main.css` | 195.1 KB |
| `css/visual-upgrade.css` | 84.0 KB |
| `js/main.js` | 38.3 KB |
| `css/articles.css` | 33.4 KB |
| `css/rtl.css` | 19.9 KB |
| `css/services.css` | 13.9 KB |
| `js/i18n.js` | 11.3 KB |
| `css/lang-toggle.css` | 3.0 KB |

Images under `public/assets/img/` total roughly **38.9 MB** on disk. That is the full library, not the per-page payload, but it is the largest asset category by a wide margin and the most likely place to find real wins.

None of these files are minified or bundled: there is no Node, Vite or Tailwind build step in the project.

## Query behaviour (code-level)

| Path | Behaviour |
|------|-----------|
| `/articles` (unfiltered) | `Article::published()->forListing()->with(['category','tags'])` — `forListing()` selects only card columns, so article HTML bodies are never loaded for the grid |
| `/articles?q=` / `?tag=` | Same scopes plus `search()` / `withTag()`, paginated **9 per page** with `withQueryString()` |
| `/articles/{slug}` | Single row with `category` and `tags` eager-loaded, plus `relatedArticles(3)` |
| `/` | Published service catalog ordered by `sort_order` |
| Filament Requests table | `modifyQueryUsing(fn ($q) => $q->with('service'))` to avoid N+1 on the service column |

Relations are eager-loaded on the public paths, so no N+1 pattern is visible in the code. This has not been confirmed with a query profiler.

## Indexes that matter

`articles(language, status, published_at)`, `articles(status, published_at)`, `services(status, sort_order)`, `services(language, status, published_at)`, `requests(status, created_at)`, `article_tag(tag_id)`, unique `article_redirects(old_path)`. Full list: [DATABASE.md](DATABASE.md).

## Caching layers

| Layer | State |
|-------|-------|
| Config / route / view cache | Available; locally only views were cached at the time of verification |
| Application cache driver | `file` |
| Sessions | `file` |
| Queue | `sync` — no background processing |
| HTTP caching | `SecurityHeaders` forces `no-store` on `/admin`, `/livewire`, `/forms` and POST responses; other responses use framework defaults |
| Service worker | Documents network-first, static assets cache-then-network ([PWA.md](PWA.md)) |

`php artisan config:cache route:cache view:cache` is part of the deployment procedure ([DEPLOYMENT.md](DEPLOYMENT.md)).

## Front-end characteristics

- Asset cache busting is manual via query strings (`visual-upgrade.css?v=1314`, `main.js?v=1119`, `i18n.js?v=1116`); every publish point must be bumped together.
- CSS is layered: original `main.css` → `rtl.css` → `visual-upgrade.css` overlay. The overlay adds weight rather than replacing the base sheet.
- Language switching is client-side DOM attribute swapping, so both languages ship in every page.

## Status summary

| Item | Status |
|------|--------|
| Asset sizes measured | PASS (values above) |
| Query patterns reviewed for N+1 | PASS (code review only) |
| Lighthouse performance / accessibility / SEO scores | NOT TESTED |
| Core Web Vitals (LCP, CLS, INP) | NOT TESTED |
| Server response times under load | NOT TESTED |
| Image optimisation audit | NOT TESTED |
| Production performance on meetaj.ir | BLOCKED |
