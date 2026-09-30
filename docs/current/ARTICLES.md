# Articles — Meet AJ

**Authority:** AUTHORITATIVE article subsystem document.
**Verified:** 2026-10-01: source validation, isolated feature tests and local operational import after a consistent SQLite backup. Remote production was not changed.
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md).

## Counts (local database, 2026-10-01)

| Item | Count |
|------|-------|
| Articles | **25** |
| Published English articles | 25 |
| Articles with a non-null `published_at` | 25 |
| Persian or German article rows | 0 |
| `article_redirects` rows | 25 |
| Categories | 19 |
| Tags | 24 |
| Article ↔ tag links | 44 |

The imported library uses one shared `language = en` row per article. Both editorial versions live in `data-fa` / `data-en` HTML and `presentation.localizations`; language selection does not create or update database rows.

## Import

Source of truth for content: the 25 original files in `resources/legacy/articles/*.html`. They are never deleted or rewritten by the CMS.

```bash
php artisan articles:import-legacy             # import missing rows
php artisan articles:import-legacy --update-existing --dry-run # preview source updates
php artisan articles:import-legacy --update-existing          # update changed sources
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
| `/articles/{slug}` | Default English edition and English SEO; clean canonical |
| `/articles/{slug}?lang=fa` | Persian edition with its own canonical |
| `/articles/{slug}?lang=en` | Compatible English alias; canonical points to the clean URL |
| `/articles/{slug}.html` | **301** to `/articles/{slug}`, query string preserved, no redirect chain |
| unknown slug | 404 |

## Bilingual enterprise editions

All 25 source files contain complete current FA/EN editions. The base URL serves English content and SEO; `?lang=fa` serves Persian. An old `?lang=en` link or an unsupported language value renders English with the clean canonical. Cookies/local storage do not override article URLs. Both pages return reciprocal `fa`/`en` hreflang and an `x-default` pointing to the English base URL. Sitemap entries cover both versions.

`ArticleLocalization` selects prose, headings, TOC, metadata, Article/FAQ schemas and sharing titles before Blade renders. Code blocks are shared and protected from translation. With JavaScript, the floating language control update the existing text, direction, metadata, schemas, share links and URL using `history.replaceState`; they do not fetch or reload the document. Direct requests to either language URL return a complete localized response without JavaScript. The inline FA/EN row above the date has been removed; alternate links remain in the document head for SEO. The switch payload contains metadata only, without duplicate article bodies. Related links retain the selected language. Historical editions are removed from public pages, including previously imported content; `docs/enterprise-articles/originals.zip` retains exact original files.

`LegacyArticleImporter` reads the `article-localizations` JSON block from each source into `presentation.localizations`. Existing database rows need `--update-existing` to receive the replacement sources. On 2026-10-01, all 25 local operational rows were updated after a consistent SQLite backup. The remote server was not updated from this environment. Review `docs/enterprise-articles/bilingual-report.md` and the dry-run before deployment, and publish updated assets through the existing deployment workflow.

The 25 canonical slugs:

`creating-a-bootable-usb`, `downgrade-mikrotik-routeros-firmware-safely`, `enable-ssh-linux-complete-guide`, `http-vs-https-ssl-certificate-impact`, `imap-vs-pop3-email-protocol-comparison`, `install-dfs-server-windows-server`, `install-mikrotik-chr-vmware-workstation`, `install-vmware-esxi-vmware-workstation-vmcisr`, `linux-cli-common-commands`, `linux-security-account-access-management`, `mikrotik-block-port-scanners`, `mikrotik-block-website`, `mikrotik-openvpn-setup-v7`, `mikrotik-unequal-dual-wan-load-balancing-ecmp`, `netbox-installation-setup-ubuntu`, `nginx-installation-configuration-ubuntu`, `set-static-ip-ubuntu-server-netplan`, `sql-server-automatic-backup-job`, `ubuntu-date-time-settings`, `vmware-esxi-8-installation-basic-configuration`, `vsphere-standard-switch-vs-distributed-switch`, `windows-cmd-common-network-commands`, `windows-hardware-info-cmd-vs-dxdiag`, `windows-password-reset-secure-access-recovery`.

## Redirects

`ArticleController@legacy` looks up `article_redirects.old_path` for `/articles/{slug}.html` and falls back to a direct slug match. It aborts with 404 when the target is German, unpublished, or has a future `published_at`. Otherwise it issues a single 301 to `Article::path()`.

Changing a slug in Filament writes an additional `article_redirects` row, so older URLs keep working. Deleting an article cascades its redirect rows away.

## Categories

Categories are stored in the database per language and can be extended from Filament. `categories.sort_order` controls the public filter order; lower numbers appear first. `articles.category_id` is nullable with **set null** on delete. Public cards, filters, badges and related teasers use `Category::accentColor()` (stored `accent_color` or the slug palette). `data-topic` remains only for Isotope filter keys.

## Tags

Eight tags: Linux, Microsoft, MikroTik, VMware, Windows Server, Networking, Security, DevOps. The pivot `article_tag` has a composite primary key and cascades from both sides. Tags drive the `?tag=` filter, the tag chips on cards and detail pages, and part of the related-article ranking.

## Search

`Article::scopeSearch()` runs `LIKE` comparisons against `title`, `excerpt`, `content`, the related category `name`, and related tag `name` / `slug`. `scopeWithTag()` adds the tag filter. `ArticleController@index` paginates filtered results 9 per page with `withQueryString()`.

Both the library and the results use `scopeForListing()`, which selects only the card columns, so article HTML is searched in the database but never sent to the browser in listings.

This is SQL `LIKE` matching, not a search engine: there is no Meilisearch, Algolia, or MySQL full-text index.

## Related articles

`Article::relatedArticles(3)` returns at most three other published articles in the same language, ranking category matches, shared tags and title-word overlap, then sort order and ID. It is rendered by `resources/views/articles/partials/related.blade.php`.

## SEO

`App\Services\ArticleSeo` builds the article head: canonical (`canonical_url` override, otherwise `{APP_URL}/articles/{slug}`), Open Graph, Twitter card, and JSON-LD `Article` from `seo_data.schema` or generated from the row. Breadcrumbs render a `BreadcrumbList`. Share links come from `App\Services\ArticleShareLinks`. Full rules: [SEO.md](SEO.md).

## Content integrity

`php artisan site:compare-content` compares rendered Laravel output against the original HTML for every article: complete body, bilingual attributes, headings, and SEO tokens.

Result on 2026-09-21: **Failures: 0** for all 25 articles. Evidence: [../qa/CONTENT-INTEGRITY.md](../qa/CONTENT-INTEGRITY.md).

## Languages

Articles share an English database identity. The clean URL renders the complete English edition and SEO. `?lang=fa` renders the complete Persian edition and SEO. Both use the same stored bilingual HTML and code; `i18n.js` changes language in place after loading. There are no `/fa/...` article URLs and no German articles. Publishing `language = de` throws `ValidationException`.

## Publication rules

An article is public only when all of these hold: `language` is `en` or `fa`, `status = published`, `published_at` is not null, and `published_at <= now()`. A future date simply keeps the article hidden — there is no scheduler or queue that flips it later.

## Admin management

Filament **Content → Articles** (`ArticleResource`), available to admins and editors through `ArticlePolicy::canManageContent()`:

- Identity: title, slug, language, category, tags (multi-select), excerpt
- Image: optional upload, JPEG/PNG/WebP, max 5 MB
- Body: `content` required, HTML or rich editor
- SEO (collapsed): `meta_title`, `meta_description`, `canonical_url`
- Publishing: `sort_order` (lower numbers display first), `status`, `published_at` in `config('cms.display_timezone')`, with helper text that German must stay draft

The table supports title search, sortable columns, language and status display, category, `published_at` and a toggleable `updated_at`. Detail: [ADMIN.md](ADMIN.md).
