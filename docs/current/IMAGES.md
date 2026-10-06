# Image folders and publishing

Reviewed 2026-10-06. Image sources belong in `resources/assets/img/`; generated website copies belong in `public/assets/img/`. Edit the source and run `php artisan site:publish-assets`.

```text
resources/assets/img/
├── banners/
│   ├── site/                  Homepage hero and other site banners
│   └── articles/              Article cover and gallery banners
│       └── optimized/         Existing small cover thumbnails
├── articles/
│   └── {article-slug}/        Images embedded inside that article's body
├── avatars/
│   ├── profile/               Author/profile photographs
│   └── testimonials/          Testimonial portraits
├── brand/                     Brand logo
├── icons/                     Favicon and app icons
└── screenshots/               PWA desktop/mobile previews
```

## Adding images

| Role | Source placement | Website URL |
|---|---|---|
| Homepage banner | `resources/assets/img/banners/site/hero-bg.jpg` | `/assets/img/banners/site/hero-bg.jpg` |
| Article banner | `resources/assets/img/banners/articles/{name}.png` | `/assets/img/banners/articles/{name}.png` |
| Image inside article | `resources/assets/img/articles/{article-slug}/{name}.png` | `/assets/img/articles/{article-slug}/{name}.png` |

Use descriptive filenames. Keep article body images in the article's slug folder, including future screenshots and diagrams. Banner metadata, cards and galleries refer to `banners/articles/`; body `<img>` elements refer to `articles/{slug}/`.

The Linux Auditor and PBR Client previously reused their banner file inside the article body. Each body now has its own identical initial copy in the article folder, so replacing a body screenshot does not replace the banner. The Linux builder uses separate `$image` and `$bodyImage` variables.

## CMS uploads

The public storage disk keeps uploads separate from versioned assets:

- Featured-image uploads: `storage/app/public/images/banners/articles/`, served at `/storage/images/banners/articles/`.
- New-article visual editor attachments: `storage/app/public/images/articles/{slug}/`; unsaved articles use `images/articles/drafts/`. Only JPEG/PNG/WebP up to 5 MB are accepted. The attachment button is available in the visual editor.
- Testimonial uploads: `storage/app/public/images/avatars/testimonials/`.

Existing uploaded files retain their paths. Imported bilingual articles use the HTML editor; add body image URLs using the table above and preserve language attributes and markup. This reorganization does not move third-party package assets, screenshots in historical QA folders, or ignored runtime previews into the live website.

## Migration and compatibility

`config/image-paths.php` records moved image URLs. `2026_10_06_000023_organize_image_paths` updates article image fields/body/metadata, homepage JSON and testimonial avatars, preserving CMS prose, IDs, publication dates and timestamps. Provenance hashes advance only when the stored hash matched the exact pre-move source; earlier content drift is preserved.

For existing installations, deploy sources and code, clear stale configuration, publish images, then migrate:

```bash
php artisan optimize:clear
php artisan site:publish-assets
php artisan migrate --force
php artisan optimize
```

Use the normal database backup procedure before migrating. Do not run `articles:import-legacy --refresh` for a path change. Legacy image URLs redirect with HTTP 301 using an explicit allowlist; unknown paths return 404. Publication removes old generated copies only when their SHA-256 matches the new copy. Article rendering also resolves old imported banner paths to their organized locations.

The PWA manifest now points to maintained icons and screenshots. Four previously public-only pictures were recovered as versioned sources. Editorial preview JPGs live in `docs/enterprise-articles/images/`, outside the web root.

## Checks

```bash
node scripts/check-images.cjs
node scripts/check-documentation.cjs
php artisan test --filter 'ImageOrganizationTest|ArticleImageTest'
```

The image checker verifies source/output hashes, categorized placement and literal image references in maintained inputs. It does not inspect external image hosts or interactive upload behavior. Verification evidence: [image organization audit](../qa/IMAGE-ORGANIZATION-2026-10-06.md).
