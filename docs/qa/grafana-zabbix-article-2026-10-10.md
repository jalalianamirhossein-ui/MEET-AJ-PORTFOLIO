# Grafana and Zabbix enterprise article implementation report

Implementation date: 10 October 2026. Official version review: 9 October 2026. Repository: `jalalianamirhossein-ui/MEET-AJ-PORTFOLIO`.

## Publication-order correction (10 October 2026)

The original configured placement below Zabbix has been superseded. Public lists,
the sitemap and the default admin table now use publication time descending,
then article ID descending for identical timestamps. Grafana's publication date
was incorrectly copied from its technical review date; source schema and package
metadata now publish on 10 October, while the version review remains 9 October.
Both public/source ZIPs contain the corrected metadata. Same-day publication
dates for Oracle, Tomcat and Zabbix agree with their first repository additions
on 9 October and have been preserved.

[Guarded migration](../../database/migrations/2026_10_10_000054_use_chronological_article_order.php)
repairs only the known original Grafana import, preserving CMS edits, manual
publication dates, visibility and timestamps. The configured local CMS database
is still missing, so no real database or deployed site has been changed.
See [current ordering and deployment instructions](../current/ARTICLE-ORDERING.md).

Regression checks cover chronological home/library/search/tag results, same-day
and identical timestamps, unpublished/future visibility, admin ordering, migration
idempotence and manual date protection. [Feature tests](../../tests/Feature/ArticleOrderingTest.php)
and existing Persian date tests pass. Documentation links, image references,
repository security and Grafana package/syntax checks pass. No commit, push or
deployment was performed for this correction. The implementation record below
retains the original article work and its dated validation evidence.

Implemented the complete bilingual article directly in the existing Laravel website. The Zabbix, Oracle, Apache Tomcat, Redis, MongoDB and Linux Security Auditor articles informed the importer, localization, image, SEO, navigation and download conventions. Shared website components retain their existing layout.

English title: **Install Grafana on Ubuntu and Integrate with Zabbix – Complete Enterprise Monitoring Guide**.

Persian title: **آموزش جامع نصب گرافانا روی لینوکس و اتصال به زبیکس همراه با ساخت داشبوردهای مانیتورینگ سازمانی**.

Slug: `grafana-installation-zabbix-integration`. Route: `/articles/grafana-installation-zabbix-integration`. Intended canonical URL: [Grafana and Zabbix monitoring article](https://meetaj.ir/articles/grafana-installation-zabbix-integration). This URL describes the implemented route; deployment has not been performed.

## Article and integration

- [Bilingual CMS HTML source](../../resources/legacy/articles/grafana-installation-zabbix-integration.html)
- [English article](../../resources/content/articles/grafana-installation-zabbix-integration/article.en.md) and [Persian article](../../resources/content/articles/grafana-installation-zabbix-integration/article.fa.md)
- [Metadata, SEO, FAQ and version record](../../resources/content/articles/grafana-installation-zabbix-integration/metadata.json)
- [Package README](../../resources/content/articles/grafana-installation-zabbix-integration/README.md)
- [CMS registration migration](../../database/migrations/2026_10_10_000053_add_grafana_zabbix_article.php)
- [Laravel integration tests](../../tests/Feature/GrafanaZabbixArticleTest.php) and [collector behavior tests](../../tests/test_grafana_collector.py)
- [Article builder](../../scripts/build-grafana-article.py), [download builder](../../scripts/build-grafana-package.py), [image renderer](../../scripts/build-grafana-images.py) and [static verifier](../../scripts/verify-grafana-article.py)

The article contains all 15 requested chapters, followed by conclusion, eight FAQ entries and references/downloads: 18 authored sections in total. It includes 42 identical English/Persian code blocks, bilingual prose, tables, alt text and captions, explicit LTR commands, and 12 operational troubleshooting scenarios using Symptoms → Root Cause → Diagnostic Commands → Expected Output → Resolution.

The migration imports only the new slug without replacing existing CMS edits or publishing an existing draft. It also adds the reciprocal link to an existing Zabbix CMS row when absent, preserving that row's editorial copy, status, metadata and timestamps. The existing importer supplies listing/search, category, tags, redirects, localized SEO and the database-driven sitemap. Registration, canonical rendering and sitemap inclusion pass isolated database tests. The configured local SQLite database `.runtime/cms.sqlite` is missing; the migration has not been applied to a real CMS database.

Integration changes:

- `app/Services/ArticleTagAssigner.php`: Grafana, Zabbix, Monitoring, Linux, DevOps, Infrastructure and Observability tags using the existing English technical-tag convention.
- `config/article-order.php`: the article follows the existing enterprise Zabbix installation article.
- `resources/content/article-structure.json`: chapter order, dependencies and canonical aliases for installation/checklist.
- `resources/content/article-technical-contracts.json`: preservation hashes for all 42 reviewed command/configuration blocks.
- The Zabbix HTML, English/Persian source articles and builder now include a reciprocal related-article link. Other Zabbix procedures were preserved.
- `docs/DOCUMENTATION-INDEX.md`: generated inventory includes the new source documents and this report.

## Official versions and compatibility

| Component | Verified baseline | Evidence |
| --- | --- | --- |
| Ubuntu Server | 24.04 LTS | Target operating system |
| Grafana OSS | 13.2.3 stable, released 29 September 2026 | [Official stable download selector](https://grafana.com/grafana/download?edition=oss) |
| Zabbix application plugin | 6.9.1 stable; minimum Grafana 11.6.0 | [Official signed plugin catalog](https://grafana.com/grafana/plugins/alexanderzobnin-zabbix-app/) |
| Existing Zabbix Server/API | 7.0 LTS | [Official plugin configuration and token support](https://grafana.com/docs/plugins/alexanderzobnin-zabbix-app/latest/configure/) |

Compatibility is based on official documentation and versioned source inspection, rather than a live integration claim. The article installs from the stable Grafana APT repository, verifies the scoped signing key, records the actual installed version and excludes beta/nightly/RC channels.

Dashboard queries match [plugin v6.9.1 query definitions](https://github.com/grafana/grafana-zabbix/blob/v6.9.1/src/datasource/types/query.ts), including schema 13, Metrics/Text/Problems query types, structured query variables and the Problems panel identifier. Linux and Windows item names/keys were checked against the official Zabbix 7.0 OS templates. Windows Event Log is explicitly configured as a supported custom active item; the article does not claim default template collection of arbitrary event channels.

## Generated PNG images

All seven assets are real optimized PNG files, decoded and dimension/hash verified. The banner was generated with the built-in ImageGen tool and resized to the requested dimensions. The six technical illustrations were rendered from original deterministic drawing code with readable English labels, accurate relationships and clearly marked illustrative dashboard values.

Banner design brief: modern dark enterprise monitoring, Grafana and Zabbix branding, Linux and Windows servers, dashboard screens, HTTPS/API relationships, English text only. Technical diagrams prioritize readable labels and verified API/backup relationships. No actual credential or live monitoring screenshot is present.

| Source filename | Dimensions | Bytes |
| --- | --- | ---: |
| `banners/grafana-zabbix-enterprise-monitoring-banner.png` | 1000 × 1000 | 1,255,820 |
| `content/grafana-zabbix-enterprise-architecture.png` | 1920 × 1080 | 126,563 |
| `content/grafana-ubuntu-installation-workflow.png` | 1920 × 1080 | 94,041 |
| `content/grafana-zabbix-plugin-integration.png` | 1920 × 1080 | 128,902 |
| `content/grafana-linux-windows-monitoring-dashboard.png` | 1920 × 1080 | 114,274 |
| `content/grafana-enterprise-noc-dashboard.png` | 1920 × 1080 | 117,004 |
| `content/grafana-security-backup-architecture.png` | 1920 × 1080 | 130,245 |

Source root: `resources/assets/img/articles/`. Matching public copies use the existing ignored `public/assets/img/articles/` convention. Each content image appears in its corresponding chapter; the banner uses the shared hero component. [Image manifest](../../resources/content/articles/grafana-installation-zabbix-integration/images.json) records SHA256, dimensions, size and provenance. Both the contact sheet and English/Persian browser screenshots were visually inspected.

## Dashboard JSON definitions

| Authored importable dashboard | Panels | Key behavior |
| --- | ---: | --- |
| [Linux dashboard](../../resources/content/articles/grafana-installation-zabbix-integration/linux-dashboard.json) | 11 | CPU/load, memory, filesystem, network/interface traffic, uptime, availability and Problems |
| [Windows dashboard](../../resources/content/articles/grafana-installation-zabbix-integration/windows-dashboard.json) | 12 | CPU/memory/disk/network, services, uptime, availability, explicitly configured Event Log and Problems |
| [Enterprise NOC dashboard](../../resources/content/articles/grafana-installation-zabbix-integration/enterprise-noc-dashboard.json) | 13 | Eight overview/freshness stats, four Top 10 performance panels and current Problems |

These are valid authored classic Grafana dashboard structures, rather than fabricated exports. They contain no data points, credentials or purported live values. They use the provisioned `zabbix-enterprise` data-source UID and supported core/Problems panel types.

NOC counts are implemented by the included HTTPS API collector and eight-item Zabbix trapper template. Host and problem counts share the same monitored-host scope; unknown availability remains explicit. Critical means Zabbix Disaster severity 5, and High means exactly severity 4. Empty authorization scope, API failure and sender rejection fail instead of publishing false zeros. The template includes a five-minute collector-freshness trigger. The last-success date uses the official `scale(1000)` function to convert epoch seconds to Grafana milliseconds.

Performance ranking reduces each series to its latest value, sorts descending and limits to ten. Interface All retains the inbound-item filter; NOC group All combines actual environment-filtered group options. Overview counts describe the configured collector environment independently of narrowed performance selectors. Zabbix host permissions remain the access-control boundary.

## Downloadable configurations

The source folder is `resources/content/articles/grafana-installation-zabbix-integration/`. Twenty-five individually linked downloads and the complete ZIP are published as local files under `public/downloads/grafana-installation-zabbix-integration/`. English/Persian article Markdown, metadata and image manifest are also included in the ZIP/public folder.

- HTTPS: [ACME bootstrap](../../public/downloads/grafana-installation-zabbix-integration/nginx-bootstrap.conf), [Nginx production proxy](../../public/downloads/grafana-installation-zabbix-integration/nginx-grafana.conf), [Grafana INI](../../public/downloads/grafana-installation-zabbix-integration/grafana.ini).
- Provisioning: [Zabbix data source](../../public/downloads/grafana-installation-zabbix-integration/zabbix-datasource.yaml), [Zabbix application](../../public/downloads/grafana-installation-zabbix-integration/zabbix-app.yaml), [dashboard provider](../../public/downloads/grafana-installation-zabbix-integration/dashboard-provider.yaml), [environment template](../../public/downloads/grafana-installation-zabbix-integration/grafana.env.example), [systemd environment drop-in](../../public/downloads/grafana-installation-zabbix-integration/grafana-environment.conf).
- Dashboards: the Linux, Windows and enterprise NOC JSON definitions linked above, mirrored in public downloads.
- API/NOC: [safe API probe](../../public/downloads/grafana-installation-zabbix-integration/zabbix-api-probe.py), [collector](../../public/downloads/grafana-installation-zabbix-integration/noc-collector.py), [collector environment](../../public/downloads/grafana-installation-zabbix-integration/noc-collector.env.example), [service](../../public/downloads/grafana-installation-zabbix-integration/noc-collector.service), [timer](../../public/downloads/grafana-installation-zabbix-integration/noc-collector.timer), [Zabbix trapper template](../../public/downloads/grafana-installation-zabbix-integration/zabbix-noc-template.json).
- Optional database performance: [minimum read-only PostgreSQL grants](../../public/downloads/grafana-installation-zabbix-integration/postgres-readonly.sql). Direct DB remains optional and API metadata/authorization remains required.
- Recovery: [backup script](../../public/downloads/grafana-installation-zabbix-integration/grafana-backup.sh), [backup environment](../../public/downloads/grafana-installation-zabbix-integration/grafana-backup.env.example), [backup service](../../public/downloads/grafana-installation-zabbix-integration/grafana-backup.service), [daily timer](../../public/downloads/grafana-installation-zabbix-integration/grafana-backup.timer), [bilingual restore runbook](../../public/downloads/grafana-installation-zabbix-integration/restore-runbook.md), [bilingual security checklist](../../public/downloads/grafana-installation-zabbix-integration/security-checklist.md), [README](../../public/downloads/grafana-installation-zabbix-integration/README.md).
- [Complete downloadable configuration ZIP](../../public/downloads/grafana-installation-zabbix-integration/grafana-configuration-package.zip).

Environment files contain conspicuous replacement values only. API tokens use secure provisioning fields and protected local files. Secret generation preserves an existing encryption key. Backup archives contain sensitive configuration and TLS keys and require encrypted off-site storage; they must never be copied into public downloads or Git.

## Validation evidence

| Check | Result |
| --- | --- |
| Article/static verifier | PASS: 15 chapters, 18 sections, 42 identical bilingual code blocks, 29 Bash blocks, seven PNGs, three dashboards, three provisioning YAML files, 25 download files and ZIP parity |
| Focused Laravel integration | PASS: three tests, 329 assertions, including preservation/idempotence of the reciprocal CMS link |
| Shared article structure plus focused tests | PASS: six tests, 12,499 assertions |
| Collector behavior | PASS: three tests; mixed/unknown availability, matching problem scope and failure handling |
| Existing Python article tests | PASS: seven tests |
| Repository article structure audit | PASS: 42 bilingual sources, zero issues |
| Nginx syntax | PASS: bootstrap and HTTPS configurations using official portable Nginx 1.28.0; HTTPS paths adapted to an ignored temporary test certificate |
| PHP syntax | PASS: migration, new integration test and modified tag assigner |
| Desktop/mobile browser | PASS: English/Persian at 1365 × 900 and 390 × 844; TOC, copy buttons, locale switching, LTR code, overflow, images and downloads |
| Full Laravel suite | Completed: 189 tests, 54,555 assertions, zero errors, one preservation-snapshot failure during source regeneration, one MySQL-dependent skip; final stable technical-content rerun recorded below |
| Final stable technical-content rerun | PASS: four tests, 237 assertions; the full-suite preservation mismatch is resolved against the final files |
| Regeneration | PASS: all repository builders in an isolated copy; zero normalization, heading, code, image, section-reference or technical-content drift, and stable repeated generation |
| Documentation | PASS: 167 Markdown documents, 1,118 local links, zero broken links |
| Images | PASS: 124 source images, 379 references, zero errors |
| Repository security | PASS: 992 files inventoried, 839 text files scanned, zero errors |
| Git whitespace | PASS: `git diff --check` |

The initial full feature run executed 189 tests with 25,124 assertions, 13 failures, one error and one skip. One failure identified the empty architecture anchor and was repaired by attaching it to the authored architecture figure. The remaining failures involved an existing article scheduled for 10 October 2026 at 10:30 Asia/Tehran, Windows sandbox file-read restrictions, and disabled PHP GD. The final rerun enables installed GD, runs outside those sandbox file-read restrictions and uses an isolated QA clock of 10 October 2026 at 12:00 Asia/Tehran. Production dates, the system clock and database contents are not changed by that QA clock. MySQL-only coverage is unavailable without a MySQL test server.

During the full rerun, three health-check command blocks were corrected to send the configured Host header when `enforce_domain=true`. A technical-content test had already imported the earlier commands and later compared them against the newly rebuilt preservation hashes. That single mismatch is a concurrent regeneration/test snapshot, rather than a claim that the full invocation exited successfully. With source regeneration finished, all four `ArticleTechnicalContentTest` methods passed (237 assertions), including the failing complete bilingual Blade-artifact check. The final focused article tests also passed (three tests, 329 assertions). No known article failure remains; the full invocation and targeted rerun are reported separately.

Transient logs, JUnit XML, browser fixtures/screenshots, temporary dependencies and Nginx test certificates reside under ignored `storage/app/` or `.runtime/`. The tracked article/configuration files contain no test certificate private keys. Runtime QA conditions are documented rather than hidden by changes to application behavior.

## Runtime testing limitations

No authorized live Zabbix API, Linux/Windows agents or running Grafana instance was supplied. Real API authentication, dashboard import/rendering against OS items, TLS issuance/renewal, notifications and complete backup/restore/decryption drills have not been performed. Collector unit tests mock the API and sender. Nginx syntax validation establishes configuration syntax, not a deployed HTTPS service. Static JSON/YAML validation establishes structure and documented compatibility, not successful operational integration.

The local configured CMS database is absent. CMS behavior is verified with isolated SQLite test databases; registration will take effect when the new migration is applied to the intended CMS database. No production connection was attempted.

## Git state

The initial checkout was clean. All current modifications belong to this article implementation and reciprocal Zabbix/documentation integration. Existing unrelated content and website components were preserved. Final `git status --short --untracked-files=all`: nine modified tracked files and 76 new untracked files, all uncommitted.

| Created file group | Count |
| --- | ---: |
| Article source/package folder (25 downloads, English/Persian Markdown, metadata, image manifest and ZIP) | 30 |
| Matching public download folder | 30 |
| PNG banner and content illustrations | 7 |
| Bilingual legacy/CMS HTML source | 1 |
| Builder/validation scripts | 4 |
| Laravel and Python test files | 2 |
| CMS registration migration | 1 |
| This implementation report | 1 |

No commit, push, pull request, deployment or remote database change was performed.
