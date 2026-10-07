# Article layout and TrueNAS repair — 2026-10-07

Reviewed all 36 published local articles, their maintained sources, card metadata, rendered body structure, tables, FAQ controls and table-of-contents targets.

## Repairs

- Reuse authored sections instead of appending duplicate generic introductions, architecture, prerequisites, configuration, security, troubleshooting and references. Remove a persisted automatic section only if its normalized English content still exactly matches the generated template. Preserve author edits and old fragment IDs.
- Apply the shared table class and a horizontal scroll container to every table. Convert clear pasted Markdown tables without changing code blocks, translated prose or inline markup. Constrain the reading columns and long text so tables cannot widen the page.
- Preserve the number of authored FAQ questions in bilingual articles. Build missing FAQ structured data from the questions and answers actually rendered on the page.
- Fix empty TrueNAS English card text, keep the original Persian text, preserve its QNAP primary category during imports and derive heading colors from the category accent. Darken heading colors for readability, including light brand palettes.
- Repair the TrueNAS comparison table in the maintained HTML, remove literal Markdown task markers, add eight relevant Persian FAQs, clarify NAS file sharing versus iSCSI block storage, specify a separate SSD boot device and pin the source links to the documented 25.04 edition.
- Update stylesheet version parameters and copy the CSS sources to the local public assets.

## Validation

- All 36 local article pages were opened in the browser at a narrow viewport: no page-level horizontal overflow, unstyled tables or unresolved TOC destinations. Desktop TrueNAS hero and comparison table were visually inspected. The QNAP filter and FA/EN card switching work.
- Focused suite: **16 tests, 12,191 assertions, all passing**. It checks every source article, table wrapping, unique IDs, code preservation, repeated normalization, preserved CMS edits, localization, FAQ metadata and TrueNAS taxonomy.
- Full suite: **118 tests, 18 failures, 1 skipped**. Compared the 18 failing cases with the original Article, ArticleContentStandardizer, ArticlePresentation, ArticleSeo and LegacyArticleImporter classes from HEAD; the same 18 cases fail there. No newly failing test case was found. Existing failures concern old counts, editorial ordering, asset expectations, palette expectations, content comparison and the SQL package. Logs are in `storage/app/article-layout-*-tests.*`.
- Final local content audit: no raw Markdown tables, unstyled tables, duplicate IDs or unresolved body fragment links across the 36 articles.

The local SQLite database was backed up with SQLite VACUUM INTO before the targeted TrueNAS import. Only TrueNAS was reimported; other authored database content is unchanged. Shared structure cleanup runs at display time. The remote production site was not modified, and infrastructure commands in the articles were not executed.

## Sources checked for the TrueNAS wording

- [TrueNAS 25.04 hardware guide](https://www.truenas.com/docs/scale/25.04/gettingstarted/scalehardwareguide/)
- [Installing TrueNAS 25.04](https://www.truenas.com/docs/scale/25.04/gettingstarted/install/installingscale/)

The installation guide specifies a 20 GB boot device; hardware guidance has different figures across editions, so the article identifies the installation edition instead of claiming an unversioned current minimum.

## Deployment

Deploy the application changes and public CSS, then run the new metadata migration. Back up the production database and review any CMS edits before applying the targeted source update:

```sh
php artisan migrate --force
php artisan articles:import-legacy --update-existing --slug=truenas-zfs-enterprise --dry-run
php artisan articles:import-legacy --update-existing --slug=truenas-zfs-enterprise
```

Shared layout fixes apply to existing article rows without reimporting the entire library.

## Follow-up: contents navigation and spacing

- Keep the TOC pinned beside the text on desktop, with a wider column, natural text wrapping and comfortable link spacing. Its rounded frame uses the article's primary filter color. The panel stays below the fixed language control and fits the available viewport; only long lists scroll inside it, while the title remains visible. On mobile it becomes an initially collapsed native disclosure above the reading area; an accessible navigation script updates its open state when the viewport crosses the desktop breakpoint.
- Preserve natural table word boundaries instead of splitting technical terms in the middle.
- Checked all 36 article routes at desktop width: the TOC uses sticky positioning, its frame matches the article heading accent, its height fits the viewport and there is no page overflow. On TrueNAS, followed both a middle section and the final references link: the panel stays 72 px below the top controls and the last navigation entry remains reachable. Mobile retains the initially collapsed disclosure in the normal page flow.
- Rechecked TrueNAS at a 275 px mobile viewport: contents expand and collapse without horizontal overflow; the desktop sticky positioning and height limit do not apply. Saved a preview of the pinned navigation beside the final references section in `storage/app/pinned-article-toc-preview.jpg`.
- TrueNAS color repair: migration 000042 creates QNAP when missing, sets its purple accent and assigns the article's category, theme and QNAP tag without replacing CMS content, dates, status, other metadata or tags. This closes the gap in migration 000041, which only reassigned the category if QNAP already existed. QNAP also has a purple fallback when no valid accent is saved; valid CMS colors remain authoritative.
- Existing rendering tests and color regression coverage: 14 tests, 10,509 assertions passed (`CategoryFilterColorsTest|ArticleLayoutAuditTest|BilingualEnterpriseArticleTest`). This includes missing and gold categories, repeated migration execution, preserved CMS content and the article card accent. Public CSS/JS are synchronized and the reading stylesheet cache version is 8. The production site has not been modified; deploy the new navigation script and run migration 000042 there with the application changes.

## Shared article/filter palette

- Article cards, related cards and page accents use the primary filter palette. The assigned brand category takes precedence, then an explicit stored brand filter, then an assigned brand tag. Articles without a brand retain their category/default palette. Secondary topic tags do not replace the primary brand.
- The English category color is the shared filter source for translated article rows too. Missing category accents fall back to the brand palette; brand tag filter chips use the same saved category color. Generic legacy categories cannot mask an assigned brand accent. Cards include their primary filter class so the matching filter can select them.
- Derive secondary colors, gradient accents and muted surfaces from that same primary color; old CSS theme colors no longer introduce unrelated hues. No bulk content reimport or database rewrite is required for this palette change.
- Browser checks: all 36 library cards matched their primary filter color; all 36 article pages used filter palette colors with secondary and gradient accents derived from the same primary color and no page overflow.
- Focused regression suite: 23 tests, 10,837 assertions passed (`AdminThemeTest|CategoryFilterColorsTest|ArticleLayoutAuditTest|BilingualEnterpriseArticleTest`), including rendering all 36 article routes, missing brand categories, generic categories, translated rows, CMS color edits and multiple brand tags. Admin color expectations now reflect the public brand filter palette, including the fallback after clearing a stored color. The additional UniFi test still has its previously recorded 26-versus-23 section-count failure, confirmed in the baseline logs.
