# Multilingual — Meet AJ

> Maintenance review: 2026-10-06. The current local snapshot has 27 of 28 rows with paired localization metadata. This review did not certify full translation completeness. See [current status](PROJECT-STATUS.md) and [the dated audit](../qa/STRUCTURE-DOCUMENTATION-AUDIT-2026-10-06.md).

Verified locally on **2026-10-01**. Current state: [PROJECT-STATUS.md](PROJECT-STATUS.md).

## Article editions

The historical 25-article upgrade added FA/EN prose and metadata. On 2026-10-06, 27 of 28 local article rows contain paired localization metadata. Full translation completeness was not established by this structural review. Each article has one English database identity. `presentation.localizations` stores the two SEO editions, and `data-fa` / `data-en` carry paired prose in the stored HTML. Code blocks are protected from translation.

| Request | Server-rendered edition | Canonical |
|---------|-------------------------|-----------|
| `/articles/{slug}`, no preference | English, LTR | clean URL |
| same URL, preference cookie `lang=fa` | Persian, RTL | clean URL |
| same URL, preference cookie `lang=en` | English, LTR | clean URL |
| same URL, unsupported cookie value | English fallback | clean URL |
| retired article `?lang=…` URL | 301 to the path without `lang`; other parameters preserved | clean URL |

The homepage, library and articles use the same preference. JavaScript reads local storage first, then the `lang` cookie, then the server-rendered article language or the browser-language fallback on other pages. Laravel accepts the plain, non-sensitive `lang` cookie written by this control; only `fa` selects Persian article content. Direct requests with that cookie return complete content and SEO without JavaScript. There are no separate `/fa/...` or `/en/...` routes. Legacy `.html` redirects remove obsolete `lang` query parameters in the same redirect.

## Switching in the browser

The shared floating language button updates existing prose, headings, TOC, direction, title, description, keywords, Open Graph, Article/FAQ schema and sharing titles. Canonical, sharing URLs and article links stay clean in both languages. The control saves the preference for subsequent navigation and refreshes without fetching or reloading the article. A restored back/forward page re-applies the saved language. The switch payload contains metadata only, not two copies of the article body. The first Persian switch may load the RTL stylesheet. All public pages load `i18n.js?v=1407`.

The inline FA/EN row above the article date is removed. Historical article editions are also removed from public pages; exact originals remain in `docs/enterprise-articles/originals.zip`.

Homepage and article-library UI translation still uses the saved preference and `data-fa` / `data-en`. Article card titles remain English. The English sidebar name uses `white-space: nowrap` in `glass-system.css`.

Category filters and tag links read their Persian labels from the matching category row (`slug` and `language=fa`). The `other` tag maps to the `others` category. Translations are loaded together with tags, so rendering labels does not query the database for each tag. English technical names are preserved; missing translations use the existing technical name, with Storage / استوریج as the storage fallback. The `tags` table itself has one name per tag and no separate Persian-name column.

## SEO and CMS

Both languages use one clean canonical. No language-specific `hreflang` URLs are emitted because there are no distinct translation URLs. The sitemap lists each article once. Article responses use private, revalidated HTTP caching so shared caches do not mix cookie-selected languages. See [SEO.md](SEO.md).

`config/cms.php` allows EN/FA publicly and DE in drafts. German publication is rejected and `/de` returns 404. Do not add German alternates without published translations.

The importer reads each source's `article-localizations` JSON. Existing rows require the explicitly reviewed `articles:import-legacy --update-existing` operation; copying HTML files alone is insufficient. See [DEPLOYMENT.md](DEPLOYMENT.md).

## Validation

`BilingualEnterpriseArticleTest` renders every article in both languages using the plain preference cookie and checks titles, schemas, shared canonicals, absence of translation URLs, FAQ text, section IDs and exact code-block parity. Browser checks cover homepage-to-article language continuity, the floating toggle, refresh and clean URLs. Full local results: [2026-10-01 audit](../qa/FULL-AUDIT-2026-10-01.md).
