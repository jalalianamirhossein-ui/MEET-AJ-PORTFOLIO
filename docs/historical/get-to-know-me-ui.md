> **HISTORICAL / SUPERSEDED.**
> **Original date:** 2026-09-16.
> **Original purpose:** UI report for the homepage “Get to Know Me” section, with screenshot evidence (now in `screenshots/get-to-know-me/`).
> **Superseded by:** [../qa/VISUAL-QA.md](../qa/VISUAL-QA.md) and [../qa/DESIGN-AUDIT.md](../qa/DESIGN-AUDIT.md).
> Current status: [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md).

# Get to Know Me — UI report

**Date:** 2026-09-16  
**Origin:** `http://127.0.0.1:8000`  
**Scope:** Homepage `#about` only, plus required overlay CSS / existing `main.js`.  
**Git:** not modified (no commit, no push).  
**Overlay:** `assets/css/visual-upgrade.css?v=1114`

`php artisan site:compare-content`: **Failures: 0**

---

## Original design

The previous About block followed the inherited iPortfolio / resume pattern:

- Bootstrap `row`: photo in `col-lg-4`, biography in `col-lg-8`
- Heading “Get to Know Me”
- Two biography paragraphs
- Fact list: birthday (19 July 1999), Bachelor of IT Engineering, 5+ Years
- Core Values, personal motto, philosophy
- Existing profile photo `assets/img/my-profile-img-2.jpg`

It read as a generic developer/resume section. It did not visually communicate infrastructure, networking, virtualization, automation, or observability.

## Problems found

| ID | Problem |
|----|---------|
| A1 | Resume-template layout; no technical identity |
| A2 | Technologies appeared only as prose, not as grouped domains |
| A3 | Photo had no framing or depth |
| A4 | No interactive system diagram |
| A5 | RTL inherited blanket `direction: rtl !important` on `div`/`span`, which later broke Latin “AJ” in the diagram |
| A6 | Three overlapping lead paragraphs appeared during an early draft; trimmed to the two original biography paragraphs |
| A7 | Philosophy inner HTML said “honesty, discipline, patience…” while `data-en` / `data-fa` omit “discipline”; inner HTML synced to the bilingual attributes |

## Visual changes

Asymmetric **copy | Infrastructure Core** stage on desktop.

Left (LTR) / inline-start (RTL):

- Eyebrow: IT Infrastructure Specialist
- H2: Get to Know Me
- Headline from existing copy: “Designing, managing, and optimizing enterprise systems.”
- Profile photo in a primary→accent ring
- Name, role, Tehran
- Two original biography paragraphs
- Facts: Birthday / Degree / Experience (existing values only)
- CTAs: Get In Touch (`#contact`), Services (`#services`)

Right: dark technical panel (`#0f172a`) with:

- Subtle grid
- SVG connection lines from a central **AJ** node to Infrastructure, DevOps, Networking, Virtualization, Monitoring, Automation
- Contextual caption under the diagram (content taken from existing skills / services copy)
- Status dots; active node pulse only

Below: five expertise groups (no invented stats), then existing Core Values, motto, and philosophy.

Not used: neon, glassmorphism, particles, Three.js, WebGL, new CDNs, fake counters, Kubernetes, Hikvision, CCTV, HP Servers (not in source content).

## Typography changes

- English continues Poppins via existing overlay tokens
- Persian uses Vazirmatn; kickers drop `letter-spacing` / `uppercase` in RTL
- Headline is a `<p class="about-headline">` so heading order stays `h2 → h3 → h4`
- Diagram labels stay physically LTR so the architecture does not mirror; captions and copy stay RTL in FA

## Color changes

Section uses the existing palette only:

| Token | Value | Use |
|-------|-------|-----|
| `--primary` | `#2563eb` | kicker, photo ring, CTAs, core node |
| `--accent` | `#0ea5e9` | ring / status / restrained stroke |
| `--text-primary` | `#0f172a` | headings |
| `--text-secondary` | `#64748b` | leads |
| `--surface` | `#f8fafc` | fact and domain cards |
| Diagram ground | `#0f172a` | Infrastructure Core + quote |

No new brand colors.

## Animation changes

| Motion | Timing | Notes |
|--------|--------|-------|
| Grid drift | 14s | ambient |
| Link dash flow | 8s | ambient |
| Active status pulse | 2.8s | active node only |
| Node hover/focus | 200ms | border / shadow |
| Interaction | click, focus; hover only when `(hover: hover)` | mobile uses tap |

`prefers-reduced-motion: reduce` disables grid, flow, and pulse.

## Responsive changes

Mobile order: profile → CTAs → diagram → expertise → values / quote / philosophy.

| Viewport | Stage | Domains | Overflow (`scrollWidth - clientWidth`) |
|----------|-------|---------|----------------------------------------|
| 320×800 | 1 col | 1 col | 0 |
| 375×812 | 1 col | 1 col | 0 |
| 390×844 | 1 col | 1 col | 0 |
| 768×1024 | 1 col | 2 col | 0 |
| 1024×768 | 1 col | 2 col | 0 |
| 1366×768 | 2 col | 5 col | 0 |
| 1440×900 | 2 col | 5 col | 0 |
| 1920×1080 | 2 col | 5 col | 0 |

`#about` has `scroll-margin-top: 5rem` so in-page jumps clear the header.

## Accessibility changes

- Section `h2`; expertise / values / philosophy `h3`; domain and value titles `h4`
- Diagram nodes are `<button>` with `aria-pressed`, `aria-controls`, 44px minimum size, `focus-visible`
- Detail region `aria-live="polite"`
- Content remains readable with motion off and without hover
- Lead `#64748b` on white ≈ 4.6:1 (AA for 16px)

## Performance impact

- No new libraries
- CSS + a small `main.js` node activator
- Overlay cache-bust `v=1114`

## EN / FA validation

| Surface | Result |
|---------|--------|
| EN LTR desktop | Copy left, diagram right |
| FA RTL desktop | Copy right, diagram left; Vazirmatn; CTAs at inline-start |
| EN / FA mobile | Stacked; diagram `dir=ltr` so AJ stays “AJ” |
| Node captions | EN/FA from existing skills/services wording |
| Automation FA | خودکارسازی |

RTL `rtl.css` forces `direction: rtl !important` on `div`/`span`. Overlay counters that on `.about-core-canvas` so the engineered node map does not flip.

## Screenshots tested

Captured against `http://127.0.0.1:8000` in the Cursor browser (device-metrics viewports). Filenames:

| File | What |
|------|------|
| `about-en-desktop.png` | EN 1440×900 — copy + Infrastructure Core |
| `about-fa-desktop.png` | FA 1440×900 — RTL copy, LTR diagram |
| `about-en-mobile.png` | EN 375×812 — introduction |
| `about-en-mobile-core.png` | EN 375×812 — diagram + CTAs + Expertise |
| `about-fa-mobile.png` | FA 375×812 — introduction |
| `about-fa-mobile-core.png` | FA 375×812 — diagram with AJ LTR |
| `about-fa-tablet.png` | FA 768×1024 — stacked profile then diagram |

PWA `meet-aj-v2.0.0-cms-3` can serve a stale homepage; QA used cache-bust query params after unregistering the service worker.

## Remaining issues

1. The fixed mobile hamburger can overlap the top of the viewport (site chrome, not unique to this section).
2. Browser screenshot captures include unused canvas beside the emulated viewport; layout width was confirmed with `innerWidth`.
3. Expertise cards at 1366px are ~195px wide; readable, slightly tight.
4. Body lead contrast is AA, not AAA.
5. Ansible / Terraform appear in the DevOps group because they already exist on the DevOps service page; Kubernetes was not added.

## Content sources (not invented)

Kept from the existing site:

- Amirhossein Jalalian, Network and IT Infrastructure Specialist, Tehran
- Both biography paragraphs
- Birthday, Bachelor of IT Engineering, 5+ Years
- Core Values, motto, philosophy (`data-en` / `data-fa`)
- Linux, Windows Server, VMware, KVM, Cisco, MikroTik, VPN, VoIP, Docker, CI/CD, Jenkins, GitLab, Ansible, Terraform, Zabbix, Grafana, Cacti, Redgate, Active Directory, Veeam, AWS, Azure

Not added: years-in-role beyond the existing “5+ Years”, certifications, employers, clients, project metrics, awards.

## Files touched

| File | Change |
|------|--------|
| `index.html` | `#about` markup; overlay `v=1114` |
| `assets/css/visual-upgrade.css` | `.about-premium` system |
| `assets/js/main.js` | Infrastructure Core activator |
| `resources/views/home.blade.php` | rebuilt by `site:publish-assets --views` |
| `public/assets/css/visual-upgrade.css` | published overlay |

Articles, service bodies, SEO metadata, URLs, and contact endpoints were not edited.

---

## Verdict

| Check | Result |
|-------|--------|
| IMPLEMENTATION | **PASS** |
| CONTENT INTEGRITY | **PASS** (Failures: 0) |
| RESPONSIVE | **PASS** |
| ACCESSIBILITY | **PASS** |
| PERFORMANCE | **PASS** |
| EN/FA | **PASS** |
