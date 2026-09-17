> **HISTORICAL.** Pre-implementation audit of the static site (2026-09-14). Not the current Laravel CMS status. See [PROJECT-STATUS.md](../current/PROJECT-STATUS.md).

# Current site analysis — audit before implementation

Audit completed: 2026-09-14. Source: complete local file inventory and HTML/link scan, manual review of shared scripts, styles, PHP endpoints, manifest, service worker, sitemap, and existing documentation. The public homepage at https://meetaj.ir was also inspected; local files are the migration source of truth.

## Baseline and preservation

- Original branch: `main`; commit: `a9b23d5`.
- Migration/backup branch created: `feature/laravel-migration`.
- Pre-existing working change: 19 added lines in `assets/css/main.css`. A branch alone does not save uncommitted work: `baseline-files.json` records hashes and `baseline-main-css.patch` preserves that diff separately.
- No original file will be deleted or overwritten. Laravel will be added at the repository root; only its `public/` directory may be the production document root. Legacy HTML and PHP remain outside that root as source/rollback material.
- No AGENTS.md found in the repository. `.claude/skills/` is development tooling, not website runtime code.

## Current architecture and pages

Static bilingual English/Persian portfolio with client-side translations (`data-en`, `data-fa`), RTL switching, localStorage preferences, Bootstrap layouts, and a PHP mail handler. No package manifest, database, MVC framework, authentication, or CMS exists. React preloader examples are standalone demonstrations, not an application build.

32 HTML files: homepage, 23 articles, six services, one preloader example, one language-toggle partial. `current-site-inventory.json` records each page's title, description, canonical, schema types, asset references, form fields, and links. All existing HTML local file references resolved in the initial scan; external links and fragment targets require separate checks.

| Page family | Current URLs | Target behavior |
| --- | --- | --- |
| Homepage | `/`, `/index.html` | Blade `/`; 301 `/index.html` to `/`, preserving browser fragments |
| Homepage sections | `#hero`, `#about`, `#resume`, `#services`, `#portfolio`, `#contact` and existing section IDs | Preserve IDs and navigation |
| Articles | `/articles/{filename}.html`, 23 entries listed below | DB-backed `/articles/{slug}`; permanent legacy redirects |
| Article listing | Homepage `/#portfolio` | Preserve grid; add `/articles` with pagination |
| Services | `/services/network-design.html`, `/services/system-administration.html`, `/services/devops-automation.html`, `/services/monitoring-security.html`, `/services/virtualization-solutions.html`, `/services/technical-consulting.html` | Preserve paths and service layouts in Blade |
| Contact/token | POST `/forms/contact.php`; GET `/forms/get-csrf-token.php` | Laravel routes retaining plain-text `OK` and JSON `{token}` contracts |
| Public support | `/manifest.json`, `/sw.js`, `/sitemap.xml`, `/robots.txt`, `/docs/netbox_installation_guide_v2.pdf` | Publish assets/PDF; dynamic SEO endpoints and safe replacement worker |
| Examples | `/preloader.html`, React examples, `/partials/lang-toggle.html` | Retain original source; examples not required as CMS routes |

## Articles and metadata

Articles duplicate header/sidebar navigation, footer, head tags, assets, hero, and content wrappers. Content includes Persian translations in attributes, code-copy controls, section IDs, author panels, and varied hero/TOC markup. A generic rewrite would lose design or translations. Import content plus original presentation metadata and render through Blade components. Existing translated text must survive the import.

All 23 articles have canonical, description and OpenGraph title tags. Six include Article JSON-LD; the other 17 need generated Article schema. Existing metadata sometimes uses relative image URLs or inconsistent dates. Preserve original meta text and known publication dates, normalize URLs, and use sitemap lastmod only as a documented fallback where publication date is absent. Service pages have translated title attributes, Service JSON-LD, canonical, social tags and inline FAQ/form logic.

The sitemap includes both `/` and `/index.html` (duplicate canonical content), all article URLs and all six services. Robots currently allows all paths. The CMS sitemap must omit redirects, drafts, future publications and admin URLs; robots should disallow admin and dynamic submission endpoints.

## CSS, JavaScript and assets

- `assets/css/main.css` (199,829 bytes including existing user edit): design tokens, sidebar, hero, portfolio, contact, responsive/accessibility overrides.
- `articles.css` (34,241 bytes), `services.css` (14,256), `rtl.css` (20,378), `lang-toggle.css` (8,991); standalone `preloader.css`.
- `assets/js/main.js`: accessible mobile navigation, animations, Isotope filtering, gallery/carousel, form submission, code-copy and article progress.
- `assets/js/i18n.js`: bilingual text updates, language preference, RTL stylesheet and translated page titles.
- Bundled vendors: Bootstrap, Bootstrap Icons/fonts, AOS, Swiper, GLightbox, Typed.js, PureCounter, Waypoints, Isotope, ImagesLoaded, PHP Email Form. Vendor assets exist locally despite README drift.
- Profile/hero/logo/icons/testimonials; 24 large portfolio PNG sources and 23 optimized JPEG thumbnails. Preserve original files and asset paths. No frontend package installation or redesign is necessary.
- `sw.js` caches documents and general GETs, excluding forms/PHP only: adding `/admin` unchanged would cache private pages. A replacement worker must clear legacy caches and avoid caching HTML, admin, Livewire, tokens, and uploaded CMS content.
- Initial whole-tree inventory: 451 files, 63,329,233 bytes including development skills, vendor files and media. This is not the production upload size.

## Forms and backend

Seven forms: homepage plus six service quote forms. All submit name/email/subject/message, honeypot `website`, and `csrf_token`; services also collect optional phone. Preserve subject using an additional nullable database column, otherwise quote context is lost. Current JavaScript retrieves a fresh token, posts to `/forms/contact.php`, and requires literal `OK`. The current PHP handler validates, rate-limits using filesystem state, and emails via the bundled library; it does not persist requests. CMS success will mean a saved request visible in admin. Email notification delivery is separate from required request storage.

## Required database tables

| Table | Fields/purpose |
| --- | --- |
| users | Laravel authentication; hashed password; enum-like admin/editor role; remember token |
| articles | Requested id, title, unique slug, excerpt, HTML content, featured_image, category_id, meta_title, meta_description, status, published_at, timestamps; JSON presentation metadata to retain translations/layout and original SEO |
| categories | id, name, unique slug, timestamps; belongs to article management |
| article_redirects | Historical unique slug to article ID; preserves old links after later slug edits |
| requests | id, name, email, nullable phone, nullable subject, message, status, created_at, updated_at |
| sessions/password_reset_tokens | Laravel session/auth support; no public registration |

Use MySQL/MariaDB with utf8mb4, foreign keys, unique slugs and publication/status indexes. File cache/session alternatives work on shared hosting; SQLite is suitable only for isolated automated tests.

## Migration risks and decisions

1. Serving repository root would expose credentials and let physical HTML/PHP/sitemap bypass Laravel. Document root must be `public/`; shared-host fallback uploads only public contents into `public_html`.
2. Preserve baseline hashes and CSS diff; publish copies of allowlisted assets only. Do not publish `.claude`, `.git`, forms PHP, source HTML, or docs containing operational details.
3. Content import must be transactional/idempotent and skip existing slugs by default; never overwrite subsequent CMS edits on deployment.
4. Preserve translated attributes, CSS classes, anchors, inline page-specific behavior, and UTF-8. Sanitize editor HTML against XSS while keeping needed layout/translation attributes.
5. Drafts/future articles must be excluded from every public listing/detail/sitemap and redirects must resolve only published records. Slug changes need collision checks against redirect history.
6. Laravel CSRF protection must accept the legacy field name without exempting contact routes. Form validation errors must remain readable plain text for existing JavaScript.
7. Admin/editor policy enforcement must protect requests even through direct Livewire requests, not just hide menu links. File uploads must reject executable/SVG content.
8. Existing service worker must be retired safely; avoid stale publication states or caching authenticated pages.
9. No PHP, Composer, or MySQL was found on PATH during audit. Runtime/dependency installation and executable tests require environment preparation; report actual results separately.
10. Laravel 11 is the explicitly requested framework major. Its support lifecycle and dependency advisories must be checked during setup and documented; do not silently change the requested architecture.

## Audit exit criteria

Full file enumeration, page/URL/form/SEO inventory, shared runtime review and architecture decisions completed before application code. The implementation may now begin after saving inventory/baseline artifacts. See `testing-report.md` for implementation validation, not this pre-migration baseline.

## Complete article URL inventory

- `/articles/creating-a-bootable-usb.html` → `/articles/creating-a-bootable-usb`
- `/articles/downgrade-mikrotik-routeros-firmware-safely.html` → `/articles/downgrade-mikrotik-routeros-firmware-safely`
- `/articles/enable-ssh-linux-complete-guide.html` → `/articles/enable-ssh-linux-complete-guide`
- `/articles/http-vs-https-ssl-certificate-impact.html` → `/articles/http-vs-https-ssl-certificate-impact`
- `/articles/imap-vs-pop3-email-protocol-comparison.html` → `/articles/imap-vs-pop3-email-protocol-comparison`
- `/articles/install-dfs-server-windows-server.html` → `/articles/install-dfs-server-windows-server`
- `/articles/install-mikrotik-chr-vmware-workstation.html` → `/articles/install-mikrotik-chr-vmware-workstation`
- `/articles/install-vmware-esxi-vmware-workstation-vmcisr.html` → `/articles/install-vmware-esxi-vmware-workstation-vmcisr`
- `/articles/linux-cli-common-commands.html` → `/articles/linux-cli-common-commands`
- `/articles/linux-security-account-access-management.html` → `/articles/linux-security-account-access-management`
- `/articles/mikrotik-block-port-scanners.html` → `/articles/mikrotik-block-port-scanners`
- `/articles/mikrotik-block-website.html` → `/articles/mikrotik-block-website`
- `/articles/mikrotik-openvpn-setup-v7.html` → `/articles/mikrotik-openvpn-setup-v7`
- `/articles/mikrotik-unequal-dual-wan-load-balancing-ecmp.html` → `/articles/mikrotik-unequal-dual-wan-load-balancing-ecmp`
- `/articles/nginx-installation-configuration-ubuntu.html` → `/articles/nginx-installation-configuration-ubuntu`
- `/articles/set-static-ip-ubuntu-server-netplan.html` → `/articles/set-static-ip-ubuntu-server-netplan`
- `/articles/sql-server-automatic-backup-job.html` → `/articles/sql-server-automatic-backup-job`
- `/articles/ubuntu-date-time-settings.html` → `/articles/ubuntu-date-time-settings`
- `/articles/vmware-esxi-8-installation-basic-configuration.html` → `/articles/vmware-esxi-8-installation-basic-configuration`
- `/articles/vsphere-standard-switch-vs-distributed-switch.html` → `/articles/vsphere-standard-switch-vs-distributed-switch`
- `/articles/windows-cmd-common-network-commands.html` → `/articles/windows-cmd-common-network-commands`
- `/articles/windows-hardware-info-cmd-vs-dxdiag.html` → `/articles/windows-hardware-info-cmd-vs-dxdiag`
- `/articles/windows-password-reset-secure-access-recovery.html` → `/articles/windows-password-reset-secure-access-recovery`
