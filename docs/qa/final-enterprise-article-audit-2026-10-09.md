# Final enterprise article structure and numbering audit — 2026-10-09

Repository: `MEET-AJ-PORTFOLIO`. Work performed directly in the existing checkout. No deployment, commit, push or production database access. This report measures changes against the completed previous structure/prose audit, not against an earlier Git commit.

## Summary

| Measure | Result |
|---|---:|
| repository articles discovered | 41 |
| repository articles audited | 41 |
| language editions audited | 82 |
| corrected articles | 9 |
| source articles corrected | 8 |
| rendering only articles corrected | 1 |
| h2 headings renumbered | 93 |
| language heading labels renumbered | 186 |
| sections reordered | 13 |
| duplicate sections removed | 0 |
| broken fragment targets fixed | 0 |
| incorrect toc targets fixed | 1 |
| bilingual content inconsistencies fixed | 0 |
| editorial review labels removed this audit | 0 |
| real cms records audited | 0 |
| source toc entries renumbered | 79 |

Eight authoritative HTML articles and their available Markdown/generator paths were corrected. SQL Server has an additional rendering-only TOC correction, making nine affected articles. All 41 sources and 82 rendered language editions were inspected. The 93 main-heading changes represent 186 corresponding EN/FA labels. Intentionally unnumbered headings remain unnumbered. The 13 section movements count the explicitly moved complete sections, rather than every shifted index.

No duplicate section, missing translation or review-date label remained in the baseline collection. Accordingly, none was removed/added in this follow-up; the earlier audit’s 20 editorial label removals remain intact. Software dates, timestamps and structured SEO dates were retained.

All 593 source code blocks remain present. 592 retain identical executable text. One stale Zabbix HTML code copy was synchronized with the already-correct package script, downloads and both Markdown editions: its archive path list now includes `zabbix-backup-failure.service`. No executable download or authoritative script was changed. IDs, image URLs, download links, head metadata and JSON-LD were preserved in every article.

## Architecture and permanent corrections

The source inventory remains `resources/legacy/articles/*.html`; content packages are inputs/copies, not additional article records. `ArticleStructure` and `scripts/article_structure.py` extend the existing shared JSON policy. They order complete sections, renumber only integer-prefixed H2s, update explicit local section/chapter references (including Persian ranges/conjunctions), and synchronize source TOC numbers. Code, nested heading numbering, lists, versions and IP addresses are protected. Raw-source/stored-number validators run independently of normalization.

TrueNAS historical IDs now use a semantic heading map instead of visible heading numbers. Markdown matching ignores old number prefixes. The content-only migration repairs all stored publication states without reimporting over CMS edits or changing timestamps/SEO fields. Explicit source updates also preserve creation/update/publication timestamps and state. The same policy remains in use for importers, builders and rendering; no independent section-ordering engine or template/schema was added.

The Blade TOC is generated after removing embedded legacy footers. Previously SQL Server’s `author` section could capture a later H2 and produce a spurious author navigation entry. The existing external author card and fragment remain available. Final TOC labels must equal their target H2 text in both locales.

## Validation results

| Check | Final result |
|---|---|
| phpunit full | passed; tests=182, assertions=50302, errors=0, failures=0, skipped=1 |
| phpunit final article regressions | passed; tests=11, assertions=18152, errors=0, failures=0, skipped=0 |
| phpunit ordering and localization | passed; tests=14, assertions=29077, errors=0, failures=0, skipped=0 |
| frontend | passed; tests=6, failures=0 |
| python source regressions | passed; tests=3, failures=0 |
| source structure | passed; articles=41, issues=0 |
| rendered structure anchors localization | passed; editions=82, issues=0 |
| browser | passed; cases=36, issues=0, articles=9 |
| regeneration | passed; writers=13, runs_per_writer=2 |
| images | passed; source_images=117, references=354, errors=0 |
| explicit section references | passed;  |
| documentation | passed;  |
| repository security and layout | passed; errors=0 |
| diff whitespace | passed;  |
| cms read only synthetic coverage | passed; articles=44, editions=88, database_byte_identical=True |
| actual cms | unavailable;  |
| frontend build | not_applicable;  |
| production deployment | unexecuted;  |

PHPUnit ran all existing Feature tests with the project isolation settings, portable PHP 8.5/GD and an in-memory SQLite database. A local wrapper bypassed the Windows sandbox’s bootstrap readability check; it did not alter test discovery. The sole skipped MySQL test requires an actual MySQL test connection. Exact skip name and timing are in the JSON report. Frontend: `node --test tests/frontend/*.test.cjs`. Python: `python tests/test_article_structure.py`. Structure: `python scripts/audit-article-structure.py`. Rendered audit: `php scripts/audit-article-content.php enterprise-final --isolated`. Regeneration: `python scripts/check-article-regeneration.py --php .runtime/php85/php.exe`.

The regeneration check runs all 13 writers/loaders twice in an ignored copy, compares headings, executable text, explicit section references and image URLs with maintained sources, and verifies repeat generation/normalization stability. Thirty changed bilingual prose attributes were independently compared with their expected section-reference mappings; all matched. Markdown normalization was also checked independently for every maintained Markdown edition. Browser checks used actual exported Blade HTML, Chrome/Playwright and local public assets. All nine affected articles were tested in EN/FA at desktop/mobile sizes, including TOC target/label/click behavior, numbering, RTL, LTR code, viewport containment, image/download requests, canonical URLs and language switching. Three representative screenshots were additionally inspected visually.

## CMS coverage and remaining limitations

The configured local SQLite file is absent. The real CMS audit reports `unavailable`, zero inspected records and exit code 2. Actual CMS-only drafts/scheduled records therefore remain unaudited. The read-only command queries every state, uses a database-enforced read-only transaction and requires explicit operator opt-in for remote connections. The CMS supports draft/published; scheduled records are published with a future date. Archived is not currently supported.

Read-only behavior was tested on a disposable local fixture containing 41 repository articles plus three synthetic CMS records: one draft, one published and one scheduled. All 44 records / 88 editions were included and the SQLite file remained byte-identical. The deliberately incomplete synthetic localization metadata produced three Persian findings and exit 1, as expected. These fixtures do not establish coverage of actual unavailable CMS records.

Infrastructure commands were not executed against real Redis, TrueNAS, Zabbix or other systems. MySQL-specific behavior remains skipped. No production database migration, deployment, commit or push was performed. No repository content/numbering/anchor issue remains in the final validation. Applying the migration and auditing actual CMS-only records requires the operator-controlled database to become available.

## Per-article before and after

Source outlines below include stable IDs. Nginx’s existing SEO authoring appendix is retained in its source; those three metadata sections are omitted by the existing public standardizer. The final public outline is recorded separately in the JSON. FAQ and references remain at the public end.

### `cisco-catalyst-layer-2-layer-3-switch-hardening`

Headings renumbered: **2**; sections moved: **0**. Validation: **passed**.

**EN before:** 1. Pre-Hardening Checks (`prechecks`) → Architecture and deployment contract (`architecture`) → 2. Common Hardening Baseline: Identity and AAA (`baseline`) → 3. SSH Hardening (`ssh`) → 4. Disable Unnecessary Services (`services`) → 5. Layer 2 Switch Hardening (`l2`) → 6. DHCP Snooping (`dhcp`) → 7. Dynamic ARP Inspection (`dai`) → 8. IP Source Guard (`ipsg`) → 9. STP Hardening (`stp`) → 10. Port Security (`port-security`) → 11. Storm Control (`storm`) → 12. Trunk Hardening (`trunk`) → 13. Unused Ports (`unused`) → 14. Layer 3 Switch Hardening (`l3`) → 15. Layer 3 ACL Hardening (`svi-acl`) → 16. Routing Protocol Security — OSPF (`routing`) → 17. NTP Hardening (`ntp`) → 18. Syslog (`syslog`) → 19. SNMP — AuthPriv First (`snmp`) → 20. Login Attack Protection (`login`) → 22. Final Independent Configurations (`final-configs`) → 21. Verification and Acceptance (`verification`) → 23. Important Warnings (`warnings`) → 24. Final Hardening Checklist (`checklist`) → Official Cisco References (`references`)

**EN after:** 1. Pre-Hardening Checks (`prechecks`) → Architecture and deployment contract (`architecture`) → 2. Common Hardening Baseline: Identity and AAA (`baseline`) → 3. SSH Hardening (`ssh`) → 4. Disable Unnecessary Services (`services`) → 5. Layer 2 Switch Hardening (`l2`) → 6. DHCP Snooping (`dhcp`) → 7. Dynamic ARP Inspection (`dai`) → 8. IP Source Guard (`ipsg`) → 9. STP Hardening (`stp`) → 10. Port Security (`port-security`) → 11. Storm Control (`storm`) → 12. Trunk Hardening (`trunk`) → 13. Unused Ports (`unused`) → 14. Layer 3 Switch Hardening (`l3`) → 15. Layer 3 ACL Hardening (`svi-acl`) → 16. Routing Protocol Security — OSPF (`routing`) → 17. NTP Hardening (`ntp`) → 18. Syslog (`syslog`) → 19. SNMP — AuthPriv First (`snmp`) → 20. Login Attack Protection (`login`) → 21. Final Independent Configurations (`final-configs`) → 22. Verification and Acceptance (`verification`) → 23. Important Warnings (`warnings`) → 24. Final Hardening Checklist (`checklist`) → Official Cisco References (`references`)

**FA before:** ۱. بررسی پیش از هاردنینگ (`prechecks`) → معماری و الزامات استقرار (`architecture`) → ۲. تنظیمات مشترک: Identity و AAA (`baseline`) → ۳. امنیت SSH (`ssh`) → ۴. غیرفعال‌سازی سرویس غیرضروری (`services`) → ۵. هاردنینگ سوئیچ لایه ۲ (`l2`) → ۶. DHCP Snooping (`dhcp`) → ۷. Dynamic ARP Inspection (`dai`) → ۸. IP Source Guard (`ipsg`) → ۹. هاردنینگ STP (`stp`) → ۱۰. Port Security (`port-security`) → ۱۱. Storm Control (`storm`) → ۱۲. هاردنینگ Trunk (`trunk`) → ۱۳. پورت بلااستفاده (`unused`) → ۱۴. هاردنینگ سوئیچ لایه ۳ (`l3`) → ۱۵. هاردنینگ ACL لایه ۳ (`svi-acl`) → ۱۶. امنیت پروتکل Routing — OSPF (`routing`) → ۱۷. امنیت NTP (`ntp`) → ۱۸. Syslog (`syslog`) → ۱۹. SNMP با اولویت AuthPriv (`snmp`) → ۲۰. حفاظت در برابر حمله Login (`login`) → ۲۲. پیکربندی‌های نهایی مستقل (`final-configs`) → ۲۱. اعتبارسنجی و پذیرش (`verification`) → ۲۳. هشدارهای مهم (`warnings`) → ۲۴. چک‌لیست نهایی (`checklist`) → منابع رسمی Cisco (`references`)

**FA after:** ۱. بررسی پیش از هاردنینگ (`prechecks`) → معماری و الزامات استقرار (`architecture`) → ۲. تنظیمات مشترک: Identity و AAA (`baseline`) → ۳. امنیت SSH (`ssh`) → ۴. غیرفعال‌سازی سرویس غیرضروری (`services`) → ۵. هاردنینگ سوئیچ لایه ۲ (`l2`) → ۶. DHCP Snooping (`dhcp`) → ۷. Dynamic ARP Inspection (`dai`) → ۸. IP Source Guard (`ipsg`) → ۹. هاردنینگ STP (`stp`) → ۱۰. Port Security (`port-security`) → ۱۱. Storm Control (`storm`) → ۱۲. هاردنینگ Trunk (`trunk`) → ۱۳. پورت بلااستفاده (`unused`) → ۱۴. هاردنینگ سوئیچ لایه ۳ (`l3`) → ۱۵. هاردنینگ ACL لایه ۳ (`svi-acl`) → ۱۶. امنیت پروتکل Routing — OSPF (`routing`) → ۱۷. امنیت NTP (`ntp`) → ۱۸. Syslog (`syslog`) → ۱۹. SNMP با اولویت AuthPriv (`snmp`) → ۲۰. حفاظت در برابر حمله Login (`login`) → ۲۱. پیکربندی‌های نهایی مستقل (`final-configs`) → ۲۲. اعتبارسنجی و پذیرش (`verification`) → ۲۳. هشدارهای مهم (`warnings`) → ۲۴. چک‌لیست نهایی (`checklist`) → منابع رسمی Cisco (`references`)

No section movement was needed; existing technical dependencies were preserved.

| Stable ID | EN number | FA number |
|---|---|---|
| `final-configs` | 22 → 21 | ۲۲ → ۲۱ |
| `verification` | 21 → 22 | ۲۱ → ۲۲ |

### `mikrotik-ping-triggered-policy-routing`

Headings renumbered: **6**; sections moved: **0**. Validation: **passed**.

**EN before:** 1. Introduction (`introduction`) → 2. Architecture (`architecture`) → 3. Root Cause / Technical Limitation (`technical-limitation`) → 4. Control Interface (`control-interface`) → 5. Local Networks (`local-networks`) → 6. Enable Trigger (`enable-trigger`) → 7. Disable Trigger (`disable-trigger`) → 8. RouterOS v7 Routing Table (`routing-table`) → 9. Mangle Policy Routing (`mangle-policy`) → 10. Removal Script (`removal-script`) → 11. Scheduler (`scheduler`) → 12. FastTrack (`fasttrack`) → 13. NAT (`nat`) → 14. Connection Tracking (`connection-tracking`) → 18. Security Considerations (`security`) → 20. Complete Example Configuration (`complete-configuration`) → 15. Testing (`testing`) → 16. Expected Result (`expected-result`) → 17. Troubleshooting (`troubleshooting`) → 19. Production Best Practices (`best-practices`) → Conclusion (`conclusion`) → FAQ (`faq`)

**EN after:** 1. Introduction (`introduction`) → 2. Architecture (`architecture`) → 3. Root Cause / Technical Limitation (`technical-limitation`) → 4. Control Interface (`control-interface`) → 5. Local Networks (`local-networks`) → 6. Enable Trigger (`enable-trigger`) → 7. Disable Trigger (`disable-trigger`) → 8. RouterOS v7 Routing Table (`routing-table`) → 9. Mangle Policy Routing (`mangle-policy`) → 10. Removal Script (`removal-script`) → 11. Scheduler (`scheduler`) → 12. FastTrack (`fasttrack`) → 13. NAT (`nat`) → 14. Connection Tracking (`connection-tracking`) → 15. Security Considerations (`security`) → 16. Complete Example Configuration (`complete-configuration`) → 17. Testing (`testing`) → 18. Expected Result (`expected-result`) → 19. Troubleshooting (`troubleshooting`) → 20. Production Best Practices (`best-practices`) → Conclusion (`conclusion`) → FAQ (`faq`)

**FA before:** ۱. مقدمه (`introduction`) → ۲. معماری (`architecture`) → ۳. محدودیت فنی Firewall (`technical-limitation`) → ۴. Interface کنترلی (`control-interface`) → ۵. شبکه‌های داخلی (`local-networks`) → ۶. Trigger فعال‌سازی (`enable-trigger`) → ۷. Trigger غیرفعال‌سازی (`disable-trigger`) → ۸. Routing Table در RouterOS v7 (`routing-table`) → ۹. Policy Routing با Mangle (`mangle-policy`) → ۱۰. Script حذف (`removal-script`) → ۱۱. Scheduler (`scheduler`) → ۱۲. FastTrack (`fasttrack`) → ۱۳. NAT (`nat`) → ۱۴. Connection Tracking (`connection-tracking`) → ۱۸. ملاحظات امنیتی (`security`) → ۲۰. پیکربندی کامل مثال (`complete-configuration`) → ۱۵. آزمون (`testing`) → ۱۶. نتیجه مورد انتظار (`expected-result`) → ۱۷. عیب‌یابی (`troubleshooting`) → ۱۹. توصیه‌های Production (`best-practices`) → جمع‌بندی (`conclusion`) → پرسش‌های متداول (`faq`)

**FA after:** ۱. مقدمه (`introduction`) → ۲. معماری (`architecture`) → ۳. محدودیت فنی Firewall (`technical-limitation`) → ۴. Interface کنترلی (`control-interface`) → ۵. شبکه‌های داخلی (`local-networks`) → ۶. Trigger فعال‌سازی (`enable-trigger`) → ۷. Trigger غیرفعال‌سازی (`disable-trigger`) → ۸. Routing Table در RouterOS v7 (`routing-table`) → ۹. Policy Routing با Mangle (`mangle-policy`) → ۱۰. Script حذف (`removal-script`) → ۱۱. Scheduler (`scheduler`) → ۱۲. FastTrack (`fasttrack`) → ۱۳. NAT (`nat`) → ۱۴. Connection Tracking (`connection-tracking`) → ۱۵. ملاحظات امنیتی (`security`) → ۱۶. پیکربندی کامل مثال (`complete-configuration`) → ۱۷. آزمون (`testing`) → ۱۸. نتیجه مورد انتظار (`expected-result`) → ۱۹. عیب‌یابی (`troubleshooting`) → ۲۰. توصیه‌های Production (`best-practices`) → جمع‌بندی (`conclusion`) → پرسش‌های متداول (`faq`)

No section movement was needed; existing technical dependencies were preserved.

| Stable ID | EN number | FA number |
|---|---|---|
| `security` | 18 → 15 | ۱۸ → ۱۵ |
| `complete-configuration` | 20 → 16 | ۲۰ → ۱۶ |
| `testing` | 15 → 17 | ۱۵ → ۱۷ |
| `expected-result` | 16 → 18 | ۱۶ → ۱۸ |
| `troubleshooting` | 17 → 19 | ۱۷ → ۱۹ |
| `best-practices` | 19 → 20 | ۱۹ → ۲۰ |

### `mongodb-installation-configuration-production-deployment`

Headings renumbered: **17**; sections moved: **5**. Validation: **passed**.

**EN before:** 1. What Is MongoDB? (`introduction`) → 3. Server Requirements (`prerequisites`) → 4. Pre-Installation Checks (`pre-installation`) → 2. Architecture Overview (`architecture`) → 5. Install MongoDB on Ubuntu 22.04 / 24.04 (`ubuntu-install`) → 6. Install MongoDB on Debian 12 (`debian-install`) → 7. Install on RHEL / Rocky / AlmaLinux (`rhel-install`) → 8. MongoDB Configuration (`configuration`) → 9. Secure Remote Access (`remote-access`) → 10. MongoDB Authentication (`authentication`) → 11. Dedicated Application User (`application-user`) → 12. Firewall Hardening (`firewall`) → 13. MongoDB Security Hardening (`security`) → 14. TLS Encryption (`tls`) → 15. Production Replica Set (`replica-set`) → 16. Replica Set Internal Authentication (`replica-security`) → 17. MongoDB Connection Strings and Secrets (`connection-string`) → 21. Logging and Slow Queries (`logging`) → 22. Monitoring and Alerting (`monitoring`) → 18. Backup and Tested Recovery (`backup`) → 24. Verification Checklist (`verification`) → 23. Troubleshooting (`troubleshooting`) → 19. Performance Checks (`performance`) → 20. Indexing and Query Plans (`indexing`) → 25. Production Checklist (`best-practices`) → Operational Handover (`conclusion`) → Frequently Asked Questions (`faq`) → Official References (`official-references`)

**EN after:** 1. What Is MongoDB? (`introduction`) → 2. Server Requirements (`prerequisites`) → 3. Architecture Overview (`architecture`) → 4. Pre-Installation Checks (`pre-installation`) → 5. Install MongoDB on Ubuntu 22.04 / 24.04 (`ubuntu-install`) → 6. Install MongoDB on Debian 12 (`debian-install`) → 7. Install on RHEL / Rocky / AlmaLinux (`rhel-install`) → 8. MongoDB Configuration (`configuration`) → 9. MongoDB Authentication (`authentication`) → 10. Dedicated Application User (`application-user`) → 11. Firewall Hardening (`firewall`) → 12. MongoDB Security Hardening (`security`) → 13. TLS Encryption (`tls`) → 14. Secure Remote Access (`remote-access`) → 15. Replica Set Internal Authentication (`replica-security`) → 16. Production Replica Set (`replica-set`) → 17. MongoDB Connection Strings and Secrets (`connection-string`) → 18. Logging and Slow Queries (`logging`) → 19. Monitoring and Alerting (`monitoring`) → 20. Backup and Tested Recovery (`backup`) → 21. Verification Checklist (`verification`) → 22. Performance Checks (`performance`) → 23. Indexing and Query Plans (`indexing`) → 24. Troubleshooting (`troubleshooting`) → 25. Production Checklist (`best-practices`) → Operational Handover (`conclusion`) → Frequently Asked Questions (`faq`) → Official References (`official-references`)

**FA before:** ۱. MongoDB چیست؟ (`introduction`) → ۳. پیش‌نیاز سرور (`prerequisites`) → ۴. بررسی پیش از نصب (`pre-installation`) → ۲. نمای معماری (`architecture`) → ۵. نصب MongoDB روی Ubuntu 22.04 / 24.04 (`ubuntu-install`) → ۶. نصب MongoDB روی Debian 12 (`debian-install`) → ۷. نصب روی RHEL / Rocky / AlmaLinux (`rhel-install`) → ۸. پیکربندی MongoDB (`configuration`) → ۹. دسترسی Remote امن (`remote-access`) → ۱۰. احراز هویت MongoDB (`authentication`) → ۱۱. حساب اختصاصی Application (`application-user`) → ۱۲. ایمن‌سازی Firewall (`firewall`) → ۱۳. ایمن‌سازی MongoDB (`security`) → ۱۴. رمزنگاری TLS (`tls`) → ۱۵. Replica Set در Production (`replica-set`) → ۱۶. احراز هویت داخلی Replica Set (`replica-security`) → ۱۷. Connection String و Secretها (`connection-string`) → ۲۱. Log و Slow Query (`logging`) → ۲۲. مانیتورینگ و Alert (`monitoring`) → ۱۸. Backup و بازیابی آزموده‌شده (`backup`) → ۲۴. چک‌لیست اعتبارسنجی (`verification`) → ۲۳. عیب‌یابی (`troubleshooting`) → ۱۹. بررسی Performance (`performance`) → ۲۰. Index و Query Plan (`indexing`) → ۲۵. چک‌لیست Production (`best-practices`) → تحویل عملیاتی (`conclusion`) → پرسش‌های متداول (`faq`) → منابع رسمی (`official-references`)

**FA after:** ۱. MongoDB چیست؟ (`introduction`) → ۲. پیش‌نیاز سرور (`prerequisites`) → ۳. نمای معماری (`architecture`) → ۴. بررسی پیش از نصب (`pre-installation`) → ۵. نصب MongoDB روی Ubuntu 22.04 / 24.04 (`ubuntu-install`) → ۶. نصب MongoDB روی Debian 12 (`debian-install`) → ۷. نصب روی RHEL / Rocky / AlmaLinux (`rhel-install`) → ۸. پیکربندی MongoDB (`configuration`) → ۹. احراز هویت MongoDB (`authentication`) → ۱۰. حساب اختصاصی Application (`application-user`) → ۱۱. ایمن‌سازی Firewall (`firewall`) → ۱۲. ایمن‌سازی MongoDB (`security`) → ۱۳. رمزنگاری TLS (`tls`) → ۱۴. دسترسی Remote امن (`remote-access`) → ۱۵. احراز هویت داخلی Replica Set (`replica-security`) → ۱۶. Replica Set در Production (`replica-set`) → ۱۷. Connection String و Secretها (`connection-string`) → ۱۸. Log و Slow Query (`logging`) → ۱۹. مانیتورینگ و Alert (`monitoring`) → ۲۰. Backup و بازیابی آزموده‌شده (`backup`) → ۲۱. چک‌لیست اعتبارسنجی (`verification`) → ۲۲. بررسی Performance (`performance`) → ۲۳. Index و Query Plan (`indexing`) → ۲۴. عیب‌یابی (`troubleshooting`) → ۲۵. چک‌لیست Production (`best-practices`) → تحویل عملیاتی (`conclusion`) → پرسش‌های متداول (`faq`) → منابع رسمی (`official-references`)

Movements:

- `architecture`: position 4 → 3. Move before pre-installation implementation checks.
- `remote-access`: position 9 → 14. Move after authentication, firewall and TLS prerequisites.
- `replica-security`: position 16 → 15. Prepare internal member authentication before replica-set startup.
- `performance`: position 23 → 22. Move before troubleshooting so diagnostic expectations are established.
- `indexing`: position 24 → 23. Keep query-plan/index checks with performance before troubleshooting.

| Stable ID | EN number | FA number |
|---|---|---|
| `prerequisites` | 3 → 2 | ۳ → ۲ |
| `architecture` | 2 → 3 | ۲ → ۳ |
| `authentication` | 10 → 9 | ۱۰ → ۹ |
| `application-user` | 11 → 10 | ۱۱ → ۱۰ |
| `firewall` | 12 → 11 | ۱۲ → ۱۱ |
| `security` | 13 → 12 | ۱۳ → ۱۲ |
| `tls` | 14 → 13 | ۱۴ → ۱۳ |
| `remote-access` | 9 → 14 | ۹ → ۱۴ |
| `replica-security` | 16 → 15 | ۱۶ → ۱۵ |
| `replica-set` | 15 → 16 | ۱۵ → ۱۶ |
| `logging` | 21 → 18 | ۲۱ → ۱۸ |
| `monitoring` | 22 → 19 | ۲۲ → ۱۹ |
| `backup` | 18 → 20 | ۱۸ → ۲۰ |
| `verification` | 24 → 21 | ۲۴ → ۲۱ |
| `performance` | 19 → 22 | ۱۹ → ۲۲ |
| `indexing` | 20 → 23 | ۲۰ → ۲۳ |
| `troubleshooting` | 23 → 24 | ۲۳ → ۲۴ |

### `nginx-reverse-proxy-multiple-domains-single-ip-443`

Headings renumbered: **17**; sections moved: **0**. Validation: **passed**.

**EN before:** 1. Introduction (`introduction`) → 2. Scenario (`scenario`) → 3. Architecture (`architecture`) → 4. How SNI Works (`sni`) → 20. Why NAT Alone Is Not Enough (`nat`) → 21. Enterprise Architecture Example (`enterprise`) → 6. Nginx Installation (`installation`) → 5. DNS Configuration (`dns`) → 7. Backend Connectivity Check (`backend-connectivity`) → 8. SSL Certificate (`certificates`) → 9. Complete Nginx Configuration (`complete-configuration`) → 10. HTTP to HTTPS Redirect (`http-redirect`) → 11. Default Server Security (`default-server`) → 12. WebSocket Support and Upload Size (`websocket`) → 13. Logging (`logging`) → 14. Security Hardening (`hardening`) → 15. Testing Configuration and Renewal (`testing`) → 16. curl --resolve: Test Before DNS Cutover (`resolve`) → 17. openssl s_client: SNI and Certificate Verification (`openssl`) → 18. Troubleshooting (`troubleshooting`) → 19. Root Cause Analysis (`root-cause`) → 22. Security Recommendations and Operational Limits (`recommendations`) → 23. Conclusion (`conclusion`) → Frequently Asked Questions (`faq`) → Official References (`official-references`) → 24. SEO Title (`seo-title`) → 25. Meta Description (`meta-description`) → 26. Keywords (`keywords`)

**EN after:** 1. Introduction (`introduction`) → 2. Scenario (`scenario`) → 3. Architecture (`architecture`) → 4. How SNI Works (`sni`) → 5. Why NAT Alone Is Not Enough (`nat`) → 6. Enterprise Architecture Example (`enterprise`) → 7. Nginx Installation (`installation`) → 8. DNS Configuration (`dns`) → 9. Backend Connectivity Check (`backend-connectivity`) → 10. SSL Certificate (`certificates`) → 11. Complete Nginx Configuration (`complete-configuration`) → 12. HTTP to HTTPS Redirect (`http-redirect`) → 13. Default Server Security (`default-server`) → 14. WebSocket Support and Upload Size (`websocket`) → 15. Logging (`logging`) → 16. Security Hardening (`hardening`) → 17. Testing Configuration and Renewal (`testing`) → 18. curl --resolve: Test Before DNS Cutover (`resolve`) → 19. openssl s_client: SNI and Certificate Verification (`openssl`) → 20. Troubleshooting (`troubleshooting`) → 21. Root Cause Analysis (`root-cause`) → 22. Security Recommendations and Operational Limits (`recommendations`) → 23. Conclusion (`conclusion`) → Frequently Asked Questions (`faq`) → Official References (`official-references`) → 24. SEO Title (`seo-title`) → 25. Meta Description (`meta-description`) → 26. Keywords (`keywords`)

**FA before:** ۱. مقدمه (`introduction`) → ۲. سناریو (`scenario`) → ۳. معماری (`architecture`) → ۴. نحوه کار SNI (`sni`) → ۲۰. Why NAT Alone Is Not Enough؛ چرا NAT کافی نیست؟ (`nat`) → ۲۱. مثال معماری سازمانی (`enterprise`) → ۶. نصب Nginx (`installation`) → ۵. تنظیم DNS (`dns`) → ۷. بررسی ارتباط با Backend (`backend-connectivity`) → ۸. گواهی SSL و TLS (`certificates`) → ۹. تنظیمات کامل Nginx (`complete-configuration`) → ۱۰. هدایت HTTP به HTTPS (`http-redirect`) → ۱۱. امنیت Default Server (`default-server`) → ۱۲. WebSocket و اندازه Upload (`websocket`) → ۱۳. لاگ‌گیری (`logging`) → ۱۴. تقویت امنیت (`hardening`) → ۱۵. تست تنظیمات و تمدید (`testing`) → ۱۶. تست با curl --resolve پیش از تغییر DNS (`resolve`) → ۱۷. تست SNI و گواهی با openssl s_client (`openssl`) → ۱۸. عیب‌یابی (`troubleshooting`) → ۱۹. تحلیل علت ریشه‌ای (`root-cause`) → ۲۲. توصیه‌های امنیتی و محدودیت عملیاتی (`recommendations`) → ۲۳. نتیجه‌گیری (`conclusion`) → پرسش‌های متداول (`faq`) → منابع رسمی (`official-references`) → ۲۴. عنوان SEO (`seo-title`) → ۲۵. توضیحات متا (`meta-description`) → ۲۶. کلمات کلیدی (`keywords`)

**FA after:** ۱. مقدمه (`introduction`) → ۲. سناریو (`scenario`) → ۳. معماری (`architecture`) → ۴. نحوه کار SNI (`sni`) → ۵. Why NAT Alone Is Not Enough؛ چرا NAT کافی نیست؟ (`nat`) → ۶. مثال معماری سازمانی (`enterprise`) → ۷. نصب Nginx (`installation`) → ۸. تنظیم DNS (`dns`) → ۹. بررسی ارتباط با Backend (`backend-connectivity`) → ۱۰. گواهی SSL و TLS (`certificates`) → ۱۱. تنظیمات کامل Nginx (`complete-configuration`) → ۱۲. هدایت HTTP به HTTPS (`http-redirect`) → ۱۳. امنیت Default Server (`default-server`) → ۱۴. WebSocket و اندازه Upload (`websocket`) → ۱۵. لاگ‌گیری (`logging`) → ۱۶. تقویت امنیت (`hardening`) → ۱۷. تست تنظیمات و تمدید (`testing`) → ۱۸. تست با curl --resolve پیش از تغییر DNS (`resolve`) → ۱۹. تست SNI و گواهی با openssl s_client (`openssl`) → ۲۰. عیب‌یابی (`troubleshooting`) → ۲۱. تحلیل علت ریشه‌ای (`root-cause`) → ۲۲. توصیه‌های امنیتی و محدودیت عملیاتی (`recommendations`) → ۲۳. نتیجه‌گیری (`conclusion`) → پرسش‌های متداول (`faq`) → منابع رسمی (`official-references`) → ۲۴. عنوان SEO (`seo-title`) → ۲۵. توضیحات متا (`meta-description`) → ۲۶. کلمات کلیدی (`keywords`)

No section movement was needed; existing technical dependencies were preserved.

| Stable ID | EN number | FA number |
|---|---|---|
| `nat` | 20 → 5 | ۲۰ → ۵ |
| `enterprise` | 21 → 6 | ۲۱ → ۶ |
| `installation` | 6 → 7 | ۶ → ۷ |
| `dns` | 5 → 8 | ۵ → ۸ |
| `backend-connectivity` | 7 → 9 | ۷ → ۹ |
| `certificates` | 8 → 10 | ۸ → ۱۰ |
| `complete-configuration` | 9 → 11 | ۹ → ۱۱ |
| `http-redirect` | 10 → 12 | ۱۰ → ۱۲ |
| `default-server` | 11 → 13 | ۱۱ → ۱۳ |
| `websocket` | 12 → 14 | ۱۲ → ۱۴ |
| `logging` | 13 → 15 | ۱۳ → ۱۵ |
| `hardening` | 14 → 16 | ۱۴ → ۱۶ |
| `testing` | 15 → 17 | ۱۵ → ۱۷ |
| `resolve` | 16 → 18 | ۱۶ → ۱۸ |
| `openssl` | 17 → 19 | ۱۷ → ۱۹ |
| `troubleshooting` | 18 → 20 | ۱۸ → ۲۰ |
| `root-cause` | 19 → 21 | ۱۹ → ۲۱ |

### `redis-installation-configuration-replication`

Headings renumbered: **19**; sections moved: **2**. Validation: **passed**.

**EN before:** Introduction: verified versions and deployment scope (`introduction`) → 1. What is Redis? (`what-is-redis`) → 2. Redis in enterprise architecture (`enterprise-use`) → Prerequisites and deployment scenario (`prerequisites`) → 14. Redis replication architecture (`replication-architecture`) → 15. Replication alone is not high availability (`replication-is-not-ha`) → 17. Replication vs Sentinel vs Redis Cluster (`cluster-comparison`) → 3. Install Redis on Ubuntu (`installation`) → 4. Check the Redis service (`service-diagnostics`) → 5. Configuration layout and backup (`configuration-files`) → 6. Configure the Redis primary (`primary-configuration`) → 7. Security hardening and network segmentation (`security-hardening`) → 8. Authentication using Redis ACL (`authentication-acl`) → 9. Persistence: RDB and AOF (`persistence`) → 10. Configure the Redis replica (`replica-configuration`) → 11. Verify replication status (`verify-replication`) → 12. Practical replication test (`replication-test`) → 13. Read from replicas (`read-scaling`) → 16. Redis Sentinel high availability (`sentinel`) → 19. Monitoring and operational metrics (`monitoring`) → 20. Prometheus, Grafana and alerting (`prometheus-grafana`) → 22. Production troubleshooting runbook (`troubleshooting`) → 21. Backup and restore (`backup-restore`) → 18. Memory management and eviction policy (`memory-management`) → 23. Production acceptance checklist (`production-checklist`) → Conclusion: release acceptance (`conclusion`) → Frequently asked questions (`faq`) → Official references and deployment templates (`references-downloads`)

**EN after:** Introduction: verified versions and deployment scope (`introduction`) → 1. What is Redis? (`what-is-redis`) → 2. Redis in enterprise architecture (`enterprise-use`) → Prerequisites and deployment scenario (`prerequisites`) → 3. Redis replication architecture (`replication-architecture`) → 4. Replication alone is not high availability (`replication-is-not-ha`) → 5. Replication vs Sentinel vs Redis Cluster (`cluster-comparison`) → 6. Install Redis on Ubuntu (`installation`) → 7. Check the Redis service (`service-diagnostics`) → 8. Configuration layout and backup (`configuration-files`) → 9. Configure the Redis primary (`primary-configuration`) → 10. Security hardening and network segmentation (`security-hardening`) → 11. Authentication using Redis ACL (`authentication-acl`) → 12. Persistence: RDB and AOF (`persistence`) → 13. Configure the Redis replica (`replica-configuration`) → 14. Verify replication status (`verify-replication`) → 15. Practical replication test (`replication-test`) → 16. Read from replicas (`read-scaling`) → 17. Redis Sentinel high availability (`sentinel`) → 18. Monitoring and operational metrics (`monitoring`) → 19. Prometheus, Grafana and alerting (`prometheus-grafana`) → 20. Backup and restore (`backup-restore`) → 21. Memory management and eviction policy (`memory-management`) → 22. Production troubleshooting runbook (`troubleshooting`) → 23. Production acceptance checklist (`production-checklist`) → Conclusion: release acceptance (`conclusion`) → Frequently asked questions (`faq`) → Official references and deployment templates (`references-downloads`)

**FA before:** مقدمه؛ نسخه‌های بررسی‌شده و محدوده استقرار (`introduction`) → ۱. Redis چیست؟ (`what-is-redis`) → ۲. کاربرد Redis در معماری Enterprise (`enterprise-use`) → پیش‌نیازها و سناریوی استقرار (`prerequisites`) → ۱۴. Redis Replication Architecture (`replication-architecture`) → ۱۵. Replication به‌تنهایی High Availability نیست (`replication-is-not-ha`) → ۱۷. تفاوت Replication، Sentinel و Redis Cluster (`cluster-comparison`) → ۳. نصب Redis روی Ubuntu (`installation`) → ۴. بررسی سرویس Redis (`service-diagnostics`) → ۵. ساختار Configuration و Backup پیش از تغییر (`configuration-files`) → ۶. تنظیم Redis Primary (`primary-configuration`) → ۷. Security Hardening و تفکیک شبکه (`security-hardening`) → ۸. Authentication با Redis ACL (`authentication-acl`) → ۹. Persistence؛ RDB و AOF (`persistence`) → ۱۰. راه‌اندازی Redis Replica (`replica-configuration`) → ۱۱. بررسی وضعیت Replication (`verify-replication`) → ۱۲. تست عملی Replication (`replication-test`) → ۱۳. Read From Replica و Read Scaling (`read-scaling`) → ۱۶. Redis Sentinel و High Availability (`sentinel`) → ۱۹. Monitoring و Metricهای عملیاتی (`monitoring`) → ۲۰. Prometheus، Grafana و Alerting (`prometheus-grafana`) → ۲۲. Runbook عملیاتی Troubleshooting (`troubleshooting`) → ۲۱. Backup و Restore (`backup-restore`) → ۱۸. Memory Management و Eviction Policy (`memory-management`) → ۲۳. Production Checklist و معیار پذیرش (`production-checklist`) → جمع‌بندی؛ پذیرش استقرار (`conclusion`) → پرسش‌های متداول Redis در Production (`faq`) → منابع رسمی و Templateهای استقرار (`references-downloads`)

**FA after:** مقدمه؛ نسخه‌های بررسی‌شده و محدوده استقرار (`introduction`) → ۱. Redis چیست؟ (`what-is-redis`) → ۲. کاربرد Redis در معماری Enterprise (`enterprise-use`) → پیش‌نیازها و سناریوی استقرار (`prerequisites`) → ۳. Redis Replication Architecture (`replication-architecture`) → ۴. Replication به‌تنهایی High Availability نیست (`replication-is-not-ha`) → ۵. تفاوت Replication، Sentinel و Redis Cluster (`cluster-comparison`) → ۶. نصب Redis روی Ubuntu (`installation`) → ۷. بررسی سرویس Redis (`service-diagnostics`) → ۸. ساختار Configuration و Backup پیش از تغییر (`configuration-files`) → ۹. تنظیم Redis Primary (`primary-configuration`) → ۱۰. Security Hardening و تفکیک شبکه (`security-hardening`) → ۱۱. Authentication با Redis ACL (`authentication-acl`) → ۱۲. Persistence؛ RDB و AOF (`persistence`) → ۱۳. راه‌اندازی Redis Replica (`replica-configuration`) → ۱۴. بررسی وضعیت Replication (`verify-replication`) → ۱۵. تست عملی Replication (`replication-test`) → ۱۶. Read From Replica و Read Scaling (`read-scaling`) → ۱۷. Redis Sentinel و High Availability (`sentinel`) → ۱۸. Monitoring و Metricهای عملیاتی (`monitoring`) → ۱۹. Prometheus، Grafana و Alerting (`prometheus-grafana`) → ۲۰. Backup و Restore (`backup-restore`) → ۲۱. Memory Management و Eviction Policy (`memory-management`) → ۲۲. Runbook عملیاتی Troubleshooting (`troubleshooting`) → ۲۳. Production Checklist و معیار پذیرش (`production-checklist`) → جمع‌بندی؛ پذیرش استقرار (`conclusion`) → پرسش‌های متداول Redis در Production (`faq`) → منابع رسمی و Templateهای استقرار (`references-downloads`)

Movements:

- `backup-restore`: position 23 → 22. Move before troubleshooting; teach recoverable backups before failure handling.
- `memory-management`: position 24 → 23. Move before troubleshooting; establish capacity/eviction expectations first.

| Stable ID | EN number | FA number |
|---|---|---|
| `replication-architecture` | 14 → 3 | ۱۴ → ۳ |
| `replication-is-not-ha` | 15 → 4 | ۱۵ → ۴ |
| `cluster-comparison` | 17 → 5 | ۱۷ → ۵ |
| `installation` | 3 → 6 | ۳ → ۶ |
| `service-diagnostics` | 4 → 7 | ۴ → ۷ |
| `configuration-files` | 5 → 8 | ۵ → ۸ |
| `primary-configuration` | 6 → 9 | ۶ → ۹ |
| `security-hardening` | 7 → 10 | ۷ → ۱۰ |
| `authentication-acl` | 8 → 11 | ۸ → ۱۱ |
| `persistence` | 9 → 12 | ۹ → ۱۲ |
| `replica-configuration` | 10 → 13 | ۱۰ → ۱۳ |
| `verify-replication` | 11 → 14 | ۱۱ → ۱۴ |
| `replication-test` | 12 → 15 | ۱۲ → ۱۵ |
| `read-scaling` | 13 → 16 | ۱۳ → ۱۶ |
| `sentinel` | 16 → 17 | ۱۶ → ۱۷ |
| `monitoring` | 19 → 18 | ۱۹ → ۱۸ |
| `prometheus-grafana` | 20 → 19 | ۲۰ → ۱۹ |
| `backup-restore` | 21 → 20 | ۲۱ → ۲۰ |
| `memory-management` | 18 → 21 | ۱۸ → ۲۱ |

### `truenas-zfs-enterprise`

Headings renumbered: **14**; sections moved: **5**. Validation: **passed**.

**EN before:** Introduction (`section-1`) → 1. What is TrueNAS? (`section-2`) → 2. What is NAS, and what problem does TrueNAS solve? (`section-3`) → 3. Enterprise uses for TrueNAS (`section-4`) → 4. TrueNAS advantages over a general file server (`section-5`) → 6. Minimum and recommended production hardware (`section-7`) → 5. ZFS architecture and key concepts (`section-6`) → 7. Enterprise network design (`section-8`) → 8. Download the ISO and create bootable USB media (`section-9`) → 9. Install TrueNAS on bare metal (`section-10`) → 10. Configure static IP, DNS, gateway and NTP (`section-11`) → 11. Create a storage pool (`section-12`) → 12. Create datasets (`section-13`) → 13. Create users and groups (`section-14`) → 22. Security Hardening (`section-23`) → 14. Configure SMB for Windows (`section-15`) → 15. Map a network drive in Windows (`section-16`) → 16. Configure NFS for Linux and VMware (`section-17`) → 17. iSCSI for virtualization (`section-18`) → 25. Practical example for a medium-sized company (`section-26`) → 23. Monitoring and alerting (`section-24`) → 24. Common mistakes (`section-25`) → 26. Initial troubleshooting and diagnostic commands (`section-27`) → 18. Automated snapshots (`section-19`) → 19. ZFS replication to a second TrueNAS server (`section-20`) → 20. SMART and scrub (`section-21`) → 21. Configuration backup (`section-22`) → 27. Final production deployment checklist (`section-28`) → When is TrueNAS a suitable choice, and when is it not? (`section-29`) → TrueNAS frequently asked questions (`faq`) → Official references (`section-30`)

**EN after:** Introduction (`section-1`) → 1. What is TrueNAS? (`section-2`) → 2. What is NAS, and what problem does TrueNAS solve? (`section-3`) → 3. Enterprise uses for TrueNAS (`section-4`) → 4. TrueNAS advantages over a general file server (`section-5`) → 5. Minimum and recommended production hardware (`section-7`) → 6. ZFS architecture and key concepts (`section-6`) → 7. Enterprise network design (`section-8`) → 8. Download the ISO and create bootable USB media (`section-9`) → 9. Install TrueNAS on bare metal (`section-10`) → 10. Configure static IP, DNS, gateway and NTP (`section-11`) → 11. Create a storage pool (`section-12`) → 12. Create datasets (`section-13`) → 13. Create users and groups (`section-14`) → 14. Security Hardening (`section-23`) → 15. Configure SMB for Windows (`section-15`) → 16. Map a network drive in Windows (`section-16`) → 17. Configure NFS for Linux and VMware (`section-17`) → 18. iSCSI for virtualization (`section-18`) → 19. Automated snapshots (`section-19`) → 20. ZFS replication to a second TrueNAS server (`section-20`) → 21. Configuration backup (`section-22`) → 22. Practical example for a medium-sized company (`section-26`) → 23. SMART and scrub (`section-21`) → 24. Monitoring and alerting (`section-24`) → 25. Initial troubleshooting and diagnostic commands (`section-27`) → 26. Common mistakes (`section-25`) → 27. Final production deployment checklist (`section-28`) → When is TrueNAS a suitable choice, and when is it not? (`section-29`) → TrueNAS frequently asked questions (`faq`) → Official references (`section-30`)

**FA before:** مقدمه (`section-1`) → 1. TrueNAS چیست؟ (`section-2`) → 2. NAS چیست و TrueNAS چه مشکلی را حل می‌کند؟ (`section-3`) → 3. کاربردهای TrueNAS در سازمان (`section-4`) → 4. مزایای TrueNAS نسبت به File Server معمولی (`section-5`) → 6. حداقل و Recommended Hardware برای Production (`section-7`) → 5. معماری ZFS و مفاهیم کلیدی (`section-6`) → 7. طراحی شبکه سازمانی (`section-8`) → 8. دانلود ISO و ساخت Bootable USB (`section-9`) → 9. نصب TrueNAS روی Bare Metal (`section-10`) → 10. تنظیم Static IP، DNS، Gateway و NTP (`section-11`) → 11. ساخت Storage Pool (`section-12`) → 12. ساخت Dataset (`section-13`) → 13. ایجاد User و Group (`section-14`) → ۲۲. سخت‌سازی امنیتی (`section-23`) → 14. راه‌اندازی SMB برای Windows (`section-15`) → 15. Map Network Drive در Windows (`section-16`) → 16. راه‌اندازی NFS برای Linux و VMware (`section-17`) → 17. iSCSI برای Virtualization (`section-18`) → 25. سناریوی واقعی شرکت متوسط (`section-26`) → 23. Monitoring و Alerting (`section-24`) → 24. اشتباهات رایج (`section-25`) → 26. Troubleshooting اولیه و Commandهای Diagnostic (`section-27`) → 18. Snapshot خودکار (`section-19`) → 19. ZFS Replication به TrueNAS دوم (`section-20`) → 20. SMART و Scrub (`section-21`) → 21. Backup از Configuration (`section-22`) → 27. Checklist نهایی Production Deployment (`section-28`) → چه زمانی TrueNAS انتخاب مناسبی است و چه زمانی نیست؟ (`section-29`) → پرسش‌های متداول TrueNAS (`faq`) → منابع رسمی (`section-30`)

**FA after:** مقدمه (`section-1`) → 1. TrueNAS چیست؟ (`section-2`) → 2. NAS چیست و TrueNAS چه مشکلی را حل می‌کند؟ (`section-3`) → 3. کاربردهای TrueNAS در سازمان (`section-4`) → 4. مزایای TrueNAS نسبت به File Server معمولی (`section-5`) → 5. حداقل و Recommended Hardware برای Production (`section-7`) → 6. معماری ZFS و مفاهیم کلیدی (`section-6`) → 7. طراحی شبکه سازمانی (`section-8`) → 8. دانلود ISO و ساخت Bootable USB (`section-9`) → 9. نصب TrueNAS روی Bare Metal (`section-10`) → 10. تنظیم Static IP، DNS، Gateway و NTP (`section-11`) → 11. ساخت Storage Pool (`section-12`) → 12. ساخت Dataset (`section-13`) → 13. ایجاد User و Group (`section-14`) → ۱۴. سخت‌سازی امنیتی (`section-23`) → 15. راه‌اندازی SMB برای Windows (`section-15`) → 16. Map Network Drive در Windows (`section-16`) → 17. راه‌اندازی NFS برای Linux و VMware (`section-17`) → 18. iSCSI برای Virtualization (`section-18`) → 19. Snapshot خودکار (`section-19`) → 20. ZFS Replication به TrueNAS دوم (`section-20`) → 21. Backup از Configuration (`section-22`) → 22. سناریوی واقعی شرکت متوسط (`section-26`) → 23. SMART و Scrub (`section-21`) → 24. Monitoring و Alerting (`section-24`) → 25. Troubleshooting اولیه و Commandهای Diagnostic (`section-27`) → 26. اشتباهات رایج (`section-25`) → 27. Checklist نهایی Production Deployment (`section-28`) → چه زمانی TrueNAS انتخاب مناسبی است و چه زمانی نیست؟ (`section-29`) → پرسش‌های متداول TrueNAS (`faq`) → منابع رسمی (`section-30`)

Movements:

- `section-19`: position 24 → 20. Snapshots follow storage/sharing configuration, before replication.
- `section-20`: position 25 → 21. Replication follows snapshots, before operations/troubleshooting.
- `section-22`: position 27 → 22. Configuration backup follows replication, before the complete example.
- `section-21`: position 26 → 24. SMART/scrub maintenance accompanies monitoring.
- `section-25`: position 22 → 27. Common mistakes accompany troubleshooting and the final checklist.

| Stable ID | EN number | FA number |
|---|---|---|
| `section-7` | 6 → 5 | 6 → 5 |
| `section-6` | 5 → 6 | 5 → 6 |
| `section-23` | 22 → 14 | ۲۲ → ۱۴ |
| `section-15` | 14 → 15 | 14 → 15 |
| `section-16` | 15 → 16 | 15 → 16 |
| `section-17` | 16 → 17 | 16 → 17 |
| `section-18` | 17 → 18 | 17 → 18 |
| `section-19` | 18 → 19 | 18 → 19 |
| `section-20` | 19 → 20 | 19 → 20 |
| `section-26` | 25 → 22 | 25 → 22 |
| `section-21` | 20 → 23 | 20 → 23 |
| `section-24` | 23 → 24 | 23 → 24 |
| `section-27` | 26 → 25 | 26 → 25 |
| `section-25` | 24 → 26 | 24 → 26 |

### `ubiquiti-unifi-wireless-mesh-network`

Headings renumbered: **13**; sections moved: **0**. Validation: **passed**.

**EN before:** 1. What Is Wireless Mesh in UniFi? (`wireless-mesh`) → 2. When to Use Mesh and When to Use Ethernet (`when-to-mesh`) → 4. Prerequisites (`prerequisites`) → 3. Parent AP and Mesh AP Architecture (`architecture`) → 11. RF Design and 5 GHz Backhaul (`rf-backhaul`) → 12. Channel Planning (`channels`) → 13. RSSI and Signal Strength (`signal`) → Enterprise Design Recommendations (`enterprise-design`) → 5. Adopt the Devices (`adoption`) → 6. Create the Shared SSID (`ssid`) → 7. Enable Wireless Meshing (`enable-meshing`) → 8. Configure the Wired Mesh Parent (`mesh-parent`) → 9. Configure Mesh Connect on Wireless APs (`mesh-connect`) → 14. VLANs Across the Mesh (`vlans`) → 15. Client Roaming Between APs (`roaming`) → 10. Verify Wireless Uplink (`wireless-uplink`) → 16. Performance Testing and Acceptance (`performance`) → 17. Troubleshooting (`troubleshooting`) → Troubleshooting Checklist (`troubleshooting-checklist`) → Best Practices Checklist (`best-practices`) → Conclusion (`conclusion`) → FAQ (`faq`) → Official References (`official-references`)

**EN after:** 1. What Is Wireless Mesh in UniFi? (`wireless-mesh`) → 2. When to Use Mesh and When to Use Ethernet (`when-to-mesh`) → 3. Prerequisites (`prerequisites`) → 4. Parent AP and Mesh AP Architecture (`architecture`) → 5. RF Design and 5 GHz Backhaul (`rf-backhaul`) → 6. Channel Planning (`channels`) → 7. RSSI and Signal Strength (`signal`) → Enterprise Design Recommendations (`enterprise-design`) → 8. Adopt the Devices (`adoption`) → 9. Create the Shared SSID (`ssid`) → 10. Enable Wireless Meshing (`enable-meshing`) → 11. Configure the Wired Mesh Parent (`mesh-parent`) → 12. Configure Mesh Connect on Wireless APs (`mesh-connect`) → 13. VLANs Across the Mesh (`vlans`) → 14. Client Roaming Between APs (`roaming`) → 15. Verify Wireless Uplink (`wireless-uplink`) → 16. Performance Testing and Acceptance (`performance`) → 17. Troubleshooting (`troubleshooting`) → Troubleshooting Checklist (`troubleshooting-checklist`) → Best Practices Checklist (`best-practices`) → Conclusion (`conclusion`) → FAQ (`faq`) → Official References (`official-references`)

**FA before:** ۱. Wireless Mesh در UniFi چیست؟ (`wireless-mesh`) → ۲. Mesh چه زمانی مناسب است و چه زمانی Ethernet لازم است؟ (`when-to-mesh`) → ۴. پیش‌نیازها (`prerequisites`) → ۳. معماری Parent AP و Mesh AP (`architecture`) → ۱۱. طراحی RF و Backhaul روی 5GHz (`rf-backhaul`) → ۱۲. Channel Planning (`channels`) → ۱۳. RSSI و Signal Strength (`signal`) → توصیه‌های طراحی سازمانی — Enterprise Design Recommendations (`enterprise-design`) → ۵. Adoption تجهیزات (`adoption`) → ۶. ایجاد SSID مشترک (`ssid`) → ۷. فعال‌سازی Wireless Meshing (`enable-meshing`) → ۸. تنظیم Mesh Parent روی AP کابلی (`mesh-parent`) → ۹. تنظیم Mesh Connect روی APهای Wireless (`mesh-connect`) → ۱۴. VLAN در شبکه Mesh (`vlans`) → ۱۵. Roaming کاربران بین APها (`roaming`) → ۱۰. بررسی Wireless Uplink (`wireless-uplink`) → ۱۶. Performance Testing و معیار پذیرش (`performance`) → ۱۷. عیب‌یابی (`troubleshooting`) → چک‌لیست عیب‌یابی (`troubleshooting-checklist`) → چک‌لیست Best Practiceها (`best-practices`) → نتیجه‌گیری (`conclusion`) → پرسش‌های متداول — FAQ (`faq`) → منابع رسمی (`official-references`)

**FA after:** ۱. Wireless Mesh در UniFi چیست؟ (`wireless-mesh`) → ۲. Mesh چه زمانی مناسب است و چه زمانی Ethernet لازم است؟ (`when-to-mesh`) → ۳. پیش‌نیازها (`prerequisites`) → ۴. معماری Parent AP و Mesh AP (`architecture`) → ۵. طراحی RF و Backhaul روی 5GHz (`rf-backhaul`) → ۶. Channel Planning (`channels`) → ۷. RSSI و Signal Strength (`signal`) → توصیه‌های طراحی سازمانی — Enterprise Design Recommendations (`enterprise-design`) → ۸. Adoption تجهیزات (`adoption`) → ۹. ایجاد SSID مشترک (`ssid`) → ۱۰. فعال‌سازی Wireless Meshing (`enable-meshing`) → ۱۱. تنظیم Mesh Parent روی AP کابلی (`mesh-parent`) → ۱۲. تنظیم Mesh Connect روی APهای Wireless (`mesh-connect`) → ۱۳. VLAN در شبکه Mesh (`vlans`) → ۱۴. Roaming کاربران بین APها (`roaming`) → ۱۵. بررسی Wireless Uplink (`wireless-uplink`) → ۱۶. Performance Testing و معیار پذیرش (`performance`) → ۱۷. عیب‌یابی (`troubleshooting`) → چک‌لیست عیب‌یابی (`troubleshooting-checklist`) → چک‌لیست Best Practiceها (`best-practices`) → نتیجه‌گیری (`conclusion`) → پرسش‌های متداول — FAQ (`faq`) → منابع رسمی (`official-references`)

No section movement was needed; existing technical dependencies were preserved.

| Stable ID | EN number | FA number |
|---|---|---|
| `prerequisites` | 4 → 3 | ۴ → ۳ |
| `architecture` | 3 → 4 | ۳ → ۴ |
| `rf-backhaul` | 11 → 5 | ۱۱ → ۵ |
| `channels` | 12 → 6 | ۱۲ → ۶ |
| `signal` | 13 → 7 | ۱۳ → ۷ |
| `adoption` | 5 → 8 | ۵ → ۸ |
| `ssid` | 6 → 9 | ۶ → ۹ |
| `enable-meshing` | 7 → 10 | ۷ → ۱۰ |
| `mesh-parent` | 8 → 11 | ۸ → ۱۱ |
| `mesh-connect` | 9 → 12 | ۹ → ۱۲ |
| `vlans` | 14 → 13 | ۱۴ → ۱۳ |
| `roaming` | 15 → 14 | ۱۵ → ۱۴ |
| `wireless-uplink` | 10 → 15 | ۱۰ → ۱۵ |

### `zabbix-server-linux-windows-agents-backup`

Headings renumbered: **5**; sections moved: **1**. Validation: **passed**.

**EN before:** Introduction and deployment scope (`introduction`) → 2. Server preparation and prerequisites (`prerequisites`) → 1. Enterprise Zabbix architecture (`architecture`) → 3. Installation: Server, PostgreSQL and the frontend (`configuration`) → 4. Install Agent 2 on Ubuntu Linux (`linux-agent`) → 5. Install Agent 2 on Windows Server (`windows-agent`) → 8. Security hardening (`security`) → 6. Practical monitoring and triggers (`monitoring`) → 7. Alerts and notifications (`notifications`) → 12. Production troubleshooting (`troubleshooting`) → 9. Database and configuration backup (`database-backup`) → 10. Automated daily backup (`automated-backup`) → 11. Restore and disaster recovery (`recovery`) → 13. Production checklist (`best-practices`) → Operational handover and runtime acceptance (`conclusion`) → Frequently asked questions (`faq`) → Official references, downloads and related articles (`official-references`)

**EN after:** Introduction and deployment scope (`introduction`) → 1. Server preparation and prerequisites (`prerequisites`) → 2. Enterprise Zabbix architecture (`architecture`) → 3. Installation: Server, PostgreSQL and the frontend (`configuration`) → 4. Install Agent 2 on Ubuntu Linux (`linux-agent`) → 5. Install Agent 2 on Windows Server (`windows-agent`) → 6. Security hardening (`security`) → 7. Practical monitoring and triggers (`monitoring`) → 8. Alerts and notifications (`notifications`) → 9. Database and configuration backup (`database-backup`) → 10. Automated daily backup (`automated-backup`) → 11. Restore and disaster recovery (`recovery`) → 12. Production troubleshooting (`troubleshooting`) → 13. Production checklist (`best-practices`) → Operational handover and runtime acceptance (`conclusion`) → Frequently asked questions (`faq`) → Official references, downloads and related articles (`official-references`)

**FA before:** مقدمه و محدوده استقرار (`introduction`) → ۲. آماده‌سازی و پیش‌نیاز سرور (`prerequisites`) → ۱. معماری سازمانی Zabbix (`architecture`) → ۳. نصب Server، PostgreSQL و Frontend (`configuration`) → ۴. نصب Agent 2 روی Ubuntu Linux (`linux-agent`) → ۵. نصب Agent 2 روی Windows Server (`windows-agent`) → ۸. امن‌سازی (`security`) → ۶. مانیتورینگ و Trigger عملی (`monitoring`) → ۷. هشدار و اعلان (`notifications`) → ۱۲. عیب‌یابی Production (`troubleshooting`) → ۹. بکاپ Database و Configuration (`database-backup`) → ۱۰. بکاپ روزانه خودکار (`automated-backup`) → ۱۱. Restore و بازیابی بحران (`recovery`) → ۱۳. چک‌لیست Production (`best-practices`) → تحویل عملیاتی و پذیرش Runtime (`conclusion`) → پرسش‌های متداول (`faq`) → منابع رسمی، دانلود و مقاله مرتبط (`official-references`)

**FA after:** مقدمه و محدوده استقرار (`introduction`) → ۱. آماده‌سازی و پیش‌نیاز سرور (`prerequisites`) → ۲. معماری سازمانی Zabbix (`architecture`) → ۳. نصب Server، PostgreSQL و Frontend (`configuration`) → ۴. نصب Agent 2 روی Ubuntu Linux (`linux-agent`) → ۵. نصب Agent 2 روی Windows Server (`windows-agent`) → ۶. امن‌سازی (`security`) → ۷. مانیتورینگ و Trigger عملی (`monitoring`) → ۸. هشدار و اعلان (`notifications`) → ۹. بکاپ Database و Configuration (`database-backup`) → ۱۰. بکاپ روزانه خودکار (`automated-backup`) → ۱۱. Restore و بازیابی بحران (`recovery`) → ۱۲. عیب‌یابی Production (`troubleshooting`) → ۱۳. چک‌لیست Production (`best-practices`) → تحویل عملیاتی و پذیرش Runtime (`conclusion`) → پرسش‌های متداول (`faq`) → منابع رسمی، دانلود و مقاله مرتبط (`official-references`)

Movements:

- `troubleshooting`: position 10 → 13. Move after backup automation and disaster recovery.

| Stable ID | EN number | FA number |
|---|---|---|
| `prerequisites` | 2 → 1 | ۲ → ۱ |
| `architecture` | 1 → 2 | ۱ → ۲ |
| `security` | 8 → 6 | ۸ → ۶ |
| `monitoring` | 6 → 7 | ۶ → ۷ |
| `notifications` | 7 → 8 | ۷ → ۸ |

Rendered backup script now includes zabbix-backup-failure.service, matching the unchanged authoritative script, both Markdown editions and download.

### `sql-server-automatic-backup-job`

Headings renumbered: **0**; sections moved: **0**. Validation: **passed**.

**EN source before and after (unchanged):** Automated backups must lead to a working restore (`introduction`) → Versions, service identities and implementation scope (`prerequisites`) → Design schedules and recovery chains for Jira and Confluence (`architecture`) → Choose recovery models before enabling log jobs (`recovery-model`) → Per-database paths and storage capacity (`backup-folders`) → Procedure, whitelist and folder provisioning (`configuration`) → Build Agent jobs and test manually (`agent-jobs`) → Cleanup while retaining the recovery-chain base (`cleanup`) → Encryption and certificate/private-key recovery (`security`) → History, VERIFYONLY and an actual restore (`validation`) → Diagnose job failures from audit to storage (`troubleshooting`) → Freshness, alerts and independent backup copies (`best-practices`) → SQL Server Automated Backup FAQ (`faq`) → Microsoft backup and restore references (`official-references`)

**FA source before and after (unchanged):** Backup خودکار باید به Restore برسد (`introduction`) → نسخه‌ها، هویت سرویس و حدود اجرا (`prerequisites`) → طراحی زمان‌بندی و زنجیره برای Jira و Confluence (`architecture`) → انتخاب Recovery Model پیش از فعال‌کردن Log Job (`recovery-model`) → مسیر مستقل هر Database و ظرفیت Storage (`backup-folders`) → Procedure و آماده‌سازی Whitelist و پوشه‌ها (`configuration`) → ساخت Jobهای Agent و آزمون دستی (`agent-jobs`) → Cleanup با حفظ پایه زنجیره Retention (`cleanup`) → Encryption و بازیابی Certificate و Private Key (`security`) → History، VERIFYONLY و Restore واقعی (`validation`) → تشخیص خطای Job از Audit تا Storage (`troubleshooting`) → Freshness، Alert و نسخه مستقل Backup (`best-practices`) → سوالات متداول دربارهٔ Backup خودکار SQL Server (`faq`) → منابع Backup و Restore در Microsoft (`official-references`)

Before: Legacy author footer captured the following generated H2, producing a spurious #author TOC entry.

After: Strip the embedded footer before TOC extraction; article heading order/content unchanged.

## Complete repository inventory

| Slug | Result | Code blocks |
|---|---|---:|
| `10-essential-group-policies-windows-domain` | Reviewed; passed; no correction needed | 36 |
| `apache-tomcat-linux-installation-security-hardening` | Reviewed; passed; no correction needed | 40 |
| `cisco-catalyst-layer-2-layer-3-switch-hardening` | Corrected source; passed | 30 |
| `creating-a-bootable-usb` | Reviewed; passed; no correction needed | 1 |
| `deploy-msi-active-directory-group-policy` | Reviewed; passed; no correction needed | 30 |
| `downgrade-mikrotik-routeros-firmware-safely` | Reviewed; passed; no correction needed | 3 |
| `enable-ssh-linux-complete-guide` | Reviewed; passed; no correction needed | 5 |
| `fortigate-sd-wan-load-balancing-failover` | Reviewed; passed; no correction needed | 27 |
| `http-vs-https-ssl-certificate-impact` | Reviewed; passed; no correction needed | 3 |
| `imap-vs-pop3-email-protocol-comparison` | Reviewed; passed; no correction needed | 3 |
| `install-dfs-server-windows-server` | Reviewed; passed; no correction needed | 4 |
| `install-mikrotik-chr-vmware-workstation` | Reviewed; passed; no correction needed | 1 |
| `install-vmware-esxi-vmware-workstation-vmcisr` | Reviewed; passed; no correction needed | 1 |
| `linux-cli-common-commands` | Reviewed; passed; no correction needed | 3 |
| `linux-security-account-access-management` | Reviewed; passed; no correction needed | 4 |
| `linux-security-auditor-bash` | Reviewed; passed; no correction needed | 5 |
| `mikrotik-block-port-scanners` | Reviewed; passed; no correction needed | 3 |
| `mikrotik-block-website` | Reviewed; passed; no correction needed | 4 |
| `mikrotik-firewall-hardening-input-forward-chain` | Reviewed; passed; no correction needed | 7 |
| `mikrotik-openvpn-setup-v7` | Reviewed; passed; no correction needed | 3 |
| `mikrotik-pbr-client` | Reviewed; passed; no correction needed | 2 |
| `mikrotik-ping-triggered-policy-routing` | Corrected source; passed | 43 |
| `mikrotik-unequal-dual-wan-load-balancing-ecmp` | Reviewed; passed; no correction needed | 1 |
| `mongodb-installation-configuration-production-deployment` | Corrected source; passed | 57 |
| `netbox-installation-setup-ubuntu` | Reviewed; passed; no correction needed | 6 |
| `nginx-installation-configuration-ubuntu` | Reviewed; passed; no correction needed | 4 |
| `nginx-reverse-proxy-multiple-domains-single-ip-443` | Corrected source; passed | 41 |
| `oracle-database-26ai-installation-oracle-linux` | Reviewed; passed; no correction needed | 48 |
| `oxidized-network-device-configuration-backup` | Reviewed; passed; no correction needed | 5 |
| `redis-installation-configuration-replication` | Corrected source; passed | 58 |
| `set-static-ip-ubuntu-server-netplan` | Reviewed; passed; no correction needed | 5 |
| `sql-server-automatic-backup-job` | Corrected rendered TOC; passed | 16 |
| `truenas-zfs-enterprise` | Corrected source; passed | 26 |
| `ubiquiti-unifi-wireless-mesh-network` | Corrected source; passed | 5 |
| `ubuntu-date-time-settings` | Reviewed; passed; no correction needed | 4 |
| `vmware-esxi-8-installation-basic-configuration` | Reviewed; passed; no correction needed | 3 |
| `vsphere-standard-switch-vs-distributed-switch` | Reviewed; passed; no correction needed | 2 |
| `windows-cmd-common-network-commands` | Reviewed; passed; no correction needed | 4 |
| `windows-hardware-info-cmd-vs-dxdiag` | Reviewed; passed; no correction needed | 2 |
| `windows-password-reset-secure-access-recovery` | Reviewed; passed; no correction needed | 2 |
| `zabbix-server-linux-windows-agents-backup` | Corrected source; passed | 46 |

Full machine-readable evidence, bilingual source/public outlines, movement reasons, numbering changes, browser cases and per-article preservation checks: [JSON report](final-enterprise-article-audit-2026-10-09.json).
