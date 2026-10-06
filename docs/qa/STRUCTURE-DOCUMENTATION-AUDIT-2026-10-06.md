# Structure and documentation audit — 2026-10-06

Scope: local repository layout, source ownership, documentation navigation, migration/file inventory, read-only database counts and existing tests. Production was not inspected. Concurrent PBR Client changes appeared during review; they were preserved and reflected in the final inventory.

## Structure changes

- Renamed `resources/content/articles/linux-security-Bash-Check/` to `resources/content/articles/linux-security-auditor-bash/`, matching the article slug. Updated the publisher, article test and package instructions. The public Bash download URL is unchanged.
- Moved `scripts/sql-backup-english.txt` into its article package as `resources/content/articles/sql-server-automatic-backup-job/english-source.txt`; updated the bilingual generator input path. Editorial data now sits with article content rather than executable maintenance tools.
- Added package/script READMEs, an explicit private/public MSI source policy and a generated complete documentation inventory.
- Kept Laravel folders, generated public assets and the retired admin CSS compatibility stub in their established places. The source ZIP remains outside the web root. Optional legacy service HTML and the NetBox PDF source are absent; documentation identifies that state rather than linking to a nonexistent PDF.

## Documentation

Maintained guides now describe content packages, deployment scripts, optional Node checks, current counts and known failures. Archived reports and phases retain their historical test/count evidence, with a current-status notice. Broken archive links were repaired without changing recorded results. Design references explicitly identify the current glass overlay.

`node scripts/check-documentation.cjs --write-index` creates [the complete inventory](../DOCUMENTATION-INDEX.md), then validates relative file links. The check covers root, docs, deploy and resources Markdown; it does not validate external URLs or heading anchors.

## Final local snapshot

| Item | Result |
|---|---|
| Maintained article HTML / local article rows | 28 / 28 |
| Paired localization metadata | 27 rows; completeness not certified |
| Redirects / categories / tags / article-tag links | 28 / 19 / 24 / 50 |
| Services / visible catalog entries | 13 / 12 |
| Testimonials / homepage sections / users / requests | 9 / 7 / 0 / 0 |
| Migration files / local ledger | 23 / 24; all current files report Ran |

The final concurrent migration `2026_10_06_000022_move_pbr_client_download_to_bottom` places the client download at the end of its article. Current configured priority is PBR Client, ping-triggered PBR, then Linux Auditor. The ledger has one historical record beyond the current file set.

## Verification

| Check | Result |
|---|---|
| Documentation local file links | PASS; see generated inventory and checker output |
| Frontend scroll-reveal tests | PASS: 4 tests |
| Full PHP suite | FAIL: 96 tests, 1,369 assertions, 2 errors, 16 failures, 1 skipped |
| Focused Linux/PBR Client suite after the package move | FAIL: 4 tests, 25 assertions, 2 failures; both failures are Linux ordering assertions; PBR Client tests pass |
| Local source/database content comparison | FAIL: 28 differences initially; 27 in the final rerun after concurrent content changes |

The standard PHPUnit entry point could not open its bootstrap: this sandbox reports `is_readable('vendor/autoload.php')` false, although PHP can require the file. To collect evidence, an ignored `storage/app/run-phpunit-audit.php` required Composer autoload directly and invoked PHPUnit with an ignored copy of `phpunit.xml` without the bootstrap attribute, preserving the testing environment and pointing to the existing feature suite. Tracked PHPUnit configuration and vendor code were not changed.

Full-suite errors include missing article localization metadata and a duplicate category slug. Failures include article counts/order and filesystem-readability behavior. Concurrent changes mean this is evidence from the recorded run, not a stable passing release certification. Focused failures assert the old Linux placement (numeric order 1 and former lead article); the maintained configuration includes the additional client article.

The final comparison rerun reported **27 failures**; the initial 28-failure snapshot changed while concurrent PBR work was ongoing. Content comparison reported source hashes and bilingual marker differences. No broad article replacement, migration reset or production operation was performed for this documentation task. Review `articles:import-legacy --update-existing --dry-run` and back up CMS edits before deciding to replace stored content.

## Maintained article filenames

The following list is generated from `resources/legacy/articles/` during this review.

- `creating-a-bootable-usb.html`
- `downgrade-mikrotik-routeros-firmware-safely.html`
- `enable-ssh-linux-complete-guide.html`
- `http-vs-https-ssl-certificate-impact.html`
- `imap-vs-pop3-email-protocol-comparison.html`
- `install-dfs-server-windows-server.html`
- `install-mikrotik-chr-vmware-workstation.html`
- `install-vmware-esxi-vmware-workstation-vmcisr.html`
- `linux-cli-common-commands.html`
- `linux-security-account-access-management.html`
- `linux-security-auditor-bash.html`
- `mikrotik-block-port-scanners.html`
- `mikrotik-block-website.html`
- `mikrotik-openvpn-setup-v7.html`
- `mikrotik-pbr-client.html`
- `mikrotik-ping-triggered-policy-routing.html`
- `mikrotik-unequal-dual-wan-load-balancing-ecmp.html`
- `netbox-installation-setup-ubuntu.html`
- `nginx-installation-configuration-ubuntu.html`
- `oxidized-network-device-configuration-backup.html`
- `set-static-ip-ubuntu-server-netplan.html`
- `sql-server-automatic-backup-job.html`
- `ubuntu-date-time-settings.html`
- `vmware-esxi-8-installation-basic-configuration.html`
- `vsphere-standard-switch-vs-distributed-switch.html`
- `windows-cmd-common-network-commands.html`
- `windows-hardware-info-cmd-vs-dxdiag.html`
- `windows-password-reset-secure-access-recovery.html`

File integrity checks confirm that the moved SQL English input, Linux Bash script and Linux builder are identical to their original Git text after normalizing checkout line endings. PHP syntax checks pass for the publisher, moved builder and updated Linux test. Documentation links: **110 Markdown files, 677 local targets, zero broken file links**. A repository-wide whitespace check identifies a line in the concurrent PBR Client HTML change; that file was preserved.
