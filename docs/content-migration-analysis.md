# Meet AJ content migration analysis

Date: 2026-09-14. Planning-only review of the completed audit, not a new implementation. Read with `laravel-implementation-plan.md`, which contains proposed schema, scope choices, migration risks, SEO policy and the approval gate.

## Content inventory

- 23 source HTML articles; all listed in the exact route map below.
- Six service HTML pages and homepage `index.html`.
- Portfolio is the homepage `#portfolio` article-card grid, not a separate set of portfolio-detail pages. Some README examples describe files not present; use the audited inventory as source of truth.
- Two other HTML assets: preloader demonstration and language-toggle partial. Keep sources; compatibility copies are outside the sitemap.
- Seven forms: homepage plus all six service pages.
- Current language behavior is English/Persian via data-en/data-fa attributes, language preference, direction/RTL styles and JavaScript. German is a requested addition; the audit found no complete German article content.

## Article structure, images and metadata

Article files duplicate head metadata, sidebar/navigation, hero, content wrappers, code-copy controls, article footer, shared footer and scripts. Preserve per-article TOCs, section anchors, bilingual data attributes, escaped code and source-specific markup when extracting common Blade components.

Images include original profile/hero/logo/PWA assets, testimonials, 24 portfolio PNG sources and 23 optimized JPEG thumbnails. The grid thumbnail and gallery/detail image can differ; preserve both paths and alt text. All initial HTML local file references resolved in the audit. Copy public assets without modifying originals, and verify hashes on the published copies.

All 23 articles have canonical URL, meta description and OpenGraph title. Six include Article JSON-LD and 17 need generated schema. Preserve existing title/description/social text and structured-data details. Normalize only URLs needed for approved routing/absolute image references. Do not invent publication dates from sitemap modification dates. The homepage includes Person/WebSite/BreadcrumbList schema; services include Service schema and current canonical/social tags.

## Forms

All seven forms POST name/email/subject/message, honeypot website and csrf_token. Service forms also send optional phone. Existing scripts GET `/forms/get-csrf-token.php` for JSON `{token}`, then POST `/forms/contact.php` expecting literal `OK` on successful delivery.

Laravel keeps these endpoint paths and the response contract. Retain server validation, session CSRF, throttling and readable error responses. The final core-table proposal uses `requests` as explicitly requested in the later exact migration list, with an additional nullable subject column to preserve service quote context. `contact_requests` is an alternative naming choice, not a second data store. See the plan's scope reconciliation before approval.

## Existing-to-Laravel URL map

Existing public URLs remain reachable. The 23 article `.html` routes use the explicitly requested permanent redirects to clean article paths; service paths stay exactly the same. Original physical HTML remains outside the Laravel public directory.

| Existing URL | Proposed Laravel URL / response |
| --- | --- |
| `/` | `/` — Blade homepage |
| `/index.html` | 301 `/` |
| `/#hero`, `/#about`, `/#stats`, `/#skills`, `/#resume`, `/#services`, `/#portfolio`, `/#testimonials`, `/#contact` | Same fragment IDs and current behavior |
| `/forms/contact.php` | Same POST URL — Laravel controller |
| `/forms/get-csrf-token.php` | Same GET URL — Laravel token controller |
| `/sitemap.xml` | Same URL — dynamic XML |
| `/robots.txt` | Same URL — preserved crawl intent and admin exclusions |
| `/assets/**`, `/manifest.json`, `/sw.js` | Same public URLs |
| `/docs/netbox_installation_guide_v2.pdf` | Same download URL |
| `/preloader.html`, `/preloader.css`, `/partials/lang-toggle.html` | Compatible public copies; excluded from sitemap |

## Risks relevant to content

Source files must remain unchanged; source HTML must not accidentally be published where it bypasses Laravel. Import must preserve all 23 source identities across repeated runs and later slug edits. Do not strip Persian text, attributes, code or optimized/full-size image distinctions. Do not silently replace mixed-language original SEO text with generated prose. Do not publish empty German translations or advertise unavailable hreflang variants.

The original service worker can cache future private CMS routes: preserve its public offline behavior while adding targeted exclusions in the new public copy after approval. The original i18n is two-language code: three-language support needs a separately tested integration while retaining original files and EN/FA behavior.

The full source metadata, file paths and link inventory remain available in `current-site-inventory.json`. Original hashes remain in `baseline-files.json`. This analysis does not authorize implementation; wait for approval of `laravel-implementation-plan.md`.

## Complete article and service map

| Original URL | Laravel URL | Behavior |
| --- | --- | --- |
| `/articles/creating-a-bootable-usb.html` | `/articles/creating-a-bootable-usb` | 301 to published article |
| `/articles/downgrade-mikrotik-routeros-firmware-safely.html` | `/articles/downgrade-mikrotik-routeros-firmware-safely` | 301 to published article |
| `/articles/enable-ssh-linux-complete-guide.html` | `/articles/enable-ssh-linux-complete-guide` | 301 to published article |
| `/articles/http-vs-https-ssl-certificate-impact.html` | `/articles/http-vs-https-ssl-certificate-impact` | 301 to published article |
| `/articles/imap-vs-pop3-email-protocol-comparison.html` | `/articles/imap-vs-pop3-email-protocol-comparison` | 301 to published article |
| `/articles/install-dfs-server-windows-server.html` | `/articles/install-dfs-server-windows-server` | 301 to published article |
| `/articles/install-mikrotik-chr-vmware-workstation.html` | `/articles/install-mikrotik-chr-vmware-workstation` | 301 to published article |
| `/articles/install-vmware-esxi-vmware-workstation-vmcisr.html` | `/articles/install-vmware-esxi-vmware-workstation-vmcisr` | 301 to published article |
| `/articles/linux-cli-common-commands.html` | `/articles/linux-cli-common-commands` | 301 to published article |
| `/articles/linux-security-account-access-management.html` | `/articles/linux-security-account-access-management` | 301 to published article |
| `/articles/mikrotik-block-port-scanners.html` | `/articles/mikrotik-block-port-scanners` | 301 to published article |
| `/articles/mikrotik-block-website.html` | `/articles/mikrotik-block-website` | 301 to published article |
| `/articles/mikrotik-openvpn-setup-v7.html` | `/articles/mikrotik-openvpn-setup-v7` | 301 to published article |
| `/articles/mikrotik-unequal-dual-wan-load-balancing-ecmp.html` | `/articles/mikrotik-unequal-dual-wan-load-balancing-ecmp` | 301 to published article |
| `/articles/nginx-installation-configuration-ubuntu.html` | `/articles/nginx-installation-configuration-ubuntu` | 301 to published article |
| `/articles/set-static-ip-ubuntu-server-netplan.html` | `/articles/set-static-ip-ubuntu-server-netplan` | 301 to published article |
| `/articles/sql-server-automatic-backup-job.html` | `/articles/sql-server-automatic-backup-job` | 301 to published article |
| `/articles/ubuntu-date-time-settings.html` | `/articles/ubuntu-date-time-settings` | 301 to published article |
| `/articles/vmware-esxi-8-installation-basic-configuration.html` | `/articles/vmware-esxi-8-installation-basic-configuration` | 301 to published article |
| `/articles/vsphere-standard-switch-vs-distributed-switch.html` | `/articles/vsphere-standard-switch-vs-distributed-switch` | 301 to published article |
| `/articles/windows-cmd-common-network-commands.html` | `/articles/windows-cmd-common-network-commands` | 301 to published article |
| `/articles/windows-hardware-info-cmd-vs-dxdiag.html` | `/articles/windows-hardware-info-cmd-vs-dxdiag` | 301 to published article |
| `/articles/windows-password-reset-secure-access-recovery.html` | `/articles/windows-password-reset-secure-access-recovery` | 301 to published article |
| `/services/devops-automation.html` | `/services/devops-automation.html` | 200, same service URL |
| `/services/monitoring-security.html` | `/services/monitoring-security.html` | 200, same service URL |
| `/services/network-design.html` | `/services/network-design.html` | 200, same service URL |
| `/services/system-administration.html` | `/services/system-administration.html` | 200, same service URL |
| `/services/technical-consulting.html` | `/services/technical-consulting.html` | 200, same service URL |
| `/services/virtualization-solutions.html` | `/services/virtualization-solutions.html` | 200, same service URL |
