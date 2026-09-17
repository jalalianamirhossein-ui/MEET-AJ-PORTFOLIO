# Content integrity — Meet AJ

**Date:** 2026-09-17
**Command:** `php artisan site:compare-content` (`App\Console\Commands\CompareLegacyContent`)
**Result:** **Failures: 0**
**Related:** [../current/ARTICLES.md](../current/ARTICLES.md) · [QA-MATRIX.md](QA-MATRIX.md)

## What the command proves

For every imported article it renders the Laravel page and compares it against the original file in `articles/*.html`, checking four things per article:

1. complete body content,
2. bilingual attributes (`data-en` / `data-fa`) preserved,
3. headings preserved,
4. SEO tokens preserved.

## Result

All **23** articles reported `PASS` with the note “complete body, bilingual attributes, headings, seo”:

`creating-a-bootable-usb`, `downgrade-mikrotik-routeros-firmware-safely`, `enable-ssh-linux-complete-guide`, `http-vs-https-ssl-certificate-impact`, `imap-vs-pop3-email-protocol-comparison`, `install-dfs-server-windows-server`, `install-mikrotik-chr-vmware-workstation`, `install-vmware-esxi-vmware-workstation-vmcisr`, `linux-cli-common-commands`, `linux-security-account-access-management`, `mikrotik-block-port-scanners`, `mikrotik-block-website`, `mikrotik-openvpn-setup-v7`, `mikrotik-unequal-dual-wan-load-balancing-ecmp`, `nginx-installation-configuration-ubuntu`, `set-static-ip-ubuntu-server-netplan`, `sql-server-automatic-backup-job`, `ubuntu-date-time-settings`, `vmware-esxi-8-installation-basic-configuration`, `vsphere-standard-switch-vs-distributed-switch`, `windows-cmd-common-network-commands`, `windows-hardware-info-cmd-vs-dxdiag`, `windows-password-reset-secure-access-recovery`.

Summary line: `Failures: 0`.

## Original files are never modified

The importer reads `articles/*.html` and `services/*.html` and never writes back to them. `scripts/verify-originals.php` can re-check the original tree against the SHA-256 baseline in `docs/baseline-files.json`.

## Scope and limits

| Item | Status |
|------|--------|
| 23 articles compared against source HTML | PASS |
| Homepage and service pages | Covered by the same command run in the master audit pass on 2026-09-17 |
| Persian rendering correctness (meaning, not markup) | NOT TESTED — the command checks that `data-fa` attributes survive, not translation quality |
| Image files byte-compared | NOT TESTED |
| Production output on meetaj.ir | BLOCKED |

## History

Earlier repairs made while reaching this clean result are recorded in [../historical/content-integrity-fixes.md](../historical/content-integrity-fixes.md). That log is historical; this file is the current result.
