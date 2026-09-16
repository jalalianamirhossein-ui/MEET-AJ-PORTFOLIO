> **HISTORICAL.** Site QA snapshot from the CMS implementation pass. Authoritative current QA: [QA-MATRIX.md](../QA-MATRIX.md). Later UI evidence: [final-ui-qa.md](../final-ui-qa.md).

# Final site QA report

Date: 2026-09-16  
Origin: `http://127.0.0.1:8000`  
PHPUnit SQLite: **OK, 23 tests, 510 assertions, 1 skipped** (`MysqlSchemaTest`)  
PHPUnit MariaDB (`phpunit.mysql.xml`): **OK (23 tests, 517 assertions)**  
`site:compare-content`: **31/31 PASS** (home, 6 services, article index, 23 articles)

## Verdict

**Git readiness: NOT READY**

Passing locally: boot, SQLite tests, MariaDB integration tests, article import, content compare, HTTP route crawl, browser contact + service quote (`OK` stored with phone/subject; honeypot not stored), service worker registration.

Not passing the production gate:

| Item | Status |
| --- | --- |
| DirectAdmin production database | BLOCKED |
| Full 8-viewport × every HTML page matrix | FAIL (incomplete) |
| Filament browser login/CRUD (password field) | BLOCKED (automation policy) |
| PWA offline navigation / cache-update cycle | FAIL (SW registered; offline not proven; old cache name `cms-2` still present until refresh) |
| Live DirectAdmin SMTP | BLOCKED |
| Keyboard / reduced-motion on every page | FAIL (not executed) |

No secrets were committed. Local QA user passwords exist only in gitignored `.runtime/qa-users.json`.

## Content completeness

| Family | Result | Evidence |
| --- | --- | --- |
| Homepage | PASS | compare-content; sections in a11y tree |
| 6 services | PASS | compare-content; network-design a11y + quote POST |
| `/articles` | PASS | 23 cards in a11y + CDP `cards: 23` |
| 23 articles | PASS | compare-content body/headings/`data-fa`/SEO |
| German public | CONTENT_SOURCE_MISSING | `/de` 404 (expected) |

## Browser contact

Executed in the page context against live `/forms/*`:

| Step | Result |
| --- | --- |
| CSRF token JSON | PASS |
| Invalid payload | 400 `Invalid name (2-50 characters required)` |
| Honeypot | 200 `OK`, 0 DB rows for that email |
| Valid homepage submit | 200 exact `OK`; DB row with phone + subject |
| Valid service quote | 200 exact `OK`; DB row stored |
| Rate limit | PASS in PHPUnit (5 then 429). Not re-exhausted in the browser (would block further QA). |

## Filament

| Check | Result |
| --- | --- |
| `/admin/login` HTTP 200 | PASS |
| Guest `/admin` 302 login | PASS |
| `actingAs` admin `/admin` 200 | PASS |
| Editor `viewAny` Request false; admin true | PASS |
| Livewire ListArticles as admin | PASS (sqlite run after lock fix) |
| Livewire ManageRequests as editor forbidden | PASS |
| Model create draft / publish / delete | PASS |
| Browser typed login | BLOCKED |
| Browser click-through search/filter/upload/status | BLOCKED |

## PWA

| Check | Result |
| --- | --- |
| `navigator.serviceWorker` active | PASS (scope `/`) |
| Cache present | PASS (`meet-aj-v2.0.0-cms-2` observed; `cms-3` published to `public/sw.js` but not yet activated in that tab) |
| Private prefixes in worker | PASS (code: `/admin`, `/livewire`, `/forms`, `.php`) |
| Offline homepage | FAIL — `Network.emulateNetworkConditions` not executed |
| Install prompt | FAIL — not exercised |

## Responsive (CDP overflow)

`overflow = scrollWidth > clientWidth + 2`

| Page | Sizes measured | Overflow |
| --- | --- | --- |
| `/` | 320×800, 375×812, 768×1024 | none |
| `/services/network-design.html` | 320×568 | none |
| `/articles` | 1440×900 | none |
| `/articles/ubuntu-date-time-settings` | 1440×900 | none |

Unmeasured page×size combinations are **FAIL**, not assumed PASS. Mobile menu control exists at 320 (`#menu-toggle`).

## Public URL table

EN/FA **PASS** means bilingual source attributes or language-n/a. FA interactively proven on Ubuntu (prior session) and service a11y; this session did not re-toggle every page.

SEO/Canonical **PASS** from PHPUnit + compare-content + Ubuntu canonical `.../articles/ubuntu-date-time-settings` (no `.html`).

Assets **PASS** when the page HTTP-succeeded and shared `/assets/css/main.css` + `/assets/js/i18n.js` returned 200.

PWA column: **PASS** only for `sw.js`/`manifest`/`/` where SW was observed; others **FAIL** (offline not proven per URL).

| URL | Expected HTTP | Actual HTTP | Content | EN | FA | SEO | Canonical | Assets | Responsive | Accessibility | PWA | Result |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `/` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/index.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/services/devops-automation.html` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/services/monitoring-security.html` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/services/network-design.html` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/services/system-administration.html` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/services/technical-consulting.html` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/services/virtualization-solutions.html` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/creating-a-bootable-usb.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/creating-a-bootable-usb` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/downgrade-mikrotik-routeros-firmware-safely.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/downgrade-mikrotik-routeros-firmware-safely` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/enable-ssh-linux-complete-guide.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/enable-ssh-linux-complete-guide` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/http-vs-https-ssl-certificate-impact.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/http-vs-https-ssl-certificate-impact` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/imap-vs-pop3-email-protocol-comparison.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/imap-vs-pop3-email-protocol-comparison` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/install-dfs-server-windows-server.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/install-dfs-server-windows-server` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/install-mikrotik-chr-vmware-workstation.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/install-mikrotik-chr-vmware-workstation` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/install-vmware-esxi-vmware-workstation-vmcisr.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/install-vmware-esxi-vmware-workstation-vmcisr` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/linux-cli-common-commands.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/linux-cli-common-commands` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/linux-security-account-access-management.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/linux-security-account-access-management` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/mikrotik-block-port-scanners.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/mikrotik-block-port-scanners` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/mikrotik-block-website.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/mikrotik-block-website` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/mikrotik-openvpn-setup-v7.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/mikrotik-openvpn-setup-v7` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/mikrotik-unequal-dual-wan-load-balancing-ecmp.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/mikrotik-unequal-dual-wan-load-balancing-ecmp` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/nginx-installation-configuration-ubuntu.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/nginx-installation-configuration-ubuntu` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/set-static-ip-ubuntu-server-netplan.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/set-static-ip-ubuntu-server-netplan` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/sql-server-automatic-backup-job.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/sql-server-automatic-backup-job` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/ubuntu-date-time-settings.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/ubuntu-date-time-settings` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/vmware-esxi-8-installation-basic-configuration.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/vmware-esxi-8-installation-basic-configuration` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/vsphere-standard-switch-vs-distributed-switch.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/vsphere-standard-switch-vs-distributed-switch` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/windows-cmd-common-network-commands.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/windows-cmd-common-network-commands` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/windows-hardware-info-cmd-vs-dxdiag.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/windows-hardware-info-cmd-vs-dxdiag` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/windows-password-reset-secure-access-recovery.html` | 301 | 301 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/articles/windows-password-reset-secure-access-recovery` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/articles/does-not-exist` | 404 | 404 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | FAIL | FAIL |
| `/de` | 404 | 404 | CONTENT_SOURCE_MISSING | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/sitemap.xml` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/robots.txt` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/manifest.json` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/sw.js` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/docs/netbox_installation_guide_v2.pdf` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/assets/css/main.css` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/assets/js/i18n.js` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/forms/get-csrf-token.php` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |
| `/admin/login` | 200 | 200 | PASS | PASS | PASS | PASS | PASS | PASS | FAIL | FAIL | PASS | FAIL |
| `/admin` guest | 302 | 302 | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS |

Article `.html` 301s were also asserted in PHPUnit for all 23 slugs (one hop, query string preserved).

## What remains before READY

1. Measure overflow at 320/375/390/768/1024/1366/1440/1920 on every HTML page (or accept a reduced signed-off sample).
2. Complete Filament UI login, search, filters, image upload, request status (human or approved automation).
3. DevTools offline: homepage, one article, static CSS; confirm `/admin` and `/forms` stay out of Cache Storage.
4. DirectAdmin MySQL migrate (not fresh) + SMTP values from the host.
5. Keyboard / Escape / reduced-motion pass on home, one service, one article.
