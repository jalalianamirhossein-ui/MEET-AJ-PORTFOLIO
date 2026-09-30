# SEO — Meet AJ

Verified locally on **2026-10-01** against routes, `ArticleLocalization`, `ArticleSeo`, Blade output and the feature suite. This does not establish search indexing or remote deployment state.

## Article identity and languages

English uses `/articles/{slug}`; Persian uses `/articles/{slug}?lang=fa`. Each response has a localized title, description, keywords, Open Graph/Twitter copy and a self-canonical. The old `?lang=en` alias has a clean English canonical. The default article edition is English regardless of a saved Persian UI preference.

Both editions expose reciprocal EN/FA alternate links and an English `x-default`. No German alternate is emitted. The floating language control updates metadata and URL in place. Direct requests also work without JavaScript, so crawler correctness does not depend on clicking the control.

## Structured data

Article pages include Article, BreadcrumbList and FAQPage JSON-LD. Article language/headline and FAQ questions/answers match the selected edition and visible content. The SQL backup article retains eight FAQ entries. Other current articles have their reviewed FAQs. Schema validity does not guarantee a search-engine rich result.

The homepage retains its existing person/site structured data. Imported images use their configured public asset URLs or CMS uploads.

## Sitemap and redirects

`GET /sitemap.xml` is generated from published English article identities. With the current library it contains the homepage plus 50 article edition URLs and 100 EN/FA alternate links. Article `lastmod` uses the stored modification date, including English content updates. Draft/future/German articles and removed service detail pages are excluded. The article library itself is not currently a separate sitemap entry.

Legacy `/articles/{slug}.html` URLs redirect once to the clean path while preserving language. `/index.html` redirects to `/`. Slug-history redirects work through `article_redirects`; unknown or unpublished destinations return 404. Removed `/services/...` routes return 404.

`robots.txt` excludes `/admin`, `/livewire` and `/forms` and points to the configured sitemap. Robots rules do not replace authorization.

## Deployment checks

Set `APP_URL` to the real canonical HTTPS origin. Deploy PHP/views/assets and update existing imported rows together. Check both language URLs in page source, then the sitemap and redirects. A CDN must forward `lang` and vary article cache entries by it. Publish the versioned assets and purge stale application/CDN responses after a release.

See [DEPLOYMENT.md](DEPLOYMENT.md), [MULTILINGUAL.md](MULTILINGUAL.md) and [the current audit](../qa/FULL-AUDIT-2026-10-01.md). Production crawler/indexing checks were not performed in this local review.
