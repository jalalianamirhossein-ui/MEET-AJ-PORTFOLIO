# SEO — Meet AJ

**Authority:** AUTHORITATIVE SEO document.  
**Verified:** 2026-09-16 against Blade heads, `App\Services\ArticleSeo`, `SitemapController`, `RobotsController`, `PublicSiteTest`, `CmsOperationsTest`.

## Canonical URLs

| Page | Canonical |
|------|-----------|
| Home | `https://meetaj.ir/` (hardcoded in `home.blade.php`) |
| Articles index | `{APP_URL}/articles` |
| Article detail | `Article::canonicalUrl()` (override `canonical_url` if set; otherwise `{APP_URL}/articles/{slug}` **without** `.html`) |
| Services | `{APP_URL}/services/{slug}` (**without** `.html`) |

`/index.html` is **not** canonical; it 301s to `/`.  
`/services/{slug}.html` is **not** canonical; it 301s to `/services/{slug}` (query string preserved).

## Open Graph and Twitter

Article detail (`articles/show.blade.php` + `ArticleSeo`): `og:title`, `og:description`, `og:url`, `og:type` (default `article`), `og:image`; Twitter `twitter_card` (default `summary`), title, description, optional image.

Homepage and service pages include original Open Graph tags from the static HTML (absolute `meetaj.ir` URLs). Articles index sets `og:title` “Articles | Meet AJ”.

## JSON-LD

- Home: original Person / WebSite / etc. scripts from `index.html`
- Services: `Service` schema from the published row (name, description, url, provider Person, `Offer` with actual `price`/`priceCurrency`). No ratings or reviews.
- Articles: `Article` schema from import `seo_data.schema` or a generated `Article` object (`headline`, `description`, `image`, `author` Person AmirHossein Jalalian, `mainEntityOfPage`)

## sitemap.xml (`GET /sitemap.xml`)

Includes:

1. `{APP_URL}/`
2. Each **published English** service canonical URL (`/services/{slug}`, not `.html`)
3. Each **published English** article canonical URL

Excludes:

- `/admin`, `/livewire`, `/forms`
- `/articles/{slug}.html` legacy URLs
- `/index.html`
- `/articles` listing (not added by the controller)
- German URLs
- Draft / future / non-`en` articles

Content-Type: `application/xml; charset=UTF-8`. PHPUnit asserts well-formed XML and canonical-only article URLs.

## robots.txt (`GET /robots.txt`)

```
User-agent: *
Allow: /
Disallow: /admin
Disallow: /admin/
Disallow: /livewire
Disallow: /livewire/
Disallow: /forms/
Sitemap: {APP_URL}/sitemap.xml
```

## Article redirects

`article_redirects.old_path` → current article. Importer writes `/articles/{slug}.html` for each of the 23 files. Further slug changes append new unique `old_path` rows. Delete article → redirects **cascade**.

## index.html redirect

`GET /index.html` → **301** `/`.

## hreflang

**Not implemented.** EN and FA share URLs (client-side language). No `link rel="alternate" hreflang`. Do not add invented DE alternates.

## Production crawlers

Indexing on meetaj.ir after CMS cutover: **NOT TESTED**.
