# QA matrix — Meet AJ Laravel CMS

**Authority:** AUTHORITATIVE QA evidence.  
**Date:** 2026-09-16  
**Do not treat older phase reports as current PASS/FAIL.**

Legend:

- **LOCAL TESTED** — PHPUnit (`php artisan test`) and/or `site:compare-content` and/or local browser on `http://127.0.0.1:8000`
- **INTEGRATION TESTED** — MariaDB via `phpunit.mysql.xml`
- **PRODUCTION TESTED** — meetaj.ir / DirectAdmin (none in this matrix)
- Status: **PASS** | **FAIL** | **NOT TESTED** | **BLOCKED**

Latest default suite: **31 tests, 593 assertions, 1 skipped, 0 failures**.  
`site:compare-content`: **Failures: 0**.  
MariaDB `MysqlSchemaTest`: **1 test, 7 assertions, OK**.

Skipped in default sqlite suite: `MysqlSchemaTest` (runs only when MySQL is bound).

---

## Public pages

| URL / Feature | Expected | Actual | Test method | Status | Notes |
|---------------|----------|--------|-------------|--------|-------|
| `GET /` | 200, section IDs, EN default | 200, IDs present | PHPUnit `test_homepage_and_index_redirect`; `site:compare-content`; browser QA | PASS · LOCAL TESTED | Overlay `visual-upgrade.css?v=1120` |
| `GET /index.html` | 301 → `/` | 301 | PHPUnit | PASS · LOCAL TESTED | Master prompt listed 200; implementation and tests use 301 |
| `GET /articles` | 200, 23 cards, filters | 200 | PHPUnit + browser | PASS · LOCAL TESTED | Filter controls are `<button type="button">` |
| `GET /services/{slug}` | 200, landing + hidden quote form | 200 | PHPUnit `ServiceCatalogTest` / `PublicSiteTest`; browser | PASS · LOCAL TESTED | Form revealed by CTA |
| `GET /services/{slug}.html` | 301 → clean slug | 301 | PHPUnit | PASS · LOCAL TESTED | Query string preserved |

## Article pages (23)

Each `GET /articles/{slug}` expected 200 with original H1/body tokens. Each `GET /articles/{slug}.html` expected single 301 to the clean URL.

Slugs: `creating-a-bootable-usb`, `downgrade-mikrotik-routeros-firmware-safely`, `enable-ssh-linux-complete-guide`, `http-vs-https-ssl-certificate-impact`, `imap-vs-pop3-email-protocol-comparison`, `install-dfs-server-windows-server`, `install-mikrotik-chr-vmware-workstation`, `install-vmware-esxi-vmware-workstation-vmcisr`, `linux-cli-common-commands`, `linux-security-account-access-management`, `mikrotik-block-port-scanners`, `mikrotik-block-website`, `mikrotik-openvpn-setup-v7`, `mikrotik-unequal-dual-wan-load-balancing-ecmp`, `nginx-installation-configuration-ubuntu`, `set-static-ip-ubuntu-server-netplan`, `sql-server-automatic-backup-job`, `ubuntu-date-time-settings`, `vmware-esxi-8-installation-basic-configuration`, `vsphere-standard-switch-vs-distributed-switch`, `windows-cmd-common-network-commands`, `windows-hardware-info-cmd-vs-dxdiag`, `windows-password-reset-secure-access-recovery`.

| URL / Feature | Expected | Actual | Test method | Status | Notes |
|---------------|----------|--------|-------------|--------|-------|
| 23 article details | 200 | 200 | PHPUnit `test_all_articles_redirect_once_and_render`; compare-content | PASS · LOCAL TESTED | FA H1 regression fixed in importer `attr()` |
| 23 legacy `.html` redirects | 301 once to clean slug | 301 | PHPUnit | PASS · LOCAL TESTED | Query string survives |
| Unknown slug | 404 | 404 | PHPUnit | PASS · LOCAL TESTED | |

## Forms

| URL / Feature | Expected | Actual | Test method | Status | Notes |
|---------------|----------|--------|-------------|--------|-------|
| `GET /forms/get-csrf-token.php` | JSON token + success | JSON | PHPUnit | PASS · LOCAL TESTED | `no-store` |
| `POST /forms/contact.php` valid | `OK`, row in `requests` | OK + persist | PHPUnit | PASS · LOCAL TESTED | |
| Honeypot `website` | `OK`, no row | OK, no row | PHPUnit | PASS · LOCAL TESTED | |
| Invalid fields | 400 text | 400 | PHPUnit | PASS · LOCAL TESTED | |
| Rate limit | 429 after 5/hour | 429 | PHPUnit `test_contact_rate_limit_returns_429` | PASS · LOCAL TESTED | |
| Mail transport down | still persist | persist | PHPUnit | PASS · LOCAL TESTED | |
| Production SMTP | deliver mail | — | — | NOT TESTED · BLOCKED | Optional |

## SEO / robots / PWA files

| URL / Feature | Expected | Actual | Test method | Status | Notes |
|---------------|----------|--------|-------------|--------|-------|
| `/sitemap.xml` | XML; home + published EN services (clean URLs) + published EN articles; no `.html` | as expected | PHPUnit | PASS · LOCAL TESTED | Listing `/articles` not in sitemap |
| `/robots.txt` | Disallow admin/livewire/forms; Sitemap line | as expected | PHPUnit | PASS · LOCAL TESTED | |
| Article SEO head | canonical, OG, Twitter, JSON-LD | present | PHPUnit `test_article_pages_have_complete_seo_and_clean_canonicals` | PASS · LOCAL TESTED | |
| `/manifest.json` | 200 JSON | published file | code + publish command | PASS · implemented; installability NOT TESTED | |
| `/sw.js` | worker `meet-aj-v2.0.0-cms-3` | file present | code review | IMPLEMENTED · offline NOT TESTED | |
| `/offline.html` | fallback document | file present | code | IMPLEMENTED · NOT TESTED offline | |
| hreflang | — | absent | grep `resources/` / `app/` | N/A | Intentionally not implemented |

## Admin

| URL / Feature | Expected | Actual | Test method | Status | Notes |
|---------------|----------|--------|-------------|--------|-------|
| `/admin/login` | 200 | 200 | PHPUnit | PASS · LOCAL TESTED | |
| `/admin` guest | redirect to login | redirect | PHPUnit | PASS · LOCAL TESTED | |
| Article CRUD publish/draft/delete | works for manager | works | PHPUnit | PASS · LOCAL TESTED | |
| Service CRUD publish/draft/price | admin only | admin only | PHPUnit `ServiceCatalogTest` | PASS · LOCAL TESTED | Editors forbidden |
| Editor cannot open contact requests | denied | denied | PHPUnit | PASS · LOCAL TESTED | |
| Users resource in sidebar | hidden | hidden | code + route:list after optimize:clear | PASS as mitigated | `/admin/users` and `/admin/cms-users` exist; nav still off |
| Browser login + edit | usable | — | — | NOT TESTED this pass | Prior UI work did not re-login Filament |

## German

| URL / Feature | Expected | Actual | Test method | Status | Notes |
|---------------|----------|--------|-------------|--------|-------|
| `GET /de` | 404 | 404 | PHPUnit | PASS · LOCAL TESTED | |
| Publish DE article | ValidationException | throws | PHPUnit ContentRules | PASS · LOCAL TESTED | No DE HTML source |

## Database / schema

| Feature | Expected | Actual | Test method | Status | Notes |
|---------|----------|--------|-------------|--------|-------|
| SQLite migrations | 8 tables including `services` | created | PHPUnit RefreshDatabase | PASS · LOCAL TESTED | |
| MariaDB constraints | unique/FK behaviour | OK | `phpunit.mysql.xml` MysqlSchemaTest | PASS · INTEGRATION TESTED | Host `127.0.0.1:3307` |
| DirectAdmin MySQL | migrated production | — | — | BLOCKED · NOT TESTED | |

## UI / a11y (browser, 2026-09-16)

Evidence file: [final-ui-qa.md](final-ui-qa.md) (REFERENCE). Filters 44px, persistent cards, EN/FA fonts, 10-viewport matrix recorded there. Lighthouse: **NOT TESTED**.

## Production / DirectAdmin

| Feature | Expected | Actual | Test method | Status | Notes |
|---------|----------|--------|-------------|--------|-------|
| PHP 8.4 on host | 8.4 | unknown | — | BLOCKED | |
| Document root `public/` | Laravel front controller | unknown | — | BLOCKED | |
| HTTPS + HSTS | headers on meetaj.ir | unknown | — | BLOCKED | |
| Live contact on meetaj.ir | OK | unknown | — | BLOCKED | |

## Production readiness

**NOT READY / BLOCKED** until DirectAdmin items pass. Local automated tests: **PASS**.
