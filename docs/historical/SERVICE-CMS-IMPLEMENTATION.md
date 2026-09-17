> **HISTORICAL / SUPERSEDED.**
> **Original date:** 2026-09-16.
> **Original purpose:** implementation report for the service catalog CMS, including the test counts and git state of that day.
> **Superseded by:** [../current/SERVICES.md](../current/SERVICES.md) and [../phases/phase-07-service-cms.md](../phases/phase-07-service-cms.md).
> Current status: [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md). Test counts below (30 tests / 584 assertions, 35 routes) are from that date and were **not** rewritten.

# Service CMS implementation — Meet AJ

**Date:** 2026-09-16  
**Scope:** Homepage service catalog, Filament Service CMS, pricing, requests, SEO, EN/FA (no invented DE).

## Services implemented

Six original quote pages imported from `services/*.html` (source of truth; prices from JSON-LD, not invented):

| Slug | AED (source) | sort_order |
|------|----------------|------------|
| `network-design` | 4900 | 1 |
| `system-administration` | 3900 | 2 |
| `devops-automation` | 6900 | 3 |
| `monitoring-security` | 4200 | 4 |
| `virtualization-solutions` | 5900 | 5 |
| `technical-consulting` | 2500 | 6 |

Public query: published English rows, `sort_order` then `id`. Drafts never appear on homepage, detail, sitemap, or navigation.

## Database changes

- `2026_09_16_000007_create_services_table` — `services` with JSON `features` / `process` / `faq` / `presentation`, unique `(language, slug)` and `(translation_key, language)`
- `2026_09_16_000008_add_service_id_to_requests_table` — nullable FK `requests.service_id` → `services` (`nullOnDelete`)

No duplicate service tables. Model: `App\Models\Service`. Policy: admin-only CRUD.

## Admin functionality

Filament `ServiceResource` under **Content** (sort 3), `/admin/services`. Admin only.

Create / read / update / delete (confirmed) / publish / unpublish / replicate / search / filters (language, status, price type) / sort / bulk publish (skips DE) / bulk unpublish.

`RequestResource`: Service column + filter. Homepage contacts show “no service”.

## Homepage changes

`index.html` marker `id="service-catalog"`. `LegacySitePublisher` injects `@include('components.service-card')`. Cards: icon, title link, short description, DB price, View Details, Request Service. Overlay `visual-upgrade.css?v=1109`. No Tailwind / shadcn.

## Service detail pages

- Canonical: `/services/{slug}`
- Legacy: `/services/{slug}.html` → **301** once (query string preserved)
- Template: `resources/views/services/show.blade.php`
- Sections from imported source: hero, pricing, features, exclusions, deliverables, timeline, SLA, add-ons, FAQ, quote form
- No invented ratings, clients, or German copy

## Pricing

Admin-controlled `price`, `price_currency` (default AED from source), `price_type` (`fixed` / `starting_from` / `custom_quote`), `price_label`. Blade calls `Service::displayPrice()`.

## Request system

Existing `POST /forms/contact.php` (CSRF, honeypot, validation, 5/hour). Optional `service` slug → `service_id` when the slug is a published English service. Homepage form unchanged when `service` is omitted.

## SEO

Detail pages: title, description, canonical (clean URL), Open Graph, Twitter, JSON-LD `Service` + `Offer` using actual price/currency. Sitemap lists published clean service URLs only (no `.html`).

## Languages

EN public rows; FA via `data-fa` / `presentation.*_fa` (same as articles). DE cannot be published (`ValidationException`). Switcher remains EN/FA (`i18n.js`). No invented German services.

## Accessibility

Skip link, labeled form fields, `role="alert"` errors, FAQ `aria-expanded` / `aria-controls`, 44px control height, visible `:focus-visible`, semantic `h1`/`h2`/`h3`. Homepage catalog links are real `<a>` elements.

## Responsive QA

| Viewport | Method | Result |
|----------|--------|--------|
| Desktop ~1440 (FA) | Browser screenshot of `#services` | Six compact cards, prices, two CTAs, no new UI kit |
| 375×812 | CDP device metrics on detail | Single column, price readable, back link visible |
| 320 / 390 / 414 / 768 / 1024 / 1366 / 1920 | CSS breakpoints in `main.css` + overlay (`991px` / `767px` / `575px`) | Implemented; **not** fully re-screenshoted this pass |

No horizontal overflow observed on the desktop catalog screenshot. RTL catalog verified in FA.

## Tests

| Command | Result |
|---------|--------|
| `php artisan test` | **30 tests, 584 assertions, 1 skipped, 0 failures** |
| `php artisan site:compare-content` | **Failures: 0** |
| `php artisan route:list` | 35 routes including `services.show`, `services.legacy`, `/admin/services` |
| `php artisan migrate` | `000007` + `000008` applied on local SQLite |
| `php artisan services:import-legacy` | 6 imported, 0 skipped |

Skipped test: `MysqlSchemaTest` (MySQL not bound in default sqlite suite).

## Files changed (primary)

- `app/Models/Service.php`, `app/Models/Request.php`
- `app/Policies/ServicePolicy.php`
- `app/Http/Controllers/{Home,Service,Contact,Sitemap}Controller.php`
- `app/Http/Requests/StoreContactRequest.php`
- `app/Mail/ContactReceivedMail.php`
- `app/Services/{LegacyServiceImporter,LegacySitePublisher}.php`
- `app/Filament/Resources/ServiceResource.php` + Pages
- `app/Filament/Resources/RequestResource.php`
- `app/Console/Commands/{ImportLegacyServices,CompareLegacyContent}.php`
- `database/migrations/2026_09_16_000007_create_services_table.php`
- `database/migrations/2026_09_16_000008_add_service_id_to_requests_table.php`
- `database/seeders/DatabaseSeeder.php`
- `routes/web.php`
- `resources/views/services/show.blade.php`
- `resources/views/components/service-card.blade.php`
- `index.html` (catalog marker + CSS cache `v=1109`)
- `assets/css/visual-upgrade.css`
- `tests/Feature/{PublicSite,CmsOperations,ContentRules,MysqlSchema,ServiceCatalog}Test.php`
- Docs: `README.md`, `DEPLOYMENT.md`, `docs/{PROJECT-STATUS,ADMIN,DATABASE,QA-MATRIX,ARCHITECTURE,SEO,MULTILINGUAL,SECURITY,SERVICES,DOCUMENTATION-INDEX}.md`

Original `services/*.html` files were **not** rewritten.

## Git

Inspected `.git/HEAD` and `.git/config` (git.exe is **not** on PATH; `git` command not found).

| Item | Value |
|------|--------|
| Working branch (HEAD) | `feature/laravel-migration` |
| `laravel` branch | **not created** (Git CLI missing) |
| Remote | `AJ` → `https://github.com/jalalianamirhossein-ui/MEET-AJ-PORTFOLIO.git` |
| Commit | **BLOCKED** |
| Push | **BLOCKED** (`REMOTE PUSH: BLOCKED`) |

Do not treat this as a successful commit or push. Install Git for Windows (or add `git.exe` to PATH), then:

```bash
git switch -c laravel
git status
git add …
git commit -m "feat: complete Laravel CMS and service catalog"
git push -u AJ laravel
```

Do not push `main`/`master`. Do not force push.

## Remaining blockers

1. Git CLI missing → branch/commit/push not executed
2. DirectAdmin production cutover still BLOCKED
3. Interactive Filament Services UI login **not** exercised in a real admin session
4. Full nine-viewport screenshot matrix not fully captured
5. Live SMTP still optional / NOT TESTED
