# SEO — Meet AJ

> Maintenance review: 2026-10-06. The current library contains 28 local article rows. Sitemap entries depend on publication scopes, not a hardcoded article count. See [current status](PROJECT-STATUS.md) and [the dated audit](../qa/STRUCTURE-DOCUMENTATION-AUDIT-2026-10-06.md).

Verified locally on **2026-10-01** against routes, `ArticleLocalization`, `ArticleSeo`, Blade output and the feature suite. This does not establish search indexing or remote deployment state.

## Article identity and languages

English and Persian share `/articles/{slug}`. The saved `lang` cookie selects the server-rendered content and localized title, description, keywords and Open Graph/Twitter copy. English is the default without a valid Persian preference. Both languages have the same clean canonical. Retired `?lang=…` article links return 301 to the same path without that parameter, preserving other query parameters.

There are no distinct translation URLs, so no language-specific alternate links are emitted. The floating control updates metadata in place while the URL stays unchanged. Crawlers without a preference receive the complete English edition. Persian content remains accessible through the shared language control and preference cookie; this structure does not provide separately addressable Persian search landing pages.

Google documents separate URLs for independently discoverable language versions and `hreflang` annotations between them. The shared URL here follows the requested homepage-style behavior. See [Google's multilingual site guidance](https://developers.google.com/search/docs/specialty/international/managing-multi-regional-sites) and [localized URL annotations](https://developers.google.com/search/docs/specialty/international/localized-versions).

## Structured data

Article pages include Article, BreadcrumbList and FAQPage JSON-LD. Article language/headline and FAQ questions/answers match the selected edition and visible content. The SQL backup article retains eight FAQ entries. Other current articles have their reviewed FAQs. Schema validity does not guarantee a search-engine rich result.

The homepage retains its existing person/site structured data. Imported images use their configured public asset URLs or CMS uploads.

## Sitemap and redirects

`GET /sitemap.xml` is generated from published English article identities. With the current library it contains the homepage plus published clean article URLs (28 published local rows on 2026-10-06). Article `lastmod` uses the stored modification date. Draft/future/German articles and removed service detail pages are excluded. The article library itself is not currently a separate sitemap entry.

Legacy `/articles/{slug}.html` URLs redirect once to the clean path, removing `lang` while preserving unrelated query parameters. `/index.html` redirects to `/`. Slug-history redirects work through `article_redirects`; unknown or unpublished destinations return 404. Removed `/services/...` routes return 404.

`robots.txt` excludes `/admin`, `/livewire` and `/forms` and points to the configured sitemap. Robots rules do not replace authorization.

## Deployment checks

Set `APP_URL` to the real canonical HTTPS origin. Deploy PHP/views/assets together. Check the same article URL with no preference and with cookie `lang=fa`, then the sitemap and redirects. A CDN must forward the preference cookie and respect the article response's `Cache-Control: private`; do not force shared caching of article HTML. Publish the versioned assets and purge stale application/CDN responses after a release. Updating source content also requires the reviewed import procedure.

See [DEPLOYMENT.md](DEPLOYMENT.md), [MULTILINGUAL.md](MULTILINGUAL.md) and [the current audit](../qa/FULL-AUDIT-2026-10-01.md). Production crawler/indexing checks were not performed in this local review.
