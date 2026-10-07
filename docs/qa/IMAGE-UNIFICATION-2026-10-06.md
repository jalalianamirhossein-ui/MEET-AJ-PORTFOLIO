# Article image unification — 2026-10-06

Supersedes the previous image folder layout. Current guide: [IMAGES.md](../current/IMAGES.md).

## Final placement

- `resources/assets/img/articles/banners/`: exactly 28 original PNG banners, named after the matching article slug.
- `resources/assets/img/articles/content/`: reserved for future article-body pictures; tracked with `.gitkeep`. Add `{article-slug}/` subfolders when adding distinct pictures.
- `screenshots/`: retained because the PWA manifest references the desktop/mobile previews. These are installation previews, not article-body images.

The old `banners/articles/`, its `optimized/` subfolder and duplicate article-specific banner folders were removed. Cards, galleries and article heroes share the one banner. Existing inline banner illustrations also reference it, without a duplicate file. New uploads use neighboring `images/articles/banners/` and `images/articles/content/{slug}/` paths on the public storage disk.

## Removed files

28 source files were removed after tracing their dependencies: 24 optimized copies, 2 duplicated inline banner copies, the unused `windows-3.png` banner and unused `testimonial_photo_3.jpg`. All 28 retained article banners matched their pre-rename SHA-256; their bytes and resolution are unchanged. There are 47 maintained website image files overall. Testimonial images still referenced by migration seeds were retained.

Known historical image URLs redirect directly to current assets, including previous optimized paths. Deleted unused images have no active target. The old reference-only service worker's unused Windows image entry was removed. Maintained article sources, builders, frontend views and generator inputs use the final paths.

## Data and verification

Migration `2026_10_06_000024_unify_article_images` reused the repeatable path migration after a consistent SQLite backup. IDs, publication dates and timestamps were verified unchanged. Eligible source hashes advanced only when matching the pre-change source; existing unrelated source/database drift remains at 27 comparison failures.

There are now 25 migration files and 26 local migration ledger rows. The new migration reports Ran. The existing deployment sequence (publish new assets before migrating) remains in [the image guide](../current/IMAGES.md).

- Public render checks: 30 pages and 269 rendered image references; no missing images or old database paths.
- Source/output integrity check: all 47 images match their published copies; 222 maintained references checked with zero errors; no uncategorized or extra published images.
- Focused tests: PASS, 13 tests and 444 assertions. They cover the one-banner-per-article inventory, absence of duplicate folders, legacy redirects, custom uploads, shared banner rendering, preserved editorial data and repeatability.
- PHP syntax and local Markdown link checks pass. No interactive browser upload or production server verification was performed.

Historical QA reports retain their original counts; this follow-up describes the final simplified layout.
