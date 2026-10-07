# Image folders and publishing

Reviewed 2026-10-06 after the image unification follow-up. Edit sources in `resources/assets/img/`, then run `php artisan site:publish-assets`.

```text
resources/assets/img/
├── articles/
│   ├── banners/       One original banner per article: {article-slug}.png
│   └── content/       Future images embedded in article text
├── banners/site/      Homepage banner
├── avatars/profile/   Profile photographs
├── avatars/testimonials/
├── brand/             Logo
├── icons/             App icons and favicon
└── screenshots/       PWA installation previews
```

## Article images

There are exactly 28 article banners. Names match the article URL slug, for example `linux-security-auditor-bash.png` and `mikrotik-pbr-client.png`. Cards, hero images, gallery links and SEO use the same full-quality file. The `optimized` folder has been removed.

For future images inside an article, place files in `articles/content/{article-slug}/`, for example:

```html
<img src="/assets/img/articles/content/mikrotik-pbr-client/settings.png"
     alt="Client settings" loading="lazy">
```

The empty `content/` folder is tracked with `.gitkeep`. Create an article slug subfolder when adding a real screenshot or diagram. The two previously duplicated banner copies inside article folders have been removed; existing inline banner illustrations reference the single banner file until replaced by distinct content images.

## CMS uploads

- Featured-image uploads: `storage/app/public/images/articles/banners/`.
- New-article visual editor images: `storage/app/public/images/articles/content/{slug}/`; unsaved articles use `content/drafts/`. JPEG/PNG/WebP, maximum 5 MB.
- Testimonial uploads: `storage/app/public/images/avatars/testimonials/`.

Uploads are served under `/storage/`. Existing uploads retain their paths. Imported bilingual articles use the HTML editor, where you can insert the image URL shown above without replacing translation attributes.

## Screenshots dependency

`resources/static/manifest.json` references `screenshots/screenshot-wide.jpg` and `screenshots/screenshot-narrow.jpg` as desktop/mobile installation previews. They are live PWA metadata, separate from article content screenshots. Removing them requires removing the manifest's `screenshots` entries too. Both images are retained.

## Migration and compatibility

`config/image-paths.php` maps historical image URLs directly to current files. Migration `2026_10_06_000024_unify_article_images` updates existing installations that already ran the first organization migration. Image fields, body references, JSON metadata, homepage data and testimonial paths are updated without replacing editorial content or publication timestamps. Provenance hashes advance only for matching prior sources; unrelated content drift remains.

Back up the database, deploy code and sources, then run:

```bash
php artisan optimize:clear
php artisan site:publish-assets
php artisan migrate --force
php artisan optimize
```

Do not reimport article bodies for a path-only change. The publisher removes known retired generated files once their new target exists and their old source is absent. Cached old image URLs redirect with 301 to current assets. Unknown or removed unused image paths return 404.

Twenty-eight redundant/unused source files were deleted: 24 optimized copies, 2 duplicate body images, 1 unused Windows banner and 1 unused testimonial image. The 28 retained article banners preserve their original bytes. Other testimonial images required by existing migration seeds are retained. There are 47 maintained website image files in total. Historical QA screenshots and third-party package assets keep their own folders outside this article layout.

## Checks

```bash
node scripts/check-images.cjs
node scripts/check-documentation.cjs
php artisan test --filter 'ImageOrganizationTest|ArticleImageTest'
```

Evidence: [unification audit](../qa/IMAGE-UNIFICATION-2026-10-06.md).
