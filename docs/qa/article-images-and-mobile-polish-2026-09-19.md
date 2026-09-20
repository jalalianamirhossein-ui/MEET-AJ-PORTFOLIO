# Article images and mobile polish

## Live diagnosis

Read-only checks of https://meetaj.ir/ on 2026-09-19 found:

- All 23 article card image URLs referenced full-size PNGs, approximately 1.1–2.1 MB each.
- All 23 returned HTTP 200 and `image/png` in HEAD requests. No persistent missing-file response was reproduced; this does not rule out intermittent delivery failures.
- The published optimized counterpart `/assets/img/portfolio/optimized/other-1.jpg` returned HTTP 200 and 60,571 bytes, versus 1,905,228 bytes for its PNG.
- The 23 source PNGs total 34,783,682 bytes. Their existing JPG counterparts total 1,536,504 bytes: 95.6% less image data across the complete set. Native lazy loading means those totals are not the initial page transfer.

## Changes

Cards, article covers and social image metadata select the existing optimized JPG when published. Full-size originals remain available for the gallery preview. Existing database records work without reimporting or resetting content. If no optimized counterpart exists, the original URL remains in use.

CMS uploads now resolve to `/storage/articles/...`, and replacing a featured image takes precedence over imported thumbnail/gallery metadata. Absolute remote URLs retain their host and scheme.

The Hero name has a slow blue/cyan gradient animation, disabled when reduced motion is preferred. The mobile Expertise section uses two columns with a full-width final category, and falls back to one column below 340 px.

## Deploy this change

Upload the changed application, Blade, CSS and source HTML files, then run from the Laravel project directory:

```sh
php artisan site:publish-assets
php artisan optimize:clear
```

Ensure `public/assets/img/portfolio/optimized/` is included. For CMS-uploaded images, keep `storage/app/public/articles/` in persistent storage and ensure `public/storage` points to `storage/app/public` (`php artisan storage:link` when the link does not exist). Do not reimport or reset the production database for this fix.

The homepage CSS URL is versioned as `site-modules.css?v=1844`. Purge cached HTML at the CDN if it still serves a prior version. Verify article card `src` values use `/assets/img/portfolio/optimized/*.jpg` after deployment.

These changes were implemented locally; no production files or data were modified.

Regression verification: 25 tests passed, 701 assertions (article image resolution, article library, public site, production audit).
