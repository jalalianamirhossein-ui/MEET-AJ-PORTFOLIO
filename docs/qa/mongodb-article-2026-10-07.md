# MongoDB production article — 2026-10-07

Created the complete Persian/English article at `/articles/mongodb-installation-configuration-production-deployment`. It includes all 25 requested numbered sections, operational handover, eight FAQs and official references. The existing locale controls switch metadata, content and figure alt text; command blocks remain identical and LTR. Article/FAQ structured data, table-of-contents destinations, the MongoDB tag, search, sitemap and legacy URL redirect are integrated.

## Sources and version decisions

- Current stable branch: MongoDB Community 9.0; official notes list 9.0.2 as released and 9.0.3 as upcoming on the review date. Installation selects the latest signed patch available from the chosen official branch.
- Ubuntu 22.04/24.04 and RHEL-compatible 8/9 examples use the official 9.0 repositories. Debian 12 uses supported 8.0; the current 9.x support matrix requires Debian 13.
- Verified the distribution-specific templates in MongoDB's official `mongodb/docs` source under `content/manual/manual/source/includes/deploy/`, including Ubuntu noble/jammy, Debian and RHEL 8/9 definitions. The Community 9 signing key is `https://pgp.mongodb.com/server-9.asc`, which returned HTTP 200.
- Direct APT/RPM repository metadata requests returned HTTP 403 from this authoring network. Both article languages disclose that live package availability must be checked on the target server. No Linux database deployment was executed on this Windows workstation.

Official sources are linked in each relevant article section and the final references list. Principal references: [release index](https://www.mongodb.com/docs/manual/release-notes/), [9.0 release notes](https://www.mongodb.com/docs/manual/release-notes/9.0/), [Community platform matrix](https://www.mongodb.com/docs/community-platform-support/) and [Linux package installation](https://www.mongodb.com/docs/manual/administration/install-community-linux/).

## Technical decisions

Document package defaults separately: APT uses `mongodb` and `/var/lib/mongodb`; RPM uses `mongod` and `/var/lib/mongo`. Include loopback-only initial bootstrap, private interface/firewall restrictions, TLS+SCRAM behavior with CA/SAN validation, shared-key distribution before replica initiation, the localhost-exception first-user sequence, and the official production recommendation for X.509 member authentication. Separate application, cluster administration, monitoring, backup and restore roles. Passwords are prompted rather than placed in executable URIs. Explain full-dump/oplog constraints, isolated restores, Community audit limitations and version-dependent THP guidance.

## Image assets

Five separate images were generated with the built-in ImageGen tool, using the complete prompts saved in `resources/content/articles/mongodb-installation-configuration-production-deployment/image-prompts.json`. Following the user's image organization update, the banner lives in `resources/assets/img/articles/banners/` and the four diagrams in `resources/assets/img/articles/content/`. Final dimensions were verified: banner 1000×1000; all four diagrams 1920×1080. Images contain English labels and the requested infrastructure/security theme. All five are referenced in the published article with SEO alt text.

## Verification results

- Focused feature suite: **12 tests, 10,958 assertions, zero failures/errors**. Covers the new article and existing bilingual enterprise/article image behavior, localized metadata and structured data, identical commands, navigation destinations, assets, tags/search/sitemap and non-overwriting publication.
- Snippet parser: **32 Bash and 12 JavaScript blocks pass syntax checks**. `mongosh` database-selection statements are normalized only in the scratch JavaScript parser copies. This does not prove MongoDB runtime behavior. YAML parameters were reviewed against the official documentation; no target-server configuration validation is claimed.
- Full suite: **129 tests, 19,749 assertions, 16 failures, one skipped**. All 16 failing case names also occur in the earlier `article-content-review-full.xml` baseline. No newly failing case was identified. Existing failures concern old counts/order, source comparisons, missing assets and the SQL package; these unrelated cases were left unchanged.
- Global image checker finds five pre-existing FortiGate source-asset omissions and no MongoDB image error.
- Browser QA passed in Persian/English desktop and mobile layouts. At the measured mobile CSS viewport (391 pixels), document `scrollWidth` equals `clientWidth` (380 pixels); command blocks remain LTR in both locales. The responsive contents disclosure opens and navigates to the architecture section; its diagram loads at its full 1920-pixel source width. No missing fragment destination was found.
- `git diff --check` passed (only normal Windows line-ending warnings).

Detailed evidence in ignored local storage:

```text
storage/app/mongodb-article-focused.xml
storage/app/mongodb-article-full.xml
storage/app/mongodb-article-full.log
storage/app/mongodb-snippet-checks/report.json
storage/app/mongodb-article-desktop-fa.jpg
storage/app/mongodb-article-desktop-en.jpg
storage/app/mongodb-article-mobile-fa.jpg
storage/app/mongodb-article-mobile-en.jpg
```

## Local application and deployment

Before importing, took a consistent SQLite snapshot at `storage/app/private/article-revisions/before-mongodb-20261007-223623.sqlite`. Applied migration `2026_10_07_000045_add_mongodb_production_article` to the local CMS. It imports only this slug, creates no duplicate article and preserves later CMS edits. Published the source assets with `php artisan site:publish-assets`. The remote production website was not modified.

The editorial source is `scripts/build-mongodb-article.py`; generated standalone reading copies and metadata are under `resources/content/articles/mongodb-installation-configuration-production-deployment/`. To rebuild article text, run the builder with Python. After deploying the application sources and assets, back up the target site's database and run:

```bash
php artisan site:publish-assets
php artisan migrate --force
```

Use `scripts/verify-mongodb-snippets.py --bash <BASH-PATH> --node <NODE-PATH>` for syntax-only verification. Run the focused PHP feature suite with the project's configured PHP/PHPUnit runtime. No commit or remote publication was performed.

## Image organization follow-up

Moved the original five PNGs into the existing `banners`/`content` folders without changing their bytes. Updated the article builder, generated HTML/Markdown, image preparation helper and existing image assertions. Migration `2026_10_07_000046_organize_mongodb_image_paths` updates only image references in the MongoDB CMS record; other content and publication metadata are preserved. Historical asset URLs map to the new files through the existing image compatibility configuration. Source provenance advances only for the exact previous source hash. Republishing assets retires the old generated copies.

Applied the image-path migration locally and republished the assets. Existing MongoDB/article image checks pass: **6 tests, 168 assertions** (`storage/app/mongodb-image-move-focused.xml`). The old source directory and generated old banner copy are absent. No article text or commands were changed.
