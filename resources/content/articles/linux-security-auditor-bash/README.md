# Linux Security Auditor article package

The maintained article is `resources/legacy/articles/linux-security-auditor-bash.html`. It contains complete English and Persian editions using the existing `data-en` / `data-fa` markup and `article-localizations` JSON. No YAML front matter, new router, translation rows or language-specific URL scheme is introduced.

Both languages use slug `linux-security-auditor-bash` and canonical path `/articles/linux-security-auditor-bash`. The existing `lang` preference cookie and floating switch choose the edition. `?lang=fa` is not a translation URL; the existing router redirects it to the clean shared path. The production URL, after deployment, is `https://meetaj.ir/articles/linux-security-auditor-bash`.

The article imports with `sort_order = 0`, compatible with the MySQL unsigned column. Home, article library and filtered results prioritize its slug before the existing date/order rules. Never assign a negative sort order. The initial -1 approach was corrected after the production MySQL import rejected it; SQLite tests did not enforce this unsigned constraint.

- Category: existing Linux category.
- Tags: existing Linux, Ubuntu and SSH vocabulary.
- Main image: `/assets/img/articles/banners/linux-security-auditor-bash.png`, published from the supplied asset. Existing inline illustrations reuse that banner file. Add distinct body images under `/assets/img/articles/content/linux-security-auditor-bash/`.
- Download: `/docs/linux-security-auditor/security-audit.sh`, published directly from this directory by `LegacySitePublisher`. There is one maintained script source; the public copy is generated and ignored by Git.
- SEO: localized title, description and keywords; shared canonical; localized Open Graph, Twitter and Article/FAQ schema through the existing services.
- TOC: 25 bilingual section anchors, imported with the established `article-nav-item` convention.
- Code: existing article-code panels and copy controls; Bash examples use static command/option/string tokens, preserving code text in both languages. This is example highlighting, not a general Bash parser.

## Regeneration and deployment

Run from the project root with its PHP 8.4 runtime. On this Windows workspace the interpreter is `.runtime/php84/php.exe`; use the deployment host's normal PHP interpreter there.

```bash
php resources/content/articles/linux-security-auditor-bash/build.php
php artisan articles:import-legacy --dry-run --slug=linux-security-auditor-bash
php artisan site:publish-assets --views
php artisan articles:import-legacy --slug=linux-security-auditor-bash
php artisan view:cache
```

After subsequent article edits, review the existing CMS row, take a database backup and use `--update-existing --slug=linux-security-auditor-bash` for the scoped import. Do not use `--refresh`. Unrelated CMS article bodies should not be replaced as part of this article's deployment. The importer assigns tags; no global tag synchronization is required for this addition.

## Local verification, 2026-10-05

- Generated source and importer metadata validated; English and Persian responses returned 200 with the expected language, direction, SEO and identical executable examples.
- New article imported into the local SQLite database after a consistent `VACUUM INTO` backup. Existing article bodies were not updated. The remote production server was not deployed.
- `php artisan site:publish-assets --views`: successful, 164 public assets published.
- `php artisan view:cache`: successful. This Laravel site has no npm build step.
- Full PHPUnit suite: 86 tests, 6819 assertions, 1 skipped, no failures. The MySQL integration test remains environment-dependent.
- Dedicated article and existing bilingual tests passed. Internal target slugs, TOC anchors, image paths, download attribute, CLI coverage, no Windows/resource paths in rendered HTML, and script source/public SHA-256 equality checked.
- Actual static download request returned HTTP 200, `application/x-sh`.
- Browser verification: image loaded, FA/EN switch updated title, description and direction, Bash option tokens received their expected CSS color, and narrow/default layouts had no horizontal document overflow. Lazy images below the viewport are expected to load when scrolled into view.
- Git Bash syntax validation and isolated self-test passed; see `VALIDATION.md` for the distinction from native Linux host testing. ShellCheck and native Linux integration were not newly verified.
- `site:compare-content`: the new article, homepage and article index PASS. Twenty-five old source/database comparisons fail due to pre-existing stale source hashes and, on some rows, bilingual attribute differences. The scoped import intentionally leaves those CMS rows unchanged.

## Source changes

`security-audit.sh`: help text only, adding real CLI examples, report-parent requirements and the distinction between cost, terminal detail and policy. Security logic, version and distribution dispatch were not modified.

`VALIDATION.md`: clarified missing historical report/catalogue/regression artifacts, documented active scoring/status behavior, supplied actual CLI examples, and separated the current Windows checks from historical Linux integration claims.

Application changes are limited to publishing the actual download, assigning existing tags and styling the authored Bash tokens. Existing count-based tests now expect 26 published imported articles. `LinuxSecurityAuditorArticleTest` adds source-backed integration checks.

Documentation reviewed 2026-10-06. Current structure and verification: [project status](../../../../docs/current/PROJECT-STATUS.md).

Image organization (2026-10-06): [banner, article-body and upload folder guide](../../../../docs/current/IMAGES.md). Run `node scripts/check-images.cjs` after publishing images.
