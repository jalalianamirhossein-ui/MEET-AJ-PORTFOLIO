# Multilingual — Meet AJ

**Authority:** AUTHORITATIVE language document.  
**Verified:** 2026-09-16 against `config/cms.php`, `Article` model, views, `assets/js/i18n.js`, and `PublicSiteTest::test_german_routes_are_not_public`.

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

- Switcher: `#lang-toggle` (homepage, listing, article, services)
- Persistence: `localStorage` and cookie `lang` (`en` \| `fa`) — `assets/js/i18n.js`
- Styles: `assets/css/rtl.css` then `assets/css/visual-upgrade.css`
- `html[dir=rtl]` uses Vazirmatn; LTR uses Poppins (overlay)

## Language switcher

Client-side only. No Laravel locale middleware for FA. No URL prefix.

## hreflang

**Not implemented.** EN and FA share the same canonical URLs, so alternate `hreflang` tags are not emitted. Do not document them as present.

## German routes

PHPUnit asserts `/de` is 404. There is no `routes/web.php` entry for `/de`. Older plans that described a public `/de` architecture are **SUPERSEDED**.
