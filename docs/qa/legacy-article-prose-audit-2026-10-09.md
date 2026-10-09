# Legacy article prose audit — 2026-10-09

Follow-up to the [full 41-article structure audit](article-structure-audit-2026-10-09.md). The historical collection is the 25 slugs in `docs/enterprise-articles/runbooks.json`; both English and Persian prose were reviewed for completeness, logical dependencies, agreement with the examples, and recovery/acceptance explanations. Shared rendering checks cover all 41 available sources (82 language editions).

This follow-up changes three authored sources and shared localization/TOC rendering. It does not deploy, alter production data, create article records, or change technical commands.

## Confirmed corrections

- All 25 historical articles had generated conclusion copy left in English in Persian server HTML. Twenty-four also had generated best-practice sections with untranslated list items: 49 affected sections. Generated copy now has bilingual leaf paragraphs/list items and is localized when added. Persian generated copy uses the available Persian article title. List/callout structure survives language switching.
- The generated TOC now uses the requested language for visible link text and accessible labels before JavaScript. The same repair applies to the whole collection.
- Single-quoted language attributes now localize correctly (including the MongoDB bootstrap paragraph). Paired download/reference anchor labels also localize: 70 labels across Cisco and MongoDB. Existing link destinations are retained.
- An already bilingual FAQ heading is preserved rather than receiving duplicate language attributes (UniFi).
- DFS: a sentence ending at “from a management host with modules and delegation” now explicitly tells the reader to run the following commands with DFSN/DFSR modules and required delegated rights. Both languages agree.
- Oxidized: complete known-host preparation and foreground-test explanation paragraphs now precede the first foreground command.
- Chrony: the restart/clock-step warning now precedes the command block, directs the reader to execute one command at a time, and requires inspection after `chronyd -p` before restarting.
- The three source corrections are also reflected in the historical EN/FA runbook inputs.

No additional editorial review-date labels were found or removed in this follow-up. The preceding full audit removed 20 labels. Operational timestamps, release/advisory dates and metadata remain preserved.

## Before/after procedure order

| Article | Before | After |
|---|---|---|
| `oxidized-network-device-configuration-backup` | Inventory → foreground command → foreground explanation → unit/start/Git checks → known-host preparation | Inventory → known-host preparation → foreground explanation → foreground command → unit/start/Git checks |
| `ubuntu-date-time-settings` | Source lines → print/restart/verification commands → expected results → restart warning | Source lines → staged-execution/restart warning → unchanged print/restart/verification commands → expected results |
| `install-dfs-server-windows-server` | Share creation → incomplete management-host instruction → DFS commands | Share creation → complete permissions/modules/delegation instruction → unchanged DFS commands |

No H2 sections were moved in this follow-up; the preceding audit already repaired their order. Per-slug before/after section inventories are retained in the full audit report and this follow-up’s JSON evidence.

## Every historical article inspected

“Locale repair” below includes generated prose and the TOC; it does not imply rewriting the article’s technical explanation.

| Slug | Prose/dependency checks | Authored correction |
|---|---|---|
| `creating-a-bootable-usb` | ISO authenticity, disk identification, UEFI/BIOS and boot acceptance | Locale repair; no authored rewrite needed. |
| `downgrade-mikrotik-routeros-firmware-safely` | RouterOS versus RouterBOOT, factory limits, backup and rollback | Locale repair; no authored rewrite needed. |
| `enable-ssh-linux-complete-guide` | Key login before hardening, effective configuration and session recovery | Locale repair; no authored rewrite needed. |
| `http-vs-https-ssl-certificate-impact` | TLS scope, certificate validation, staged HSTS and renewal | Locale repair; no authored rewrite needed. |
| `imap-vs-pop3-email-protocol-comparison` | Retrieval versus SMTP, OAuth, retention and deletion behavior | Locale repair; no authored rewrite needed. |
| `install-dfs-server-windows-server` | DFS-N versus DFS-R, permissions, initial sync and independent backup | Completed the NTFS/delegation instruction in EN and FA. |
| `install-mikrotik-chr-vmware-workstation` | Isolated VMnets, interface mapping, licensing and IPv4 limits | Locale repair; no authored rewrite needed. |
| `install-vmware-esxi-vmware-workstation-vmcisr` | Nested lab scope, build-specific errors and nested-guest acceptance | Locale repair; no authored rewrite needed. |
| `linux-cli-common-commands` | Evidence before intervention, resource interpretation and exit codes | Locale repair; no authored rewrite needed. |
| `linux-security-account-access-management` | Least privilege, sudo negative tests and independent credential revocation | Locale repair; no authored rewrite needed. |
| `mikrotik-block-port-scanners` | Input versus forward, TCP-only example, rule placement and heuristic limits | Locale repair; no authored rewrite needed. |
| `mikrotik-block-website` | DNS versus SNI, resolver exposure, ECH/QUIC and separate tests | Locale repair; no authored rewrite needed. |
| `mikrotik-openvpn-setup-v7` | Named server version, PKI, PPP, return routes and forbidden-access tests | Locale repair; no authored rewrite needed. |
| `mikrotik-unequal-dual-wan-load-balancing-ecmp` | PCC versus historical ECMP URL, connection weights and failover limits | Locale repair; no authored rewrite needed. |
| `netbox-installation-setup-ubuntu` | Pinned release dependencies, API permissions, workers and coordinated restore | Locale repair; no authored rewrite needed. |
| `nginx-installation-configuration-ubuntu` | Backend readiness, proxy trust, certificate prerequisites and health checks | Locale repair; no authored rewrite needed. |
| `oxidized-network-device-configuration-backup` | Host-key verification before first poll, service identity and fetch freshness | Moved known-host preparation and the foreground-test explanation before the foreground command. |
| `set-static-ip-ubuntu-server-netplan` | YAML ownership, console access, try acceptance and post-reboot checks | Locale repair; no authored rewrite needed. |
| `sql-server-automatic-backup-job` | Recovery model, chain dependencies, proxy/engine permissions and restore acceptance | Locale repair; no authored rewrite needed. |
| `ubuntu-date-time-settings` | Clock versus timezone, source ownership and restart/step policy | Moved and clarified the pre-restart warning; explicitly requires one-command-at-a-time execution. |
| `vmware-esxi-8-installation-basic-configuration` | Compatibility, disk selection, management and configuration versus VM backup | Locale repair; no authored rewrite needed. |
| `vsphere-standard-switch-vs-distributed-switch` | Feature comparison, migration decision, canary uplinks and recovery access | Locale repair; no authored rewrite needed. |
| `windows-cmd-common-network-commands` | DNS/TCP/SMB separation, evidence capture and limited corrections | Locale repair; no authored rewrite needed. |
| `windows-hardware-info-cmd-vs-dxdiag` | CIM versus DxDiag, units, provider limitations and inventory versus health | Locale repair; no authored rewrite needed. |
| `windows-password-reset-secure-access-recovery` | Identity type, reset versus unlock, delegated rights and EFS/DPAPI risks | Locale repair; no authored rewrite needed. |

## Validation

- Full project suite: **177 tests, 47,254 assertions, zero failures/errors, one skipped MySQL-specific engine test** (the suite uses SQLite).
- Reusable source structure audit: **41 bilingual sources, zero issues**; all before/after executable/image/JSON-LD comparisons pass.
- Isolated PHP content audit: **82 language editions, zero structural or localization issues**.
- Exported server HTML: **82 editions with matching body and TOC locale text before JavaScript**.
- Headless Chrome: **28 locale/viewport cases, zero failures** across bootable USB, DFS, Oxidized, Chrony, vSS/vDS, MongoDB and UniFi. Checks include language switching, translated prose/link/TOC labels, stable code and IDs, LTR code, working TOC clicks and article image loading. Persian desktop body and mobile layout were visually inspected.
- Frontend tests: **6 passed**.
- Image validation: **117 source images, 354 references, zero errors**.
- Documentation link validation: **159 Markdown files, zero broken local links**.
- Changed PHP files pass syntax checks; `git diff --check` passes.

[Machine-readable per-article before/after evidence](legacy-article-prose-audit-2026-10-09.json) includes all 25 historical section inventories and the preservation results for all 41 sources.

Reproduce the collection checks with `python scripts/audit-article-structure.py`, `php scripts/audit-article-content.php local --isolated`, the existing PHPUnit suite, and `node --test tests/frontend/*.test.cjs`. The Windows QA run used the available portable PHP 8.5 runtime and an untracked bootstrap wrapper because the restricted environment reports some readable dependency files as unreadable.

## Evidence and limits

- Exact before/after source comparison covers all 41 files: ordered code blocks, image tags, JSON-LD, meta/link tags and localization metadata remain identical. Only DFS, Oxidized and Chrony authored HTML differs during this follow-up. Historical runbook executable fences also remain identical.
- The reusable PHP audit now rejects rendered prose that does not match its requested locale, in addition to order, hierarchy, anchors, empty sections and review-date checks. Regression tests cover both locales, structured generated lists, single-quoted translations, paired link labels and the two procedure prerequisites.
- Representative version-sensitive claims were cross-checked against primary sources: [NetBox requirements](https://netbox.readthedocs.io/en/stable/installation/), [NetBox 4.7 release notes](https://netbox.readthedocs.io/en/stable/release-notes/version-4.7/), [MikroTik OpenVPN](https://help.mikrotik.com/docs/spaces/ROS/pages/2031655/OpenVPN), and [Oxidized 0.37.0 SSH options](https://raw.githubusercontent.com/ytti/oxidized/0.37.0/lib/oxidized/input/sshbase.rb). The documented example versions were retained.
- The configured local CMS database is absent. CMS-only published/draft records therefore remain unavailable for inspection. The isolated import/test database includes all repository sources; no production database was read or changed.
- This is a prose, structure, source-preservation and application-rendering audit. Infrastructure commands were not executed against real routers, hypervisors, Windows hosts or database servers. Version-specific lab acceptance remains necessary before using a procedure in an actual environment.
