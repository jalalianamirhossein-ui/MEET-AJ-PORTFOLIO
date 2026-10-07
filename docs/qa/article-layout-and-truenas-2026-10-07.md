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

- Replace the narrow sticky TOC with a native, initially collapsed disclosure above the reading area on every article. Expanding it reveals the complete list in the normal page flow, with two columns on larger screens and one on mobile. Remove the internal scrollbar and decorative side rule, retain visible keyboard focus, and give links comfortable spacing.
- Let the body use the full reading width and wrap table text at natural word boundaries instead of splitting technical terms in the middle.
- Checked all 36 article routes at a 275 px browser viewport: no page overflow, initially collapsed contents, no TOC scroll container or side border. Visually checked the expanded contents on desktop and mobile, followed a TrueNAS section link, and inspected its wider comparison table.
- Existing rendering tests: 10 tests, 10,481 assertions passed (`ArticleLayoutAuditTest|BilingualEnterpriseArticleTest`). Public CSS is synchronized and the reading stylesheet cache version is 6. The production site has not been modified.
