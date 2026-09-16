> **HISTORICAL / SUPERSEDED phase log.** Current importer command and article counts: [PROJECT-STATUS.md](../PROJECT-STATUS.md), [README.md](../../README.md).

# Phase 4 — article importer

Status: **23/23 imported** on the local SQLite database. Repeatable via `php artisan articles:import-legacy`.

## Implemented work

- Command: `php artisan articles:import-legacy {--dry-run} {--refresh}`
- Reads original `articles/*.html` (23 files) and homepage portfolio cards.
- Stores EN body HTML, bilingual `data-en`/`data-fa`, TOC HTML, metadata, canonical/OG/Twitter, JSON-LD when present, source path, SHA-256.
- Writes `/articles/{slug}.html` → article 301 rows in `article_redirects`.
- Transactional. Existing slugs are skipped unless `--refresh` (refresh deletes previously imported articles/redirects; local-only, not for production content edits).
- Publication dates: original schema `datePublished` / `dateCreated` when present; otherwise original file mtime (`source_file_mtime`). `now()` is not used.

## Files changed

- `app/Console/Commands/ImportLegacyArticles.php`
- `app/Services/LegacyArticleImporter.php`
- `app/Services/ArticleHtmlSanitizer.php`
- `app/Console/Commands/CompareLegacyContent.php`

## Commands executed

```text
php artisan articles:import-legacy --dry-run
php artisan articles:import-legacy
php artisan articles:import-legacy --refresh
php artisan site:compare-content
```

`site:compare-content` (2026-09-16): **Failures: 0** — all 23 articles PASS (complete body, bilingual attributes, headings).

## Tests executed

`test_all_articles_redirect_once_and_render` asserts 23 rows, 301 from `.html`, 200 on clean URLs, canonical, JSON-LD, `data-fa`, `#article-content`.

HTTP crawl: every `.html` article URL returned `301` to the matching clean URL; every clean URL returned `200`. Unknown slug returned `404`. Query string `?ref=nav` survived in the PHPUnit redirect assertion.

## Real results

Imported slugs (23):

creating-a-bootable-usb, downgrade-mikrotik-routeros-firmware-safely, enable-ssh-linux-complete-guide, http-vs-https-ssl-certificate-impact, imap-vs-pop3-email-protocol-comparison, install-dfs-server-windows-server, install-mikrotik-chr-vmware-workstation, install-vmware-esxi-vmware-workstation-vmcisr, linux-cli-common-commands, linux-security-account-access-management, mikrotik-block-port-scanners, mikrotik-block-website, mikrotik-openvpn-setup-v7, mikrotik-unequal-dual-wan-load-balancing-ecmp, nginx-installation-configuration-ubuntu, set-static-ip-ubuntu-server-netplan, sql-server-automatic-backup-job, ubuntu-date-time-settings, vmware-esxi-8-installation-basic-configuration, vsphere-standard-switch-vs-distributed-switch, windows-cmd-common-network-commands, windows-hardware-info-cmd-vs-dxdiag, windows-password-reset-secure-access-recovery.

Original HTML files remain at repository `articles/`, not in `public/articles/`.

## Blockers

- German translations were not created (none exist in source).
- `--refresh` is destructive of CMS article rows. Do not use it on a production database that has editorial edits.
