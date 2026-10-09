# Enterprise Zabbix article implementation report

Date: 9 October 2026. Repository: `jalalianamirhossein-ui/MEET-AJ-PORTFOLIO`.

Implemented the complete English and Persian article using the existing Laravel article importer, language attributes, SEO/schema pipeline, navigation, image directories and public-download conventions. Oracle 26ai, Tomcat, MongoDB, Redis and Linux Security Auditor sources were inspected. Existing articles and the shared website layout were not edited.

Article route: `/articles/zabbix-server-linux-windows-agents-backup`.
Intended canonical URL: [Zabbix installation and recovery article](https://meetaj.ir/articles/zabbix-server-linux-windows-agents-backup). This report does not assert that the URL has been deployed or that the CMS database import has run.

## Article and CMS files

- [Bilingual HTML source](../../resources/legacy/articles/zabbix-server-linux-windows-agents-backup.html)
- [Complete English article](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/article.en.md)
- [Complete Persian article](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/article.fa.md)
- [Metadata and compatibility record](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/metadata.json)
- [Image dimensions and SHA256 manifest](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/images.json)
- [CMS registration migration](../../database/migrations/2026_10_09_000049_add_zabbix_enterprise_article.php)
- [Laravel integration tests](../../tests/Feature/ZabbixEnterpriseArticleTest.php)
- [Article builder](../../scripts/build-zabbix-article.py), [image preparation](../../scripts/prepare-zabbix-images.py), [static verifier](../../scripts/verify-zabbix-article.py)

Modified integration files: `app/Services/ArticleTagAssigner.php` adds Zabbix and its five article tags; `config/article-order.php` adds the article after Linux Security Auditor. The migration imports only this slug without overwriting existing CMS content. Its rollback preserves editorial content. Existing importer behavior supplies listing, search, related cards, redirects and database-driven sitemap registration; focused tests cover these paths but have not run without PHP.

There are 17 sections: introduction, all 13 requested chapters, conclusion, FAQ and references/downloads. The article includes 46 code blocks, eight bilingual FAQ entries, bilingual captions/alt text, canonical/Article/FAQ metadata, related articles and operational checklists. Code remains identical between languages. All example endpoint addresses use RFC 5737; domains use example.com.

## Generated images

All six files are actual generated PNGs. Diagram arrows and labels were visually reviewed; the architecture and recovery illustrations were revised for technical accuracy. Images were downsampled/padded without cutting labels. The manifest records final hashes. Matching public copies were created under ignored `public/assets/img/articles/`.

| Source image | Dimensions |
| --- | --- |
| [zabbix-enterprise-monitoring-banner.png](../../resources/assets/img/articles/banners/zabbix-enterprise-monitoring-banner.png) | 512 × 512 |
| [zabbix-enterprise-architecture.png](../../resources/assets/img/articles/content/zabbix-enterprise-architecture.png) | 1920 × 1080 |
| [zabbix-server-installation-workflow.png](../../resources/assets/img/articles/content/zabbix-server-installation-workflow.png) | 1920 × 1080 |
| [zabbix-linux-windows-agent-topology.png](../../resources/assets/img/articles/content/zabbix-linux-windows-agent-topology.png) | 1920 × 1080 |
| [zabbix-security-hardening.png](../../resources/assets/img/articles/content/zabbix-security-hardening.png) | 1920 × 1080 |
| [zabbix-backup-disaster-recovery.png](../../resources/assets/img/articles/content/zabbix-backup-disaster-recovery.png) | 1920 × 1080 |

## Reusable downloads

Each of the following has a byte-identical public copy under `public/downloads/zabbix-server-linux-windows-agents-backup/`, and is included in the [complete ZIP](../../public/downloads/zabbix-server-linux-windows-agents-backup/zabbix-configuration-package.zip). A matching source ZIP is retained with the article. There are 12 individual downloads plus the ZIP.

- [Linux Agent 2 configuration](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/zabbix-agent2-linux.conf)
- [Windows Agent 2 configuration](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/zabbix-agent2-windows.conf)
- [PowerShell MSI installer](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/install-zabbix-agent2.ps1)
- [Backup Bash script](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/zabbix-backup.sh)
- [Backup environment template](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/zabbix-backup.env)
- [Backup service](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/zabbix-backup.service)
- [Daily timer](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/zabbix-backup.timer)
- [Failure notification service](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/zabbix-backup-failure.service)
- [Notification hook](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/zabbix-backup-notify)
- [Restore runbook](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/restore-runbook.md)
- [Security checklist](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/security-checklist.md)
- [Bilingual README](../../resources/content/articles/zabbix-server-linux-windows-agents-backup/README.md)

Backup controls include root-only storage, mandatory mounted backup volume, protected state-directory flock, conservative capacity check, consistent custom-format pg_dump, compressed configuration archive, full dump decoding and checksums, atomic publication, exit codes/logging, independent failure notification, encrypted restic off-site integration and retention after successful verification/upload. Database passwords are not embedded. Remote retention and notification transport require operator configuration. Integrity decoding is explicitly distinguished from a real restoration test.

The Windows installer requires approved MSI SHA256 and valid approved Authenticode signer, protected unique PSK input, restricted ACLs and a scoped firewall rule. It refuses an existing agent. It does not execute during website validation.

## Version compatibility decisions

| Component | Decision and verification |
| --- | --- |
| Zabbix Server/Frontend/SQL/Agent 2 | Latest released LTS branch 7.0; stable 7.0.31 verified for this review. Official Noble amd64 Server and Agent 2 package entries: `1:7.0.31-1+ubuntu24.04`. Keep Server/Frontend/SQL on the same patch. |
| Zabbix 8.0 | Review-time 8.0.0rc1 is excluded from production guidance. |
| Operating system | Ubuntu Server 24.04 LTS, maintained official repositories. |
| PostgreSQL | Ubuntu PostgreSQL 16; same-major physical restore, compatible pg_restore for logical recovery. |
| Nginx / PHP-FPM | Ubuntu 1.24 / PHP 8.3 meet Zabbix 7.0 requirements; actual security revisions are discovered with APT, not falsely frozen. |
| Windows | Server 2022/2025 x64, stable OpenSSL Agent 2 MSI. `DONOTSTART=1` requires 7.0.22 or newer; prefer verified 7.0.31/current stable 7.0 patch. |
| Templates | Official Linux/Windows by Zabbix agent templates support Agent 2; active variants used for active collection. |

Sources checked: [Zabbix lifecycle](https://www.zabbix.com/life_cycle_and_release_policy), [7.0.31 release notes](https://www.zabbix.com/rn/rn7.0.31), [official Ubuntu package index](https://repo.zabbix.com/zabbix/7.0/ubuntu/dists/noble/main/binary-amd64/Packages), [official Ubuntu selector](https://www.zabbix.com/download?zabbix=7.0&os_distribution=ubuntu&os_version=24.04&components=server_frontend_agent&db=pgsql&ws=nginx), [7.0 requirements](https://www.zabbix.com/documentation/7.0/en/manual/installation/requirements). The article also links official MSI, encryption, template, notification, MFA/API, PostgreSQL, pgBackRest and restic documentation at the relevant chapters. Recheck repository candidates and lifecycle at deployment time.

## Validation results

| Check | Result |
| --- | --- |
| Article static verifier | PASS: 17 sections/unique IDs/TOC, both languages, eight FAQ entries, translation attributes, local related slugs, image paths, all 13 download links, ZIP integrity/content parity. |
| Images specific to this article | PASS: six PNGs decode and match requested dimensions, SHA256 manifest and public copies; bilingual alt/caption metadata. |
| Bash syntax | PASS: backup script, notification hook and all 30 Bash article blocks via `bash -n`. No installation commands executed. |
| PowerShell syntax | PASS: native PowerShell AST parser for installer and all three article blocks. No MSI/configuration actions executed. |
| Repository security check | PASS: `node scripts/check-repository-security.cjs`, zero errors. |
| Documentation links | PASS: `node scripts/check-documentation.cjs`, zero broken local links. |
| Git whitespace | PASS: `git diff --check`; normal Windows line-ending warnings only. |
| Full repository image check | FAIL: 40 existing unrelated banner source/public mismatches. All six new Zabbix images pass. Unrelated assets left intact. |
| Frontend tests | 5 PASS, 1 FAIL: Swiper source/public asset equality. The prototype-pollution exercise completes before that equality assertion; unrelated vendor files left intact. |
| Laravel/PHPUnit focused and full suite | BLOCKED: PHP executable is unavailable. Existing vendor dependencies are present. Official portable PHP download attempts timed out/reset; focused and full commands could not start. No claim of passing tests or PHP lint. |
| Local CMS migration / browser render | NOT RUN: no usable PHP runtime and no local CMS database. Migration and runtime feature tests delivered for the normal PHP 8.4+ Laravel environment. |
| Live Zabbix installation, backup, restore | NOT RUN: this is a Windows website workspace, without Ubuntu/PostgreSQL/Zabbix or Windows MSI staging instances. |

## Remaining acceptance work

1. In the normal Laravel PHP 8.4+ environment, apply the migration and run focused `vendor/phpunit/phpunit/phpunit --filter ZabbixEnterpriseArticleTest`, then the full suite. Verify both locale views, listing, tag/search, related cards, download HTTP responses, redirect and sitemap. Review rendered desktop/mobile RTL/LTR pages.
2. Provision isolated Ubuntu 24.04 and Windows Server 2022/2025 staging. Execute installation and current-candidate checks; verify services, frontend, agent/template compatibility and TLS with approved secrets.
3. Validate metric arrival, service/event-log items, trigger PROBLEM/recovery, maintenance/dependencies, supported email/Telegram media and escalation delivery to an approved test destination.
4. Exercise backup success, disk-space failure, overlap rejection, off-site failure, timer startup/timeout notification and retention boundaries. Verify no old successful sets are removed before new verified/off-site success.
5. Retrieve an off-site backup into isolation, restore compatible database/configuration/keys/scripts, validate historical and fresh lab metrics/alerts and record achieved RPO/RTO. For large deployments, test physical backup/WAL/PITR coverage and restore.

## Git status and scope

No commit, push or deployment was performed. New article/package/images/migration/tests/helpers/report remain untracked; only the tag assigner and article-order configuration were edited for this task. Public asset copies and temporary QA output are ignored by existing repository rules.

Pre-existing unrelated changes preserved: `README.md`, `app/Http/Controllers/SitemapController.php`, `docs/current/SEO.md`, and untracked `tests/Feature/SitemapRobotsTest.php`. No unrelated article source or image was changed.
