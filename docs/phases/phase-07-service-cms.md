> **HISTORICAL phase log.** Full contemporaneous report: [../historical/SERVICE-CMS-IMPLEMENTATION.md](../historical/SERVICE-CMS-IMPLEMENTATION.md). Current service documentation: [../current/SERVICES.md](../current/SERVICES.md).

# Phase 07 — Service CMS

**Phase:** 07
**Date:** 2026-09-16
**Status:** **PASS** locally · authenticated Filament services UI **BLOCKED**

## Objective

Turn six hardcoded service quote pages into a database-driven catalog with admin-controlled pricing, without inventing any price or German copy.

## Changes

- Migration `2026_09_16_000007_create_services_table` — `services` with JSON `features` / `process` / `faq` / `presentation` and unique `(language, slug)` plus `(translation_key, language)`.
- Migration `2026_09_16_000008_add_service_id_to_requests_table` — nullable FK `requests.service_id` → `services` with `nullOnDelete`.
- `App\Models\Service` with `publicCatalog()`, `displayPrice()` and German-publishing rejection; `ServicePolicy` admin-only.
- `LegacyServiceImporter` and `php artisan services:import-legacy` reading `services/*.html`; prices taken from the original JSON-LD.
- Filament `ServiceResource` under **Content**, plus a Service column and filter on `RequestResource`.
- Homepage marker `id="service-catalog"` with `components/service-card.blade.php` injected by `LegacySitePublisher`.
- Canonical `/services/{slug}` with a single 301 from `/services/{slug}.html`; `resources/views/services/show.blade.php` renders hero, pricing, features, exclusions, deliverables, timeline, SLA, add-ons, FAQ and a gated quote form.
- Optional `service` field on the contact contract so a quote request links to its service.

Prices imported (AED, `sort_order` in brackets): network-design 4900 (1), system-administration 3900 (2), devops-automation 6900 (3), monitoring-security 4200 (4), virtualization-solutions 5900 (5), technical-consulting 2500 (6).

## Files changed

`app/Models/{Service,Request}.php`, `app/Policies/ServicePolicy.php`, `app/Http/Controllers/{Home,Service,Contact,Sitemap}Controller.php`, `app/Http/Requests/StoreContactRequest.php`, `app/Mail/ContactReceivedMail.php`, `app/Services/{LegacyServiceImporter,LegacySitePublisher}.php`, `app/Filament/Resources/ServiceResource.php` + pages, `app/Filament/Resources/RequestResource.php`, `app/Console/Commands/{ImportLegacyServices,CompareLegacyContent}.php`, the two migrations, `database/seeders/DatabaseSeeder.php`, `routes/web.php`, `resources/views/services/show.blade.php`, `resources/views/components/service-card.blade.php`, `index.html`, `assets/css/visual-upgrade.css`, and the feature tests.

Original `services/*.html` files were **not** rewritten.

## Commands executed

```text
php artisan migrate
php artisan services:import-legacy
php artisan route:list
php artisan site:compare-content
php artisan test
```

## Tests

| Command | Result at the time |
|---------|--------------------|
| `php artisan test` | 30 tests, 584 assertions, 1 skipped, 0 failures |
| `php artisan site:compare-content` | Failures: 0 |
| `php artisan route:list` | 35 routes including `services.show`, `services.legacy`, `/admin/services` |
| `php artisan services:import-legacy` | 6 imported, 0 skipped |

The skip was `MysqlSchemaTest`. (The suite later grew to 39 tests / 647 assertions — see [../current/TESTING.md](../current/TESTING.md).)

## Results

Six published English services render on the homepage and at their canonical URLs, with prices read from the database and JSON-LD `Offer` values matching. Drafts, future dates and German rows stay invisible. Requests submitted from a service page carry `service_id`.

## Blockers

1. Git CLI missing, so no branch, commit or push was made.
2. DirectAdmin cutover still BLOCKED.
3. Interactive Filament Services UI never exercised in a real admin session.
4. Full viewport screenshot matrix not captured.
5. Live SMTP still optional and NOT TESTED.
