# Services — Meet AJ

**Authority:** AUTHORITATIVE service catalog document.
**Verified:** 2026-09-21 against the live `services` table, `app/Models/Service.php`, `app/Filament/Resources/ServiceResource.php`, `app/Policies/ServicePolicy.php`, and `resources/views/components/service-card.blade.php`.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md).

## Current catalog

The 2026-09-21 local recheck found **13 published English service records, 12 visible in the homepage catalog**. The six legacy records below retain fixed AED prices. Technical Consulting has `show_in_catalog = false`; the other five and seven custom-quote records are visible. The custom-quote slugs are `mikrotik-routing-multi-wan`, `hp-enterprise-server`, `sql-server-high-availability`, `jira-implementation`, `voip-infrastructure`, `cctv-surveillance`, and `network-security`.

| Legacy # | Slug | Title | Price |
|---|------|-------|-------|
| 1 | `network-design` | Network Design & Implementation | AED 4,900 |
| 2 | `system-administration` | System Administration | AED 3,900 |
| 3 | `devops-automation` | DevOps & Automation | AED 6,900 |
| 4 | `monitoring-security` | Monitoring & Security | AED 4,200 |
| 5 | `virtualization-solutions` | Virtualization Solutions | AED 5,900 |
| 6 | `technical-consulting` | Technical Consulting | AED 2,500 |

AED is the currency used by the original static pages and their JSON-LD. Prices are editorial data: change them in Filament, never in Blade or documentation.

## Architecture

The catalog is **database-driven**. English rows with `status = published`, `published_at <= now()`, and `show_in_catalog = true` appear in the homepage catalog. Standalone service detail pages have been removed.

| Layer | Class or file |
|-------|---------------|
| Table | `services` ([DATABASE.md](DATABASE.md)) |
| Model | `App\Models\Service` |
| Policy | `App\Policies\ServicePolicy` (admin only) |
| Admin | `App\Filament\Resources\ServiceResource` (Content group) |
| Import | `php artisan services:import-legacy` → `App\Services\LegacyServiceImporter` |
| Public | `HomeController@index` catalog |
| View | `components/service-card.blade.php` and the homepage service drawer |
| Requests | `requests.service_id` nullable FK → `services`, set null on delete |

## Database model and fields

| Field | Role |
|-------|------|
| `title`, `slug`, `language`, `translation_key` | Identity; unique `(language, slug)` and `(translation_key, language)` |
| `short_description` | Homepage card text |
| `description` | Optional extended homepage copy |
| `content` | Longer stored overview |
| `features`, `process`, `faq` | JSON arrays edited as Filament repeaters |
| `price`, `price_currency`, `price_label`, `price_type` | Pricing, admin-controlled |
| `featured_image` | Optional JPEG/PNG/WebP; cards fall back to an icon |
| `presentation` | JSON: `icon`, Persian strings (`title_fa`, `short_description_fa`, `description_fa`, `price_label_fa`, `cta_fa`, …), catalog features and `form_subject` |
| `seo_title`, `seo_description`, `og_title`, `og_description` | Retained legacy metadata; no standalone service page emits it |
| `sort_order` | Homepage order, lower first |
| `status`, `published_at` | Publication gate |

## Pricing

`Service::displayPrice($locale)` decides what a visitor sees in the homepage catalog/drawer:

- `price_type = fixed` → the formatted amount, for example `AED 4,900`
- `price_type = starting_from` → a “starting from” phrasing
- `price_type = custom_quote`, or an empty `price` → `price_label` if set, otherwise `Request a quote` (Persian: `درخواست پیش‌فاکتور` from `presentation.quote_label_fa`)

No price is hardcoded in a Blade template.

## Publication

A service is shown in the homepage catalog only when `language` is in `config('cms.public_languages')` (`en`, `fa`), `status = published`, `published_at` is set and not in the future, and `show_in_catalog = true`. Drafts, future dates, German rows, and hidden catalog rows never appear in the catalog. Publishing `language = de` throws `ValidationException` in the model.

## Translations

English and Persian share the same URL. Persian copy comes from `presentation.*_fa` values rendered into `data-fa` attributes, applied client-side by `assets/js/i18n.js` with RTL styling from `rtl.css`. There is no German service content and no `/de` route.


## Public placement

| URL | Behaviour |
|-----|-----------|
| `/#services` | Homepage catalog, ordered by `sort_order` |
| `/services/{slug}` | Removed; returns 404 |
| `/services/{slug}.html` | Removed; returns 404 |

Services are not listed in `/sitemap.xml`.

## Requests from the homepage catalog

The homepage service catalog opens the shared contact form and posts to `POST /forms/contact.php` (CSRF, honeypot `website`, validation, rate limit) with an extra `service` field carrying the slug. `ContactController` stores `requests.service_id` when the slug matches a published English service; an unknown slug is ignored and the row is still saved with `service_id = NULL`. Detail: [REQUESTS.md](REQUESTS.md).

## Filament management

Filament **Content → Services**, admin only (`ServicePolicy`); editors receive an authorization failure, which PHPUnit asserts.

- Form sections: General, Content (repeaters for features, process, FAQ), Pricing, Media, SEO, Publishing
- Table columns: title, language, status, formatted price, currency, sort order, published at, updated at
- Filters: language, status, price type
- Actions: edit, replicate as draft, publish, unpublish, delete with confirmation, bulk publish/unpublish

## Authorization

| Action | Admin | Editor | Guest |
|--------|-------|--------|-------|
| View the public catalog | yes | yes | yes |
| List or edit services in Filament | yes | no | no |
| Change prices or publish | yes | no | no |

## Images

`featured_image` accepts JPEG, PNG and WebP up to 5 MB through the Filament upload field; the model rejects executable suffixes. Cards fall back to a Bootstrap icon taken from `presentation.icon` when no image is set.

## SEO

Service records no longer emit standalone page metadata or sitemap URLs. Homepage SEO remains in `home.blade.php`.

## Import

```bash
php artisan services:import-legacy
php artisan services:import-legacy --dry-run
php artisan services:import-legacy --refresh
```

The importer reads the six legacy service HTML sources for missing records, skips existing records, then synchronizes the extended homepage catalog through `HomepageServiceCatalog`. `--refresh` deletes existing service rows before re-importing, so it discards editorial price and copy changes. `DatabaseSeeder` runs the article and service importers after rebuilding Blade views.
