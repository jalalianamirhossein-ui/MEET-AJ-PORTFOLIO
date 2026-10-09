# Oracle Database 26ai article — implementation and validation

Reviewed and implemented on 9 October 2026 in `D:\MEET AJ PORTFOLIO`.

## Article and architecture

English title: **Install and Secure Oracle Database 26ai on Oracle Linux: Complete Enterprise Guide**.

Persian title: **آموزش جامع نصب و راه‌اندازی Oracle Database 26ai روی Oracle Linux همراه با امنیت، بکاپ و بازیابی**.

Slug: `oracle-database-26ai-installation-oracle-linux`.

Canonical route: `/articles/oracle-database-26ai-installation-oracle-linux`.

Existing Redis, MongoDB and Linux security article sources, builders, importer, content standardizer, tag catalog, ordering and rendering conventions were inspected before implementation. The new article reuses the existing bilingual `data-en`/`data-fa` HTML, one CMS record with localization metadata, language cookie/control, Linux category/theme, responsive tables, LTR code, TOC, related cards, article/FAQ schema, sitemap and canonical routing. No page component, stylesheet, route or article system was added.

There are **14 authored sections**, including all ten requested technical chapters, architecture, conclusion, eight FAQs and references/downloads. Both editorial translations contain **48 identical code blocks**, of which **21 are Bash**. The package includes ten LF-ended operational templates and **42 official Oracle reference links**.

## Technical verification

The [official Linux download page](https://www.oracle.com/database/technologies/oracle26ai-linux-downloads.html) and [26ai installation guide](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/running-rpm-packages-to-install-oracle-database.html) were checked before selecting the OL9 x86_64 Enterprise RPM, preinstallation package, configure script and media checksum. The public base identifies `23.26.1.0.0`; the product name is 26ai. The article requires a separate current MOS certification/RU check and distinguishes Enterprise from Free throughout.

The article follows the official OL9 certification rows, including the RU-specific UEK8 requirement, rather than assuming every OL9 kernel is supported. RAM installation floors are separated from workload capacity estimates. Official documentation was also consulted for Multitenant, licensing, Oracle Restart/SRVCTL, listener parameters, authentication, unified auditing, TLS, TDE, RMAN configuration/encryption/retention/validation/restore/recovery, ADRCI and Oracle Linux service/firewall/SELinux management.

Service examples begin with the documented RPM script and inspect package provenance and loaded integration. The systemd service name is derived from that script; no custom Oracle startup unit is supplied. Boot startup, PDB saved state and remote readiness are checked separately. Oracle Restart is introduced as local resource monitoring with a single ownership model, rather than host failover.

Security examples retain SELinux and firewalld, use narrow source allowlists, protect administrative accounts, separate schema/runtime identities, and apply auditing/password profiles. TLS examples use current `TLS_` parameters, CA/name verification, wallet prerequisites and loopback registration. Licensing boundaries cover on-premises Advanced Security for TDE/RMAN disk encryption, compression choices and optional Diagnostics/Tuning packs.

RMAN covers ARCHIVELOG/FRA, ordinary full versus incremental level 0, daily differential level 1, archived redo, controlfile/SPFILE, recovery-window retention, repository record retention, encryption and validation. Automation uses `/backup/oracle/`, requires a mounted backup filesystem, prevents overlap with `flock`, uses restrictive permissions, returns nonzero failures, rotates logs and provides weekly/daily/15-minute systemd timers. Cleanup requires separately verified off-host recovery-chain custody. The script contains no destructive archive-input deletion or embedded password. Recoveries identify destructive commands, current versus backup controlfiles, PITR/RESETLOGS consequences, key dependencies and isolated restore testing.

## Created files

| File | Purpose |
| --- | --- |
| [article.en.md](../../resources/content/articles/oracle-database-26ai-installation-oracle-linux/article.en.md) | Complete English source |
| [article.fa.md](../../resources/content/articles/oracle-database-26ai-installation-oracle-linux/article.fa.md) | Complete Persian source |
| [metadata.json](../../resources/content/articles/oracle-database-26ai-installation-oracle-linux/metadata.json) | Bilingual SEO, FAQs, edition/platform and official references |
| [README.md](../../resources/content/articles/oracle-database-26ai-installation-oracle-linux/README.md) | Maintenance and rebuild instructions |
| [CMS HTML](../../resources/legacy/articles/oracle-database-26ai-installation-oracle-linux.html) | Importable bilingual article |
| [build-oracle-article.py](../../scripts/build-oracle-article.py) | Reproducible article/template generation and public mirrors |
| [build-oracle-images.py](../../scripts/build-oracle-images.py) | Exact technical diagrams and banner export/verification |
| [image-manifest.json](../../resources/content/articles/oracle-database-26ai-installation-oracle-linux/image-manifest.json) | Dimensions, integrity hashes, file sizes and provenance |
| [image-prompts.json](../../resources/content/articles/oracle-database-26ai-installation-oracle-linux/image-prompts.json) | Banner prompt and diagram specifications |
| [Publication migration](../../database/migrations/2026_10_09_000048_add_oracle_enterprise_article.php) | Imports only this slug; preserves subsequent editorial changes |
| [OracleEnterpriseArticleTest.php](../../tests/Feature/OracleEnterpriseArticleTest.php) | Publication, localization, navigation, SEO, assets and preservation regression checks |

Ten templates exist in both `resources/content/articles/oracle-database-26ai-installation-oracle-linux/` and `public/downloads/oracle-database-26ai-installation-oracle-linux/`:

- `listener-hardening.ora`
- `tls-integration.ora`
- `rman-baseline.rman`
- `oracle-rman-backup.sh`
- `oracle-rman.env`
- `oracle-rman@.service` (custom backup job only)
- `oracle-rman-daily.timer`
- `oracle-rman-weekly.timer`
- `oracle-rman-archivelog.timer`
- `oracle-rman.logrotate`

## Generated images

All files are real optimized PNGs with English text, verified dimensions/integrity and exact public mirrors. The banner uses the built-in ImageGen tool; its 1254 × 1254 native output was resampled to the required delivery dimensions. The five diagrams were drawn with Pillow for precise topology and consistent styling. No stock image, watermark or placeholder is used.

| Source asset | Dimensions | Bytes |
| --- | --- | --- |
| [oracle-database-26ai-banner.png](../../resources/assets/img/articles/banners/oracle-database-26ai-banner.png) | 1000 × 1000 | 1,167,612 |
| [oracle-database-architecture.png](../../resources/assets/img/articles/content/oracle-database-architecture.png) | 1920 × 1080 | 94,020 |
| [oracle-database-installation-workflow.png](../../resources/assets/img/articles/content/oracle-database-installation-workflow.png) | 1920 × 1080 | 102,233 |
| [oracle-database-systemd-service.png](../../resources/assets/img/articles/content/oracle-database-systemd-service.png) | 1920 × 1080 | 98,412 |
| [oracle-database-security-hardening.png](../../resources/assets/img/articles/content/oracle-database-security-hardening.png) | 1920 × 1080 | 106,498 |
| [oracle-database-rman-backup-recovery.png](../../resources/assets/img/articles/content/oracle-database-rman-backup-recovery.png) | 1920 × 1080 | 110,274 |

The banner appears in the existing hero/listing. All five content diagrams appear in their corresponding article sections with bilingual alt text/captions, intrinsic dimensions and lazy loading. Source assets are versioned under `resources/assets`; generated `public/assets` mirrors follow the existing ignored/publication convention.

## Article registration and local integration

Modified [ArticleTagAssigner.php](../../app/Services/ArticleTagAssigner.php) to register `oracle` / `Oracle Database` and assign this slug to Oracle/Linux. Modified [article-order.php](../../config/article-order.php) to place Oracle after the existing Linux security auditor, preserving established priority.

The migration was applied to the local CMS after a consistent SQLite backup at `.runtime/cms-before-oracle-20261009.sqlite`. Final source updates were imported with `articles:import-legacy --update-existing --slug=oracle-database-26ai-installation-oracle-linux`; its output reported exactly one updated article. Ordering was synchronized using the existing service. The migration itself skips existing rows, and its regression test confirms it does not overwrite editorial changes on rerun.

The article is present in the existing homepage/listing, Oracle-tag search, detail page, sitemap and legacy `.html` redirect. Related articles resolve through the existing system. No existing article source was edited by this work.

## Validation results

- Full PHPUnit suite: **163 tests, 23,561 assertions, zero failures/errors, one skip** (MySQL-specific constraint test; local test backend is SQLite). Final JUnit: `storage/app/oracle-article-full-final.xml`.
- Final focused Oracle, Linux security auditor and ordering tests: **6 tests, 347 assertions, all passed**, including a rerun after the final technical wording/command review. JUnit: `storage/app/oracle-article-focused-final.xml`.
- Static bilingual verification: 14 sections per language, 48 identical code blocks, 21 Bash examples, 10 byte-identical LF templates/public copies, six dimension/hash-checked PNGs and 42 official references.
- `bash -n` passed for the backup script and the combined 21 Bash blocks. Parsing did not execute any Linux/Oracle command.
- `node scripts/check-images.cjs`: 111 source images, 331 references, zero errors at final article validation.
- `node scripts/check-documentation.cjs`: 150 Markdown documents, 898 local links, zero broken links after delivery documentation was added.
- `node scripts/check-repository-security.cjs`: 842 files inventoried, 706 text files scanned, zero security/layout errors after delivery documentation was added.
- Existing frontend tests: six passed. Composer strict validation passed. PHP migration lint passed. Article routes, Blade compilation and `git diff --check` passed.
- Browser QA verified actual Persian RTL and English LTR views, existing theme/banner/TOC, diagram rendering, narrow-screen wrapping and internal horizontal scrolling for code/tables. At the observed narrow content viewport of 283 px, document width was 272 px; code remained LTR and table wrappers retained their contents without page overflow. This is an observed in-app viewport rather than a claim of a particular physical device model.
- Image contact-sheet review found and corrected one diagram text-layout issue before delivery. All six final assets were inspected.

Local browser evidence is saved under ignored `storage/app/`: `oracle-desktop-en.jpg`, `oracle-desktop-fa.jpg`, `oracle-backup-en.jpg`, `oracle-narrow-en.jpg`, `oracle-narrow-fa.jpg` and `oracle-contact-sheet.png`. These are QA evidence, not article images.

The initial full-suite run detected the existing requirement that Linux security auditor lead the Linux category. The ordering was corrected; the full rerun and focused regressions passed. No test expectation was weakened.

The project has no npm build/package manifest. Applicable existing checks and Blade compilation were run rather than reporting an inapplicable npm build.

## Runtime limitations

This Windows repository does not provide an Oracle Linux 9 server, Enterprise binaries, licenses or a configured keystore. Database installation, Linux service/SELinux/firewall execution, SQL/RMAN execution, backup scheduling, TLS negotiation and actual restore/recovery have **not** been executed. Bash syntax checks, reference review and website tests do not establish those operational outcomes. The article explicitly states this in both languages.

Deployment requires a currently certified OS/kernel/RU combination, valid Enterprise/optional-feature entitlements, reviewed environment-specific values, provisioned mounts/DNS, CA-signed wallets, independently protected keys/off-host storage, and a real isolated restore drill. Oracle download authentication/licensing is performed through an approved channel; the article does not fabricate unattended download URLs. Backup retention cleanup and destructive recovery operations have explicit prerequisites. Production transfer/monitoring integrations remain environment-specific.

No repository commit, remote push or production publication/deployment was performed. Concurrent Apache Tomcat changes were present during this task and were preserved. The Git snapshot below distinguishes the complete workspace from this article's deliverables.

## Final Git status

The following snapshot is generated after the delivery files exist. Oracle files are new/untracked and the two shared registration files are modified. The workspace also contains unrelated concurrent Tomcat work.

```text
 M app/Services/ArticleTagAssigner.php
 M config/article-order.php
?? database/migrations/2026_10_09_000048_add_apache_tomcat_article.php
?? database/migrations/2026_10_09_000048_add_oracle_enterprise_article.php
?? docs/qa/apache-tomcat-article-2026-10-09.md
?? docs/qa/oracle-database-26ai-article-2026-10-09.md
?? public/downloads/apache-tomcat-linux-installation-security-hardening/
?? public/downloads/oracle-database-26ai-installation-oracle-linux/
?? resources/assets/img/articles/banners/apache-tomcat-linux-security-banner.png
?? resources/assets/img/articles/banners/oracle-database-26ai-banner.png
?? resources/assets/img/articles/content/apache-tomcat-production-architecture.png
?? resources/assets/img/articles/content/apache-tomcat-security-hardening.png
?? resources/assets/img/articles/content/apache-tomcat-systemd-service.png
?? resources/assets/img/articles/content/oracle-database-architecture.png
?? resources/assets/img/articles/content/oracle-database-installation-workflow.png
?? resources/assets/img/articles/content/oracle-database-rman-backup-recovery.png
?? resources/assets/img/articles/content/oracle-database-security-hardening.png
?? resources/assets/img/articles/content/oracle-database-systemd-service.png
?? resources/content/articles/apache-tomcat-linux-installation-security-hardening/
?? resources/content/articles/oracle-database-26ai-installation-oracle-linux/
?? resources/legacy/articles/apache-tomcat-linux-installation-security-hardening.html
?? resources/legacy/articles/oracle-database-26ai-installation-oracle-linux.html
?? scripts/build-oracle-article.py
?? scripts/build-oracle-images.py
?? scripts/build-tomcat-article.py
?? tests/Feature/ApacheTomcatArticleTest.php
?? tests/Feature/OracleEnterpriseArticleTest.php
```
