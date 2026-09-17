# Articles — Meet AJ

**Authority:** AUTHORITATIVE article subsystem document.
**Verified:** 2026-09-17 against `app/Models/Article.php`, `app/Http/Controllers/ArticleController.php`, `app/Services/Legacy*`, `app/Filament/Resources/ArticleResource.php`, `resources/views/articles/**`, the live database, and `php artisan site:compare-content`.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md).

## Counts (live database, 2026-09-17)

| Item | Count |
|------|-------|
| Articles | **23** |
| Published English articles | 23 |
| Articles with a non-null `published_at` | 23 |
| Persian or German article rows | 0 |
| `article_redirects` rows | 23 |
| Categories | 10 (5 concepts × EN/FA) |
| Tags | 8 |
| Article ↔ tag links | 38 |

Every article row is `language = en`, `status = published`. Persian article copy lives inside the stored HTML as `data-fa` attributes, not as separate rows.

## Import

Source of truth for content: the 23 original files in `articles/*.html` at the repository root. They are never deleted or rewritten by the CMS.

```bash
php artisan articles:import-legacy             # import or update
php artisan articles:import-legacy --dry-run   # report only
php artisan articles:import-legacy --refresh   # delete existing rows, then re-import
```

`App\Services\LegacyArticleImporter` parses each file and writes:

- `title`, `slug`, `excerpt`, `content` (sanitised by `ArticleHtmlSanitizer`)
- `category_id` resolved from the original filter class
- `seo_data` JSON (`og_*`, `twitter_*`, `robots`, `original_canonical`, `schema`, `date_provenance`)
- `presentation` JSON (`source_file`, `source_hash`, `toc_html`, `hero_title_en` / `hero_title_fa`, `card_title_*`, `card_excerpt_*`, `category_label_*`, `thumbnail`, `image_alt`)
- one `article_redirects` row per file

`--refresh` discards editorial changes. Do not run it on a database that has been edited in Filament.

Tag assignment is a separate, repeatable command backed by `App\Services\ArticleTagAssigner`:

```bash
php artisan articles:sync-tags
```

## Slugs and URLs

| URL | Behaviour |
|-----|-----------|
| `/articles` | Library: full card grid, or paginated results when filtered |
| `/articles?q=…` | Search results, 9 per page, query string preserved |
| `/articles?tag=…` | Tag filter, same pagination |
| `/articles/{slug}` | Canonical article detail |
| `/articles/{slug}.html` | **301** to `/articles/{slug}`, query string preserved, no redirect chain |
| unknown slug | 404 |

The 23 canonical slugs:

`creating-a-bootable-usb`, `downgrade-mikrotik-routeros-firmware-safely`, `enable-ssh-linux-complete-guide`, `http-vs-https-ssl-certificate-impact`, `imap-vs-pop3-email-protocol-comparison`, `install-dfs-server-windows-server`, `install-mikrotik-chr-vmware-workstation`, `install-vmware-esxi-vmware-workstation-vmcisr`, `linux-cli-common-commands`, `linux-security-account-access-management`, `mikrotik-block-port-scanners`, `mikrotik-block-website`, `mikrotik-openvpn-setup-v7`, `mikrotik-unequal-dual-wan-load-balancing-ecmp`, `nginx-installation-configuration-ubuntu`, `set-static-ip-ubuntu-server-netplan`, `sql-server-automatic-backup-job`, `ubuntu-date-time-settings`, `vmware-esxi-8-installation-basic-configuration`, `vsphere-standard-switch-vs-distributed-switch`, `windows-cmd-common-network-commands`, `windows-hardware-info-cmd-vs-dxdiag`, `windows-password-reset-secure-access-recovery`.

## Redirects

`ArticleController@legacy` looks up `article_redirects.old_path` for `/articles/{slug}.html` and falls back to a direct slug match. It aborts with 404 when the target is German, unpublished, or has a future `published_at`. Otherwise it issues a single 301 to `Article::path()`.

Changing a slug in Filament writes an additional `article_redirects` row, so older URLs keep working. Deleting an article cascades its redirect rows away.

## Categories

Five concepts, each stored twice (English and Persian) sharing a `translation_key`: Microsoft / مایکروسافت, Linux / لینوکس, MikroTik / میکروتیک, VMware / مجازی‌سازی, Others / سایر. `articles.category_id` is nullable with **set null** on delete.

## Tags

Eight tags: Linux, Microsoft, MikroTik, VMware, Windows Server, Networking, Security, DevOps. The pivot `article_tag` has a composite primary key and cascades from both sides. Tags drive the `?tag=` filter, the tag chips on cards and detail pages, and part of the related-article ranking.

## Search

`Article::scopeSearch()` matches the term against title, excerpt and slug (not the full HTML body), combined with `scopeWithTag()` for the tag filter. `ArticleController@index` paginates filtered results 9 per page with `withQueryString()`. The unfiltered library loads all published articles at once through `scopeForListing()`, which selects only the card columns and never loads `content`.

Do not describe this as full-text search of article bodies.

## Related articles

`Article::relatedArticles(3)` returns at most three other published articles in the same language, preferring shared tags, then the same category, then recency. It is rendered by `resources/views/articles/partials/related.blade.php`.

## SEO

`App\Services\ArticleSeo` builds the article head: canonical (`canonical_url` override, otherwise `{APP_URL}/articles/{slug}`), Open Graph, Twitter card, and JSON-LD `Article` from `seo_data.schema` or generated from the row. Breadcrumbs render a `BreadcrumbList`. Share links come from `App\Services\ArticleShareLinks`. Full rules: [SEO.md](SEO.md).

## Content integrity

`php artisan site:compare-content` compares rendered Laravel output against the original HTML for every article: complete body, bilingual attributes, headings, and SEO tokens.

Result on 2026-09-17: **Failures: 0** for all 23 articles. Evidence: [../qa/CONTENT-INTEGRITY.md](../qa/CONTENT-INTEGRITY.md).

## Languages

Articles are stored in English. The Persian visitor experience comes from `data-fa` attributes inside the stored HTML plus `assets/js/i18n.js` and `rtl.css`. There are no `/fa/...` article URLs and no German articles. Publishing `language = de` throws `ValidationException`.

## Publication rules

An article is public only when all of these hold: `language` is `en` or `fa`, `status = published`, `published_at` is not null, and `published_at <= now()`. A future date simply keeps the article hidden — there is no scheduler or queue that flips it later.

## Admin management

Filament **Content → Articles** (`ArticleResource`), available to admins and editors through `ArticlePolicy::canManageContent()`:

- Identity: title, slug, language, category, tags (multi-select), excerpt
- Image: optional upload, JPEG/PNG/WebP, max 5 MB
- Body: `content` required, HTML or rich editor
- SEO (collapsed): `meta_title`, `meta_description`, `canonical_url`
- Publishing: `status`, `published_at` in `config('cms.display_timezone')`, with helper text that German must stay draft

The table supports title search, sortable columns, language and status display, category, `published_at` and a toggleable `updated_at`. Detail: [ADMIN.md](ADMIN.md).
