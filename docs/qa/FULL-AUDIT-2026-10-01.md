# Local project audit — 2026-10-01

> Documentation maintenance: 2026-10-06. This document retains its original evidence date and scope; recorded tests and counts were not rerun as part of updating its navigation. Use [current project status](../current/PROJECT-STATUS.md) for current counts, failures and limitations.

The final local suite passes: **85 tests, 5,453 assertions, no failures or errors, one skipped MySQL-only test**. This report covers the project review and the follow-up that makes article language selection match the homepage. Production was not accessed or changed.

## Language behavior

The homepage, article library and article pages share the saved `lang` preference. Laravel accepts the plain preference cookie written by the floating control. The same clean article URL renders Persian for cookie `lang=fa`, or English otherwise. Client initialization respects the saved preference before the server fallback.

The control changes existing text, headings, direction and metadata in place. Code blocks stay identical and the URL does not change. Related, canonical, share and copy URLs contain no language parameter. Retired article language-query URLs return 301 to the clean path, retaining unrelated parameters and browser anchors. The sitemap has 26 URLs: the homepage and 25 articles. There are no separate language alternate URLs. All public pages load `i18n.js?v=1407`.

Article HTML uses private, revalidated HTTP caching to prevent shared caches mixing cookie-selected languages. The deployment guide now documents forwarding the cookie and respecting this header. English remains the server default and the independently addressable search landing page. The SEO documentation records the implications of using one URL for both languages.

## Content and import

All 25 articles retain their operational prose and executable examples in the language follow-up. Each source loses its three obsolete language alternate tags; the SQL article's SEO paragraph now describes the shared URL instead of the retired English query parameter. The bilingual builder remains reproducible: repeating it left all 25 source hashes identical. The per-article FA/EN titles, translation/section status, SEO metadata and shared URLs are recorded in [bilingual-report.md](../enterprise-articles/bilingual-report.md).

The preceding review retained the comparison topics, removed historical content and the extra inline language row from public pages, kept the English sidebar name on one line, corrected the DFS initial-sync activation sequence and aligned the Oxidized version-pinning explanation with its command. Exact original articles remain in the ZIP archive.

A read-only comparison confirmed local article bodies and localized metadata matched the maintained sources before the metadata import. A consistent full SQLite backup was saved first. All 25 local article identities and translation keys were retained; `site:compare-content` reports **Failures: 0**. Source updates now preserve publication status and dates, so metadata changes cannot publish drafts or reset scheduled dates. The local dates were retained from the backup. No `.env` or APP_KEY was changed.

## Verification

| Check | Result |
|-------|--------|
| Final isolated PHPUnit suite | 85 tests, 5,453 assertions, zero failures/errors, one MySQL skip |
| Initial focused article/SQL/public regression suite | 15 tests, 4,202 assertions, passed |
| Final article/SQL suite after the SEO paragraph correction | 10 tests, 3,932 assertions, passed |
| FA/EN feature rendering | 25 articles in both languages; headings, localized SEO/FAQs and exact code parity |
| Real local HTTP article requests | 50 responses, correct language/canonical/private caching, zero errors |
| Sitemap | 26 clean URLs, no language-query entries |
| Source validator | 25 files, zero errors |
| Generator idempotence | 25 source hashes unchanged on repeat |
| Changed PHP/JavaScript syntax | Passed; Blade compilation also passed |
| Local source/database comparison | Failures: 0 |

Live browser checks passed for Persian homepage → article, Persian related article navigation, reopening an article, English article → homepage, Persian search results → article, switching both languages with unchanged code, and removal of obsolete `lang` while retaining `utm_source` and the section anchor. The final browser error log was empty. One suite attempt encountered a transient Windows lock on a generated Blade view during concurrent browser activity; the final serial run passed.

Earlier checks in the same review validated 113 PHP files, five JavaScript files and 40 Bash/16 PowerShell examples for syntax. The earlier route/asset smoke also completed 62 route and 57 asset checks without errors. Its old language-query expectations are superseded by the cookie-based checks above.

## Evidence and limits

Follow-up: the generated source-review paragraph was removed from both editions of all 25 articles and from the authoring generator. Official source links were retained. Previously imported bodies also have this paragraph removed at render time. A full local SQLite backup preceded the targeted content synchronization; source comparison still reports zero failures. The article/SQL regression suite passes with 10 tests and 3,907 assertions. The final full-suite result above precedes this content-only follow-up.

Detailed local artifacts are ignored under `storage/app/full-review/`: `language-final-tests.xml`, `language-final-tests.log`, `language-focused.xml`, `clean-language-http.json`, `clean-language-browser.json`, and `local-before-clean-urls-20261001.sqlite`. The original broader-review backup and evidence also remain there. [bilingual-validation.json](../enterprise-articles/bilingual-validation.json) records the current article validation summary.

Remote deployment, real MySQL, SMTP, authenticated admin browser flows, PWA installation/offline behavior, Lighthouse and load capacity were not validated. Infrastructure commands were not executed on RouterOS, ESXi, SQL Server or Windows Server. Admin authorization/workflows use isolated feature-test fixtures. Earlier dated QA reports remain historical evidence.
