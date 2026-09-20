# Multilingual — Meet AJ

**Authority:** AUTHORITATIVE language document.  
**Verified:** 2026-09-18 against `config/cms.php`, `Article` model, views, `assets/js/i18n.js`, `assets/css/lang-toggle.css`, `assets/css/site-modules.css`, PHPUnit `ProductionAuditTest`, and a Cursor browser FA pass (RTL nav, typed roles, English article titles, 0 visible `????` nodes).

## Languages

| Code | Public site | CMS | Content |
|------|-------------|-----|---------|
| **EN** | Production. Default HTML is English (`<html lang="en" dir="ltr">` until the switcher runs). | `language = en` articles are what `/articles` and `/articles/{slug}` query. | 23 imported English articles. |
| **FA** | Production. Same URLs. Client applies `data-fa` / `data-en` and `dir="rtl"` / `lang="fa"`. Font: Vazirmatn via overlay + `rtl.css`. | FA article **rows** are not required for the public FA UI; Persian copy lives in attributes inside the English HTML (and homepage/service markup). | No separate `/fa/...` routes. |
| **DE** | **Not public.** `GET /de` and `GET /de/articles` return **404**. No German hreflang. | `config('cms.languages')` includes `de`. Publishing `language=de` throws `ValidationException`. Draft DE rows are allowed. | **No German article HTML source.** Do not invent DE copy. |

`config/cms.php`:

- `languages` → `en`, `fa`, `de`
- `public_languages` → `en`, `fa`

## Database fields

- `articles.language` / `categories.language`: two-letter code
- `translation_key`: UUID shared by translations of the same work
- Unique: `(language, slug)` and `(translation_key, language)` on articles, categories, and services

Public listing does **not** show FA or DE rows as separate URLs.

## RTL / LTR

- Switcher: `#lang-toggle` (homepage, listing, and article pages)
- Persistence: `localStorage` and cookie `lang` (`en` \| `fa`) — `assets/js/i18n.js`
- Styles: `assets/css/rtl.css` then `assets/css/visual-upgrade.css`
- `html[dir=rtl]` uses Vazirmatn; LTR uses Poppins (overlay)

## Language switcher

One shared control (`#lang-switcher` / `#lang-toggle`) injected by `assets/js/i18n.js` on the homepage, article listing, and article detail pages. Palette is Meet AJ white / blue `#2563eb` / cyan — **not** burgundy. `assets/css/lang-toggle.css` is the component stylesheet; `site-modules.css` last-layer rules match it (the previous `!important` burgundy override was removed on 2026-09-18). FA active state is filled blue with white type. EN is LTR; FA sets `html[dir=rtl][lang=fa]` and enables `rtl.css`.

Client-side only. No Laravel locale middleware for FA. No URL prefix.

Article **titles stay English** in FA UI (`data-i18n-lock` on H1 and cards). Persian titles that had been stored in `meta_title` / `seo_data` were repaired to the canonical English hero title by `LegacyArticleImporter::ensureEnglishTitles()` (6 live rows on 2026-09-18).

## hreflang

**Not implemented.** EN and FA share the same canonical URLs, so alternate `hreflang` tags are not emitted. Do not document them as present.

## German routes

PHPUnit asserts `/de` is 404. There is no `routes/web.php` entry for `/de`. Older plans that described a public `/de` architecture are **SUPERSEDED**.
