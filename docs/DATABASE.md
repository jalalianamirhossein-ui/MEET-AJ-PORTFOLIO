# Database — Meet AJ Laravel CMS

**Authority:** AUTHORITATIVE schema document.  
**Source:** `database/migrations/2026_09_15_000001` … `000006` plus `2026_09_16_000007` (services) and `000008` (`requests.service_id`).  
**Verified:** 2026-09-16 against those files and `MysqlSchemaTest`.

`users`, `password_reset_tokens`, `sessions`, `categories`, `articles`, `article_redirects`, `requests`, `services`.

## Engines

| Context | Connection | Status |
|---------|------------|--------|
| Local `php artisan serve` | SQLite `database/database.sqlite` (`php artisan about`, 2026-09-16) | LOCAL TESTED |
| Default PHPUnit (`phpunit.xml`) | SQLite `:memory:` | LOCAL TESTED |
| `phpunit.mysql.xml` | MySQL/MariaDB `127.0.0.1:3307`, database `meetaj_test` | INTEGRATION TESTED (`MysqlSchemaTest` OK) |
| DirectAdmin production | Intended `mysql` / MariaDB | BLOCKED · NOT TESTED |

PHPUnit 11 ignores forced env vars from XML in some cases; `tests/TestCase.php` plus `.env.testing` isolate the default suite from the live SQLite file. Use `phpunit.mysql.xml` when MySQL must be bound.

## Tables

### `users`

Purpose: Filament login accounts.

| Column | Notes |
|--------|--------|
| `id` | PK |
| `name` | display name |
| `email` | **unique** |
| `email_verified_at` | nullable (not used by the public site) |
| `password` | hashed |
| `role` | `admin` or `editor`, default `editor` |
| `remember_token` | Laravel remember-me |
| `timestamps` | |

Relationships: sessions `user_id` → `users.id` (`nullOnDelete`). No FK from articles.

### `password_reset_tokens`

Laravel password-reset store (`email` PK, `token`, `created_at`). Created in the users migration. Public site does not expose a custom reset UI beyond Filament defaults.

### `sessions`

File vs database: production example uses `SESSION_DRIVER=file`. The table exists for `database` driver if selected.

| Column | Notes |
|--------|--------|
| `id` | PK string |
| `user_id` | nullable FK → `users`, **nullOnDelete** |
| `ip_address` | 45 chars |
| `user_agent` | text |
| `payload` | longText |
| `last_activity` | indexed |

### `categories`

Purpose: article taxonomy (Microsoft, Linux, MikroTik, VMware, Others, plus language variants).

| Column | Notes |
|--------|--------|
| `id` | PK |
| `translation_key` | UUID grouping translations |
| `name` | label |
| `slug` | 180 chars |
| `language` | 2 chars, default `en` |
| `timestamps` | |

Constraints: **unique** `(language, slug)`, **unique** `(translation_key, language)`.

Articles: `category_id` **nullOnDelete** (article kept if category deleted).

### `articles`

Purpose: CMS article records. Public listing/detail query **published English** rows.

| Column | Notes |
|--------|--------|
| `id` | PK |
| `translation_key` | UUID |
| `title`, `slug` | slug 180 chars |
| `language` | `en` / `fa` / `de` |
| `excerpt` | nullable text |
| `content` | longText (HTML) |
| `featured_image` | path or URL, nullable |
| `category_id` | nullable FK → `categories`, **nullOnDelete** |
| `meta_title`, `meta_description`, `canonical_url` | SEO overrides |
| `seo_data` | JSON (OG/Twitter/schema leftovers from import) |
| `presentation` | JSON (card labels, dates, filter class, hero fields) |
| `sort_order` | unsigned int, default 0 |
| `status` | `draft` / `published` |
| `published_at` | nullable; future dates are not shown as published |
| `timestamps` | |

Constraints: **unique** `(language, slug)`, **unique** `(translation_key, language)`.  
Indexes: `(language, status, published_at)`, `(status, published_at)`.

Publishing German (`language = de` + `published`) is rejected in the `Article` model.

### `article_redirects`

Purpose: 301 map from a previous path to the current article.

| Column | Notes |
|--------|--------|
| `id` | PK |
| `old_path` | **unique** (example `/articles/{slug}.html`) |
| `article_id` | FK → `articles`, **cascadeOnDelete** |
| `timestamps` | |

Deleting an article removes its redirect rows.

### `requests`

Purpose: inbound contact form submissions.

| Column | Notes |
|--------|--------|
| `id` | PK |
| `name` | 100 chars |
| `email` | |
| `phone` | nullable, 40 chars |
| `subject` | nullable (form still requires subject at validation) |
| `message` | text |
| `status` | default `new` |
| `service_id` | nullable FK → `services`, **nullOnDelete** |
| `timestamps` | |

Index: `(status, created_at)`, `service_id`. No user FK.

### `services`

Purpose: public service catalog. Homepage and `/services/{slug}` query **published English** rows (`Service::publicCatalog()`).

| Column | Notes |
|--------|--------|
| `id` | PK |
| `translation_key` | UUID grouping translations |
| `title`, `slug` | slug 180 chars |
| `language` | `en` / `fa` / `de` |
| `short_description`, `description` | card + hero |
| `content` | longer overview |
| `features`, `process`, `faq`, `presentation` | JSON |
| `price` | decimal(12,2), nullable (required unless `price_type = custom_quote`) |
| `price_currency` | default `AED` |
| `price_label` | optional badge |
| `price_type` | `fixed` / `starting_from` / `custom_quote` |
| `featured_image` | path, nullable; executable suffixes rejected |
| `seo_title`, `seo_description`, `og_title`, `og_description` | SEO |
| `sort_order` | homepage order |
| `status` | `draft` / `published` |
| `published_at` | required when published |
| `timestamps` | |

Constraints: **unique** `(language, slug)`, **unique** `(translation_key, language)`.  
Publishing German is rejected in the `Service` model.

## Relationships (summary)

```
categories 1 ──< articles (nullOnDelete)
articles    1 ──< article_redirects (cascadeOnDelete)
users       1 ──< sessions (nullOnDelete)
services    1 ──< requests (nullOnDelete)
```

## Production database status

Not created or migrated on DirectAdmin from this environment. Do not claim production schema validation.
