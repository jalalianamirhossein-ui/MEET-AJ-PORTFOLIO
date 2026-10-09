# Full article structure audit — 2026-10-09

Inspected **41 authoritative articles / 82 language editions**. All 41 required a rendered ordering correction; 40 source files changed. The remaining source (MikroTik PBR client) was already ordered, but shared rendering previously appended its generated sections at the end.

Local source, import, migration and browser validation passed. The configured local CMS database is absent, so this report does **not** claim inspection of unavailable CMS-only drafts or production rows. A synthetic CMS-only draft verified migration behavior and metadata preservation. No production data, deployment, commit or push was performed.

## Architecture and coverage

- Sources: `resources/legacy/articles/*.html`; all 41 files returned by `LegacyArticleImporter::articleFiles()` were included, including the existing untracked Zabbix source.
- Packages: `resources/content/articles/` contain Markdown, generators, downloads, metadata and technical assets; these are inputs/copies of the same articles.
- Import/seeding: `LegacyArticleImporter`, article-specific migrations/seeders and the SQL/PBR package loaders maintain one shared record per slug. Reimport tests confirm unchanged record count and draft/scheduled publication state.
- Rendering: `ArticleContentStandardizer` recognizes/wraps authored sections and applies existing display conventions; `ArticleStructure` repairs full-section order and editorial labels. Canonical aliases preserve legacy anchors, including sections whose IDs were previously misclassified.
- Languages: existing `data-en` / `data-fa`, `article-localizations`, `ArticleLocalization` and `i18n.js`; Persian RTL and code LTR remain in the same URL/record.
- Navigation/assets: Blade generates the TOC from final section order. `ArticlePresentation`, related article cards, responsive styles, banners, diagrams, canonical/JSON-LD and sitemap architecture remain in use.
- Persistence: current Python/PHP writers, repair tools, historical conversion output, importer and a new migration invoke the shared policy. The migration includes drafts, adds no new draft sections, and preserves stored metadata/timestamps. Historical 25-article conversion scripts remain archive workflows, not current collection builds.

## Corrections and integrity

- **20 editorial date labels removed**, counted once per language variant (10 bilingual editorial statements); mirrored fallback text and Markdown copies are not counted again.
- **597 previously unpaired elements** received EN/FA attributes: 376 in FortiGate and 221 in TrueNAS. Added locale title/description/FAQ metadata for these two articles; eight TrueNAS and twelve FortiGate FAQ answers remain complete. Existing shared official reference titles, product names and commands are retained.
- Zabbix was missing bilingual hero alt/title/caption metadata already supplied by its generator; those six locale fields were synchronized without rebuilding its technical body.
- **38 source TOC sequences reordered**. Every rendered heading/alias link resolves; no duplicate IDs, broken internal anchors or heading-level skips remain in either edition.
- FortiGate’s flat SEO section is removed as a complete container during rendering, fixing an orphan closing-section defect. Explicit authored aliases prevent the boot-test section from being classified as prerequisites and configuration backup from being classified as installation.
- Complete nested sections move intact. Procedure-local verification stays attached. MongoDB backup/restore preparation precedes the checklist that requires restoration evidence; Redis replication verification precedes Sentinel. TrueNAS hardening precedes file service implementation, and its configuration backup stays in recovery.
- Every source retained its exact executable `<pre>` blocks, image tags and JSON-LD blocks (compared as multisets after normalizing filesystem line endings). Technical release/lifecycle dates, example timestamps and publication/SEO dates remain.
- Existing canonical IDs may be assigned to authored headings instead of separate generated filler. Old authored fragment IDs remain as aliases. The before/after tables therefore show both ordering and removal of redundant generated headings; they do not indicate deletion of authored technical content.
- Existing tests were adjusted to compare exact code blocks irrespective of section position, normalize Windows download line endings, compare banner dimensions to authoritative assets, and isolate sitemap fixtures from seeded articles. Images were not resized. Ignored public assets were refreshed from their existing sources.

### Editorial date removals by slug

| Slug | Language labels removed |
| --- | ---: |
| `apache-tomcat-linux-installation-security-hardening` | 2 |
| `cisco-catalyst-layer-2-layer-3-switch-hardening` | 2 |
| `mongodb-installation-configuration-production-deployment` | 4 |
| `oracle-database-26ai-installation-oracle-linux` | 4 |
| `redis-installation-configuration-replication` | 4 |
| `zabbix-server-linux-windows-agents-backup` | 4 |

## Validation results

| Check | Result |
| --- | --- |
| Source inventory/independent dependencies | 41 articles; 0 issues |
| Both rendered locales in isolated SQLite | 82 editions; 0 structural issues |
| Exact source code/image/JSON-LD preservation | 41/41 pass |
| PHPUnit full suite | 174 tests; 34,082 assertions; 0 failures/errors; 1 MySQL-only test skipped |
| Frontend tests | 6 pass; 0 fail |
| Isolated regeneration | 13 checks: 12 writers + SQL package loader; all pass; 0 normalization drift |
| Headless Chrome desktop/mobile | 12 cases pass: FortiGate, TrueNAS and Redis, both locales; TOC references click, stable code/IDs across language switch, LTR code and local article images |
| Image audit | 117 source images; 354 references; 0 errors |
| Repository security/layout | 0 errors |
| Documentation links, PHP/Python syntax, whitespace | Passed; see maintenance commands |

Full-suite database access was in-memory only. A portable PHP 8.5 runtime was needed on this Windows machine. The normal file-read sandbox returned false for readable files, so tests used a local bootstrap wrapper and normal file-read access. GD was enabled for image-related tests/builders. The site has no frontend bundle build; current asset publication and feature/frontend checks cover its existing build process.

## Remaining verification

1. Obtain an operator-approved local copy/read-only connection for the CMS database, then run the read-only PHP audit against every real status. CMS-only drafts could not be enumerated from the absent local database. The migration is delivered and tested, but was not applied to production.
2. Run the skipped MySQL engine-specific test in the normal MySQL test environment if that backend is required. All article/import/draft migration tests passed on SQLite.

## Per-article before and after

The following tables list **all public rendered H2 sections in sequence**, English and Persian independently. H1 titles remain in the existing hero; child headings/content travel with their H2 section. Original chapter numbers and legacy fragment IDs are retained. Before uses the original shared standardizer against the captured pre-change source; after uses the repaired source and current renderer.

“Moved blocks” gives one minimal set of common heading IDs moved to obtain the new relative order. Other positions may shift as a consequence. Authored-section recognition may also fold a generated heading into its existing authored equivalent; the full tables make those changes explicit.

### 10-essential-group-policies-windows-domain

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `introduction` (Introduction / مقدمه), 18 → 1; `architecture` (Modular OU and GPO architecture / معماری Modular برای OU و GPO), 12 → 3.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `prerequisites` — Production scope and prerequisites<br>دامنه کاربرد و پیش‌نیازهای Production | `introduction` — Introduction<br>مقدمه |
| 2 | `password-lockout` — 1. Password &amp; Account Lockout Policy<br>۱. Password و Account Lockout Policy | `prerequisites` — Production scope and prerequisites<br>دامنه کاربرد و پیش‌نیازهای Production |
| 3 | `security` — 2. Microsoft Defender Antivirus Hardening<br>۲. Microsoft Defender Antivirus Hardening | `architecture` — Modular OU and GPO architecture<br>معماری Modular برای OU و GPO |
| 4 | `firewall` — 3. Windows Defender Firewall<br>۳. Windows Defender Firewall | `password-lockout` — 1. Password &amp; Account Lockout Policy<br>۱. Password و Account Lockout Policy |
| 5 | `windows-update` — 4. Windows Update / WSUS Policy<br>۴. Windows Update / WSUS Policy | `security` — 2. Microsoft Defender Antivirus Hardening<br>۲. Microsoft Defender Antivirus Hardening |
| 6 | `usb-control` — 5. USB / Removable Storage Control<br>۵. کنترل USB و Removable Storage | `firewall` — 3. Windows Defender Firewall<br>۳. Windows Defender Firewall |
| 7 | `screen-lock` — 6. Automatic Screen Lock<br>۶. قفل خودکار Workstation | `windows-update` — 4. Windows Update / WSUS Policy<br>۴. Windows Update / WSUS Policy |
| 8 | `local-admin-laps` — 7. Local Administrator Hardening &amp; Windows LAPS<br>۷. Local Administrator Hardening و Windows LAPS | `usb-control` — 5. USB / Removable Storage Control<br>۵. کنترل USB و Removable Storage |
| 9 | `rdp-hardening` — 8. RDP Hardening<br>۸. RDP Hardening | `screen-lock` — 6. Automatic Screen Lock<br>۶. قفل خودکار Workstation |
| 10 | `advanced-audit` — 9. Advanced Audit Policy<br>۹. Advanced Audit Policy | `local-admin-laps` — 7. Local Administrator Hardening &amp; Windows LAPS<br>۷. Local Administrator Hardening و Windows LAPS |
| 11 | `application-hardening` — 10. Office / Browser / PowerShell Hardening<br>۱۰. هاردنینگ Office / Browser / PowerShell | `rdp-hardening` — 8. RDP Hardening<br>۸. RDP Hardening |
| 12 | `architecture` — Modular OU and GPO architecture<br>معماری Modular برای OU و GPO | `advanced-audit` — 9. Advanced Audit Policy<br>۹. Advanced Audit Policy |
| 13 | `configuration` — Safe deployment: Test OU to Production<br>Deploy صحیح از Test OU تا Production | `application-hardening` — 10. Office / Browser / PowerShell Hardening<br>۱۰. هاردنینگ Office / Browser / PowerShell |
| 14 | `troubleshooting` — Troubleshooting and resultant-policy validation<br>Troubleshooting و اعتبارسنجی Policy مؤثر | `configuration` — Safe deployment: Test OU to Production<br>Deploy صحیح از Test OU تا Production |
| 15 | `best-practices` — Enterprise GPO best practices<br>Best Practices عملیاتی GPO | `troubleshooting` — Troubleshooting and resultant-policy validation<br>Troubleshooting و اعتبارسنجی Policy مؤثر |
| 16 | `enterprise-checklist` — Enterprise Deployment Checklist<br>Enterprise Deployment Checklist | `best-practices` — Enterprise GPO best practices<br>Best Practices عملیاتی GPO |
| 17 | `references` — Official Microsoft References<br>منابع و مراجع رسمی مایکروسافت | `enterprise-checklist` — Enterprise Deployment Checklist<br>Enterprise Deployment Checklist |
| 18 | `introduction` — Introduction<br>مقدمه | `conclusion` — Summary: purpose, target and priority<br>جدول Summary: هدف، Target و Priority |
| 19 | `conclusion` — Summary: purpose, target and priority<br>جدول Summary: هدف، Target و Priority | `faq` — Frequently Asked Questions<br>سؤالات متداول |
| 20 | `faq` — Frequently Asked Questions<br>سؤالات متداول | `official-references` — Official Microsoft References<br>منابع و مراجع رسمی مایکروسافت |
| 21 | `official-references` — Official References<br>منابع رسمی و مرجع | — |

### apache-tomcat-linux-installation-security-hardening

Source file changed: yes. Editorial labels removed: 2. Source TOC reordered: yes.

Moved blocks: `architecture` (2. Architecture and server requirements / ۲. معماری و پیش‌نیازهای سرور), 2 → 3.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — 1. Introduction: where Tomcat belongs<br>۱. مقدمه؛ جایگاه Tomcat در معماری | `introduction` — 1. Introduction: where Tomcat belongs<br>۱. مقدمه؛ جایگاه Tomcat در معماری |
| 2 | `architecture` — 2. Architecture and server requirements<br>۲. معماری و پیش‌نیازهای سرور | `prerequisites` — Server prerequisites and directory plan<br>پیش‌نیازهای سرور و طرح مسیرها |
| 3 | `prerequisites` — Server prerequisites and directory plan<br>پیش‌نیازهای سرور و طرح مسیرها | `architecture` — 2. Architecture and server requirements<br>۲. معماری و پیش‌نیازهای سرور |
| 4 | `versions` — 3. Verified stable release and Java compatibility<br>۳. نسخه Stable بررسی‌شده و سازگاری Java | `versions` — 3. Verified stable release and Java compatibility<br>۳. نسخه Stable بررسی‌شده و سازگاری Java |
| 5 | `java` — 4. Install and verify Java 21<br>۴. نصب و بررسی Java 21 | `java` — 4. Install and verify Java 21<br>۴. نصب و بررسی Java 21 |
| 6 | `configuration` — 5. Install Tomcat with protected release directories<br>۵. نصب Tomcat با مسیر نسخه محافظت‌شده | `configuration` — 5. Install Tomcat with protected release directories<br>۵. نصب Tomcat با مسیر نسخه محافظت‌شده |
| 7 | `systemd` — 6. Run Tomcat as a hardened systemd service<br>۶. اجرای Tomcat به‌صورت سرویس امن systemd | `systemd` — 6. Run Tomcat as a hardened systemd service<br>۶. اجرای Tomcat به‌صورت سرویس امن systemd |
| 8 | `security` — 7. Enterprise security hardening<br>۷. امن‌سازی Enterprise | `security` — 7. Enterprise security hardening<br>۷. امن‌سازی Enterprise |
| 9 | `nginx-https` — 8. Nginx reverse proxy and HTTPS<br>۸. Reverse Proxy با Nginx و HTTPS | `nginx-https` — 8. Nginx reverse proxy and HTTPS<br>۸. Reverse Proxy با Nginx و HTTPS |
| 10 | `firewall` — 9. Firewall: expose only the edge<br>۹. Firewall؛ فقط لبه را منتشر کنید | `firewall` — 9. Firewall: expose only the edge<br>۹. Firewall؛ فقط لبه را منتشر کنید |
| 11 | `deployment` — 10. Deploy a reviewed Java WAR<br>۱۰. استقرار WAR بررسی‌شده Java | `deployment` — 10. Deploy a reviewed Java WAR<br>۱۰. استقرار WAR بررسی‌شده Java |
| 12 | `troubleshooting` — 11. Monitoring and troubleshooting<br>۱۱. مانیتورینگ و عیب‌یابی | `troubleshooting` — 11. Monitoring and troubleshooting<br>۱۱. مانیتورینگ و عیب‌یابی |
| 13 | `upgrades` — 12. Safe upgrades and rollback<br>۱۲. ارتقای امن و Rollback | `upgrades` — 12. Safe upgrades and rollback<br>۱۲. ارتقای امن و Rollback |
| 14 | `best-practices` — 13. Production security checklist<br>۱۳. چک‌لیست امنیت Production | `best-practices` — 13. Production security checklist<br>۱۳. چک‌لیست امنیت Production |
| 15 | `conclusion` — Operational acceptance<br>پذیرش عملیاتی | `conclusion` — Operational acceptance<br>پذیرش عملیاتی |
| 16 | `faq` — Frequently asked questions<br>پرسش‌های متداول | `faq` — Frequently asked questions<br>پرسش‌های متداول |
| 17 | `official-references` — Official References, templates and related guides<br>منابع رسمی، Template و راهنماهای مرتبط | `official-references` — Official References, templates and related guides<br>منابع رسمی، Template و راهنماهای مرتبط |

### cisco-catalyst-layer-2-layer-3-switch-hardening

Source file changed: yes. Editorial labels removed: 2. Source TOC reordered: yes.

Moved blocks: `introduction` (Introduction / مقدمه), 27 → 1; `architecture` (Architecture and deployment contract / معماری و الزامات استقرار), 1 → 3; `configuration` (2. Common Hardening Baseline: Identity and AAA / ۲. تنظیمات مشترک: Identity و AAA), 28 → 4; `verification` (21. Verification and Acceptance / ۲۱. اعتبارسنجی و پذیرش), 22 → 24; `troubleshooting` (Troubleshooting / عیب‌یابی), 29 → 25.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `architecture` — Architecture and deployment contract<br>معماری و الزامات استقرار | `introduction` — Introduction<br>مقدمه |
| 2 | `prerequisites` — 1. Pre-Hardening Checks<br>۱. بررسی پیش از هاردنینگ | `prerequisites` — 1. Pre-Hardening Checks<br>۱. بررسی پیش از هاردنینگ |
| 3 | `security` — 2. Common Hardening Baseline: Identity and AAA<br>۲. تنظیمات مشترک: Identity و AAA | `architecture` — Architecture and deployment contract<br>معماری و الزامات استقرار |
| 4 | `ssh` — 3. SSH Hardening<br>۳. امنیت SSH | `configuration` — 2. Common Hardening Baseline: Identity and AAA<br>۲. تنظیمات مشترک: Identity و AAA |
| 5 | `services` — 4. Disable Unnecessary Services<br>۴. غیرفعال‌سازی سرویس غیرضروری | `security` — 3. SSH Hardening<br>۳. امنیت SSH |
| 6 | `l2` — 5. Layer 2 Switch Hardening<br>۵. هاردنینگ سوئیچ لایه ۲ | `services` — 4. Disable Unnecessary Services<br>۴. غیرفعال‌سازی سرویس غیرضروری |
| 7 | `dhcp` — 6. DHCP Snooping<br>۶. DHCP Snooping | `l2` — 5. Layer 2 Switch Hardening<br>۵. هاردنینگ سوئیچ لایه ۲ |
| 8 | `dai` — 7. Dynamic ARP Inspection<br>۷. Dynamic ARP Inspection | `dhcp` — 6. DHCP Snooping<br>۶. DHCP Snooping |
| 9 | `ipsg` — 8. IP Source Guard<br>۸. IP Source Guard | `dai` — 7. Dynamic ARP Inspection<br>۷. Dynamic ARP Inspection |
| 10 | `stp` — 9. STP Hardening<br>۹. هاردنینگ STP | `ipsg` — 8. IP Source Guard<br>۸. IP Source Guard |
| 11 | `port-security` — 10. Port Security<br>۱۰. Port Security | `stp` — 9. STP Hardening<br>۹. هاردنینگ STP |
| 12 | `storm` — 11. Storm Control<br>۱۱. Storm Control | `port-security` — 10. Port Security<br>۱۰. Port Security |
| 13 | `trunk` — 12. Trunk Hardening<br>۱۲. هاردنینگ Trunk | `storm` — 11. Storm Control<br>۱۱. Storm Control |
| 14 | `unused` — 13. Unused Ports<br>۱۳. پورت بلااستفاده | `trunk` — 12. Trunk Hardening<br>۱۲. هاردنینگ Trunk |
| 15 | `l3` — 14. Layer 3 Switch Hardening<br>۱۴. هاردنینگ سوئیچ لایه ۳ | `unused` — 13. Unused Ports<br>۱۳. پورت بلااستفاده |
| 16 | `svi-acl` — 15. Layer 3 ACL Hardening<br>۱۵. هاردنینگ ACL لایه ۳ | `l3` — 14. Layer 3 Switch Hardening<br>۱۴. هاردنینگ سوئیچ لایه ۳ |
| 17 | `routing` — 16. Routing Protocol Security — OSPF<br>۱۶. امنیت پروتکل Routing — OSPF | `svi-acl` — 15. Layer 3 ACL Hardening<br>۱۵. هاردنینگ ACL لایه ۳ |
| 18 | `ntp` — 17. NTP Hardening<br>۱۷. امنیت NTP | `routing` — 16. Routing Protocol Security — OSPF<br>۱۶. امنیت پروتکل Routing — OSPF |
| 19 | `syslog` — 18. Syslog<br>۱۸. Syslog | `ntp` — 17. NTP Hardening<br>۱۷. امنیت NTP |
| 20 | `snmp` — 19. SNMP — AuthPriv First<br>۱۹. SNMP با اولویت AuthPriv | `syslog` — 18. Syslog<br>۱۸. Syslog |
| 21 | `login` — 20. Login Attack Protection<br>۲۰. حفاظت در برابر حمله Login | `snmp` — 19. SNMP — AuthPriv First<br>۱۹. SNMP با اولویت AuthPriv |
| 22 | `verification` — 21. Verification and Acceptance<br>۲۱. اعتبارسنجی و پذیرش | `login` — 20. Login Attack Protection<br>۲۰. حفاظت در برابر حمله Login |
| 23 | `final-configs` — 22. Final Independent Configurations<br>۲۲. پیکربندی‌های نهایی مستقل | `final-configs` — 22. Final Independent Configurations<br>۲۲. پیکربندی‌های نهایی مستقل |
| 24 | `warnings` — 23. Important Warnings<br>۲۳. هشدارهای مهم | `verification` — 21. Verification and Acceptance<br>۲۱. اعتبارسنجی و پذیرش |
| 25 | `best-practices` — 24. Final Hardening Checklist<br>۲۴. چک‌لیست نهایی | `troubleshooting` — Troubleshooting<br>عیب‌یابی |
| 26 | `references` — Official Cisco References<br>منابع رسمی Cisco | `warnings` — 23. Important Warnings<br>۲۳. هشدارهای مهم |
| 27 | `introduction` — Introduction<br>مقدمه | `best-practices` — 24. Final Hardening Checklist<br>۲۴. چک‌لیست نهایی |
| 28 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `conclusion` — Conclusion<br>جمع‌بندی |
| 29 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 30 | `conclusion` — Conclusion<br>جمع‌بندی | `official-references` — Official Cisco References<br>منابع رسمی Cisco |
| 31 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 32 | `official-references` — Official References<br>منابع رسمی و مرجع | — |

### creating-a-bootable-usb

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `prerequisites` (Before writing the USB / قبل از نوشتن روی فلش), 6 → 2; `configuration` (Verify the ISO and write it with Rufus / بررسی ISO و ساخت فلش با Rufus), 11 → 5; `enterprise-recovery` (Rebuild media and prepare restore / بازسازی رسانه و آماده‌سازی Restore), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 12 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — What bootable media provides<br>USB بوتیبل چه کاری انجام می‌دهد؟ | `introduction` — What bootable media provides<br>USB بوتیبل چه کاری انجام می‌دهد؟ |
| 2 | `enterprise-architecture` — ISO, UEFI and media layout<br>ISO، UEFI و نوع رسانه | `prerequisites` — Before writing the USB<br>قبل از نوشتن روی فلش |
| 3 | `enterprise-prerequisites` — Before writing the USB<br>قبل از نوشتن روی فلش | `architecture` — ISO, UEFI and media layout<br>ISO، UEFI و نوع رسانه |
| 4 | `enterprise-security` — Image trust and credential protection<br>اعتماد به Image و حفاظت از کلیدها | `security` — Image trust and credential protection<br>اعتماد به Image و حفاظت از کلیدها |
| 5 | `enterprise-installation` — Verify the ISO and write it with Rufus<br>بررسی ISO و ساخت فلش با Rufus | `configuration` — Verify the ISO and write it with Rufus<br>بررسی ISO و ساخت فلش با Rufus |
| 6 | `prerequisites` — Boot-test the target hardware<br>آزمون بوت روی دستگاه مقصد | `enterprise-monitoring` — Boot-test the target hardware<br>آزمون بوت روی دستگاه مقصد |
| 7 | `enterprise-troubleshooting` — When the USB does not boot<br>وقتی فلش بوت نمی‌شود | `troubleshooting` — When the USB does not boot<br>وقتی فلش بوت نمی‌شود |
| 8 | `enterprise-recovery` — Rebuild media and prepare restore<br>بازسازی رسانه و آماده‌سازی Restore | `enterprise-recovery` — Rebuild media and prepare restore<br>بازسازی رسانه و آماده‌سازی Restore |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `best-practices` — Best Practices<br>Best Practiceها | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 14 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 15 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 16 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 17 | `official-references` — Official Sources<br>منابع رسمی | — |

### deploy-msi-active-directory-group-policy

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (Deployment architecture / معماری استقرار), 3 → 4; `configuration` (Configuration and Validation / Configuration و اعتبارسنجی), 23 → 10; `security` (Limit deployment with Security Filtering / محدود کردن نصب با Security Filtering), 18 → 14; `best-practices` (Enterprise Best Practices / بهترین روش‌های استقرار سازمانی), 19 → 22.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — Introduction: deploy to the computer<br>مقدمه: نصب در سطح کامپیوتر | `introduction` — Introduction: deploy to the computer<br>مقدمه: نصب در سطح کامپیوتر |
| 2 | `lab` — Lab scenario<br>سناریوی Lab | `lab` — Lab scenario<br>سناریوی Lab |
| 3 | `architecture` — Deployment architecture<br>معماری استقرار | `prerequisites` — Prerequisites and compatibility<br>پیش‌نیازها و سازگاری |
| 4 | `prerequisites` — Prerequisites and compatibility<br>پیش‌نیازها و سازگاری | `architecture` — Deployment architecture<br>معماری استقرار |
| 5 | `share` — Create the software distribution share<br>ایجاد Software Distribution Share | `share` — Create the software distribution share<br>ایجاد Software Distribution Share |
| 6 | `permissions` — Share and NTFS permissions<br>مجوزهای Share و NTFS | `permissions` — Share and NTFS permissions<br>مجوزهای Share و NTFS |
| 7 | `unc` — Test the UNC path<br>تست مسیر UNC | `unc` — Test the UNC path<br>تست مسیر UNC |
| 8 | `test-ou` — Start with a Test OU<br>شروع از Test OU | `test-ou` — Start with a Test OU<br>شروع از Test OU |
| 9 | `gpo` — Create and link the GPO<br>ایجاد و Link کردن GPO | `gpo` — Create and link the GPO<br>ایجاد و Link کردن GPO |
| 10 | `installation` — Configure Software Installation<br>پیکربندی Software Installation | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی |
| 11 | `startup-network` — Network availability during startup<br>دسترسی شبکه هنگام Startup | `installation` — Configure Software Installation<br>پیکربندی Software Installation |
| 12 | `apply` — Apply policy and restart<br>اعمال Policy و Restart | `startup-network` — Network availability during startup<br>دسترسی شبکه هنگام Startup |
| 13 | `verification` — Verify policy and installed application<br>تأیید GPO و نصب برنامه | `apply` — Apply policy and restart<br>اعمال Policy و Restart |
| 14 | `troubleshooting` — DNS and network troubleshooting<br>عیب‌یابی DNS و شبکه | `security` — Limit deployment with Security Filtering<br>محدود کردن نصب با Security Filtering |
| 15 | `rsop` — Additional Group Policy diagnostics<br>دستورهای تکمیلی بررسی Group Policy | `verification` — Verify policy and installed application<br>تأیید GPO و نصب برنامه |
| 16 | `events` — Event Viewer troubleshooting<br>عیب‌یابی با Event Viewer | `troubleshooting` — DNS and network troubleshooting<br>عیب‌یابی DNS و شبکه |
| 17 | `root-cause` — Common Problems and Root Cause Analysis<br>مشکلات رایج و تحلیل علت ریشه‌ای | `rsop` — Additional Group Policy diagnostics<br>دستورهای تکمیلی بررسی Group Policy |
| 18 | `security` — Limit deployment with Security Filtering<br>محدود کردن نصب با Security Filtering | `events` — Event Viewer troubleshooting<br>عیب‌یابی با Event Viewer |
| 19 | `best-practices` — Enterprise Best Practices<br>بهترین روش‌های استقرار سازمانی | `root-cause` — Common Problems and Root Cause Analysis<br>مشکلات رایج و تحلیل علت ریشه‌ای |
| 20 | `uninstall` — Uninstall and scope changes<br>حذف برنامه و تغییر Scope | `uninstall` — Uninstall and scope changes<br>حذف برنامه و تغییر Scope |
| 21 | `upgrade` — Upgrade and version management<br>Upgrade و مدیریت نسخه | `upgrade` — Upgrade and version management<br>Upgrade و مدیریت نسخه |
| 22 | `modern-tools` — When to consider modern management tools<br>چه زمانی ابزارهای مدیریتی جدیدتر مناسب‌اند | `best-practices` — Enterprise Best Practices<br>بهترین روش‌های استقرار سازمانی |
| 23 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `modern-tools` — When to consider modern management tools<br>چه زمانی ابزارهای مدیریتی جدیدتر مناسب‌اند |
| 24 | `conclusion` — Conclusion<br>جمع‌بندی | `conclusion` — Conclusion<br>جمع‌بندی |
| 25 | `faq` — Frequently asked questions<br>پرسش‌های متداول | `faq` — Frequently asked questions<br>پرسش‌های متداول |
| 26 | `official-references` — Official References and validation scope<br>مراجع رسمی و دامنه اعتبارسنجی | `official-references` — Official References and validation scope<br>مراجع رسمی و دامنه اعتبارسنجی |

### downgrade-mikrotik-routeros-firmware-safely

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (RouterOS and RouterBOOT are separate / تفاوت RouterOS و RouterBOOT), 10 → 3; `security` (Target-version and backup security / انتخاب نسخه مقصد و حفاظت از Backup), 4 → 4; `enterprise-recovery` (Return to a working release or use Netinstall / بازگشت به نسخه سالم و Netinstall), 5 → 5; `enterprise-monitoring` (Service checks after reboot / آزمون سرویس پس از راه‌اندازی), 7 → 7; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — When a downgrade is warranted<br>چه زمانی Downgrade لازم است؟ | `introduction` — When a downgrade is warranted<br>چه زمانی Downgrade لازم است؟ |
| 2 | `enterprise-architecture` — RouterOS and RouterBOOT are separate<br>تفاوت RouterOS و RouterBOOT | `prerequisites` — Record versions and arrange local access<br>ثبت نسخه و آماده‌سازی دسترسی محلی |
| 3 | `enterprise-prerequisites` — Record versions and arrange local access<br>ثبت نسخه و آماده‌سازی دسترسی محلی | `architecture` — RouterOS and RouterBOOT are separate<br>تفاوت RouterOS و RouterBOOT |
| 4 | `security` — Target-version and backup security<br>انتخاب نسخه مقصد و حفاظت از Backup | `security` — Target-version and backup security<br>انتخاب نسخه مقصد و حفاظت از Backup |
| 5 | `enterprise-recovery` — Return to a working release or use Netinstall<br>بازگشت به نسخه سالم و Netinstall | `enterprise-recovery` — Return to a working release or use Netinstall<br>بازگشت به نسخه سالم و Netinstall |
| 6 | `enterprise-installation` — Back up, upload packages and downgrade<br>Backup، بارگذاری Package و Downgrade | `configuration` — Back up, upload packages and downgrade<br>Backup، بارگذاری Package و Downgrade |
| 7 | `enterprise-monitoring` — Service checks after reboot<br>آزمون سرویس پس از راه‌اندازی | `enterprise-monitoring` — Service checks after reboot<br>آزمون سرویس پس از راه‌اندازی |
| 8 | `enterprise-troubleshooting` — Rejected packages or missing services<br>Package رد می‌شود یا سرویس برنمی‌گردد | `troubleshooting` — Rejected packages or missing services<br>Package رد می‌شود یا سرویس برنمی‌گردد |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 15 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 16 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 17 | `official-references` — Official Sources<br>منابع رسمی | — |

### enable-ssh-linux-complete-guide

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (User keys and server fingerprints / کلید کاربر و Fingerprint سرور), 10 → 3; `configuration` (Install the service and test key login / نصب سرویس و آزمون ورود با کلید), 12 → 5; `enterprise-monitoring` (Login records and access probes / ثبت ورود و آزمون دسترسی), 6 → 6; `enterprise-recovery` (Roll back while a session remains open / برگشت تنظیمات بدون بستن آخرین نشست), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — SSH and the server access path<br>SSH و مسیر دسترسی به سرور | `introduction` — SSH and the server access path<br>SSH و مسیر دسترسی به سرور |
| 2 | `enterprise-architecture` — User keys and server fingerprints<br>کلید کاربر و Fingerprint سرور | `prerequisites` — Account, console and package requirements<br>حساب کاربری، Console و بسته‌های لازم |
| 3 | `enterprise-prerequisites` — Account, console and package requirements<br>حساب کاربری، Console و بسته‌های لازم | `architecture` — User keys and server fingerprints<br>کلید کاربر و Fingerprint سرور |
| 4 | `enterprise-installation` — Install the service and test key login<br>نصب سرویس و آزمون ورود با کلید | `security` — Source restrictions and MFA compatibility<br>محدودیت مبدا و سازگاری با MFA |
| 5 | `enterprise-security` — Source restrictions and MFA compatibility<br>محدودیت مبدا و سازگاری با MFA | `configuration` — Install the service and test key login<br>نصب سرویس و آزمون ورود با کلید |
| 6 | `enterprise-monitoring` — Login records and access probes<br>ثبت ورود و آزمون دسترسی | `enterprise-monitoring` — Login records and access probes<br>ثبت ورود و آزمون دسترسی |
| 7 | `enterprise-troubleshooting` — From connection failure to rejected keys<br>از خطای اتصال تا رد کلید | `troubleshooting` — From connection failure to rejected keys<br>از خطای اتصال تا رد کلید |
| 8 | `enterprise-recovery` — Roll back while a session remains open<br>برگشت تنظیمات بدون بستن آخرین نشست | `enterprise-recovery` — Roll back while a session remains open<br>برگشت تنظیمات بدون بستن آخرین نشست |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### fortigate-sd-wan-load-balancing-failover

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `prerequisites` (Prerequisites / پیش‌نیازها), 19 → 8; `security` (Security Considerations / ملاحظات امنیتی), 18 → 13; `best-practices` (Best Practices / بهترین رویه‌ها), 16 → 19.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — مقدمه<br>مقدمه | `introduction` — Introduction<br>مقدمه |
| 2 | `section-2` — Load Balancing در FortiGate چیست؟<br>Load Balancing در FortiGate چیست؟ | `section-2` — What is load balancing in FortiGate?<br>Load Balancing در FortiGate چیست؟ |
| 3 | `section-3` — تفاوت Load Balancing و Failover<br>تفاوت Load Balancing و Failover | `section-3` — Load balancing versus failover<br>تفاوت Load Balancing و Failover |
| 4 | `section-4` — روش‌های Load Balancing<br>روش‌های Load Balancing | `section-4` — Load-balancing methods<br>روش‌های Load Balancing |
| 5 | `section-5` — مقایسه Algorithmها<br>مقایسه Algorithmها | `section-5` — Algorithm comparison<br>مقایسه Algorithmها |
| 6 | `section-6` — چرا Source-Destination برای ترافیک عمومی مناسب است؟<br>چرا Source-Destination برای ترافیک عمومی مناسب است؟ | `section-6` — Why use source-destination hashing for general traffic?<br>چرا Source-Destination برای ترافیک عمومی مناسب است؟ |
| 7 | `section-7` — چرا SD-WAN؟<br>چرا SD-WAN؟ | `section-7` — Why SD-WAN?<br>چرا SD-WAN؟ |
| 8 | `architecture` — معماری پیشنهادی<br>معماری پیشنهادی | `prerequisites` — Prerequisites<br>پیش‌نیازها |
| 9 | `section-9` — Performance SLA<br>Performance SLA | `architecture` — Proposed architecture<br>معماری پیشنهادی |
| 10 | `configuration` — پیاده‌سازی GUI<br>پیاده‌سازی GUI | `section-9` — Performance SLA<br>سنجش کیفیت و Performance SLA |
| 11 | `section-11` — پیاده‌سازی CLI<br>پیاده‌سازی CLI | `configuration` — GUI implementation<br>پیاده‌سازی GUI |
| 12 | `section-12` — Weighted Load Balancing برای WANهای ۲۰۰ و ۱۰۰ Mbps<br>Weighted Load Balancing برای WANهای ۲۰۰ و ۱۰۰ Mbps | `section-11` — CLI implementation<br>پیاده‌سازی CLI |
| 13 | `section-13` — Traffic Steering: SD-WAN فراتر از تقسیم بار<br>Traffic Steering: SD-WAN فراتر از تقسیم بار | `security` — Security Considerations<br>ملاحظات امنیتی |
| 14 | `section-14` — Failover Scenario و رفتار Sessionها<br>Failover Scenario و رفتار Sessionها | `section-12` — Weighted load balancing for 200 and 100 Mbps WANs<br>Weighted Load Balancing برای WANهای ۲۰۰ و ۱۰۰ Mbps |
| 15 | `troubleshooting` — Troubleshooting<br>Troubleshooting | `section-13` — Traffic steering: SD-WAN beyond load distribution<br>Traffic Steering: SD-WAN فراتر از تقسیم بار |
| 16 | `best-practices` — Best Practices<br>Best Practices | `section-14` — Failover scenarios and session behavior<br>Failover Scenario و رفتار Sessionها |
| 17 | `section-17` — Common Mistakes<br>Common Mistakes | `troubleshooting` — Troubleshooting<br>عیب‌یابی |
| 18 | `security` — Security Considerations<br>Security Considerations | `section-17` — Common Mistakes<br>اشتباهات رایج |
| 19 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `best-practices` — Best Practices<br>بهترین رویه‌ها |
| 20 | `conclusion` — Conclusion: معماری پیشنهادی Production<br>Conclusion: معماری پیشنهادی Production | `conclusion` — Conclusion: proposed production architecture<br>Conclusion: معماری پیشنهادی Production |
| 21 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | `faq` — FAQ<br>پرسش‌های متداول |
| 22 | `official-references` — References<br>References | `official-references` — References<br>منابع رسمی |

### http-vs-https-ssl-certificate-impact

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `http-https-comparison` (HTTP vs HTTPS in Practice / تفاوت HTTP و HTTPS در عمل), 2 → 2; `architecture` (From DNS to the HTTP request / از DNS تا درخواست HTTP), 11 → 4; `configuration` (Verify the certificate and introduce HSTS / بررسی گواهی و اعمال HSTS), 13 → 6; `enterprise-monitoring` (Monitor the certificate actually served / گواهی سرو‌شده را پایش کنید), 6 → 7; `enterprise-recovery` (Renewal and HSTS recovery / تمدید گواهی و برگشت HSTS), 9 → 9; `best-practices` (Best Practices / Best Practiceها), 14 → 10.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — What HTTPS protects<br>HTTPS چه چیزی را محافظت می‌کند؟ | `introduction` — What HTTPS protects<br>HTTPS چه چیزی را محافظت می‌کند؟ |
| 2 | `http-https-comparison` — HTTP vs HTTPS in Practice<br>تفاوت HTTP و HTTPS در عمل | `http-https-comparison` — HTTP vs HTTPS in Practice<br>تفاوت HTTP و HTTPS در عمل |
| 3 | `enterprise-architecture` — From DNS to the HTTP request<br>از DNS تا درخواست HTTP | `prerequisites` — Tools for inspecting TLS<br>ابزارهای بررسی TLS |
| 4 | `enterprise-prerequisites` — Tools for inspecting TLS<br>ابزارهای بررسی TLS | `architecture` — From DNS to the HTTP request<br>از DNS تا درخواست HTTP |
| 5 | `enterprise-installation` — Verify the certificate and introduce HSTS<br>بررسی گواهی و اعمال HSTS | `security` — TLS protection boundaries<br>مرز حفاظت TLS |
| 6 | `enterprise-monitoring` — Monitor the certificate actually served<br>گواهی سرو‌شده را پایش کنید | `configuration` — Verify the certificate and introduce HSTS<br>بررسی گواهی و اعمال HSTS |
| 7 | `enterprise-troubleshooting` — Diagnose certificate and browser failures<br>تشخیص خطای گواهی و مرورگر | `enterprise-monitoring` — Monitor the certificate actually served<br>گواهی سرو‌شده را پایش کنید |
| 8 | `enterprise-security` — TLS protection boundaries<br>مرز حفاظت TLS | `troubleshooting` — Diagnose certificate and browser failures<br>تشخیص خطای گواهی و مرورگر |
| 9 | `enterprise-recovery` — Renewal and HSTS recovery<br>تمدید گواهی و برگشت HSTS | `enterprise-recovery` — Renewal and HSTS recovery<br>تمدید گواهی و برگشت HSTS |
| 10 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 11 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 12 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 13 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 14 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 15 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 16 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 17 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 18 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 19 | `official-references` — Official Sources<br>منابع رسمی | — |

### imap-vs-pop3-email-protocol-comparison

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `mail-protocol-comparison` (IMAP vs POP3 Comparison / جدول مقایسه IMAP و POP3), 2 → 2; `architecture` (Retrieval, sending and authentication / دریافت، ارسال و احراز هویت), 11 → 4; `configuration` (Configure the client and test TLS / تنظیم کلاینت و آزمون TLS), 13 → 6; `enterprise-monitoring` (Synchronization health and quota / سلامت Sync و Quota), 8 → 7; `enterprise-recovery` (Message backups and client migration / Backup پیام و مهاجرت کلاینت), 9 → 9; `best-practices` (Best Practices / Best Practiceها), 14 → 10.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — Server mailbox or downloaded messages?<br>پیام روی سرور می‌ماند یا دانلود می‌شود؟ | `introduction` — Server mailbox or downloaded messages?<br>پیام روی سرور می‌ماند یا دانلود می‌شود؟ |
| 2 | `mail-protocol-comparison` — IMAP vs POP3 Comparison<br>جدول مقایسه IMAP و POP3 | `mail-protocol-comparison` — IMAP vs POP3 Comparison<br>جدول مقایسه IMAP و POP3 |
| 3 | `enterprise-architecture` — Retrieval, sending and authentication<br>دریافت، ارسال و احراز هویت | `prerequisites` — Provider settings and protocol access<br>اطلاعات Provider و مجوز پروتکل |
| 4 | `enterprise-prerequisites` — Provider settings and protocol access<br>اطلاعات Provider و مجوز پروتکل | `architecture` — Retrieval, sending and authentication<br>دریافت، ارسال و احراز هویت |
| 5 | `enterprise-installation` — Configure the client and test TLS<br>تنظیم کلاینت و آزمون TLS | `security` — TLS and supported authentication<br>TLS و روش ورود پشتیبانی‌شده |
| 6 | `enterprise-troubleshooting` — Separate retrieval and sending failures<br>تفکیک مشکل دریافت و ارسال | `configuration` — Configure the client and test TLS<br>تنظیم کلاینت و آزمون TLS |
| 7 | `enterprise-security` — TLS and supported authentication<br>TLS و روش ورود پشتیبانی‌شده | `enterprise-monitoring` — Synchronization health and quota<br>سلامت Sync و Quota |
| 8 | `enterprise-monitoring` — Synchronization health and quota<br>سلامت Sync و Quota | `troubleshooting` — Separate retrieval and sending failures<br>تفکیک مشکل دریافت و ارسال |
| 9 | `enterprise-recovery` — Message backups and client migration<br>Backup پیام و مهاجرت کلاینت | `enterprise-recovery` — Message backups and client migration<br>Backup پیام و مهاجرت کلاینت |
| 10 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 11 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 12 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 13 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 14 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 15 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 16 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 17 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 18 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 19 | `official-references` — Official Sources<br>منابع رسمی | — |

### install-dfs-server-windows-server

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (Referrals and replication paths / Referral و مسیر Replication), 10 → 3; `configuration` (Create the namespace and replication group / ساخت Namespace و گروه Replication), 12 → 5; `enterprise-monitoring` (Backlog and staging pressure / Backlog و فشار Staging), 6 → 6; `enterprise-recovery` (Restore without unintended overwrite propagation / Restore بدون انتشار Overwrite ناخواسته), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — How DFS-N and DFS-R differ<br>DFS-N و DFS-R چه تفاوتی دارند؟ | `introduction` — How DFS-N and DFS-R differ<br>DFS-N و DFS-R چه تفاوتی دارند؟ |
| 2 | `enterprise-architecture` — Referrals and replication paths<br>Referral و مسیر Replication | `prerequisites` — Prepare AD, storage and shares<br>آماده‌سازی AD، دیسک و Share |
| 3 | `enterprise-prerequisites` — Prepare AD, storage and shares<br>آماده‌سازی AD، دیسک و Share | `architecture` — Referrals and replication paths<br>Referral و مسیر Replication |
| 4 | `enterprise-security` — Share, NTFS and firewall permissions<br>مجوزهای Share، NTFS و Firewall | `security` — Share, NTFS and firewall permissions<br>مجوزهای Share، NTFS و Firewall |
| 5 | `enterprise-installation` — Create the namespace and replication group<br>ساخت Namespace و گروه Replication | `configuration` — Create the namespace and replication group<br>ساخت Namespace و گروه Replication |
| 6 | `enterprise-monitoring` — Backlog and staging pressure<br>Backlog و فشار Staging | `enterprise-monitoring` — Backlog and staging pressure<br>Backlog و فشار Staging |
| 7 | `enterprise-troubleshooting` — Stale files with a healthy namespace<br>فایل قدیمی با Namespace سالم | `troubleshooting` — Stale files with a healthy namespace<br>فایل قدیمی با Namespace سالم |
| 8 | `enterprise-recovery` — Restore without unintended overwrite propagation<br>Restore بدون انتشار Overwrite ناخواسته | `enterprise-recovery` — Restore without unintended overwrite propagation<br>Restore بدون انتشار Overwrite ناخواسته |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### install-mikrotik-chr-vmware-workstation

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (Map vNICs to router interfaces / تطبیق vNIC با Interface روتر), 10 → 3; `configuration` (Create the VM and configure the IPv4 lab / ساخت VM و تنظیم IPv4 Lab), 12 → 5; `enterprise-monitoring` (Measure throughput and resource use / اندازه‌گیری سرعت و مصرف منابع), 6 → 6; `enterprise-recovery` (Snapshots and off-VM exports / Snapshot و Export خارج VM), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — CHR for RouterOS testing<br>CHR برای آزمایش RouterOS | `introduction` — CHR for RouterOS testing<br>CHR برای آزمایش RouterOS |
| 2 | `enterprise-architecture` — Map vNICs to router interfaces<br>تطبیق vNIC با Interface روتر | `prerequisites` — Image and VM resources<br>Image و منابع ماشین مجازی |
| 3 | `enterprise-prerequisites` — Image and VM resources<br>Image و منابع ماشین مجازی | `architecture` — Map vNICs to router interfaces<br>تطبیق vNIC با Interface روتر |
| 4 | `enterprise-installation` — Create the VM and configure the IPv4 lab<br>ساخت VM و تنظیم IPv4 Lab | `security` — Keep the lab isolated<br>جلوگیری از اتصال Lab به شبکه شرکت |
| 5 | `enterprise-security` — Keep the lab isolated<br>جلوگیری از اتصال Lab به شبکه شرکت | `configuration` — Create the VM and configure the IPv4 lab<br>ساخت VM و تنظیم IPv4 Lab |
| 6 | `enterprise-monitoring` — Measure throughput and resource use<br>اندازه‌گیری سرعت و مصرف منابع | `enterprise-monitoring` — Measure throughput and resource use<br>اندازه‌گیری سرعت و مصرف منابع |
| 7 | `enterprise-troubleshooting` — Unreachable gateways or low throughput<br>قطع Gateway یا سرعت کم | `troubleshooting` — Unreachable gateways or low throughput<br>قطع Gateway یا سرعت کم |
| 8 | `enterprise-recovery` — Snapshots and off-VM exports<br>Snapshot و Export خارج VM | `enterprise-recovery` — Snapshots and off-VM exports<br>Snapshot و Export خارج VM |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### install-vmware-esxi-vmware-workstation-vmcisr

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (Two virtualization layers / دو لایه Virtualization), 10 → 3; `security` (Host security during workarounds / امنیت میزبان هنگام Workaround), 6 → 4; `enterprise-monitoring` (Host and guest resource pressure / منابع میزبان و Guest), 7 → 6; `enterprise-recovery` (Restore VMX and lab data / بازگرداندن VMX و داده Lab), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — What nested ESXi is useful for<br>کاربرد Nested ESXi | `introduction` — What nested ESXi is useful for<br>کاربرد Nested ESXi |
| 2 | `enterprise-architecture` — Two virtualization layers<br>دو لایه Virtualization | `prerequisites` — CPU, host and compatible builds<br>CPU، میزبان و Build سازگار |
| 3 | `enterprise-prerequisites` — CPU, host and compatible builds<br>CPU، میزبان و Build سازگار | `architecture` — Two virtualization layers<br>دو لایه Virtualization |
| 4 | `enterprise-installation` — Build the ESXi VM and test Host Client<br>ساخت ماشین ESXi و آزمون Host Client | `security` — Host security during workarounds<br>امنیت میزبان هنگام Workaround |
| 5 | `enterprise-troubleshooting` — Investigate VMCISr and VT-x failures<br>بررسی VMCISr و VT-x | `configuration` — Build the ESXi VM and test Host Client<br>ساخت ماشین ESXi و آزمون Host Client |
| 6 | `security` — Host security during workarounds<br>امنیت میزبان هنگام Workaround | `enterprise-monitoring` — Host and guest resource pressure<br>منابع میزبان و Guest |
| 7 | `enterprise-monitoring` — Host and guest resource pressure<br>منابع میزبان و Guest | `troubleshooting` — Investigate VMCISr and VT-x failures<br>بررسی VMCISr و VT-x |
| 8 | `enterprise-recovery` — Restore VMX and lab data<br>بازگرداندن VMX و داده Lab | `enterprise-recovery` — Restore VMX and lab data<br>بازگرداندن VMX و داده Lab |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 15 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 16 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 17 | `official-references` — Official Sources<br>منابع رسمی | — |

### linux-cli-common-commands

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (Order of investigation / ترتیب بررسی در رخداد), 10 → 3; `configuration` (CPU, memory, disks and service state / CPU، RAM، دیسک و وضعیت سرویس), 12 → 5; `enterprise-monitoring` (Compare snapshots with trends / مقایسه Snapshot با Trend), 6 → 6; `enterprise-recovery` (Before and after a correction / قبل و بعد از تغییر), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — Choose commands from the failure symptom<br>از نشانه خرابی به فرمان تشخیص | `introduction` — Choose commands from the failure symptom<br>از نشانه خرابی به فرمان تشخیص |
| 2 | `enterprise-prerequisites` — Command environment<br>محیط اجرای فرمان‌ها | `prerequisites` — Command environment<br>محیط اجرای فرمان‌ها |
| 3 | `enterprise-architecture` — Order of investigation<br>ترتیب بررسی در رخداد | `architecture` — Order of investigation<br>ترتیب بررسی در رخداد |
| 4 | `enterprise-installation` — CPU, memory, disks and service state<br>CPU، RAM، دیسک و وضعیت سرویس | `security` — Protect evidence and limit sudo<br>حفاظت از شواهد و محدودکردن sudo |
| 5 | `enterprise-troubleshooting` — df/du disagreement and network failures<br>اختلاف df و du و خطای شبکه | `configuration` — CPU, memory, disks and service state<br>CPU، RAM، دیسک و وضعیت سرویس |
| 6 | `enterprise-monitoring` — Compare snapshots with trends<br>مقایسه Snapshot با Trend | `enterprise-monitoring` — Compare snapshots with trends<br>مقایسه Snapshot با Trend |
| 7 | `enterprise-security` — Protect evidence and limit sudo<br>حفاظت از شواهد و محدودکردن sudo | `troubleshooting` — df/du disagreement and network failures<br>اختلاف df و du و خطای شبکه |
| 8 | `enterprise-recovery` — Before and after a correction<br>قبل و بعد از تغییر | `enterprise-recovery` — Before and after a correction<br>قبل و بعد از تغییر |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### linux-security-account-access-management

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (From groups to exact sudo rules / از گروه تا Rule دقیق sudo), 10 → 3; `configuration` (Create the account and validate the rule / ایجاد حساب و بررسی Rule), 12 → 5; `enterprise-monitoring` (Audit account and privilege changes / ممیزی تغییر حساب و مجوز), 7 → 6; `enterprise-recovery` (Undo a rule and retain user data / برگشت Rule و نگهداری داده کاربر), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — Authentication and command privileges differ<br>احراز هویت با مجوز اجرای فرمان فرق دارد | `introduction` — Authentication and command privileges differ<br>احراز هویت با مجوز اجرای فرمان فرق دارد |
| 2 | `enterprise-architecture` — From groups to exact sudo rules<br>از گروه تا Rule دقیق sudo | `prerequisites` — Local account and administrator access<br>حساب محلی و دسترسی مدیر |
| 3 | `enterprise-prerequisites` — Local account and administrator access<br>حساب محلی و دسترسی مدیر | `architecture` — From groups to exact sudo rules<br>از گروه تا Rule دقیق sudo |
| 4 | `enterprise-installation` — Create the account and validate the rule<br>ایجاد حساب و بررسی Rule | `security` — Restricted commands and escape paths<br>فرمان محدود و مسیرهای دورزدن آن |
| 5 | `enterprise-troubleshooting` — Privilege failure or service failure?<br>خطای مجوز یا خطای سرویس؟ | `configuration` — Create the account and validate the rule<br>ایجاد حساب و بررسی Rule |
| 6 | `enterprise-security` — Restricted commands and escape paths<br>فرمان محدود و مسیرهای دورزدن آن | `enterprise-monitoring` — Audit account and privilege changes<br>ممیزی تغییر حساب و مجوز |
| 7 | `enterprise-monitoring` — Audit account and privilege changes<br>ممیزی تغییر حساب و مجوز | `troubleshooting` — Privilege failure or service failure?<br>خطای مجوز یا خطای سرویس؟ |
| 8 | `enterprise-recovery` — Undo a rule and retain user data<br>برگشت Rule و نگهداری داده کاربر | `enterprise-recovery` — Undo a rule and retain user data<br>برگشت Rule و نگهداری داده کاربر |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### linux-security-auditor-bash

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `prerequisites` (Prerequisites / پیش‌نیازها), 24 → 2; `configuration` (Configuration and Validation / Configuration و اعتبارسنجی), 25 → 8; `best-practices` (Best Practices / Best Practiceها), 26 → 27.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — A firewall is only part of the picture<br>امنیت سرور فقط به فایروال ختم نمی‌شود | `introduction` — A firewall is only part of the picture<br>امنیت سرور فقط به فایروال ختم نمی‌شود |
| 2 | `architecture` — What does Linux Security Auditor do?<br>Linux Security Auditor چه کاری انجام می‌دهد؟ | `prerequisites` — Prerequisites<br>پیش‌نیازها |
| 3 | `distributions` — Which Linux distributions can it check?<br>روی چه توزیع‌هایی می‌توان اجرا کرد؟ | `architecture` — What does Linux Security Auditor do?<br>Linux Security Auditor چه کاری انجام می‌دهد؟ |
| 4 | `security` — What does the security score mean?<br>امتیاز امنیت را چطور بخوانیم؟ | `distributions` — Which Linux distributions can it check?<br>روی چه توزیع‌هایی می‌توان اجرا کرد؟ |
| 5 | `statuses` — Pass, warning and unknown<br>معنی وضعیت‌های گزارش | `security` — What does the security score mean?<br>امتیاز امنیت را چطور بخوانیم؟ |
| 6 | `installation` — Download and run the script<br>دانلود و اجرای اسکریپت | `statuses` — Pass, warning and unknown<br>معنی وضعیت‌های گزارش |
| 7 | `cli` — Useful commands and all available options<br>دستورهای کاربردی و گزینه‌های ابزار | `installation` — Download and run the script<br>دانلود و اجرای اسکریپت |
| 8 | `dashboard` — Reading the dashboard<br>خواندن داشبورد | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی |
| 9 | `patching` — Are updates waiting?<br>آیا آپدیتی در انتظار نصب است؟ | `cli` — Useful commands and all available options<br>دستورهای کاربردی و گزینه‌های ابزار |
| 10 | `users` — Old accounts and password settings<br>حساب‌های قدیمی و تنظیمات رمز | `dashboard` — Reading the dashboard<br>خواندن داشبورد |
| 11 | `ssh` — What SSH actually allows<br>SSH در عمل چه دسترسی‌ای می‌دهد؟ | `patching` — Are updates waiting?<br>آیا آپدیتی در انتظار نصب است؟ |
| 12 | `network` — Open ports and firewall rules<br>پورت‌های باز و قواعد فایروال | `users` — Old accounts and password settings<br>حساب‌های قدیمی و تنظیمات رمز |
| 13 | `persistence` — Scheduled jobs and startup commands<br>کارهای زمان‌بندی‌شده و فرمان‌های شروع سیستم | `ssh` — What SSH actually allows<br>SSH در عمل چه دسترسی‌ای می‌دهد؟ |
| 14 | `filesystem` — File access and disk space<br>دسترسی فایل‌ها و فضای دیسک | `network` — Open ports and firewall rules<br>پورت‌های باز و قواعد فایروال |
| 15 | `kernel` — A few checks on the Linux kernel<br>بررسی چند تنظیم امنیتی هسته لینوکس | `persistence` — Scheduled jobs and startup commands<br>کارهای زمان‌بندی‌شده و فرمان‌های شروع سیستم |
| 16 | `logging` — Will the logs help when something goes wrong?<br>اگر مشکلی پیش بیاید، لاگ کافی داریم؟ | `filesystem` — File access and disk space<br>دسترسی فایل‌ها و فضای دیسک |
| 17 | `mac` — AppArmor and SELinux<br>AppArmor و SELinux | `kernel` — A few checks on the Linux kernel<br>بررسی چند تنظیم امنیتی هسته لینوکس |
| 18 | `containers` — Docker and Podman checks<br>بررسی Docker و Podman | `logging` — Will the logs help when something goes wrong?<br>اگر مشکلی پیش بیاید، لاگ کافی داریم؟ |
| 19 | `threat` — Suspicious signs need a second look<br>نشانه مشکوک را باید دوباره بررسی کرد | `mac` — AppArmor and SELinux<br>AppArmor و SELinux |
| 20 | `read-only` — The script reports; you decide what to change<br>ابزار گزارش می‌دهد؛ تصمیم تغییر با شماست | `containers` — Docker and Podman checks<br>بررسی Docker و Podman |
| 21 | `validation` — What has been tested?<br>چه چیزهایی آزمایش شده‌اند؟ | `threat` — Suspicious signs need a second look<br>نشانه مشکوک را باید دوباره بررسی کرد |
| 22 | `limitations` — Using the report in daily operations<br>استفاده از گزارش در کار روزمره | `read-only` — The script reports; you decide what to change<br>ابزار گزارش می‌دهد؛ تصمیم تغییر با شماست |
| 23 | `related-reading` — Related guides<br>راهنماهای مرتبط | `validation` — What has been tested?<br>چه چیزهایی آزمایش شده‌اند؟ |
| 24 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `limitations` — Using the report in daily operations<br>استفاده از گزارش در کار روزمره |
| 25 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `related-reading` — Related guides<br>راهنماهای مرتبط |
| 26 | `best-practices` — Best Practices<br>Best Practiceها | `troubleshooting` — Troubleshooting<br>عیب‌یابی |
| 27 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | `best-practices` — Best Practices<br>Best Practiceها |
| 28 | `conclusion` — A practical starting point<br>یک نقطه شروع کاربردی | `conclusion` — A practical starting point<br>یک نقطه شروع کاربردی |
| 29 | `faq` — Common questions<br>پرسش‌های رایج | `faq` — Common questions<br>پرسش‌های رایج |
| 30 | `official-references` — Official References<br>منابع رسمی و مرجع | `official-references` — Official References<br>منابع رسمی و مرجع |

### mikrotik-block-port-scanners

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (Detection and drop ordering / ترتیب تشخیص و Drop), 10 → 3; `configuration` (Add rules and test counters / افزودن Rule و آزمون Counter), 12 → 5; `enterprise-monitoring` (List size and connection-tracking pressure / حجم List و فشار Connection Tracking), 7 → 6; `enterprise-recovery` (Undo rules without disabling the firewall / لغو Rule بدون خاموش‌کردن Firewall), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — Where psd fits in the firewall<br>نقش psd در Firewall | `introduction` — Where psd fits in the firewall<br>نقش psd در Firewall |
| 2 | `enterprise-architecture` — Detection and drop ordering<br>ترتیب تشخیص و Drop | `prerequisites` — Inspect the existing rules<br>بررسی Ruleهای موجود |
| 3 | `enterprise-prerequisites` — Inspect the existing rules<br>بررسی Ruleهای موجود | `architecture` — Detection and drop ordering<br>ترتیب تشخیص و Drop |
| 4 | `enterprise-installation` — Add rules and test counters<br>افزودن Rule و آزمون Counter | `security` — Allowlists, IPv6 and default deny<br>Allowlist، IPv6 و Default Deny |
| 5 | `enterprise-troubleshooting` — Zero counters and false positives<br>Counter صفر و False Positive | `configuration` — Add rules and test counters<br>افزودن Rule و آزمون Counter |
| 6 | `enterprise-security` — Allowlists, IPv6 and default deny<br>Allowlist، IPv6 و Default Deny | `enterprise-monitoring` — List size and connection-tracking pressure<br>حجم List و فشار Connection Tracking |
| 7 | `enterprise-monitoring` — List size and connection-tracking pressure<br>حجم List و فشار Connection Tracking | `troubleshooting` — Zero counters and false positives<br>Counter صفر و False Positive |
| 8 | `enterprise-recovery` — Undo rules without disabling the firewall<br>لغو Rule بدون خاموش‌کردن Firewall | `enterprise-recovery` — Undo rules without disabling the firewall<br>لغو Rule بدون خاموش‌کردن Firewall |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### mikrotik-block-website

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (DNS policy and SNI filtering differ / DNS Policy با SNI Filtering فرق دارد), 10 → 3; `configuration` (Configure the domain and test DNS/TLS separately / تنظیم دامنه و آزمون جداگانه DNS و TLS), 12 → 5; `enterprise-monitoring` (Measure user impact / سنجش اثر Policy بر کاربران), 7 → 6; `enterprise-recovery` (Undo policy and handle caches / برگشت Policy و Cache), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — What domain filtering sees<br>مسدودسازی دامنه با چه اطلاعاتی؟ | `introduction` — What domain filtering sees<br>مسدودسازی دامنه با چه اطلاعاتی؟ |
| 2 | `enterprise-architecture` — DNS policy and SNI filtering differ<br>DNS Policy با SNI Filtering فرق دارد | `prerequisites` — Resolver and RouterOS requirements<br>Resolver و نسخه RouterOS |
| 3 | `enterprise-prerequisites` — Resolver and RouterOS requirements<br>Resolver و نسخه RouterOS | `architecture` — DNS policy and SNI filtering differ<br>DNS Policy با SNI Filtering فرق دارد |
| 4 | `enterprise-security` — Avoid open recursion and CDN collateral damage<br>جلوگیری از Open Resolver و قطع CDN | `security` — Avoid open recursion and CDN collateral damage<br>جلوگیری از Open Resolver و قطع CDN |
| 5 | `enterprise-installation` — Configure the domain and test DNS/TLS separately<br>تنظیم دامنه و آزمون جداگانه DNS و TLS | `configuration` — Configure the domain and test DNS/TLS separately<br>تنظیم دامنه و آزمون جداگانه DNS و TLS |
| 6 | `enterprise-troubleshooting` — When the site still opens<br>سایت هنوز باز می‌شود | `enterprise-monitoring` — Measure user impact<br>سنجش اثر Policy بر کاربران |
| 7 | `enterprise-monitoring` — Measure user impact<br>سنجش اثر Policy بر کاربران | `troubleshooting` — When the site still opens<br>سایت هنوز باز می‌شود |
| 8 | `enterprise-recovery` — Undo policy and handle caches<br>برگشت Policy و Cache | `enterprise-recovery` — Undo policy and handle caches<br>برگشت Policy و Cache |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### mikrotik-firewall-hardening-input-forward-chain

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: no change needed.

Moved blocks: `prerequisites` (Prerequisites / پیش‌نیازها), 13 → 2; `architecture` (Architecture and Core Concepts / معماری و مفاهیم اصلی), 12 → 3; `configuration` (Configuration and Validation / Configuration و اعتبارسنجی), 14 → 6; `troubleshooting` (Firewall Troubleshooting / عیب‌یابی Firewall), 9 → 13.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — Overview<br>نمای کلی | `introduction` — Overview<br>نمای کلی |
| 2 | `input-forward` — Input and Forward: the critical distinction<br>تفاوت مهم Input و Forward | `prerequisites` — Prerequisites<br>پیش‌نیازها |
| 3 | `interfaces-addresses` — Interface Lists and Address Lists<br>Interface List و Address List | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی |
| 4 | `input-policy` — Protecting the MikroTik Router: INPUT Chain<br>محافظت از خود MikroTik: INPUT Chain | `input-forward` — Input and Forward: the critical distinction<br>تفاوت مهم Input و Forward |
| 5 | `security` — Hardening RouterOS Services and DNS<br>Hardening سرویس‌های RouterOS و DNS | `interfaces-addresses` — Interface Lists and Address Lists<br>Interface List و Address List |
| 6 | `forward-policy` — Protecting Internal Networks: FORWARD Chain<br>محافظت از شبکه‌های داخلی: FORWARD Chain | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی |
| 7 | `fasttrack` — Should We Use FastTrack in Enterprise Networks?<br>آیا FastTrack برای شبکه سازمانی مناسب است؟ | `input-policy` — Protecting the MikroTik Router: INPUT Chain<br>محافظت از خود MikroTik: INPUT Chain |
| 8 | `ordering` — Why Firewall Rule Order Matters<br>چرا ترتیب Ruleهای Firewall مهم است؟ | `security` — Hardening RouterOS Services and DNS<br>Hardening سرویس‌های RouterOS و DNS |
| 9 | `troubleshooting` — Firewall Troubleshooting<br>عیب‌یابی Firewall | `forward-policy` — Protecting Internal Networks: FORWARD Chain<br>محافظت از شبکه‌های داخلی: FORWARD Chain |
| 10 | `testing` — Testing Matrix<br>ماتریس تست | `fasttrack` — Should We Use FastTrack in Enterprise Networks?<br>آیا FastTrack برای شبکه سازمانی مناسب است؟ |
| 11 | `best-practices` — Advanced Notes and Production Checklist<br>نکات Advanced و Checklist Production | `ordering` — Why Firewall Rule Order Matters<br>چرا ترتیب Ruleهای Firewall مهم است؟ |
| 12 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `testing` — Testing Matrix<br>ماتریس تست |
| 13 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `troubleshooting` — Firewall Troubleshooting<br>عیب‌یابی Firewall |
| 14 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `best-practices` — Advanced Notes and Production Checklist<br>نکات Advanced و Checklist Production |
| 15 | `conclusion` — Conclusion<br>جمع‌بندی | `conclusion` — Conclusion<br>جمع‌بندی |
| 16 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 17 | `official-references` — Official References<br>منابع رسمی و مرجع | `official-references` — Official References<br>منابع رسمی و مرجع |

### mikrotik-openvpn-setup-v7

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (VPN forward and return paths / مسیر رفت و برگشت VPN), 10 → 3; `configuration` (Import certificates and configure server/client / واردکردن گواهی و تنظیم Server و Client), 12 → 5; `enterprise-monitoring` (Pool, sessions and certificate expiry / Pool، Session و انقضای گواهی), 7 → 6; `enterprise-recovery` (Revocation and configuration rollback / لغو گواهی و برگشت تنظیم), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — A tunnel is not an access policy<br>Tunnel با دسترسی مجاز فرق دارد | `introduction` — A tunnel is not an access policy<br>Tunnel با دسترسی مجاز فرق دارد |
| 2 | `enterprise-architecture` — VPN forward and return paths<br>مسیر رفت و برگشت VPN | `prerequisites` — Version, PKI and management access<br>نسخه، PKI و دسترسی مدیریت |
| 3 | `enterprise-prerequisites` — Version, PKI and management access<br>نسخه، PKI و دسترسی مدیریت | `architecture` — VPN forward and return paths<br>مسیر رفت و برگشت VPN |
| 4 | `enterprise-security` — Individual certificates and revocation<br>گواهی فردی و لغو دسترسی | `security` — Individual certificates and revocation<br>گواهی فردی و لغو دسترسی |
| 5 | `enterprise-installation` — Import certificates and configure server/client<br>واردکردن گواهی و تنظیم Server و Client | `configuration` — Import certificates and configure server/client<br>واردکردن گواهی و تنظیم Server و Client |
| 6 | `enterprise-troubleshooting` — From TLS errors to SSH timeouts<br>از TLS Error تا SSH Timeout | `enterprise-monitoring` — Pool, sessions and certificate expiry<br>Pool، Session و انقضای گواهی |
| 7 | `enterprise-monitoring` — Pool, sessions and certificate expiry<br>Pool، Session و انقضای گواهی | `troubleshooting` — From TLS errors to SSH timeouts<br>از TLS Error تا SSH Timeout |
| 8 | `enterprise-recovery` — Revocation and configuration rollback<br>لغو گواهی و برگشت تنظیم | `enterprise-recovery` — Revocation and configuration rollback<br>لغو گواهی و برگشت تنظیم |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### mikrotik-pbr-client

Source file changed: no; shared rendering corrected. Editorial labels removed: 0. Source TOC reordered: no change needed.

Moved blocks: `architecture` (How the application works / برنامه چگونه کار می‌کند؟), 8 → 3; `best-practices` (Best Practices / Best Practiceها), 9 → 9; `download` (Download version 1.0.2 / دانلود نسخه ۱.۰.۲), 7 → 13.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — Introduction and use cases<br>معرفی و کاربرد برنامه | `introduction` — Introduction and use cases<br>معرفی و کاربرد برنامه |
| 2 | `prerequisites` — Router prerequisites<br>پیش‌نیازهای سمت روتر | `prerequisites` — Router prerequisites<br>پیش‌نیازهای سمت روتر |
| 3 | `operation` — How the application works<br>برنامه چگونه کار می‌کند؟ | `architecture` — How the application works<br>برنامه چگونه کار می‌کند؟ |
| 4 | `configuration` — Installation and everyday use<br>نصب و استفاده روزمره | `configuration` — Installation and everyday use<br>نصب و استفاده روزمره |
| 5 | `settings` — Changing trigger IP addresses<br>آموزش تغییر IP از تنظیمات | `settings` — Changing trigger IP addresses<br>آموزش تغییر IP از تنظیمات |
| 6 | `release` — Release notes and trigger addresses<br>نکات نسخه و آدرس‌های تریگر | `release` — Release notes and trigger addresses<br>نکات نسخه و آدرس‌های تریگر |
| 7 | `download` — Download version 1.0.2<br>دانلود نسخه ۱.۰.۲ | `security` — Security Considerations<br>ملاحظات امنیتی |
| 8 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `troubleshooting` — Troubleshooting<br>عیب‌یابی |
| 9 | `best-practices` — Best Practices<br>Best Practiceها | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `security` — Security Considerations<br>ملاحظات امنیتی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | `faq` — Frequently asked questions<br>پرسش‌های متداول |
| 12 | `conclusion` — Conclusion<br>جمع‌بندی | `official-references` — Official References<br>منابع رسمی و مرجع |
| 13 | `faq` — Frequently asked questions<br>پرسش‌های متداول | `download` — Download version 1.0.2<br>دانلود نسخه ۱.۰.۲ |
| 14 | `official-references` — Official References<br>منابع رسمی و مرجع | — |

### mikrotik-ping-triggered-policy-routing

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `prerequisites` (Prerequisites / پیش‌نیازها), 21 → 2; `configuration` (Configuration and Validation / Configuration و اعتبارسنجی), 22 → 7; `security` (18. Security Considerations / ۱۸. ملاحظات امنیتی), 18 → 17; `complete-configuration` (20. Complete Example Configuration / ۲۰. پیکربندی کامل مثال), 20 → 18.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — 1. Introduction<br>۱. مقدمه | `introduction` — 1. Introduction<br>۱. مقدمه |
| 2 | `architecture` — 2. Architecture<br>۲. معماری | `prerequisites` — Prerequisites<br>پیش‌نیازها |
| 3 | `technical-limitation` — 3. Root Cause / Technical Limitation<br>۳. محدودیت فنی Firewall | `architecture` — 2. Architecture<br>۲. معماری |
| 4 | `control-interface` — 4. Control Interface<br>۴. Interface کنترلی | `technical-limitation` — 3. Root Cause / Technical Limitation<br>۳. محدودیت فنی Firewall |
| 5 | `local-networks` — 5. Local Networks<br>۵. شبکه‌های داخلی | `control-interface` — 4. Control Interface<br>۴. Interface کنترلی |
| 6 | `enable-trigger` — 6. Enable Trigger<br>۶. Trigger فعال‌سازی | `local-networks` — 5. Local Networks<br>۵. شبکه‌های داخلی |
| 7 | `disable-trigger` — 7. Disable Trigger<br>۷. Trigger غیرفعال‌سازی | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی |
| 8 | `routing-table` — 8. RouterOS v7 Routing Table<br>۸. Routing Table در RouterOS v7 | `enable-trigger` — 6. Enable Trigger<br>۶. Trigger فعال‌سازی |
| 9 | `mangle-policy` — 9. Mangle Policy Routing<br>۹. Policy Routing با Mangle | `disable-trigger` — 7. Disable Trigger<br>۷. Trigger غیرفعال‌سازی |
| 10 | `removal-script` — 10. Removal Script<br>۱۰. Script حذف | `routing-table` — 8. RouterOS v7 Routing Table<br>۸. Routing Table در RouterOS v7 |
| 11 | `scheduler` — 11. Scheduler<br>۱۱. Scheduler | `mangle-policy` — 9. Mangle Policy Routing<br>۹. Policy Routing با Mangle |
| 12 | `fasttrack` — 12. FastTrack<br>۱۲. FastTrack | `removal-script` — 10. Removal Script<br>۱۰. Script حذف |
| 13 | `nat` — 13. NAT<br>۱۳. NAT | `scheduler` — 11. Scheduler<br>۱۱. Scheduler |
| 14 | `connection-tracking` — 14. Connection Tracking<br>۱۴. Connection Tracking | `fasttrack` — 12. FastTrack<br>۱۲. FastTrack |
| 15 | `testing` — 15. Testing<br>۱۵. آزمون | `nat` — 13. NAT<br>۱۳. NAT |
| 16 | `expected-result` — 16. Expected Result<br>۱۶. نتیجه مورد انتظار | `connection-tracking` — 14. Connection Tracking<br>۱۴. Connection Tracking |
| 17 | `troubleshooting` — 17. Troubleshooting<br>۱۷. عیب‌یابی | `security` — 18. Security Considerations<br>۱۸. ملاحظات امنیتی |
| 18 | `security` — 18. Security Considerations<br>۱۸. ملاحظات امنیتی | `complete-configuration` — 20. Complete Example Configuration<br>۲۰. پیکربندی کامل مثال |
| 19 | `best-practices` — 19. Production Best Practices<br>۱۹. توصیه‌های Production | `testing` — 15. Testing<br>۱۵. آزمون |
| 20 | `complete-configuration` — 20. Complete Example Configuration<br>۲۰. پیکربندی کامل مثال | `expected-result` — 16. Expected Result<br>۱۶. نتیجه مورد انتظار |
| 21 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `troubleshooting` — 17. Troubleshooting<br>۱۷. عیب‌یابی |
| 22 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `best-practices` — 19. Production Best Practices<br>۱۹. توصیه‌های Production |
| 23 | `conclusion` — Conclusion<br>جمع‌بندی | `conclusion` — Conclusion<br>جمع‌بندی |
| 24 | `faq` — FAQ<br>پرسش‌های متداول | `faq` — FAQ<br>پرسش‌های متداول |
| 25 | `official-references` — Official References<br>منابع رسمی و مرجع | `official-references` — Official References<br>منابع رسمی و مرجع |

### mikrotik-unequal-dual-wan-load-balancing-ecmp

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `ecmp-pcc-comparison` (Why the Configuration Uses PCC / چرا مثال اجرایی از PCC استفاده می‌کند؟), 2 → 2; `architecture` (Connection marks and routing tables / Connection Mark و Routing Table), 11 → 4; `configuration` (Configure tables, mangle and NAT / تنظیم Table، Mangle و NAT), 13 → 6; `enterprise-monitoring` (Healthy gateway, failed upstream / Gateway سالم با اینترنت قطع), 6 → 7; `enterprise-recovery` (Rollback and session behavior during failover / برگشت Policy و رفتار Session در Failover), 9 → 9; `best-practices` (Best Practices / Best Practiceها), 14 → 10.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — Connection weighting is not bandwidth aggregation<br>وزن اتصال با جمع پهنای باند فرق دارد | `introduction` — Connection weighting is not bandwidth aggregation<br>وزن اتصال با جمع پهنای باند فرق دارد |
| 2 | `ecmp-pcc-comparison` — Why the Configuration Uses PCC<br>چرا مثال اجرایی از PCC استفاده می‌کند؟ | `ecmp-pcc-comparison` — Why the Configuration Uses PCC<br>چرا مثال اجرایی از PCC استفاده می‌کند؟ |
| 3 | `enterprise-architecture` — Connection marks and routing tables<br>Connection Mark و Routing Table | `prerequisites` — Before policy routing<br>پیش از Policy Routing |
| 4 | `enterprise-prerequisites` — Before policy routing<br>پیش از Policy Routing | `architecture` — Connection marks and routing tables<br>Connection Mark و Routing Table |
| 5 | `enterprise-installation` — Configure tables, mangle and NAT<br>تنظیم Table، Mangle و NAT | `security` — NAT and internal paths<br>NAT و مسیر داخلی |
| 6 | `enterprise-monitoring` — Healthy gateway, failed upstream<br>Gateway سالم با اینترنت قطع | `configuration` — Configure tables, mangle and NAT<br>تنظیم Table، Mangle و NAT |
| 7 | `enterprise-troubleshooting` — Uneven distribution and wrong return paths<br>توزیع نامناسب و مسیر پاسخ اشتباه | `enterprise-monitoring` — Healthy gateway, failed upstream<br>Gateway سالم با اینترنت قطع |
| 8 | `enterprise-security` — NAT and internal paths<br>NAT و مسیر داخلی | `troubleshooting` — Uneven distribution and wrong return paths<br>توزیع نامناسب و مسیر پاسخ اشتباه |
| 9 | `enterprise-recovery` — Rollback and session behavior during failover<br>برگشت Policy و رفتار Session در Failover | `enterprise-recovery` — Rollback and session behavior during failover<br>برگشت Policy و رفتار Session در Failover |
| 10 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 11 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 12 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 13 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 14 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 15 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 16 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 17 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 18 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 19 | `official-references` — Official Sources<br>منابع رسمی | — |

### mongodb-installation-configuration-production-deployment

Source file changed: yes. Editorial labels removed: 4. Source TOC reordered: yes.

Moved blocks: `architecture` (2. Architecture Overview / ۲. نمای معماری), 2 → 4; `backup` (18. Backup and Tested Recovery / ۱۸. Backup و بازیابی آزموده‌شده), 18 → 20; `troubleshooting` (23. Troubleshooting / ۲۳. عیب‌یابی), 23 → 22; `performance` (19. Performance Checks / ۱۹. بررسی Performance), 19 → 23; `indexing` (20. Indexing and Query Plans / ۲۰. Index و Query Plan), 20 → 24.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — 1. What Is MongoDB?<br>۱. MongoDB چیست؟ | `introduction` — 1. What Is MongoDB?<br>۱. MongoDB چیست؟ |
| 2 | `architecture` — 2. Architecture Overview<br>۲. نمای معماری | `prerequisites` — 3. Server Requirements<br>۳. پیش‌نیاز سرور |
| 3 | `prerequisites` — 3. Server Requirements<br>۳. پیش‌نیاز سرور | `pre-installation` — 4. Pre-Installation Checks<br>۴. بررسی پیش از نصب |
| 4 | `pre-installation` — 4. Pre-Installation Checks<br>۴. بررسی پیش از نصب | `architecture` — 2. Architecture Overview<br>۲. نمای معماری |
| 5 | `ubuntu-install` — 5. Install MongoDB on Ubuntu 22.04 / 24.04<br>۵. نصب MongoDB روی Ubuntu 22.04 / 24.04 | `ubuntu-install` — 5. Install MongoDB on Ubuntu 22.04 / 24.04<br>۵. نصب MongoDB روی Ubuntu 22.04 / 24.04 |
| 6 | `debian-install` — 6. Install MongoDB on Debian 12<br>۶. نصب MongoDB روی Debian 12 | `debian-install` — 6. Install MongoDB on Debian 12<br>۶. نصب MongoDB روی Debian 12 |
| 7 | `rhel-install` — 7. Install on RHEL / Rocky / AlmaLinux<br>۷. نصب روی RHEL / Rocky / AlmaLinux | `rhel-install` — 7. Install on RHEL / Rocky / AlmaLinux<br>۷. نصب روی RHEL / Rocky / AlmaLinux |
| 8 | `configuration` — 8. MongoDB Configuration<br>۸. پیکربندی MongoDB | `configuration` — 8. MongoDB Configuration<br>۸. پیکربندی MongoDB |
| 9 | `remote-access` — 9. Secure Remote Access<br>۹. دسترسی Remote امن | `remote-access` — 9. Secure Remote Access<br>۹. دسترسی Remote امن |
| 10 | `authentication` — 10. MongoDB Authentication<br>۱۰. احراز هویت MongoDB | `authentication` — 10. MongoDB Authentication<br>۱۰. احراز هویت MongoDB |
| 11 | `application-user` — 11. Dedicated Application User<br>۱۱. حساب اختصاصی Application | `application-user` — 11. Dedicated Application User<br>۱۱. حساب اختصاصی Application |
| 12 | `firewall` — 12. Firewall Hardening<br>۱۲. ایمن‌سازی Firewall | `firewall` — 12. Firewall Hardening<br>۱۲. ایمن‌سازی Firewall |
| 13 | `security` — 13. MongoDB Security Hardening<br>۱۳. ایمن‌سازی MongoDB | `security` — 13. MongoDB Security Hardening<br>۱۳. ایمن‌سازی MongoDB |
| 14 | `tls` — 14. TLS Encryption<br>۱۴. رمزنگاری TLS | `tls` — 14. TLS Encryption<br>۱۴. رمزنگاری TLS |
| 15 | `replica-set` — 15. Production Replica Set<br>۱۵. Replica Set در Production | `replica-set` — 15. Production Replica Set<br>۱۵. Replica Set در Production |
| 16 | `replica-security` — 16. Replica Set Internal Authentication<br>۱۶. احراز هویت داخلی Replica Set | `replica-security` — 16. Replica Set Internal Authentication<br>۱۶. احراز هویت داخلی Replica Set |
| 17 | `connection-string` — 17. MongoDB Connection Strings and Secrets<br>۱۷. Connection String و Secretها | `connection-string` — 17. MongoDB Connection Strings and Secrets<br>۱۷. Connection String و Secretها |
| 18 | `backup` — 18. Backup and Tested Recovery<br>۱۸. Backup و بازیابی آزموده‌شده | `logging` — 21. Logging and Slow Queries<br>۲۱. Log و Slow Query |
| 19 | `performance` — 19. Performance Checks<br>۱۹. بررسی Performance | `monitoring` — 22. Monitoring and Alerting<br>۲۲. مانیتورینگ و Alert |
| 20 | `indexing` — 20. Indexing and Query Plans<br>۲۰. Index و Query Plan | `backup` — 18. Backup and Tested Recovery<br>۱۸. Backup و بازیابی آزموده‌شده |
| 21 | `logging` — 21. Logging and Slow Queries<br>۲۱. Log و Slow Query | `verification` — 24. Verification Checklist<br>۲۴. چک‌لیست اعتبارسنجی |
| 22 | `monitoring` — 22. Monitoring and Alerting<br>۲۲. مانیتورینگ و Alert | `troubleshooting` — 23. Troubleshooting<br>۲۳. عیب‌یابی |
| 23 | `troubleshooting` — 23. Troubleshooting<br>۲۳. عیب‌یابی | `performance` — 19. Performance Checks<br>۱۹. بررسی Performance |
| 24 | `verification` — 24. Verification Checklist<br>۲۴. چک‌لیست اعتبارسنجی | `indexing` — 20. Indexing and Query Plans<br>۲۰. Index و Query Plan |
| 25 | `best-practices` — 25. Production Checklist<br>۲۵. چک‌لیست Production | `best-practices` — 25. Production Checklist<br>۲۵. چک‌لیست Production |
| 26 | `conclusion` — Operational Handover<br>تحویل عملیاتی | `conclusion` — Operational Handover<br>تحویل عملیاتی |
| 27 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 28 | `official-references` — Official References<br>منابع رسمی | `official-references` — Official References<br>منابع رسمی |

### netbox-installation-setup-ubuntu

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (Web service, database and worker / سرویس وب، Database و Worker), 10 → 3; `configuration` (Install dependencies and start services / نصب Dependency و راه‌اندازی سرویس‌ها), 12 → 5; `enterprise-monitoring` (Healthy UI with stalled jobs / UI سالم با Job متوقف), 6 → 6; `enterprise-recovery` (Coordinated backup and isolated restore / Backup هماهنگ و Restore ایزوله), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — NetBox as a network source of truth<br>NetBox به‌عنوان مرجع شبکه | `introduction` — NetBox as a network source of truth<br>NetBox به‌عنوان مرجع شبکه |
| 2 | `enterprise-architecture` — Web service, database and worker<br>سرویس وب، Database و Worker | `prerequisites` — Baseline versions and hostname<br>نسخه‌های مبنا و دامنه سرویس |
| 3 | `enterprise-prerequisites` — Baseline versions and hostname<br>نسخه‌های مبنا و دامنه سرویس | `architecture` — Web service, database and worker<br>سرویس وب، Database و Worker |
| 4 | `enterprise-installation` — Install dependencies and start services<br>نصب Dependency و راه‌اندازی سرویس‌ها | `security` — API permissions and secret protection<br>API Permission و حفاظت Secretها |
| 5 | `enterprise-security` — API permissions and secret protection<br>API Permission و حفاظت Secretها | `configuration` — Install dependencies and start services<br>نصب Dependency و راه‌اندازی سرویس‌ها |
| 6 | `enterprise-monitoring` — Healthy UI with stalled jobs<br>UI سالم با Job متوقف | `enterprise-monitoring` — Healthy UI with stalled jobs<br>UI سالم با Job متوقف |
| 7 | `enterprise-troubleshooting` — 502, CSRF and queue failures<br>502، CSRF و Queue | `troubleshooting` — 502, CSRF and queue failures<br>502، CSRF و Queue |
| 8 | `enterprise-recovery` — Coordinated backup and isolated restore<br>Backup هماهنگ و Restore ایزوله | `enterprise-recovery` — Coordinated backup and isolated restore<br>Backup هماهنگ و Restore ایزوله |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### nginx-installation-configuration-ubuntu

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (TLS at the proxy, HTTP on loopback / TLS در Proxy و HTTP در Loopback), 10 → 3; `configuration` (Install packages and configure the virtual host / نصب Package و Virtual Host), 12 → 5; `enterprise-monitoring` (Latency, 5xx and request IDs / Latency، 5xx و شناسه درخواست), 6 → 6; `enterprise-recovery` (Roll back the virtual host and reload / برگشت Virtual Host و Reload), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — Nginx in front of a backend<br>Nginx جلوی یک Backend | `introduction` — Nginx in front of a backend<br>Nginx جلوی یک Backend |
| 2 | `enterprise-architecture` — TLS at the proxy, HTTP on loopback<br>TLS در Proxy و HTTP در Loopback | `prerequisites` — Backend, DNS and certificate<br>Backend، DNS و گواهی |
| 3 | `enterprise-prerequisites` — Backend, DNS and certificate<br>Backend، DNS و گواهی | `architecture` — TLS at the proxy, HTTP on loopback<br>TLS در Proxy و HTTP در Loopback |
| 4 | `enterprise-installation` — Install packages and configure the virtual host<br>نصب Package و Virtual Host | `security` — Keys and proxy headers<br>Key و Headerهای Proxy |
| 5 | `enterprise-security` — Keys and proxy headers<br>Key و Headerهای Proxy | `configuration` — Install packages and configure the virtual host<br>نصب Package و Virtual Host |
| 6 | `enterprise-monitoring` — Latency, 5xx and request IDs<br>Latency، 5xx و شناسه درخواست | `enterprise-monitoring` — Latency, 5xx and request IDs<br>Latency، 5xx و شناسه درخواست |
| 7 | `enterprise-troubleshooting` — Diagnose 502 and 504<br>تشخیص 502 و 504 | `troubleshooting` — Diagnose 502 and 504<br>تشخیص 502 و 504 |
| 8 | `enterprise-recovery` — Roll back the virtual host and reload<br>برگشت Virtual Host و Reload | `enterprise-recovery` — Roll back the virtual host and reload<br>برگشت Virtual Host و Reload |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### nginx-reverse-proxy-multiple-domains-single-ip-443

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `prerequisites` (Prerequisites / پیش‌نیازها), 23 → 3; `nat` (20. Why NAT Alone Is Not Enough / ۲۰. Why NAT Alone Is Not Enough؛ چرا NAT کافی نیست؟), 20 → 6; `enterprise` (21. Enterprise Architecture Example / ۲۱. مثال معماری سازمانی), 21 → 7; `configuration` (6. Nginx Installation / ۶. نصب Nginx), 24 → 8.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — 1. Introduction<br>۱. مقدمه | `introduction` — 1. Introduction<br>۱. مقدمه |
| 2 | `scenario` — 2. Scenario<br>۲. سناریو | `scenario` — 2. Scenario<br>۲. سناریو |
| 3 | `architecture` — 3. Architecture<br>۳. معماری | `prerequisites` — Prerequisites<br>پیش‌نیازها |
| 4 | `sni` — 4. How SNI Works<br>۴. نحوه کار SNI | `architecture` — 3. Architecture<br>۳. معماری |
| 5 | `dns` — 5. DNS Configuration<br>۵. تنظیم DNS | `sni` — 4. How SNI Works<br>۴. نحوه کار SNI |
| 6 | `installation` — 6. Nginx Installation<br>۶. نصب Nginx | `nat` — 20. Why NAT Alone Is Not Enough<br>۲۰. Why NAT Alone Is Not Enough؛ چرا NAT کافی نیست؟ |
| 7 | `backend-connectivity` — 7. Backend Connectivity Check<br>۷. بررسی ارتباط با Backend | `enterprise` — 21. Enterprise Architecture Example<br>۲۱. مثال معماری سازمانی |
| 8 | `certificates` — 8. SSL Certificate<br>۸. گواهی SSL و TLS | `configuration` — 6. Nginx Installation<br>۶. نصب Nginx |
| 9 | `complete-configuration` — 9. Complete Nginx Configuration<br>۹. تنظیمات کامل Nginx | `dns` — 5. DNS Configuration<br>۵. تنظیم DNS |
| 10 | `http-redirect` — 10. HTTP to HTTPS Redirect<br>۱۰. هدایت HTTP به HTTPS | `backend-connectivity` — 7. Backend Connectivity Check<br>۷. بررسی ارتباط با Backend |
| 11 | `security` — 11. Default Server Security<br>۱۱. امنیت Default Server | `certificates` — 8. SSL Certificate<br>۸. گواهی SSL و TLS |
| 12 | `websocket` — 12. WebSocket Support and Upload Size<br>۱۲. WebSocket و اندازه Upload | `complete-configuration` — 9. Complete Nginx Configuration<br>۹. تنظیمات کامل Nginx |
| 13 | `logging` — 13. Logging<br>۱۳. لاگ‌گیری | `http-redirect` — 10. HTTP to HTTPS Redirect<br>۱۰. هدایت HTTP به HTTPS |
| 14 | `hardening` — 14. Security Hardening<br>۱۴. تقویت امنیت | `security` — 11. Default Server Security<br>۱۱. امنیت Default Server |
| 15 | `testing` — 15. Testing Configuration and Renewal<br>۱۵. تست تنظیمات و تمدید | `websocket` — 12. WebSocket Support and Upload Size<br>۱۲. WebSocket و اندازه Upload |
| 16 | `resolve` — 16. curl --resolve: Test Before DNS Cutover<br>۱۶. تست با curl --resolve پیش از تغییر DNS | `logging` — 13. Logging<br>۱۳. لاگ‌گیری |
| 17 | `openssl` — 17. openssl s_client: SNI and Certificate Verification<br>۱۷. تست SNI و گواهی با openssl s_client | `hardening` — 14. Security Hardening<br>۱۴. تقویت امنیت |
| 18 | `troubleshooting` — 18. Troubleshooting<br>۱۸. عیب‌یابی | `testing` — 15. Testing Configuration and Renewal<br>۱۵. تست تنظیمات و تمدید |
| 19 | `root-cause` — 19. Root Cause Analysis<br>۱۹. تحلیل علت ریشه‌ای | `resolve` — 16. curl --resolve: Test Before DNS Cutover<br>۱۶. تست با curl --resolve پیش از تغییر DNS |
| 20 | `nat` — 20. Why NAT Alone Is Not Enough<br>۲۰. Why NAT Alone Is Not Enough؛ چرا NAT کافی نیست؟ | `openssl` — 17. openssl s_client: SNI and Certificate Verification<br>۱۷. تست SNI و گواهی با openssl s_client |
| 21 | `enterprise` — 21. Enterprise Architecture Example<br>۲۱. مثال معماری سازمانی | `troubleshooting` — 18. Troubleshooting<br>۱۸. عیب‌یابی |
| 22 | `best-practices` — 22. Security Recommendations and Operational Limits<br>۲۲. توصیه‌های امنیتی و محدودیت عملیاتی | `root-cause` — 19. Root Cause Analysis<br>۱۹. تحلیل علت ریشه‌ای |
| 23 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `best-practices` — 22. Security Recommendations and Operational Limits<br>۲۲. توصیه‌های امنیتی و محدودیت عملیاتی |
| 24 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `conclusion` — 23. Conclusion<br>۲۳. نتیجه‌گیری |
| 25 | `conclusion` — 23. Conclusion<br>۲۳. نتیجه‌گیری | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 26 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | `official-references` — Official References<br>منابع رسمی |
| 27 | `official-references` — Official References<br>منابع رسمی | — |

### oracle-database-26ai-installation-oracle-linux

Source file changed: yes. Editorial labels removed: 4. Source TOC reordered: yes.

Moved blocks: `architecture` (CDB and PDB architecture / معماری CDB و PDB), 2 → 3.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — 1. Introduction: release, edition and scope<br>۱. مقدمه؛ نسخه، Edition و محدوده راهنما | `introduction` — 1. Introduction: release, edition and scope<br>۱. مقدمه؛ نسخه، Edition و محدوده راهنما |
| 2 | `architecture` — CDB and PDB architecture<br>معماری CDB و PDB | `prerequisites` — 2. Prerequisites and host diagnostics<br>۲. پیش‌نیازها و بررسی میزبان |
| 3 | `prerequisites` — 2. Prerequisites and host diagnostics<br>۲. پیش‌نیازها و بررسی میزبان | `architecture` — CDB and PDB architecture<br>معماری CDB و PDB |
| 4 | `configuration` — 3. Installation and database configuration<br>۳. نصب و پیکربندی دیتابیس | `configuration` — 3. Installation and database configuration<br>۳. نصب و پیکربندی دیتابیس |
| 5 | `system-service` — 4. Running Oracle as a service and verifying reboot startup<br>۴. اجرای Oracle به‌صورت سرویس و تأیید Startup پس از Reboot | `system-service` — 4. Running Oracle as a service and verifying reboot startup<br>۴. اجرای Oracle به‌صورت سرویس و تأیید Startup پس از Reboot |
| 6 | `security` — 5. Oracle Database security hardening<br>۵. امن‌سازی Oracle Database | `security` — 5. Oracle Database security hardening<br>۵. امن‌سازی Oracle Database |
| 7 | `rman-backup` — 6. Backup configuration using RMAN<br>۶. پیکربندی Backup با RMAN | `rman-backup` — 6. Backup configuration using RMAN<br>۶. پیکربندی Backup با RMAN |
| 8 | `automated-backup` — 7. Automated backup: guarded script, timers and monitoring<br>۷. Backup خودکار؛ Script محافظت‌شده، Timer و Monitoring | `automated-backup` — 7. Automated backup: guarded script, timers and monitoring<br>۷. Backup خودکار؛ Script محافظت‌شده، Timer و Monitoring |
| 9 | `restore-recovery` — 8. Database restore and recovery scenarios<br>۸. سناریوهای Restore و Recovery دیتابیس | `restore-recovery` — 8. Database restore and recovery scenarios<br>۸. سناریوهای Restore و Recovery دیتابیس |
| 10 | `troubleshooting` — 9. Monitoring and troubleshooting<br>۹. Monitoring و عیب‌یابی | `troubleshooting` — 9. Monitoring and troubleshooting<br>۹. Monitoring و عیب‌یابی |
| 11 | `best-practices` — 10. Production deployment checklist<br>۱۰. چک‌لیست استقرار Production | `best-practices` — 10. Production deployment checklist<br>۱۰. چک‌لیست استقرار Production |
| 12 | `conclusion` — Conclusion: acceptance requires recovery evidence<br>جمع‌بندی؛ پذیرش به شاهد Recovery نیاز دارد | `conclusion` — Conclusion: acceptance requires recovery evidence<br>جمع‌بندی؛ پذیرش به شاهد Recovery نیاز دارد |
| 13 | `faq` — Frequently asked questions<br>پرسش‌های متداول | `faq` — Frequently asked questions<br>پرسش‌های متداول |
| 14 | `official-references` — Official References and reviewed templates<br>منابع رسمی و Templateهای بررسی‌شده | `official-references` — Official References and reviewed templates<br>منابع رسمی و Templateهای بررسی‌شده |

### oxidized-network-device-configuration-backup

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `prerequisites` (Ruby, models and device accounts / Ruby، Model و حساب تجهیز), 8 → 2; `configuration` (Install the gem, configuration and systemd unit / نصب Gem، Config و Systemd Unit), 11 → 5; `enterprise-monitoring` (Fetch freshness differs from commit age / تازگی Fetch با تاریخ Commit فرق دارد), 6 → 6; `best-practices` (Best Practices / Best Practiceها), 12 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — Collect configuration and change history<br>جمع‌آوری Config و تاریخچه تغییر | `introduction` — Collect configuration and change history<br>جمع‌آوری Config و تاریخچه تغییر |
| 2 | `enterprise-architecture` — From SSH to a bare repository<br>از SSH تا Bare Repository | `prerequisites` — Ruby, models and device accounts<br>Ruby، Model و حساب تجهیز |
| 3 | `enterprise-prerequisites` — Ruby, models and device accounts<br>Ruby، Model و حساب تجهیز | `architecture` — From SSH to a bare repository<br>از SSH تا Bare Repository |
| 4 | `enterprise-security` — Known hosts and historical secrets<br>Known Hosts و Secretهای تاریخچه | `security` — Known hosts and historical secrets<br>Known Hosts و Secretهای تاریخچه |
| 5 | `enterprise-installation` — Install the gem, configuration and systemd unit<br>نصب Gem، Config و Systemd Unit | `configuration` — Install the gem, configuration and systemd unit<br>نصب Gem، Config و Systemd Unit |
| 6 | `enterprise-monitoring` — Fetch freshness differs from commit age<br>تازگی Fetch با تاریخ Commit فرق دارد | `enterprise-monitoring` — Fetch freshness differs from commit age<br>تازگی Fetch با تاریخ Commit فرق دارد |
| 7 | `enterprise-troubleshooting` — Failed nodes and incomplete configuration<br>Node Failed و Config ناقص | `troubleshooting` — Failed nodes and incomplete configuration<br>Node Failed و Config ناقص |
| 8 | `prerequisites` — Restore a commit on matching hardware<br>بازیابی Commit روی تجهیز مشابه | `enterprise-recovery` — Restore a commit on matching hardware<br>بازیابی Commit روی تجهیز مشابه |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `best-practices` — Best Practices<br>Best Practiceها | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 14 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 15 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 16 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 17 | `official-references` — Official Sources<br>منابع رسمی | — |

### redis-installation-configuration-replication

Source file changed: yes. Editorial labels removed: 4. Source TOC reordered: yes.

Moved blocks: `prerequisites` (Prerequisites and deployment scenario / پیش‌نیازها و سناریوی استقرار), 2 → 4; `replication-architecture` (14. Redis replication architecture / ۱۴. Redis Replication Architecture), 16 → 5; `replication-is-not-ha` (15. Replication alone is not high availability / ۱۵. Replication به‌تنهایی High Availability نیست), 17 → 6; `cluster-comparison` (17. Replication vs Sentinel vs Redis Cluster / ۱۷. تفاوت Replication، Sentinel و Redis Cluster), 19 → 7; `service-diagnostics` (4. Check the Redis service / ۴. بررسی سرویس Redis), 6 → 9; `backup-restore` (21. Backup and restore / ۲۱. Backup و Restore), 23 → 23; `memory-management` (18. Memory management and eviction policy / ۱۸. Memory Management و Eviction Policy), 20 → 24.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — Introduction: verified versions and deployment scope<br>مقدمه؛ نسخه‌های بررسی‌شده و محدوده استقرار | `introduction` — Introduction: verified versions and deployment scope<br>مقدمه؛ نسخه‌های بررسی‌شده و محدوده استقرار |
| 2 | `prerequisites` — Prerequisites and deployment scenario<br>پیش‌نیازها و سناریوی استقرار | `what-is-redis` — 1. What is Redis?<br>۱. Redis چیست؟ |
| 3 | `what-is-redis` — 1. What is Redis?<br>۱. Redis چیست؟ | `architecture` — 2. Redis in enterprise architecture<br>۲. کاربرد Redis در معماری Enterprise |
| 4 | `architecture` — 2. Redis in enterprise architecture<br>۲. کاربرد Redis در معماری Enterprise | `prerequisites` — Prerequisites and deployment scenario<br>پیش‌نیازها و سناریوی استقرار |
| 5 | `installation` — 3. Install Redis on Ubuntu<br>۳. نصب Redis روی Ubuntu | `replication-architecture` — 14. Redis replication architecture<br>۱۴. Redis Replication Architecture |
| 6 | `service-diagnostics` — 4. Check the Redis service<br>۴. بررسی سرویس Redis | `replication-is-not-ha` — 15. Replication alone is not high availability<br>۱۵. Replication به‌تنهایی High Availability نیست |
| 7 | `configuration` — 5. Configuration layout and backup<br>۵. ساختار Configuration و Backup پیش از تغییر | `cluster-comparison` — 17. Replication vs Sentinel vs Redis Cluster<br>۱۷. تفاوت Replication، Sentinel و Redis Cluster |
| 8 | `primary-configuration` — 6. Configure the Redis primary<br>۶. تنظیم Redis Primary | `configuration` — 3. Install Redis on Ubuntu<br>۳. نصب Redis روی Ubuntu |
| 9 | `security` — 7. Security hardening and network segmentation<br>۷. Security Hardening و تفکیک شبکه | `service-diagnostics` — 4. Check the Redis service<br>۴. بررسی سرویس Redis |
| 10 | `authentication-acl` — 8. Authentication using Redis ACL<br>۸. Authentication با Redis ACL | `configuration-files` — 5. Configuration layout and backup<br>۵. ساختار Configuration و Backup پیش از تغییر |
| 11 | `persistence` — 9. Persistence: RDB and AOF<br>۹. Persistence؛ RDB و AOF | `primary-configuration` — 6. Configure the Redis primary<br>۶. تنظیم Redis Primary |
| 12 | `replica-configuration` — 10. Configure the Redis replica<br>۱۰. راه‌اندازی Redis Replica | `security` — 7. Security hardening and network segmentation<br>۷. Security Hardening و تفکیک شبکه |
| 13 | `verify-replication` — 11. Verify replication status<br>۱۱. بررسی وضعیت Replication | `authentication-acl` — 8. Authentication using Redis ACL<br>۸. Authentication با Redis ACL |
| 14 | `replication-test` — 12. Practical replication test<br>۱۲. تست عملی Replication | `persistence` — 9. Persistence: RDB and AOF<br>۹. Persistence؛ RDB و AOF |
| 15 | `read-scaling` — 13. Read from replicas<br>۱۳. Read From Replica و Read Scaling | `replica-configuration` — 10. Configure the Redis replica<br>۱۰. راه‌اندازی Redis Replica |
| 16 | `replication-architecture` — 14. Redis replication architecture<br>۱۴. Redis Replication Architecture | `verify-replication` — 11. Verify replication status<br>۱۱. بررسی وضعیت Replication |
| 17 | `replication-is-not-ha` — 15. Replication alone is not high availability<br>۱۵. Replication به‌تنهایی High Availability نیست | `replication-test` — 12. Practical replication test<br>۱۲. تست عملی Replication |
| 18 | `sentinel` — 16. Redis Sentinel high availability<br>۱۶. Redis Sentinel و High Availability | `read-scaling` — 13. Read from replicas<br>۱۳. Read From Replica و Read Scaling |
| 19 | `cluster-comparison` — 17. Replication vs Sentinel vs Redis Cluster<br>۱۷. تفاوت Replication، Sentinel و Redis Cluster | `sentinel` — 16. Redis Sentinel high availability<br>۱۶. Redis Sentinel و High Availability |
| 20 | `memory-management` — 18. Memory management and eviction policy<br>۱۸. Memory Management و Eviction Policy | `monitoring` — 19. Monitoring and operational metrics<br>۱۹. Monitoring و Metricهای عملیاتی |
| 21 | `monitoring` — 19. Monitoring and operational metrics<br>۱۹. Monitoring و Metricهای عملیاتی | `prometheus-grafana` — 20. Prometheus, Grafana and alerting<br>۲۰. Prometheus، Grafana و Alerting |
| 22 | `prometheus-grafana` — 20. Prometheus, Grafana and alerting<br>۲۰. Prometheus، Grafana و Alerting | `troubleshooting` — 22. Production troubleshooting runbook<br>۲۲. Runbook عملیاتی Troubleshooting |
| 23 | `backup-restore` — 21. Backup and restore<br>۲۱. Backup و Restore | `backup-restore` — 21. Backup and restore<br>۲۱. Backup و Restore |
| 24 | `troubleshooting` — 22. Production troubleshooting runbook<br>۲۲. Runbook عملیاتی Troubleshooting | `memory-management` — 18. Memory management and eviction policy<br>۱۸. Memory Management و Eviction Policy |
| 25 | `best-practices` — 23. Production acceptance checklist<br>۲۳. Production Checklist و معیار پذیرش | `best-practices` — 23. Production acceptance checklist<br>۲۳. Production Checklist و معیار پذیرش |
| 26 | `conclusion` — Conclusion: release acceptance<br>جمع‌بندی؛ پذیرش استقرار | `conclusion` — Conclusion: release acceptance<br>جمع‌بندی؛ پذیرش استقرار |
| 27 | `faq` — Frequently asked questions<br>پرسش‌های متداول Redis در Production | `faq` — Frequently asked questions<br>پرسش‌های متداول Redis در Production |
| 28 | `official-references` — Official References and deployment templates<br>منابع رسمی و Templateهای استقرار | `official-references` — Official References and deployment templates<br>منابع رسمی و Templateهای استقرار |

### set-static-ip-ubuntu-server-netplan

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (YAML, renderer and resolver / YAML، Renderer و Resolver), 10 → 3; `configuration` (Write YAML and confirm netplan try / نوشتن YAML و پذیرش netplan try), 12 → 5; `enterprise-monitoring` (Tests after the change and reboot / آزمون پس از تغییر و Reboot), 5 → 6; `enterprise-recovery` (Restore YAML through the console / برگشت YAML از Console), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — Change the IP while preserving access<br>IP ثابت بدون قطع مدیریت | `introduction` — Change the IP while preserving access<br>IP ثابت بدون قطع مدیریت |
| 2 | `enterprise-architecture` — YAML, renderer and resolver<br>YAML، Renderer و Resolver | `prerequisites` — Interface and network-file ownership<br>Interface و مالک فایل شبکه |
| 3 | `enterprise-prerequisites` — Interface and network-file ownership<br>Interface و مالک فایل شبکه | `architecture` — YAML, renderer and resolver<br>YAML، Renderer و Resolver |
| 4 | `enterprise-installation` — Write YAML and confirm netplan try<br>نوشتن YAML و پذیرش netplan try | `security` — Restricted files and IPv6 paths<br>فایل محدود و مسیر IPv6 |
| 5 | `enterprise-monitoring` — Tests after the change and reboot<br>آزمون پس از تغییر و Reboot | `configuration` — Write YAML and confirm netplan try<br>نوشتن YAML و پذیرش netplan try |
| 6 | `enterprise-troubleshooting` — Valid YAML with broken networking<br>YAML سالم اما شبکه خراب | `enterprise-monitoring` — Tests after the change and reboot<br>آزمون پس از تغییر و Reboot |
| 7 | `enterprise-security` — Restricted files and IPv6 paths<br>فایل محدود و مسیر IPv6 | `troubleshooting` — Valid YAML with broken networking<br>YAML سالم اما شبکه خراب |
| 8 | `enterprise-recovery` — Restore YAML through the console<br>برگشت YAML از Console | `enterprise-recovery` — Restore YAML through the console<br>برگشت YAML از Console |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### sql-server-automatic-backup-job

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (Design schedules and recovery chains for Jira and Confluence / طراحی زمان‌بندی و زنجیره برای Jira و Confluence), 2 → 3; `security` (Encryption and certificate/private-key recovery / Encryption و بازیابی Certificate و Private Key), 11 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — Automated backups must lead to a working restore<br>Backup خودکار باید به Restore برسد | `introduction` — Automated backups must lead to a working restore<br>Backup خودکار باید به Restore برسد |
| 2 | `architecture` — Design schedules and recovery chains for Jira and Confluence<br>طراحی زمان‌بندی و زنجیره برای Jira و Confluence | `prerequisites` — Versions, service identities and implementation scope<br>نسخه‌ها، هویت سرویس و حدود اجرا |
| 3 | `prerequisites` — Versions, service identities and implementation scope<br>نسخه‌ها، هویت سرویس و حدود اجرا | `architecture` — Design schedules and recovery chains for Jira and Confluence<br>طراحی زمان‌بندی و زنجیره برای Jira و Confluence |
| 4 | `recovery-model` — Choose recovery models before enabling log jobs<br>انتخاب Recovery Model پیش از فعال‌کردن Log Job | `recovery-model` — Choose recovery models before enabling log jobs<br>انتخاب Recovery Model پیش از فعال‌کردن Log Job |
| 5 | `backup-folders` — Per-database paths and storage capacity<br>مسیر مستقل هر Database و ظرفیت Storage | `backup-folders` — Per-database paths and storage capacity<br>مسیر مستقل هر Database و ظرفیت Storage |
| 6 | `configuration` — Procedure, whitelist and folder provisioning<br>Procedure و آماده‌سازی Whitelist و پوشه‌ها | `configuration` — Procedure, whitelist and folder provisioning<br>Procedure و آماده‌سازی Whitelist و پوشه‌ها |
| 7 | `agent-jobs` — Build Agent jobs and test manually<br>ساخت Jobهای Agent و آزمون دستی | `agent-jobs` — Build Agent jobs and test manually<br>ساخت Jobهای Agent و آزمون دستی |
| 8 | `cleanup` — Cleanup while retaining the recovery-chain base<br>Cleanup با حفظ پایه زنجیره Retention | `cleanup` — Cleanup while retaining the recovery-chain base<br>Cleanup با حفظ پایه زنجیره Retention |
| 9 | `validation` — History, VERIFYONLY and an actual restore<br>History، VERIFYONLY و Restore واقعی | `security` — Encryption and certificate/private-key recovery<br>Encryption و بازیابی Certificate و Private Key |
| 10 | `troubleshooting` — Diagnose job failures from audit to storage<br>تشخیص خطای Job از Audit تا Storage | `validation` — History, VERIFYONLY and an actual restore<br>History، VERIFYONLY و Restore واقعی |
| 11 | `security` — Encryption and certificate/private-key recovery<br>Encryption و بازیابی Certificate و Private Key | `troubleshooting` — Diagnose job failures from audit to storage<br>تشخیص خطای Job از Audit تا Storage |
| 12 | `best-practices` — Freshness, alerts and independent backup copies<br>Freshness، Alert و نسخه مستقل Backup | `best-practices` — Freshness, alerts and independent backup copies<br>Freshness، Alert و نسخه مستقل Backup |
| 13 | `conclusion` — Conclusion<br>جمع‌بندی | `conclusion` — Conclusion<br>جمع‌بندی |
| 14 | `faq` — SQL Server Automated Backup FAQ<br>سوالات متداول دربارهٔ Backup خودکار SQL Server | `faq` — SQL Server Automated Backup FAQ<br>سوالات متداول دربارهٔ Backup خودکار SQL Server |
| 15 | `official-references` — Microsoft backup and restore references<br>منابع Backup و Restore در Microsoft | `official-references` — Microsoft backup and restore references<br>منابع Backup و Restore در Microsoft |

### truenas-zfs-enterprise

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: no change needed.

Moved blocks: `architecture` (5. ZFS architecture and key concepts / 5. معماری ZFS و مفاهیم کلیدی), 6 → 7; `security` (22. Security Hardening / ۲۲. سخت‌سازی امنیتی), 23 → 15; `section-26` (25. Practical example for a medium-sized company / 25. سناریوی واقعی شرکت متوسط), 26 → 20; `section-24` (23. Monitoring and alerting / 23. Monitoring و Alerting), 24 → 21; `troubleshooting` (26. Initial troubleshooting and diagnostic commands / 26. Troubleshooting اولیه و Commandهای Diagnostic), 27 → 23.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — مقدمه<br>مقدمه | `introduction` — Introduction<br>مقدمه |
| 2 | `section-2` — 1. TrueNAS چیست؟<br>1. TrueNAS چیست؟ | `section-2` — 1. What is TrueNAS?<br>1. TrueNAS چیست؟ |
| 3 | `section-3` — 2. NAS چیست و TrueNAS چه مشکلی را حل می‌کند؟<br>2. NAS چیست و TrueNAS چه مشکلی را حل می‌کند؟ | `section-3` — 2. What is NAS, and what problem does TrueNAS solve?<br>2. NAS چیست و TrueNAS چه مشکلی را حل می‌کند؟ |
| 4 | `section-4` — 3. کاربردهای TrueNAS در سازمان<br>3. کاربردهای TrueNAS در سازمان | `section-4` — 3. Enterprise uses for TrueNAS<br>3. کاربردهای TrueNAS در سازمان |
| 5 | `section-5` — 4. مزایای TrueNAS نسبت به File Server معمولی<br>4. مزایای TrueNAS نسبت به File Server معمولی | `section-5` — 4. TrueNAS advantages over a general file server<br>4. مزایای TrueNAS نسبت به File Server معمولی |
| 6 | `architecture` — 5. معماری ZFS و مفاهیم کلیدی<br>5. معماری ZFS و مفاهیم کلیدی | `prerequisites` — 6. Minimum and recommended production hardware<br>6. حداقل و Recommended Hardware برای Production |
| 7 | `prerequisites` — 6. حداقل و Recommended Hardware برای Production<br>6. حداقل و Recommended Hardware برای Production | `architecture` — 5. ZFS architecture and key concepts<br>5. معماری ZFS و مفاهیم کلیدی |
| 8 | `section-8` — 7. طراحی شبکه سازمانی<br>7. طراحی شبکه سازمانی | `section-8` — 7. Enterprise network design<br>7. طراحی شبکه سازمانی |
| 9 | `section-9` — 8. دانلود ISO و ساخت Bootable USB<br>8. دانلود ISO و ساخت Bootable USB | `section-9` — 8. Download the ISO and create bootable USB media<br>8. دانلود ISO و ساخت Bootable USB |
| 10 | `configuration` — 9. نصب TrueNAS روی Bare Metal<br>9. نصب TrueNAS روی Bare Metal | `configuration` — 9. Install TrueNAS on bare metal<br>9. نصب TrueNAS روی Bare Metal |
| 11 | `section-11` — 10. تنظیم Static IP، DNS، Gateway و NTP<br>10. تنظیم Static IP، DNS، Gateway و NTP | `section-11` — 10. Configure static IP, DNS, gateway and NTP<br>10. تنظیم Static IP، DNS، Gateway و NTP |
| 12 | `section-12` — 11. ساخت Storage Pool<br>11. ساخت Storage Pool | `section-12` — 11. Create a storage pool<br>11. ساخت Storage Pool |
| 13 | `section-13` — 12. ساخت Dataset<br>12. ساخت Dataset | `section-13` — 12. Create datasets<br>12. ساخت Dataset |
| 14 | `section-14` — 13. ایجاد User و Group<br>13. ایجاد User و Group | `section-14` — 13. Create users and groups<br>13. ایجاد User و Group |
| 15 | `section-15` — 14. راه‌اندازی SMB برای Windows<br>14. راه‌اندازی SMB برای Windows | `security` — 22. Security Hardening<br>۲۲. سخت‌سازی امنیتی |
| 16 | `section-16` — 15. Map Network Drive در Windows<br>15. Map Network Drive در Windows | `section-15` — 14. Configure SMB for Windows<br>14. راه‌اندازی SMB برای Windows |
| 17 | `section-17` — 16. راه‌اندازی NFS برای Linux و VMware<br>16. راه‌اندازی NFS برای Linux و VMware | `section-16` — 15. Map a network drive in Windows<br>15. Map Network Drive در Windows |
| 18 | `section-18` — 17. iSCSI برای Virtualization<br>17. iSCSI برای Virtualization | `section-17` — 16. Configure NFS for Linux and VMware<br>16. راه‌اندازی NFS برای Linux و VMware |
| 19 | `section-19` — 18. Snapshot خودکار<br>18. Snapshot خودکار | `section-18` — 17. iSCSI for virtualization<br>17. iSCSI برای Virtualization |
| 20 | `section-20` — 19. ZFS Replication به TrueNAS دوم<br>19. ZFS Replication به TrueNAS دوم | `section-26` — 25. Practical example for a medium-sized company<br>25. سناریوی واقعی شرکت متوسط |
| 21 | `section-21` — 20. SMART و Scrub<br>20. SMART و Scrub | `section-24` — 23. Monitoring and alerting<br>23. Monitoring و Alerting |
| 22 | `section-22` — 21. Backup از Configuration<br>21. Backup از Configuration | `section-25` — 24. Common mistakes<br>24. اشتباهات رایج |
| 23 | `security` — 22. Security Hardening<br>22. Security Hardening | `troubleshooting` — 26. Initial troubleshooting and diagnostic commands<br>26. Troubleshooting اولیه و Commandهای Diagnostic |
| 24 | `section-24` — 23. Monitoring و Alerting<br>23. Monitoring و Alerting | `section-19` — 18. Automated snapshots<br>18. Snapshot خودکار |
| 25 | `best-practices` — 24. اشتباهات رایج<br>24. اشتباهات رایج | `section-20` — 19. ZFS replication to a second TrueNAS server<br>19. ZFS Replication به TrueNAS دوم |
| 26 | `section-26` — 25. سناریوی واقعی شرکت متوسط<br>25. سناریوی واقعی شرکت متوسط | `section-21` — 20. SMART and scrub<br>20. SMART و Scrub |
| 27 | `troubleshooting` — 26. Troubleshooting اولیه و Commandهای Diagnostic<br>26. Troubleshooting اولیه و Commandهای Diagnostic | `section-22` — 21. Configuration backup<br>21. Backup از Configuration |
| 28 | `section-28` — 27. Checklist نهایی Production Deployment<br>27. Checklist نهایی Production Deployment | `best-practices` — 27. Final production deployment checklist<br>27. Checklist نهایی Production Deployment |
| 29 | `conclusion` — چه زمانی TrueNAS انتخاب مناسبی است و چه زمانی نیست؟<br>چه زمانی TrueNAS انتخاب مناسبی است و چه زمانی نیست؟ | `conclusion` — When is TrueNAS a suitable choice, and when is it not?<br>چه زمانی TrueNAS انتخاب مناسبی است و چه زمانی نیست؟ |
| 30 | `faq` — پرسش‌های متداول TrueNAS<br>پرسش‌های متداول TrueNAS | `faq` — TrueNAS frequently asked questions<br>پرسش‌های متداول TrueNAS |
| 31 | `official-references` — منابع رسمی<br>منابع رسمی | `official-references` — Official References<br>منابع رسمی |

### ubiquiti-unifi-wireless-mesh-network

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `introduction` (Introduction / مقدمه), 21 → 1; `architecture` (3. Parent AP and Mesh AP Architecture / ۳. معماری Parent AP و Mesh AP), 3 → 5; `rf-backhaul` (11. RF Design and 5 GHz Backhaul / ۱۱. طراحی RF و Backhaul روی 5GHz), 11 → 6; `channels` (12. Channel Planning / ۱۲. Channel Planning), 12 → 7; `signal` (13. RSSI and Signal Strength / ۱۳. RSSI و Signal Strength), 13 → 8; `enterprise-design` (Enterprise Design Recommendations / توصیه‌های طراحی سازمانی — Enterprise Design Recommendations), 18 → 9; `configuration` (8. Configure the Wired Mesh Parent / ۸. تنظیم Mesh Parent روی AP کابلی), 22 → 13; `wireless-uplink` (10. Verify Wireless Uplink / ۱۰. بررسی Wireless Uplink), 10 → 17; `security` (Security Considerations / ملاحظات امنیتی), 23 → 19.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `wireless-mesh` — 1. What Is Wireless Mesh in UniFi?<br>۱. Wireless Mesh در UniFi چیست؟ | `introduction` — Introduction<br>مقدمه |
| 2 | `when-to-mesh` — 2. When to Use Mesh and When to Use Ethernet<br>۲. Mesh چه زمانی مناسب است و چه زمانی Ethernet لازم است؟ | `wireless-mesh` — 1. What Is Wireless Mesh in UniFi?<br>۱. Wireless Mesh در UniFi چیست؟ |
| 3 | `architecture` — 3. Parent AP and Mesh AP Architecture<br>۳. معماری Parent AP و Mesh AP | `when-to-mesh` — 2. When to Use Mesh and When to Use Ethernet<br>۲. Mesh چه زمانی مناسب است و چه زمانی Ethernet لازم است؟ |
| 4 | `prerequisites` — 4. Prerequisites<br>۴. پیش‌نیازها | `prerequisites` — 4. Prerequisites<br>۴. پیش‌نیازها |
| 5 | `adoption` — 5. Adopt the Devices<br>۵. Adoption تجهیزات | `architecture` — 3. Parent AP and Mesh AP Architecture<br>۳. معماری Parent AP و Mesh AP |
| 6 | `ssid` — 6. Create the Shared SSID<br>۶. ایجاد SSID مشترک | `rf-backhaul` — 11. RF Design and 5 GHz Backhaul<br>۱۱. طراحی RF و Backhaul روی 5GHz |
| 7 | `enable-meshing` — 7. Enable Wireless Meshing<br>۷. فعال‌سازی Wireless Meshing | `channels` — 12. Channel Planning<br>۱۲. Channel Planning |
| 8 | `mesh-parent` — 8. Configure the Wired Mesh Parent<br>۸. تنظیم Mesh Parent روی AP کابلی | `signal` — 13. RSSI and Signal Strength<br>۱۳. RSSI و Signal Strength |
| 9 | `mesh-connect` — 9. Configure Mesh Connect on Wireless APs<br>۹. تنظیم Mesh Connect روی APهای Wireless | `enterprise-design` — Enterprise Design Recommendations<br>توصیه‌های طراحی سازمانی — Enterprise Design Recommendations |
| 10 | `wireless-uplink` — 10. Verify Wireless Uplink<br>۱۰. بررسی Wireless Uplink | `adoption` — 5. Adopt the Devices<br>۵. Adoption تجهیزات |
| 11 | `rf-backhaul` — 11. RF Design and 5 GHz Backhaul<br>۱۱. طراحی RF و Backhaul روی 5GHz | `ssid` — 6. Create the Shared SSID<br>۶. ایجاد SSID مشترک |
| 12 | `channels` — 12. Channel Planning<br>۱۲. Channel Planning | `enable-meshing` — 7. Enable Wireless Meshing<br>۷. فعال‌سازی Wireless Meshing |
| 13 | `signal` — 13. RSSI and Signal Strength<br>۱۳. RSSI و Signal Strength | `configuration` — 8. Configure the Wired Mesh Parent<br>۸. تنظیم Mesh Parent روی AP کابلی |
| 14 | `vlans` — 14. VLANs Across the Mesh<br>۱۴. VLAN در شبکه Mesh | `mesh-connect` — 9. Configure Mesh Connect on Wireless APs<br>۹. تنظیم Mesh Connect روی APهای Wireless |
| 15 | `roaming` — 15. Client Roaming Between APs<br>۱۵. Roaming کاربران بین APها | `vlans` — 14. VLANs Across the Mesh<br>۱۴. VLAN در شبکه Mesh |
| 16 | `performance` — 16. Performance Testing and Acceptance<br>۱۶. Performance Testing و معیار پذیرش | `roaming` — 15. Client Roaming Between APs<br>۱۵. Roaming کاربران بین APها |
| 17 | `troubleshooting` — 17. Troubleshooting<br>۱۷. عیب‌یابی | `wireless-uplink` — 10. Verify Wireless Uplink<br>۱۰. بررسی Wireless Uplink |
| 18 | `enterprise-design` — Enterprise Design Recommendations<br>توصیه‌های طراحی سازمانی — Enterprise Design Recommendations | `performance` — 16. Performance Testing and Acceptance<br>۱۶. Performance Testing و معیار پذیرش |
| 19 | `troubleshooting-checklist` — Troubleshooting Checklist<br>چک‌لیست عیب‌یابی | `security` — Security Considerations<br>ملاحظات امنیتی |
| 20 | `best-practices` — Best Practices Checklist<br>چک‌لیست Best Practiceها | `troubleshooting` — 17. Troubleshooting<br>۱۷. عیب‌یابی |
| 21 | `introduction` — Introduction<br>مقدمه | `troubleshooting-checklist` — Troubleshooting Checklist<br>چک‌لیست عیب‌یابی |
| 22 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `best-practices` — Best Practices Checklist<br>چک‌لیست Best Practiceها |
| 23 | `security` — Security Considerations<br>ملاحظات امنیتی | `conclusion` — Conclusion<br>نتیجه‌گیری |
| 24 | `conclusion` — Conclusion<br>نتیجه‌گیری | `faq` — FAQ<br>پرسش‌های متداول — FAQ |
| 25 | `faq` — FAQ<br>پرسش‌های متداول — FAQ | `official-references` — Official References<br>منابع رسمی |
| 26 | `official-references` — Official References<br>منابع رسمی | — |

### ubuntu-date-time-settings

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (Sources, kernel clock and applications / Source، Kernel Clock و برنامه), 9 → 3; `configuration` (Configure sources and verify synchronization / تنظیم Source و بررسی Sync), 11 → 5; `enterprise-monitoring` (Offsets and the selected source / Offset و Source منتخب), 5 → 6; `enterprise-recovery` (Restore sources before sensitive workloads / بازگشت Source و شروع Workload), 7 → 8; `best-practices` (Best Practices / Best Practiceها), 12 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — Clock time and timezone are different<br>ساعت واقعی با Timezone فرق دارد | `introduction` — Clock time and timezone are different<br>ساعت واقعی با Timezone فرق دارد |
| 2 | `enterprise-prerequisites` — Time daemon and NTP reachability<br>Daemon زمان و مسیر NTP | `prerequisites` — Time daemon and NTP reachability<br>Daemon زمان و مسیر NTP |
| 3 | `enterprise-security` — Restrict NTP and review clock steps<br>NTP محدود و سیاست Step | `architecture` — Sources, kernel clock and applications<br>Source، Kernel Clock و برنامه |
| 4 | `enterprise-installation` — Configure sources and verify synchronization<br>تنظیم Source و بررسی Sync | `security` — Restrict NTP and review clock steps<br>NTP محدود و سیاست Step |
| 5 | `enterprise-monitoring` — Offsets and the selected source<br>Offset و Source منتخب | `configuration` — Configure sources and verify synchronization<br>تنظیم Source و بررسی Sync |
| 6 | `enterprise-troubleshooting` — Zero reach or clock jumps<br>Reach صفر یا جهش ساعت | `enterprise-monitoring` — Offsets and the selected source<br>Offset و Source منتخب |
| 7 | `enterprise-recovery` — Restore sources before sensitive workloads<br>بازگشت Source و شروع Workload | `troubleshooting` — Zero reach or clock jumps<br>Reach صفر یا جهش ساعت |
| 8 | `introduction` — Introduction<br>مقدمه | `enterprise-recovery` — Restore sources before sensitive workloads<br>بازگشت Source و شروع Workload |
| 9 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `best-practices` — Best Practices<br>Best Practiceها | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 14 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 15 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 16 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 17 | `official-references` — Sources, kernel clock and applications<br>Source، Kernel Clock و برنامه | — |

### vmware-esxi-8-installation-basic-configuration

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (Boot, management and datastore paths / Boot، Management و Datastore), 10 → 3; `configuration` (Install the image and configure DCUI / نصب Image و تنظیم DCUI), 8 → 5; `enterprise-monitoring` (Host and storage health / سلامت Host و Storage), 6 → 6; `best-practices` (Best Practices / Best Practiceها), 12 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — From installation to host acceptance<br>از نصب ESXi تا پذیرش Host | `introduction` — From installation to host acceptance<br>از نصب ESXi تا پذیرش Host |
| 2 | `enterprise-architecture` — Boot, management and datastore paths<br>Boot، Management و Datastore | `prerequisites` — Compatibility, image and installation disk<br>HCL، Image و دیسک نصب |
| 3 | `enterprise-prerequisites` — Compatibility, image and installation disk<br>HCL، Image و دیسک نصب | `architecture` — Boot, management and datastore paths<br>Boot، Management و Datastore |
| 4 | `enterprise-installation` — Install the image and configure DCUI<br>نصب Image و تنظیم DCUI | `security` — Restricted management and temporary SSH<br>مدیریت محدود و SSH موقت |
| 5 | `enterprise-security` — Restricted management and temporary SSH<br>مدیریت محدود و SSH موقت | `configuration` — Install the image and configure DCUI<br>نصب Image و تنظیم DCUI |
| 6 | `enterprise-monitoring` — Host and storage health<br>سلامت Host و Storage | `enterprise-monitoring` — Host and storage health<br>سلامت Host و Storage |
| 7 | `enterprise-troubleshooting` — Lost management or datastore access<br>Management یا Datastore قطع است | `troubleshooting` — Lost management or datastore access<br>Management یا Datastore قطع است |
| 8 | `configuration` — Configuration bundles and VM backups<br>Config Bundle و Backup ماشین‌ها | `enterprise-recovery` — Configuration bundles and VM backups<br>Config Bundle و Backup ماشین‌ها |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `best-practices` — Best Practices<br>Best Practiceها | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 14 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 15 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 16 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 17 | `official-references` — Official Sources<br>منابع رسمی | — |

### vsphere-standard-switch-vs-distributed-switch

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `introduction` (The issue: consistent policies across hosts / مسئله اصلی: یکسان‌بودن Policy بین Hostها), 13 → 1; `prerequisites` (Requirements for migration / شرایط لازم فقط برای مهاجرت), 15 → 5; `architecture` (What remains during a vCenter outage? / در قطعی vCenter چه چیزی باقی می‌ماند؟), 14 → 6; `port-groups-and-uplinks` (Port Groups, Uplinks and VMkernel / Port Group، Uplink و VMkernel), 4 → 7; `configuration` (Migrate one canary uplink at a time / مهاجرت Host آزمایشی، یک Uplink در هر مرحله), 16 → 8; `troubleshooting` (VM, host or MTU failure? / مشکل VM، Host یا MTU؟), 18 → 11.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — The issue: consistent policies across hosts<br>مسئله اصلی: یکسان‌بودن Policy بین Hostها | `introduction` — The issue: consistent policies across hosts<br>مسئله اصلی: یکسان‌بودن Policy بین Hostها |
| 2 | `switch-types` — How vSS and vDS Work<br>vSS و vDS چگونه کار می‌کنند؟ | `switch-types` — How vSS and vDS Work<br>vSS و vDS چگونه کار می‌کنند؟ |
| 3 | `switch-comparison` — vSS vs vDS Comparison<br>جدول مقایسه vSS و vDS | `switch-comparison` — vSS vs vDS Comparison<br>جدول مقایسه vSS و vDS |
| 4 | `port-groups-and-uplinks` — Port Groups, Uplinks and VMkernel<br>Port Group، Uplink و VMkernel | `switch-selection` — When to Choose vSS or vDS<br>چه زمانی vSS یا vDS انتخاب کنیم؟ |
| 5 | `switch-selection` — When to Choose vSS or vDS<br>چه زمانی vSS یا vDS انتخاب کنیم؟ | `prerequisites` — Requirements for migration<br>شرایط لازم فقط برای مهاجرت |
| 6 | `enterprise-architecture` — What remains during a vCenter outage?<br>در قطعی vCenter چه چیزی باقی می‌ماند؟ | `architecture` — What remains during a vCenter outage?<br>در قطعی vCenter چه چیزی باقی می‌ماند؟ |
| 7 | `enterprise-prerequisites` — Requirements for migration<br>شرایط لازم فقط برای مهاجرت | `port-groups-and-uplinks` — Port Groups, Uplinks and VMkernel<br>Port Group، Uplink و VMkernel |
| 8 | `enterprise-installation` — Migrate one canary uplink at a time<br>مهاجرت Host آزمایشی، یک Uplink در هر مرحله | `configuration` — Migrate one canary uplink at a time<br>مهاجرت Host آزمایشی، یک Uplink در هر مرحله |
| 9 | `security` — Port-group security policy<br>Security Policy روی Port Group | `security` — Port-group security policy<br>Security Policy روی Port Group |
| 10 | `enterprise-monitoring` — Host disconnects and drift<br>Host Disconnect و Drift | `enterprise-monitoring` — Host disconnects and drift<br>Host Disconnect و Drift |
| 11 | `enterprise-troubleshooting` — VM, host or MTU failure?<br>مشکل VM، Host یا MTU؟ | `troubleshooting` — VM, host or MTU failure?<br>مشکل VM، Host یا MTU؟ |
| 12 | `enterprise-recovery` — Network recovery without vCenter<br>بازیابی شبکه بدون vCenter | `enterprise-recovery` — Network recovery without vCenter<br>بازیابی شبکه بدون vCenter |
| 13 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 14 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 15 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 16 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 17 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 18 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 19 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 20 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 21 | `official-references` — Official Sources<br>منابع رسمی | — |

### windows-cmd-common-network-commands

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (Investigation through share authorization / مسیر تشخیص تا مجوز Share), 10 → 3; `configuration` (Address, route and port commands / فرمان‌های آدرس، Route و Port), 12 → 5; `enterprise-monitoring` (Reachability differs from service function / قابل دسترس بودن با کارکرد سرویس فرق دارد), 6 → 6; `enterprise-recovery` (Limited corrections and retesting / تغییر محدود و آزمون دوباره), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — Ping, DNS and TCP establish different facts<br>Ping، DNS و TCP نتایج جدا دارند | `introduction` — Ping, DNS and TCP establish different facts<br>Ping، DNS و TCP نتایج جدا دارند |
| 2 | `enterprise-architecture` — Investigation through share authorization<br>مسیر تشخیص تا مجوز Share | `prerequisites` — CMD and PowerShell on the affected host<br>CMD و PowerShell روی میزبان واقعی |
| 3 | `enterprise-prerequisites` — CMD and PowerShell on the affected host<br>CMD و PowerShell روی میزبان واقعی | `architecture` — Investigation through share authorization<br>مسیر تشخیص تا مجوز Share |
| 4 | `enterprise-installation` — Address, route and port commands<br>فرمان‌های آدرس، Route و Port | `security` — Protect diagnostic reports<br>حفاظت از گزارش تشخیص |
| 5 | `enterprise-troubleshooting` — DNS timeout, closed port or access denied<br>DNS Timeout، Port بسته یا Access Denied | `configuration` — Address, route and port commands<br>فرمان‌های آدرس، Route و Port |
| 6 | `enterprise-monitoring` — Reachability differs from service function<br>قابل دسترس بودن با کارکرد سرویس فرق دارد | `enterprise-monitoring` — Reachability differs from service function<br>قابل دسترس بودن با کارکرد سرویس فرق دارد |
| 7 | `enterprise-security` — Protect diagnostic reports<br>حفاظت از گزارش تشخیص | `troubleshooting` — DNS timeout, closed port or access denied<br>DNS Timeout، Port بسته یا Access Denied |
| 8 | `enterprise-recovery` — Limited corrections and retesting<br>تغییر محدود و آزمون دوباره | `enterprise-recovery` — Limited corrections and retesting<br>تغییر محدود و آزمون دوباره |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### windows-hardware-info-cmd-vs-dxdiag

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `inventory-tool-comparison` (Which Tool Fits the Investigation? / کدام ابزار برای کدام بررسی؟), 2 → 2; `architecture` (Providers and inventory snapshots / Provider و Snapshot Inventory), 11 → 4; `enterprise-monitoring` (Inventory differs from health checks / Inventory با Health Check فرق دارد), 7 → 7; `enterprise-recovery` (Retain snapshots and recovery records / نگهداری Snapshot و اطلاعات بازیابی), 9 → 9; `best-practices` (Best Practices / Best Practiceها), 14 → 10.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — Choose the tool for the question<br>انتخاب ابزار بر اساس سؤال | `introduction` — Choose the tool for the question<br>انتخاب ابزار بر اساس سؤال |
| 2 | `inventory-tool-comparison` — Which Tool Fits the Investigation?<br>کدام ابزار برای کدام بررسی؟ | `inventory-tool-comparison` — Which Tool Fits the Investigation?<br>کدام ابزار برای کدام بررسی؟ |
| 3 | `enterprise-prerequisites` — Modules and read access<br>ماژول و دسترسی خواندن | `prerequisites` — Modules and read access<br>ماژول و دسترسی خواندن |
| 4 | `enterprise-architecture` — Providers and inventory snapshots<br>Provider و Snapshot Inventory | `architecture` — Providers and inventory snapshots<br>Provider و Snapshot Inventory |
| 5 | `enterprise-installation` — Collect JSON and a DxDiag report<br>جمع‌آوری JSON و گزارش DxDiag | `configuration` — Collect JSON and a DxDiag report<br>جمع‌آوری JSON و گزارش DxDiag |
| 6 | `enterprise-troubleshooting` — Null values and RAID abstractions<br>مقدار Null و دیسک پشت RAID | `security` — Reports and remote sessions<br>گزارش و Remote Session |
| 7 | `enterprise-monitoring` — Inventory differs from health checks<br>Inventory با Health Check فرق دارد | `enterprise-monitoring` — Inventory differs from health checks<br>Inventory با Health Check فرق دارد |
| 8 | `enterprise-security` — Reports and remote sessions<br>گزارش و Remote Session | `troubleshooting` — Null values and RAID abstractions<br>مقدار Null و دیسک پشت RAID |
| 9 | `enterprise-recovery` — Retain snapshots and recovery records<br>نگهداری Snapshot و اطلاعات بازیابی | `enterprise-recovery` — Retain snapshots and recovery records<br>نگهداری Snapshot و اطلاعات بازیابی |
| 10 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 11 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 12 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 13 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 14 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 15 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 16 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 17 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 18 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 19 | `official-references` — Official Sources<br>منابع رسمی | — |

### windows-password-reset-secure-access-recovery

Source file changed: yes. Editorial labels removed: 0. Source TOC reordered: yes.

Moved blocks: `architecture` (Reset, password change and unlock / Reset در برابر تغییر Password و Unlock), 10 → 3; `configuration` (Reset an AD or local account with PowerShell / Reset حساب AD یا Local با PowerShell), 12 → 5; `enterprise-monitoring` (Audit resets and lockouts / Audit Reset و Lockout), 7 → 6; `enterprise-recovery` (Secure secret delivery and independent keys / تحویل امن Secret و کلیدهای مستقل), 8 → 8; `best-practices` (Best Practices / Best Practiceها), 13 → 9.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `enterprise-intro` — Identify the account before resetting<br>پیش از Reset، نوع حساب را مشخص کنید | `introduction` — Identify the account before resetting<br>پیش از Reset، نوع حساب را مشخص کنید |
| 2 | `enterprise-architecture` — Reset, password change and unlock<br>Reset در برابر تغییر Password و Unlock | `prerequisites` — Permissions and account-specific modules<br>مجوز و ماژول نوع حساب |
| 3 | `enterprise-prerequisites` — Permissions and account-specific modules<br>مجوز و ماژول نوع حساب | `architecture` — Reset, password change and unlock<br>Reset در برابر تغییر Password و Unlock |
| 4 | `enterprise-security` — EFS, DPAPI and service accounts<br>EFS، DPAPI و حساب سرویس | `security` — EFS, DPAPI and service accounts<br>EFS، DPAPI و حساب سرویس |
| 5 | `enterprise-installation` — Reset an AD or local account with PowerShell<br>Reset حساب AD یا Local با PowerShell | `configuration` — Reset an AD or local account with PowerShell<br>Reset حساب AD یا Local با PowerShell |
| 6 | `enterprise-troubleshooting` — Recurring lockout or failed login<br>Lockout دوباره یا Login ناموفق | `enterprise-monitoring` — Audit resets and lockouts<br>Audit Reset و Lockout |
| 7 | `enterprise-monitoring` — Audit resets and lockouts<br>Audit Reset و Lockout | `troubleshooting` — Recurring lockout or failed login<br>Lockout دوباره یا Login ناموفق |
| 8 | `enterprise-recovery` — Secure secret delivery and independent keys<br>تحویل امن Secret و کلیدهای مستقل | `enterprise-recovery` — Secure secret delivery and independent keys<br>تحویل امن Secret و کلیدهای مستقل |
| 9 | `introduction` — Introduction<br>مقدمه | `best-practices` — Best Practices<br>Best Practiceها |
| 10 | `architecture` — Architecture and Core Concepts<br>معماری و مفاهیم اصلی | `conclusion` — Conclusion<br>جمع‌بندی |
| 11 | `prerequisites` — Prerequisites<br>پیش‌نیازها | `faq` — Frequently Asked Questions<br>پرسش‌های متداول |
| 12 | `configuration` — Configuration and Validation<br>Configuration و اعتبارسنجی | `official-references` — Official Sources<br>منابع رسمی |
| 13 | `best-practices` — Best Practices<br>Best Practiceها | — |
| 14 | `security` — Security Considerations<br>ملاحظات امنیتی | — |
| 15 | `troubleshooting` — Troubleshooting<br>عیب‌یابی | — |
| 16 | `conclusion` — Conclusion<br>جمع‌بندی | — |
| 17 | `faq` — Frequently Asked Questions<br>پرسش‌های متداول | — |
| 18 | `official-references` — Official Sources<br>منابع رسمی | — |

### zabbix-server-linux-windows-agents-backup

Source file changed: yes. Editorial labels removed: 4. Source TOC reordered: yes.

Moved blocks: `architecture` (1. Enterprise Zabbix architecture / ۱. معماری سازمانی Zabbix), 2 → 3; `security` (8. Security hardening / ۸. امن‌سازی), 9 → 7; `troubleshooting` (12. Production troubleshooting / ۱۲. عیب‌یابی Production), 13 → 10.

| Position | Before (EN / FA) | After (EN / FA) |
| ---: | --- | --- |
| 1 | `introduction` — Introduction and deployment scope<br>مقدمه و محدوده استقرار | `introduction` — Introduction and deployment scope<br>مقدمه و محدوده استقرار |
| 2 | `architecture` — 1. Enterprise Zabbix architecture<br>۱. معماری سازمانی Zabbix | `prerequisites` — 2. Server preparation and prerequisites<br>۲. آماده‌سازی و پیش‌نیاز سرور |
| 3 | `prerequisites` — 2. Server preparation and prerequisites<br>۲. آماده‌سازی و پیش‌نیاز سرور | `architecture` — 1. Enterprise Zabbix architecture<br>۱. معماری سازمانی Zabbix |
| 4 | `configuration` — 3. Installation: Server, PostgreSQL and the frontend<br>۳. نصب Server، PostgreSQL و Frontend | `configuration` — 3. Installation: Server, PostgreSQL and the frontend<br>۳. نصب Server، PostgreSQL و Frontend |
| 5 | `linux-agent` — 4. Install Agent 2 on Ubuntu Linux<br>۴. نصب Agent 2 روی Ubuntu Linux | `linux-agent` — 4. Install Agent 2 on Ubuntu Linux<br>۴. نصب Agent 2 روی Ubuntu Linux |
| 6 | `windows-agent` — 5. Install Agent 2 on Windows Server<br>۵. نصب Agent 2 روی Windows Server | `windows-agent` — 5. Install Agent 2 on Windows Server<br>۵. نصب Agent 2 روی Windows Server |
| 7 | `monitoring` — 6. Practical monitoring and triggers<br>۶. مانیتورینگ و Trigger عملی | `security` — 8. Security hardening<br>۸. امن‌سازی |
| 8 | `notifications` — 7. Alerts and notifications<br>۷. هشدار و اعلان | `monitoring` — 6. Practical monitoring and triggers<br>۶. مانیتورینگ و Trigger عملی |
| 9 | `security` — 8. Security hardening<br>۸. امن‌سازی | `notifications` — 7. Alerts and notifications<br>۷. هشدار و اعلان |
| 10 | `database-backup` — 9. Database and configuration backup<br>۹. بکاپ Database و Configuration | `troubleshooting` — 12. Production troubleshooting<br>۱۲. عیب‌یابی Production |
| 11 | `automated-backup` — 10. Automated daily backup<br>۱۰. بکاپ روزانه خودکار | `database-backup` — 9. Database and configuration backup<br>۹. بکاپ Database و Configuration |
| 12 | `recovery` — 11. Restore and disaster recovery<br>۱۱. Restore و بازیابی بحران | `automated-backup` — 10. Automated daily backup<br>۱۰. بکاپ روزانه خودکار |
| 13 | `troubleshooting` — 12. Production troubleshooting<br>۱۲. عیب‌یابی Production | `recovery` — 11. Restore and disaster recovery<br>۱۱. Restore و بازیابی بحران |
| 14 | `best-practices` — 13. Production checklist<br>۱۳. چک‌لیست Production | `best-practices` — 13. Production checklist<br>۱۳. چک‌لیست Production |
| 15 | `conclusion` — Operational handover and runtime acceptance<br>تحویل عملیاتی و پذیرش Runtime | `conclusion` — Operational handover and runtime acceptance<br>تحویل عملیاتی و پذیرش Runtime |
| 16 | `faq` — Frequently asked questions<br>پرسش‌های متداول | `faq` — Frequently asked questions<br>پرسش‌های متداول |
| 17 | `official-references` — Official References, downloads and related articles<br>منابع رسمی، دانلود و مقاله مرتبط | `official-references` — Official References, downloads and related articles<br>منابع رسمی، دانلود و مقاله مرتبط |

Machine-readable per-slug order, moves, integrity counts and browser/build evidence: [JSON audit](article-structure-audit-2026-10-09.json). Maintenance commands and ownership: [article structure guide](../current/ARTICLE-STRUCTURE.md).
