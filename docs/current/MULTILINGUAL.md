# Multilingual — Meet AJ

Verified locally on **2026-10-01**. Current state: [PROJECT-STATUS.md](PROJECT-STATUS.md).

## Article editions

All 25 imported articles have complete FA and EN prose, matching headings, shared executable examples, and language-specific metadata/FAQs. Each article has one English database identity. `presentation.localizations` stores the two SEO editions, and `data-fa` / `data-en` carry paired prose in the stored HTML. Code blocks are protected from translation.

| Request | Server-rendered edition | Canonical |
|---------|-------------------------|-----------|
| `/articles/{slug}` | English, LTR | clean URL |
| `/articles/{slug}?lang=fa` | Persian, RTL | same URL with `?lang=fa` |
| `/articles/{slug}?lang=en` | English alias | clean URL |
| unsupported `lang` | English fallback | clean URL |

The article URL takes precedence over cookies and local storage. Direct FA/EN requests return complete content and SEO without JavaScript. There are no separate `/fa/...` or `/en/...` routes. Legacy `.html` redirects preserve the requested language.

## Switching in the browser

The shared floating language button updates existing prose, headings, TOC, direction, title, description, keywords, canonical, Open Graph, Article/FAQ schema, share links and related-article links. `history.replaceState` changes the URL without navigating or fetching a second article. The switch payload contains metadata only, not two copies of the article body. The first Persian switch may load the RTL stylesheet.

The inline FA/EN row above the article date is removed. Historical article editions are also removed from public pages; exact originals remain in `docs/enterprise-articles/originals.zip`.

Homepage and article-library UI translation still uses the saved preference and `data-fa` / `data-en`. Article card titles remain English. The English sidebar name uses `white-space: nowrap` in `glass-system.css`.

## SEO and CMS

Article heads contain reciprocal `fa`, `en` and English `x-default` links. Each language has a self-canonical. The sitemap includes both article editions. See [SEO.md](SEO.md).

`config/cms.php` allows EN/FA publicly and DE in drafts. German publication is rejected and `/de` returns 404. Do not add German alternates without published translations.

The importer reads each source's `article-localizations` JSON. Existing rows require the explicitly reviewed `articles:import-legacy --update-existing` operation; copying HTML files alone is insufficient. See [DEPLOYMENT.md](DEPLOYMENT.md).

## Validation

`BilingualEnterpriseArticleTest` renders every article in both languages and checks titles, schemas, canonical/alternate URLs, FAQ text, section IDs and exact code-block parity. Browser checks cover the floating toggle and URL changes. Full local results: [2026-10-01 audit](../qa/FULL-AUDIT-2026-10-01.md).
