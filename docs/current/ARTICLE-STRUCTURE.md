# Article structure maintenance

The article inventory is `resources/legacy/articles/*.html` (41 sources at the 2026-10-09 audit). Packages under `resources/content/articles/` hold generator inputs, Markdown copies, downloads and diagrams. They are not separate article records. `LegacyArticleImporter::articleFiles()` defines the source inventory; it imports one shared EN/FA record per slug. Existing rows are skipped unless explicitly updating them, and updates preserve draft/scheduled publication state.

`resources/content/article-structure.json` records reviewed section order and canonical section aliases per slug. Authored aliases take precedence over inferred heading labels. Each complete section moves with its nested content. Verification commands inside an installation/configuration section stay with that procedure. MongoDB recovery setup precedes its acceptance checklist, and Redis replication checks precede Sentinel. `article-translations.json` and `article-localizations.json` supply the previously missing TrueNAS/FortiGate bilingual prose and locale metadata using the existing attributes and localization script.

`ArticleStructure` removes visible editorial review-date clauses, protects executable blocks and structured metadata, and sorts sections. `ArticleContentStandardizer` recognizes authored sections before applying existing display conventions. The importer, article builders and repair tools invoke the repair step. The 2026-10-09 structure migration repairs existing records, including drafts, without adding missing sections, creating records or changing publication/update timestamps. Applying that migration to an operator-controlled database remains a separate deployment operation.

`ArticleLocalization`, `ArticlePresentation`, `articles/show.blade.php` and `i18n.js` retain the existing bilingual, RTL/LTR and code-copy behavior. The public TOC is generated from the final rendered section order; old fragment IDs remain as aliases. The source TOC is reordered with the source sections. No separate template, locale URL or schema is introduced.

Generated prose uses bilingual leaf paragraphs/list items so server rendering and client language switching preserve the same structure. The requested locale also selects generated section text and TOC labels before JavaScript runs. The PHP audit checks rendered prose against its locale attributes, including single-quoted translations and link labels. The [legacy prose follow-up](../qa/legacy-article-prose-audit-2026-10-09.md) records the review of all 25 historical articles and the additional procedure-order repairs.

Run these checks from the project root:

```bash
python scripts/audit-article-structure.py
php scripts/audit-article-content.php local --isolated
python scripts/check-article-regeneration.py --php /path/to/php
php vendor/phpunit/phpunit/phpunit
node --test tests/frontend/*.test.cjs
node scripts/check-images.cjs
node scripts/check-documentation.cjs
node scripts/check-repository-security.cjs
```

The Python audit checks every source independently of a database and writes `storage/app/article-structure-validation.json`. It detects order/dependency mistakes, duplicate IDs, broken anchors, missing language attributes, unclassified sections, review dates, empty sections and skipped heading levels. `--fix` applies the reviewed policy to sources and Markdown copies. Optional `--baseline JSON` also compares exact code/image/JSON-LD blocks with a captured source inventory.

The PHP audit without `--isolated` is read-only against the configured database and includes every status. `--isolated` instead migrates/imports into an in-memory SQLite database; it cannot inspect CMS-only rows in an absent database. The regeneration checker runs 15 writers/loaders in an ignored copy under `storage/app/article-regeneration`, checks normalization, and writes `storage/app/structure-regeneration.json`. PHP image builders require GD. The historical enterprise conversion entry points now compile the maintained technical inputs; their archived conversion code is retained only as historical reference.

When adding an article, register its actual IDs and reviewed order in the policy, then run the audits. Use explicit canonical aliases where a heading could be misclassified, such as a hardware test versus hardware prerequisites or configuration backup versus installation. Preserve executable content and stable slugs/anchors. Editorial review labels are removed only from visible prose; publication metadata, software lifecycle dates, advisory dates and operational timestamps remain intact.

Audit evidence and all per-article before/after orders: [2026-10-09 report](../qa/article-structure-audit-2026-10-09.md).

The final numbering audit extends the same PHP/Python normalization paths: order complete sections first, then renumber only existing integer-prefixed H2 headings. Unnumbered headings stay unnumbered. Main numbers must be 1…N across the numbered subset; H3/FAQ questions, numbered lists, versions, addresses and code are excluded. Explicit section/chapter references, including Persian digits and ranges, follow the new numbers. IDs remain independent of numbering. Source TOC labels follow their targets; the Blade TOC still derives from final headings.

TrueNAS historical IDs now come from the semantic heading map in `article-structure.json`, rather than visible numbers. Markdown copies match headings by their text without the numeric prefix. Builders share this policy and generate the same numbered headings as the maintained HTML. The regeneration checker compares headings, executable text and image URLs with the maintained sources, then runs every writer a second time to detect instability.

The numbering migration repairs stored article content across all states, retaining every other field and timestamp. Source reimports also retain original creation/update/publication timestamps and publication state. The source and PHP audits check raw numbering independently of normalization, so a renderer repair cannot hide a stored error. The PHP audit without `--isolated` uses a read-only database transaction; absent SQLite files produce an explicit unavailable report (exit 2). Remote connections require the operator's explicit `--allow-remote-read-only` flag. Scheduled articles are reported separately from currently published articles; the current CMS has no archived state.

Additional regression commands:

```bash
python tests/test_article_structure.py
# Export actual Blade pages using ARTICLE_PREVIEW_EXPORT=1 with the bilingual PHPUnit test.
# Playwright and Chromium must be installed or supplied via PLAYWRIGHT_MODULE / CHROMIUM_EXECUTABLE.
node scripts/check-article-browser.cjs
```

The browser check exercises all 41 reviewed articles in both locales at desktop/mobile sizes (164 cases), including heading/TOC labels, fragment navigation, images, downloads, code direction, viewport containment, canonical URLs and language switching. It checks every contracted technical example is actually visible and exercises the real copy handler with a private clipboard stub. Screenshots are optional via `ARTICLE_BROWSER_SCREENSHOTS=1`. The [final enterprise report](../qa/final-enterprise-article-audit-2026-10-09.md) records the preceding numbering audit results and database coverage limitations.

## Technical content maintenance

`resources/content/article-technical-content.json` owns reviewed bilingual additions and exact corrections to existing examples. Run `python scripts/article_technical_content.py` to compile them into the existing HTML sections. No runtime filler is used. Existing article IDs, metadata and commands remain intact; the Windows command chapter is an intentional reviewed replacement of its previously abbreviated chapter. TrueNAS translated teaching blocks also live in its maintained Persian Markdown generator input. `upgrade-enterprise-articles.py` and `build-bilingual-articles.py` now call this compiler instead of replacing modern articles from the old 25-article archive or translating paragraphs by position. The historical runbook JSON and ZIP are archival material, not current content authorities.

`article-technical-contracts.json` records reviewed complete examples by actual procedure section for all 41 articles. The source audit and `ArticleTechnicalQuality` validate the same contracts in source and rendered HTML. These checks detect lost installation/configuration/diagnostic examples, incomplete new translations and the confirmed rate-limited Drop defect. They are preservation evidence; they do not prove device execution or replace vendor documentation and human review. Do not refresh hashes to make a failure pass: review the actual procedure first. New articles require an explicit reviewed contract and otherwise fail as unreviewed.

`ArticleTechnicalContent` is migration support using the same curated input; it does not manipulate public HTML at runtime. The content-only migration touches existing records across publication states while preserving timestamps and every other field. A Windows CMS chapter is replaced only if its full text matches an approved historical fingerprint. Customized chapters are retained and reported by the read-only quality audit for manual review. Unknown CMS slugs also remain unchanged and are reported as unreviewed. Existing source imports retain the usual publication-state protections. No migration was applied to an actual CMS database during this audit.

```bash
python scripts/article_technical_content.py
python -m unittest discover -s tests -p 'test_article_*.py'
python scripts/audit-article-structure.py
php scripts/audit-article-content.php technical --isolated
# Real CMS inventory, read-only; do not add --isolated to claim CMS coverage:
php scripts/audit-article-content.php cms
```

The regeneration check compares complete executable text and the reviewed EN/FA explanations, then executes every writer again to detect instability. Technical review evidence, per-article improvements and target-platform limitations are recorded in the [technical content report](../qa/technical-content-audit-2026-10-09.md).
