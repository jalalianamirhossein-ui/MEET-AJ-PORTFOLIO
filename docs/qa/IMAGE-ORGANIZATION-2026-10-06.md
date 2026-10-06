# Image organization audit — 2026-10-06

Scope: image placement, maintained references, published files, CMS upload destinations, local database path migration and public rendering. [Folder guide](../current/IMAGES.md).

## Changes

- Moved 69 first-party source images into banners, article-body images, avatars, brand and icons. All 69 retain their original SHA-256 bytes.
- Created two separate initial article-body copies for Linux Auditor and PBR Client, keeping their banners independently replaceable.
- Recovered four public-only images into source control: two PWA screenshots and two testimonial portraits. There are now 75 maintained website image files, with matching published output.
- Moved four editorial review JPGs into `docs/enterprise-articles/images/`. Historical QA screenshots and third-party assets retain their established ownership.
- Updated maintained HTML, Blade, CSS, manifest, application defaults, article builders, generator inputs and image tests. Fixed malformed `https://meetaj.irassets/img/...` homepage metadata URLs.
- Added allowlisted legacy URL redirects, old-import path resolution, repeatable data migration and separate CMS upload directories. Visual-editor image attachments use the article-body upload folder.

## Local data

A consistent SQLite backup and row snapshot were saved under ignored `storage/app/private/image-reorganization/` before running the migration. Verification confirms unchanged IDs, publication fields and timestamps in articles, homepage contents and testimonials. No article-body reimport was performed. Only known image URLs and eligible source-hash provenance were changed. Unrelated source/database drift remains; comparison still reports 27 failures, as before this image task.

There are now 24 migration files and 25 local ledger records. The new image migration reports Ran. A repeated application of finalized mappings is safe; tests verify idempotence and preserve custom source hashes/editorial fields.

## Checks

| Check | Evidence |
|---|---|
| Source/published image integrity and maintained references | PASS: 75 source images, 227 references, zero errors |
| Original image bytes | PASS: SHA-256 matched Git originals for all 69 moved source images |
| Rendered public pages | PASS: homepage, library and all 28 articles; 30 pages and 269 image references |
| Local database paths | PASS: no remaining legacy image paths in inspected article, homepage and testimonial fields |
| Focused PHP tests | PASS: 12 tests, 439 assertions (`ImageOrganizationTest`, `ArticleImageTest`, `ArticleFaqTranslationTest`, `MikrotikPbrClientArticleTest`) |
| PHP syntax | PASS: changed application, migration and test files |

The focused suite ran through the previously documented sandbox bootstrap wrapper under ignored `storage/app/`; tracked PHPUnit configuration was preserved. Tests cover legacy redirects, imported/optimized covers, custom uploads, inline/banner separation, path migration, preservation and repeatability. Public-page checks use Laravel's HTTP kernel; no new visual browser or interactive upload QA is claimed. The earlier full-suite failures remain documented in [the structure audit](STRUCTURE-DOCUMENTATION-AUDIT-2026-10-06.md).

Publishing initially encountered an already-present installer file that could not be overwritten. The publisher now skips a file copy when source and output hashes already match; subsequent publishing completed successfully. This keeps image publishing repeatable without rewriting unrelated unchanged downloads.
