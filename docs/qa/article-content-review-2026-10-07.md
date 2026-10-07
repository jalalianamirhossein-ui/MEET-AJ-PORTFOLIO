# Article content review — 2026-10-07

Reviewed all 36 local articles in both display locales (72 editions), including headings, paragraphs, FAQ answers, source lists and contents links. This is an editorial/structural review; infrastructure examples were preserved, not executed on their target systems.

## Corrections

- FortiGate's export used bare `h2` blocks. The shared standardizer now recognizes those authored sections before filling gaps. The article has one introduction, conclusion, FAQ and references section, preserves all 12 original questions and the Fortinet citations, and omits the redundant generated sections and the in-body SEO fields.
- Recognize FAQ headings and Persian «پیاده‌سازی» headings, preserving their existing fragment destinations when assigning consistent section IDs.
- Consolidate older parallel SQL runbook sections into the matching authored sections, retaining their prose, code, citations and fragment anchors. Restrict consolidation to known legacy IDs and headings so separate technical sections remain separate.
- Treat an empty legacy section anchor as a navigation target rather than a complete section; fill the missing SQL conclusion on fresh imports.
- Repair previously lost `#references` destinations. Remove obsolete SEO entries from stored contents lists.
- Remove duplicate bibliography entries pointing to the same page in IMAP/POP3 and MikroTik load balancing. Keep citations accompanied by distinct explanations.
- Count authored FAQ headings correctly and make repeated normalization stable, including whitespace before article footers.

## Local application and evidence

Before applying migration `2026_10_07_000043_repair_duplicate_article_sections`, took a consistent SQLite snapshot at `storage/app/private/article-revisions/article-review-20261007-182409.sqlite`. The migration updates content and stored contents metadata, preserving titles, categories, publication state/dates and other presentation fields. It can be repeated without changing its result. Existing article sources can be imported through the normal importer and receive the same rendering repairs; no bulk source import was performed over CMS content.

- `php scripts/audit-article-content.php after`: 72 editions, zero duplicate heading/ID/reference problems, broken fragment destinations, empty sections or unanswered FAQ items. The only repeated long paragraphs are the intentional Cisco command-context labels.
- Focused feature suite: **18 tests, 16,906 assertions, passing**. Includes every imported article in both languages, final page contents destinations, a single set of ending sections, preserved executable blocks, FortiGate's twelve FAQs and matching schema, duplicate reference cleanup, repeatable migrations and preserved editorial metadata.
- Full suite: **127 tests, 18,711 assertions, 17 failures, one skipped**. Sixteen failing cases also appear in the prior full-suite evidence. The additional `PublicSiteTest` failure is a sandbox file-readability error on the manifest response; it was reproduced with the original standardizer from Git HEAD. The previously recorded failures concern old editorial counts/order, source comparison, assets and the SQL package. No new failing existing case was identified from these article corrections.
- Detailed audit and test evidence: `storage/app/article-content-audit-after.json`, `storage/app/article-content-review-focused.xml` and `storage/app/article-content-review-full.xml`.

## Deployment

These fixes are applied to the local database. Deploy the updated application code and run `php artisan migrate --force` after backing up the production database. The remote production site was not modified in this review.
