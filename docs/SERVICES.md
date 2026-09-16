# Services CMS — Meet AJ

**Authority:** AUTHORITATIVE service catalog document.  
**Source of truth for copy:** `services/*.html` (six original quote pages).  
**Do not invent prices, German copy, clients, certifications, or ratings.**

## Architecture

Public catalog is **database-driven**. English rows with `status = published` and `published_at <= now()` appear on the homepage and at `/services/{slug}`. Persian copy lives in `presentation.*_fa` and `data-fa` attributes on the same English page (same pattern as articles). German rows may exist only as **drafts**.

| Layer | Class / file |
|-------|----------------|
| Table | `services` |
| Model | `App\Models\Service` |
| Policy | `App\Policies\ServicePolicy` (admin only) |
| Admin | `App\Filament\Resources\ServiceResource` (Content group) |
| Import | `php artisan services:import-legacy` → `LegacyServiceImporter` |
| Public | `HomeController` catalog + `ServiceController@show` |
| Requests | `requests.service_id` nullable FK → `services` (`nullOnDelete`) |

## URLs

| URL | Behaviour |
|-----|-----------|
| `/#services` | Homepage catalog (`sort_order`) |
| `/services/{slug}` | Canonical detail (published EN only) |
| `/services/{slug}.html` | **301** → `/services/{slug}` (query string preserved, no chain) |

Indexed `.html` URLs stay valid. Sitemap lists **clean** URLs only.

Slugs (homepage order): `network-design`, `system-administration`, `devops-automation`, `monitoring-security`, `virtualization-solutions`, `technical-consulting`.

## Fields

| Column | Notes |
|--------|--------|
| `title`, `slug`, `language`, `translation_key` | Unique `(language, slug)` and `(translation_key, language)` |
| `short_description` | Homepage card |
| `description` | Hero subtitle |
| `content` | Longer overview |
| `features`, `process`, `faq` | JSON (EN/FA pairs; process includes durations; FAQ Q/A) |
| `price`, `price_currency`, `price_label`, `price_type` | Admin-controlled. Types: `fixed`, `starting_from`, `custom_quote` |
| `featured_image` | Optional JPEG/PNG/WebP. Cards fall back to Bootstrap icon |
| `presentation` | JSON: icon, FA strings, exclusions, deliverables, SLA, add-ons, imported price labels |
| `seo_title`, `seo_description`, `og_title`, `og_description` | Detail `<head>` |
| `sort_order` | Homepage order (lower first) |
| `status`, `published_at` | `draft` / `published`. Drafts never appear publicly |

Currency default is **AED** because the original HTML/JSON-LD uses AED. Do not invent a new amount; change prices only in Filament.

Display examples from `Service::displayPrice()`:

- fixed: `AED 4,900` (imported label) or `AED 4,900` generated
- starting_from: `Starting from 500 AED`
- custom_quote / empty price: `Request a quote`

## Publishing

Only **published** English services appear on:

- homepage `#service-catalog`
- `/services/{slug}`
- `/sitemap.xml`
- public navigation/CTAs

Draft, future `published_at`, FA/DE rows, and German content are hidden. Publishing `language = de` throws `ValidationException`.

## Pricing

Prices are **not** hardcoded in Blade. Filament **Pricing** section owns `price`, `price_currency`, `price_type`, `price_label`. Imported amounts from original JSON-LD:

| Service | AED (source) |
|---------|----------------|
| Network Design | 4900 |
| System Administration | 3900 |
| DevOps & Automation | 6900 |
| Monitoring & Security | 4200 |
| Virtualization Solutions | 5900 |
| Technical Consulting | 2500 |

## Requests

Service detail form posts to the existing `/forms/contact.php` contract (CSRF, honeypot `website`, validation, 5/hour). Optional field `service` is the published slug. `ContactController` stores `requests.service_id` when the slug matches a published English service. Homepage contact without `service` still works (`service_id` null).

Filament **Requests**: Service column + filter. Admin only.

## Admin

Content → **Services**. Admin only (`ServicePolicy` / `canViewAny`). Editors cannot list or edit services.

Supports create, edit, delete (confirmed), publish, unpublish, replicate (new draft), search, filters (language / status / price type), sort, bulk publish/unpublish.

Image upload: JPEG/PNG/WebP, max 5 MB. Executable extensions rejected on the model.

## Translations

EN + FA on the public English URL via `data-en` / `data-fa`. No invented German pages. Language switcher remains EN/FA (`i18n.js`). Empty DE rows must stay draft.

## Import

```bash
php artisan services:import-legacy
php artisan services:import-legacy --dry-run
php artisan services:import-legacy --refresh
```

`--refresh` deletes existing service rows then re-imports from `services/*.html`. Do not run it after editorial price/copy changes.

`DatabaseSeeder` imports articles and services after rebuilding Blade views.
