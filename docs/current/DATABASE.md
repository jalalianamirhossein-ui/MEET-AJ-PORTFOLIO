# Database — Meet AJ

**Authority:** AUTHORITATIVE schema document.
**Verified:** 2026-09-21 by reading the live SQLite schema (`Schema::getTables()`, `getColumns()`, `getIndexes()`, `getForeignKeys()`) plus the migration files in `database/migrations/`.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md).

Column types below are the SQLite types actually reported by the database. The migrations declare portable Laravel types (`string`, `text`, `decimal`, `json`), so MySQL/MariaDB will report `varchar`, `longtext`, `decimal(12,2)` and `json` for the same columns.

## Engines

| Context | Connection | Status |
|---------|------------|--------|
| Local `php artisan serve` / artisan commands | SQLite `.runtime/cms.sqlite` (local `DB_DATABASE`) | PASS |
| Default PHPUnit suite (`phpunit.xml`) | SQLite `:memory:` | PASS |
| `phpunit.mysql.xml` | MySQL / MariaDB `127.0.0.1:3307`, database `meetaj_test` | PASS (`MysqlSchemaTest`, last run 2026-09-16) |
| DirectAdmin production | intended MySQL / MariaDB | BLOCKED · NOT TESTED |

`tests/TestCase.php` explicitly forces the default suite onto SQLite `:memory:` before boot; it does not require `.env.testing`. Bind MySQL explicitly with `phpunit.mysql.xml`.

## Migrations

| Migration | Batch | Status |
|-----------|-------|--------|
| `2026_09_15_000001_create_users_table` | 1 | Ran |
| `2026_09_15_000002_create_categories_table` | 1 | Ran |
| `2026_09_15_000003_create_articles_table` | 1 | Ran |
| `2026_09_15_000004_create_article_redirects_table` | 1 | Ran |
| `2026_09_15_000005_create_requests_table` | 1 | Ran |
| `2026_09_15_000006_create_sessions_table` | 1 | Ran |
| `2026_09_16_000007_create_services_table` | 1 | Ran |
| `2026_09_16_000008_add_service_id_to_requests_table` | 1 | Ran |
| `2026_09_17_000009_create_tags_and_request_workflow` | 1 | Ran |
| `2026_09_17_184900_add_show_in_catalog_to_services_table` | 1 | Ran |
| `2026_09_18_000010_add_accent_color_to_categories_table` | 1 | Ran |
| `2026_09_20_000011_add_sort_order_to_categories_table` | 2 | Ran |
| `2026_09_20_000012_create_testimonials_table` | 2 | Ran |
| `2026_09_20_000013_create_homepage_contents_table` | 3 | Ran |
| `2026_09_20_000014_restore_resume_content` | 4 | Ran |
| `2026_09_21_000015_refresh_testimonial_copy` | 5 | Ran |
| `2026_09_21_000016_refresh_testimonials_full_set` | 6 | Ran |
| `2026_09_21_000017_repair_missing_article_redirects` | 7 | Ran |

The users migration also creates `password_reset_tokens`. Laravel's own `migrations` table makes the migration ledger. Seventeen application migrations exist through `2026_09_21_000017_repair_missing_article_redirects`.

## Table overview

| Table | Purpose | Rows (2026-09-21) |
|-------|---------|-------------------|
| `users` | Filament login accounts | 0 |
| `password_reset_tokens` | Laravel password reset store | 0 |
| `sessions` | Session rows when the database session driver is selected | 0 |
| `categories` | Article taxonomy, one row per language | 10 |
| `articles` | Article content and SEO (25 imported) | 25 |
| `article_redirects` | 301 map from old paths to articles | 25 |
| `tags` | Flat public tag vocabulary | 8 |
| `article_tag` | Article ↔ tag pivot | 39 |
| `requests` | Inbound contact submissions | 0 |
| `services` | Service catalog and pricing (12 visible) | 13 |
| `testimonials` | Bilingual homepage testimonials | 9 |
| `homepage_contents` | Editable homepage sections | 7 |
| `migrations` | Laravel migration ledger | 17 |

There is **no** `pages` table and **no** `contact_requests` table. Homepage copy is stored in `homepage_contents`; articles, services, testimonials and requests remain dedicated relational resources.

---

## `users`

Filament login accounts. Created by `php artisan cms:create-user` or by an admin in the Users resource.

| Column | Type | Notes |
|--------|------|-------|
| `id` | integer | primary key |
| `name` | varchar | display name |
| `email` | varchar | **unique** (`users_email_unique`) |
| `email_verified_at` | datetime, nullable | not used by the public site |
| `password` | varchar | hashed |
| `role` | varchar | `admin` or `editor`, default `editor` |
| `remember_token` | varchar, nullable | |
| `created_at`, `updated_at` | datetime, nullable | |

Indexes: primary `id`, unique `email`. Foreign keys: none.
Referenced by `sessions.user_id` with **nullOnDelete**.

## `password_reset_tokens`

| Column | Type | Notes |
|--------|------|-------|
| `email` | varchar | primary key (`sqlite_autoindex`, unique) |
| `token` | varchar | |
| `created_at` | datetime, nullable | |

No foreign keys. The public site exposes no custom reset UI beyond Filament defaults.

## `sessions`

| Column | Type | Notes |
|--------|------|-------|
| `id` | varchar | primary key |
| `user_id` | integer, nullable | FK → `users.id`, **set null** on delete |
| `ip_address` | varchar, nullable | |
| `user_agent` | text, nullable | |
| `payload` | text | |
| `last_activity` | integer | indexed (`sessions_last_activity_index`) |

Production configuration uses `SESSION_DRIVER=file`, so this table is normally empty. Deleting a user leaves their session rows with `user_id = NULL`.

## `categories`

Article taxonomy. Each concept exists once per language and the two rows share a `translation_key`: Microsoft / مایکروسافت, Linux / لینوکس, MikroTik / میکروتیک, VMware / مجازی‌سازی, Others / سایر.

| Column | Type | Notes |
|--------|------|-------|
| `id` | integer | primary key |
| `translation_key` | varchar | UUID grouping translations |
| `name` | varchar | label |
| `slug` | varchar | max 180 |
| `language` | varchar | 2 characters, default `en` |
| `accent_color` | varchar, nullable | optional `#RRGGBB`. Empty uses the slug fallback in `Category::accentColor()`. Existing rows were not backfilled. |
| `sort_order` | integer | public article filter order; lower numbers first |
| `created_at`, `updated_at` | datetime, nullable | |

Unique: `(language, slug)`, `(translation_key, language)`. Foreign keys: none.
Deleting a category sets `articles.category_id` to `NULL`; the article survives.

## `testimonials`

Homepage testimonials are stored once with bilingual copy and rendered when `is_published = true`, ordered by `sort_order` and then `id`.

| Column | Type | Notes |
|--------|------|-------|
| `id` | integer | primary key |
| `quote_en`, `quote_fa` | text, nullable | bilingual quote copy |
| `author_name` | varchar | display name |
| `role_en`, `role_fa` | varchar, nullable | bilingual role |
| `company_en`, `company_fa` | varchar, nullable | bilingual company/team |
| `avatar` | varchar, nullable | public asset or uploaded storage path |
| `sort_order` | integer | homepage order; lower numbers first |
| `is_published` | boolean | controls public visibility |
| `created_at`, `updated_at` | datetime, nullable | |

## `homepage_contents`

One row controls each editable homepage section. The stable `key` is consumed by `HomepageContentCatalog` and `resources/views/home.blade.php`.

| Column | Type | Notes |
|--------|------|-------|
| `id` | integer | primary key |
| `key` | varchar | unique section key: `site`, `hero`, `about`, `stats`, `skills`, `resume` or `contact` |
| `label` | varchar | admin-facing section label |
| `content` | text / JSON | bilingual copy and structured section data |
| `is_published` | boolean | controls public visibility; default true |
| `sort_order` | integer | lower values first; default 0 |
| `created_at`, `updated_at` | datetime, nullable | |

Indexes: unique `key`, plus `(is_published, sort_order)`.

## `articles`

| Column | Type | Notes |
|--------|------|-------|
| `id` | integer | primary key |
| `translation_key` | varchar | UUID grouping translations |
| `title` | varchar | |
| `slug` | varchar | max 180 |
| `language` | varchar | `en`, `fa` or `de` |
| `excerpt` | text, nullable | |
| `content` | text | article HTML |
| `featured_image` | varchar, nullable | path or URL |
| `category_id` | integer, nullable | FK → `categories.id`, **set null** on delete |
| `meta_title` | varchar, nullable | SEO override |
| `meta_description` | text, nullable | SEO override |
| `canonical_url` | varchar, nullable | overrides the generated canonical |
| `seo_data` | text, nullable | JSON: `og_*`, `twitter_*`, `robots`, `original_canonical`, `schema`, `date_provenance` |
| `presentation` | text, nullable | JSON: `source_file`, `source_hash`, `toc_html`, `hero_title_en/fa`, `card_*`, `category_label_*`, `thumbnail`, `image_alt` and similar import metadata |
| `sort_order` | integer | default 0 |
| `status` | varchar | `draft` or `published` |
| `published_at` | datetime, nullable | future values stay invisible |
| `created_at`, `updated_at` | datetime, nullable | |

Unique: `(language, slug)`, `(translation_key, language)`.
Indexes: `(language, status, published_at)`, `(status, published_at)`.
Foreign keys: `category_id` → `categories(id)` **set null**.
Deleting an article cascades to `article_redirects` and `article_tag`.

`Article::scopePublished()` restricts to `language IN (en, fa)`, `status = published`, non-null `published_at` that is not in the future. Publishing a German row throws `ValidationException` in the model.

## `article_redirects`

| Column | Type | Notes |
|--------|------|-------|
| `id` | integer | primary key |
| `old_path` | varchar | **unique** (`article_redirects_old_path_unique`), for example `/articles/enable-ssh-linux-complete-guide.html` |
| `article_id` | integer | FK → `articles.id`, **cascade** on delete |
| `created_at`, `updated_at` | datetime, nullable | |

25 rows, one per imported article. Renaming a slug adds a new row rather than replacing the old one.

## `tags`

| Column | Type | Notes |
|--------|------|-------|
| `id` | integer | primary key |
| `name` | varchar | **unique**, max 80 |
| `slug` | varchar | **unique**, max 180 |
| `created_at`, `updated_at` | datetime, nullable | |

Current vocabulary (8): Linux, Microsoft, MikroTik, VMware, Windows Server, Networking, Security, DevOps.

## `article_tag`

| Column | Type | Notes |
|--------|------|-------|
| `article_id` | integer | FK → `articles.id`, **cascade** on delete |
| `tag_id` | integer | FK → `tags.id`, **cascade** on delete, indexed |
| `created_at`, `updated_at` | datetime, nullable | |

Composite primary key `(article_id, tag_id)` (unique). 39 links across 25 articles.

## `requests`

| Column | Type | Notes |
|--------|------|-------|
| `id` | integer | primary key |
| `name` | varchar | max 100 |
| `email` | varchar | |
| `phone` | varchar, nullable | max 40 |
| `subject` | varchar, nullable | the form still requires it at validation time |
| `message` | text | |
| `status` | varchar | default `new`; allowed values `new`, `contacted`, `in_discussion`, `quoted`, `approved`, `completed`, `cancelled` (`Request::STATUSES`) |
| `service_id` | integer, nullable | FK → `services.id`, **set null** on delete, indexed |
| `internal_notes` | text, nullable | admin-only, `$hidden` on the model |
| `created_at`, `updated_at` | datetime, nullable | |

Indexes: `(status, created_at)`, `service_id`.
Migration `000009` remapped the earlier `in_progress` / `resolved` / `spam` values onto the current workflow. There is no user foreign key: submissions are anonymous.

## `services`

| Column | Type | Notes |
|--------|------|-------|
| `id` | integer | primary key |
| `translation_key` | varchar | UUID grouping translations |
| `title` | varchar | |
| `slug` | varchar | max 180 |
| `language` | varchar | `en`, `fa` or `de` |
| `short_description` | text, nullable | homepage card |
| `description` | text, nullable | hero subtitle |
| `content` | text, nullable | longer overview |
| `features`, `process`, `faq` | text, nullable | JSON arrays |
| `price` | numeric, nullable | `decimal(12,2)` in the migration |
| `price_currency` | varchar | default `AED` |
| `price_label` | varchar, nullable | badge text |
| `price_type` | varchar | `fixed`, `starting_from` or `custom_quote` |
| `featured_image` | varchar, nullable | executable suffixes rejected by the model |
| `seo_title`, `seo_description`, `og_title`, `og_description` | varchar / text, nullable | |
| `presentation` | text, nullable | JSON: `icon`, `*_fa` strings, `deliverables`, `exclusions`, `sla`, `addons`, `form_subject`, `source_file` |
| `sort_order` | integer | homepage order |
| `status` | varchar | `draft` or `published` |
| `published_at` | datetime, nullable | required when published |
| `created_at`, `updated_at` | datetime, nullable | |

Unique: `(language, slug)`, `(translation_key, language)`.
Indexes: `(language, status, published_at)`, `(status, sort_order)`.
Foreign keys: none outbound. Referenced by `requests.service_id` with **set null**.

## Relationship summary

```
categories 1 ──< articles              (category_id, set null)
articles   1 ──< article_redirects     (article_id, cascade)
articles   * >──< tags                 (article_tag, cascade both sides)
services   1 ──< requests              (service_id, set null)
users      1 ──< sessions              (user_id, set null)
```

## Delete behaviour in plain words

- Delete a **category** → its articles stay, uncategorised.
- Delete an **article** → its redirects and tag links disappear with it.
- Delete a **tag** → the pivot rows disappear; articles stay.
- Delete a **service** → linked requests stay, with `service_id` cleared.
- Delete a **user** → their session rows stay, detached.

## Production database

Not created, not migrated, not validated on DirectAdmin from this environment: **BLOCKED / NOT TESTED**.
