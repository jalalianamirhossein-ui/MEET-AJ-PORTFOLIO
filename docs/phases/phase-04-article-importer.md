> **HISTORICAL phase log.** Current importer behaviour and counts: [../current/ARTICLES.md](../current/ARTICLES.md). Current status: [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md).

# Phase 04 — Article importer

**Phase:** 04
**Date:** 2026-09-16
**Status:** **PASS** — 23 of 23 articles imported and repeatable

## Objective

Move 23 static article pages into the CMS without losing body HTML, bilingual attributes, SEO metadata or existing URLs.

## Changes

- Command `php artisan articles:import-legacy {--dry-run} {--refresh}`.
- Reads the 23 original `articles/*.html` files plus the homepage portfolio cards.
- Stores English body HTML, bilingual `data-en` / `data-fa`, table-of-contents HTML, metadata, canonical / Open Graph / Twitter values, JSON-LD when present, the source path and a SHA-256 hash.
- Writes one `/articles/{slug}.html` → article 301 row per file into `article_redirects`.
- Transactional; existing slugs are skipped unless `--refresh`, which deletes previously imported articles and redirects.
- Publication dates come from the original `datePublished` / `dateCreated`, falling back to the source file mtime. `now()` is never used.

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

## Tests

`test_all_articles_redirect_once_and_render` asserts 23 rows, a single 301 from each `.html` path, 200 on each clean URL, canonical tags, JSON-LD, `data-fa` attributes and `#article-content`. HTTP crawl matched. An unknown slug returned 404, and the query string `?ref=nav` survived the redirect.

## Results

`site:compare-content` on 2026-09-16: **Failures: 0** — all 23 articles PASS for complete body, bilingual attributes and headings.

23 slugs imported: `creating-a-bootable-usb`, `downgrade-mikrotik-routeros-firmware-safely`, `enable-ssh-linux-complete-guide`, `http-vs-https-ssl-certificate-impact`, `imap-vs-pop3-email-protocol-comparison`, `install-dfs-server-windows-server`, `install-mikrotik-chr-vmware-workstation`, `install-vmware-esxi-vmware-workstation-vmcisr`, `linux-cli-common-commands`, `linux-security-account-access-management`, `mikrotik-block-port-scanners`, `mikrotik-block-website`, `mikrotik-openvpn-setup-v7`, `mikrotik-unequal-dual-wan-load-balancing-ecmp`, `nginx-installation-configuration-ubuntu`, `set-static-ip-ubuntu-server-netplan`, `sql-server-automatic-backup-job`, `ubuntu-date-time-settings`, `vmware-esxi-8-installation-basic-configuration`, `vsphere-standard-switch-vs-distributed-switch`, `windows-cmd-common-network-commands`, `windows-hardware-info-cmd-vs-dxdiag`, `windows-password-reset-secure-access-recovery`.

Original HTML stayed at `articles/` in the repository root, outside `public/`.

## Blockers

- No German translations were created; none exist in the source.
- `--refresh` destroys CMS article rows and must never run against a database with editorial edits.
