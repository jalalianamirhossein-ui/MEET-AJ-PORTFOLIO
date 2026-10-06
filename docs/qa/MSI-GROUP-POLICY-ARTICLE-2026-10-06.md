# MSI Group Policy article integration

Date: 2026-10-06 (Asia/Tehran).

## Architecture

- Source: `resources/legacy/articles/deploy-msi-active-directory-group-policy.html`.
- Import: migration `2026_10_06_000025_add_group_policy_msi_article.php` uses the existing `LegacyArticleImporter`, without overwriting CMS edits on repeat runs.
- One English database identity contains both full languages in `data-en`/`data-fa` and `article-localizations`. `ArticleLocalization` renders request-language content and metadata, with the site's existing preference cookie.
- Shared FA/EN URL and canonical: `https://meetaj.ir/articles/deploy-msi-active-directory-group-policy`. No new language route or hreflang scheme.
- Existing controllers automatically expose the published record in home/library/search and the dynamic sitemap. The editorial configuration places it after the two related MikroTik guides.
- Existing `ArticleSeo` generates localized Article, FAQPage and breadcrumb schemas. Seven FAQ answers match the visible text. Commands remain identical across locales. There are 24 sections with matching navigation anchors.

## Images

The user's chosen files are retained byte-for-byte under standard project names:

- `Active Directory MSI Deployment Flow.png` becomes `resources/assets/img/articles/banners/deploy-msi-active-directory-group-policy.png` (1254 × 1254).
- `Windows Group Policy MSI Deployment Guide.png` becomes `resources/assets/img/articles/content/deploy-msi-active-directory-group-policy/windows-group-policy-msi-deployment-guide.png` (2172 × 724).

The existing publisher copies them into the equivalent public paths. Cards, hero and social metadata share the same banner, as required by the existing image pattern. The inline guide is lazy-loaded, has alt text, intrinsic dimensions, a translated caption and a link to the full image. Its generated GUI panels are explicitly described as schematic; the actual separate GPMC package-selection steps are explained in text. The initial ImageGen banner is superseded by the user's banner.

## Technical review

Microsoft references support computer assignment, UNC sources, foreground processing, computer network identity, permissions/filtering, removal and upgrade relationships:

- [MSI distribution](https://learn.microsoft.com/en-us/troubleshoot/windows-server/group-policy/use-group-policy-to-install-software)
- [GPMC software settings, upgrade and removal](https://learn.microsoft.com/en-us/previous-versions/windows/it-pro/windows-server-2012-r2-and-2012/dn789187(v=ws.11))
- [Scope and Read + Apply permissions](https://learn.microsoft.com/en-us/windows-server/identity/ad-ds/manage/group-policy/group-policy-scope)
- [LocalSystem network identity](https://learn.microsoft.com/en-us/windows/win32/ad/the-localsystem-account)
- [gpupdate](https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/gpupdate), [gpresult](https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/gpresult), [shutdown](https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/shutdown)
- [PowerShell RSoP](https://learn.microsoft.com/en-us/powershell/module/grouppolicy/get-gpresultantsetofpolicy?view=windowsserver2025-ps), [TCP diagnostics](https://learn.microsoft.com/en-us/powershell/module/nettcpip/test-netconnection?view=windowsserver2025-ps)
- [nslookup](https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/nslookup), [ipconfig](https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/ipconfig)
- [Nltest](https://learn.microsoft.com/en-us/previous-versions/windows/it-pro/windows-server-2012-r2-and-2012/cc731935(v=ws.11)), [Repadmin](https://learn.microsoft.com/en-us/previous-versions/windows/it-pro/windows-server-2012-r2-and-2012/cc770963(v=ws.11))
- [Logon optimization](https://learn.microsoft.com/en-us/previous-versions/windows/desktop/policy/logon-optimization), [Group Policy diagnostics](https://learn.microsoft.com/en-us/troubleshoot/windows-server/group-policy/applying-group-policy-troubleshooting-guidance), [MSI return codes](https://learn.microsoft.com/en-us/windows/win32/msi/error-codes)

This is documentation/syntax validation, not a deployment executed against a real `corp.local` lab. Package compatibility and lifecycle behavior require the documented pilot on the actual MSI and client builds.

## Validation

- Migration and import completed locally. Subsequent imports retain one shared identity and preserve edits.
- PHP syntax check passed. Laravel `artisan optimize` passed outside the sandbox, which incorrectly reported cache writability when run inside it. No vendor/config workaround was committed.
- Focused PHPUnit: **9 tests, 206 assertions passed** (article, ordering, related guide order and images).
- Both locale responses verify content, title, description, OpenGraph title, keywords, canonical, Article/FAQ schemas, code parity, navigation, listing/search and one sitemap entry. Source/public banner hashes match.
- Browser: both images loaded; language switch updates the Persian heading, title and RTL; desktop and narrow mobile views have no document-level horizontal overflow. No browser error logs. Preview evidence is in ignored `storage/app/msi-article-desktop-fa.png` and `storage/app/msi-article-mobile-fa.png`.
- Library-count tests now derive expected counts from maintained article sources; ordering tests derive offsets from editorial configuration instead of obsolete constants.
- Full-suite and image inventory limitations are recorded in the final verification results below; unrelated existing article/CMS changes are preserved.

## Final verification results

Full PHPUnit after repairs: **103 tests, 8,828 assertions, zero errors or failures, 1 skipped**. The skipped test requires the separate MySQL/MariaDB integration configuration. Frontend tests: **4 passed**. The MikroTik PBR Client article now has complete bilingual prose, metadata and FAQ schema. Fixture assertions respect migrated content and editorial configuration; markup assertions include the presentation layer's section class. Migration 000026 updates only recognized original article bodies and preserves CMS edits.

Image inventory after restoring maintained PWA screenshot sources and publishing assets: **51 source images, 227 references, zero errors**. The article images and PWA screenshots have matching source and published files.

Final Laravel optimization passed for configuration, events, routes, views, Blade icons and Filament. Migrations 000025 and 000026 are marked Ran. `git diff --check` reports no whitespace defects. Local test logs are saved in ignored `storage/app/fix-errors-tests-final.log` and `storage/app/fix-errors-tests-final.xml`.
