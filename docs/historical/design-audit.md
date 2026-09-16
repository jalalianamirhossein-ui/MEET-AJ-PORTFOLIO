> **HISTORICAL.** Visual audit that preceded the overlay upgrade. Later work: [visual-upgrade-report.md](visual-upgrade-report.md), [final-ui-qa.md](../final-ui-qa.md). Current status: [PROJECT-STATUS.md](../PROJECT-STATUS.md).

# Meet AJ — Visual Design Audit

Date: 2026-09-16  
Origin: `http://127.0.0.1:8000`  
Scope: Homepage, 6 service pages, `/articles`, article detail template, EN/FA, PWA, existing a11y, existing CSS tokens.  
Mode: Read-only visual/UX audit first. No identity replacement. No content rewrite.

## Skills used

| Requested skill | Status | How it was applied |
| --- | --- | --- |
| anthropic-frontend-design | Not installed as `SKILL.md` | Distinctiveness, hierarchy, restraint: keep the photo hero and sidebar identity; remove template noise (rainbow bars, hover-scale titles, hover-only article titles). |
| using-ui-stack | Not installed as `SKILL.md` | Stack is Laravel Blade + existing CSS, not React/shadcn/Tailwind. `ui-ux-pro-max` Laravel CSV recommends Tailwind/Vite; **not applied** because it would replace the migrated frontend. Tokens stay CSS variables. |
| visual-qa-testing | Not installed as `SKILL.md` | Browser screenshots + CDP layout inspection of `/`, `/articles`, `/services/network-design.html`, article URL. |
| responsive-testing | Not installed as `SKILL.md` | Viewport overflow previously measured on a subset; this audit notes remaining gaps and CSS risks (3rem service titles, hero CTA wrap). |
| accessibility-auditing | Not installed as `SKILL.md` | Mapped to `ui-ux-pro-max` UX rules: contrast 4.5:1, 44×44 targets, skip links, focus, hover-not-only, reduced motion, form labels. |
| ui-ux-pro-max | Installed | Product match: **B2B Service** (navy/blue trust) + **Portfolio/Personal** (work showcase). Style: Swiss/minimal technical, not generic SaaS. Motion: low. |
| design-system | Installed | Three-layer tokens: reuse existing primitives (`--primary` `#2563eb`) and add missing spacing/radius/shadow semantic tokens. |
| ui-styling | Installed | Guidance only. Do **not** introduce shadcn or Tailwind. |

Design direction kept:

- Primary `#2563eb`, surfaces `#ffffff` / `#f8fafc`, text `#0f172a`
- White glass sidebar (current live look), not the unused rainbow header rule
- Blue footer (live), not unused `--footer-bg: #dc2626`
- Photo hero, Poppins + Vazirmatn, existing service/article content

## Existing token inventory (reuse)

Already defined in `assets/css/main.css` `:root`:

- Color: `--primary`, `--primary-hover`, `--accent`, `--background`, `--surface`, `--text-primary`, `--text-secondary`, `--divider-border`
- Font: `--default-font` (later overridden to Roboto), `--persian-font`
- Shadows: `--shadow-primary`, `--shadow-surface`

Missing as a shared scale (present only in `services.css`): spacing (`--space-*`), radius, shadow levels, container widths, control states.

Conflict: `services.css` and a late `main.css` patch re-declare `:root` (Roboto, `--accent-color: #0050a0`, Apple `#007AFF` button shadows, purple `btn-accent`).

## Findings

| ID | Problem | Location | Severity | Recommended solution | Expected impact |
| --- | --- | --- | --- | --- | --- |
| A01 | Published article count is **0**. Homepage grid and `/articles` render filters with an empty isotope container (`height: 0`). Known article URL 404s. | SQLite `articles` table; `#portfolio .isotope-container`; `/articles`; `/articles/ubuntu-date-time-settings` | **Critical** | Re-import with existing `articles:import-legacy --refresh`. Do not invent articles. | Restores 23 cards and article pages. Required before visual QA of articles. |
| A02 | `/articles` is a fragment: filters only, no site header, footer, or mobile menu. Looks disconnected from the portfolio. | `resources/views/articles/index.blade.php` | **High** | Reuse the existing sidebar/header + footer pattern from homepage/article detail. Keep listing content. | One-site navigation; professional listing. |
| A03 | Article card titles/excerpts are hover-only overlays (`opacity: 0`). Fails tap-first and technical-portfolio readability. | `.portfolio-info` in `main.css` | **High** | Persistent caption block under the thumbnail; keep preview/details links visible; 44×44 hit targets. | Cards communicate topics without hover. |
| A04 | Hero primary CTA is glass `#007AFF` on a busy photo. Low contrast vs the solid black secondary button. | `.hero-actions .btn-primary.btn-modern` | **High** | Solid `--primary` + white label; outline secondary on dark photo; min 44px; no extra libraries. | Clear “who / what / contact” path. |
| A05 | Conflicting design tokens: rainbow `.header` vs live white sidebar; `--footer-bg` red unused; critical CSS paints header gradient and navy `.hero-title`; late patch sets Roboto and `#0050a0`. | `main.css` L737, L65, home critical CSS, L7138 | **High** | Overlay tokens: Poppins default, keep live white header, align buttons to `--primary`, do not activate red footer. | Consistent professional chrome. |
| A06 | Typography scale is ad hoc (hero 64px / 2.2rem subtitle, section 32px, service H1 3rem, testimonials Segoe UI). | Homepage, `services.css`, testimonials | **Medium** | Document and apply a type scale via variables. Cap service H1 on small screens. | Calmer hierarchy. |
| A07 | Spacing/radius/shadow not shared. Cards use 12 / 20 / 24px radius; lifts of 8–10px feel like a template. | Profile, stats, services, portfolio, testimonials | **Medium** | `--radius-*`, `--shadow-*`, `--space-*`; hover lift ≤4px. | Quieter, more technical. |
| A08 | Visual noise: rainbow profile/stat bars, title `scale` on hover, infinite `profileFloat` / heartbeat, stacked hero gradients in unused rules. | About cards, hero title, header img, footer heart | **Medium** | Single-accent bars; disable decorative motion under `prefers-reduced-motion`; remove title scale. | More trustworthy, less “SaaS landing”. |
| A09 | Service pages are a second visual system (system UI font, no site nav/footer, large empty canvas). Original service layout must stay. | 6 `services/*.blade.php` + `services.css` | **Medium** | Load Poppins/Vazirmatn already used on the site; tighten hero/cards/CTA/form; keep copy and endpoints. | Same brand, still a focused quote page. |
| A10 | Article detail already has a stronger system (`articles.css`, 56rem measure, code theme, reduced motion). Residual: decorative hero shapes, 8-col body vs 56rem token, Google Fonts CDN already present. | `articles/show.blade.php`, `articles.css` | **Low–Medium** | Tighten hero, keep content HTML untouched, slightly stronger body type rhythm. | Publication feel without restyling content. |
| A11 | Testimonials use Segoe UI and generic overlay cards; carousel exists with pause control (keep). | `#testimonials` | **Low** | Inherit Poppins; quieter quote surface. | Matches rest of site. |
| A12 | Contact form is functional (labels, honeypot, 44px). Visual: mixed radii and heavy message gradients. | `#contact` | **Low** | Tokenize inputs/focus; keep contracts (`csrf_token`, `OK`, honeypot `website`). | Cleaner CTA without API change. |
| A13 | Duplicate CSS (hero defined 3+ times, header many times) makes overrides fragile. | `main.css` ~9.8k lines | **Medium** (maintainability) | Additive `visual-upgrade.css` after existing sheets. Do not rewrite `main.css`. | Safer polish. |
| A14 | Responsive: previous QA found no overflow on sampled sizes; full 10-viewport × all pages matrix incomplete. Service `h1` 3rem, hero buttons column on ≤575px. | CSS media queries | **Medium** until retested | Fluid type; check 320–1920 after CSS; fix real overflow only. | No layout regressions. |
| A15 | Accessibility: skip links, focus-visible, 44px nav, reduced-motion on articles/services exist. Risks: hover-only cards (A03), hero CTA contrast (A04), remaining infinite animations. | Global | **Medium** | Keep all existing a11y; upgrade must not remove skip/focus/ARIA. | WCAG 2.2 alignment preserved. |
| A16 | Performance: do not add UI libraries, video, or new CDNs. Cache-bust CSS. SW cache `cms-3`. | PWA / CSS | **Constraint** | One extra CSS file, no JS frameworks, no new webfonts. | LCP/CLS stable. |

## Design system to implement (additive)

Layering: primitive (existing hex) → semantic (purpose) → component.

```css
:root {
  /* reuse --primary #2563eb, --surface, --text-primary */
  --space-1: 0.25rem; --space-2: 0.5rem; --space-3: 0.75rem;
  --space-4: 1rem; --space-5: 1.25rem; --space-6: 1.5rem;
  --space-8: 2rem; --space-10: 2.5rem; --space-12: 3rem; --space-16: 4rem;
  --radius-sm: 8px; --radius-md: 12px; --radius-lg: 16px; --radius-xl: 20px;
  --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.06);
  --shadow-md: 0 8px 24px rgba(15, 23, 42, 0.08);
  --shadow-lg: 0 16px 40px rgba(15, 23, 42, 0.10);
  --container: 1140px;
  --measure: 40rem;
  --focus-ring: 2px solid var(--primary);
  --control-height: 44px;
}
```

Type scale (approximate): 14 / 16 / 18 / 22 / 28 / 36 / 48. Line-height body 1.6–1.7. Headings 1.2–1.3.

States: hover (color + 2px lift max), focus-visible (existing ring), disabled opacity 0.5 + `cursor: not-allowed`.

## Out of scope (preservation)

- Article HTML/body text, Persian/English copy, URLs, redirects, DB schema, Filament, contact API, PWA behavior, skip links, existing focus styles unless a visual change would hide them.

## Implementation order

1. Restore articles from original HTML via existing importer.
2. Add `assets/css/visual-upgrade.css` and link it after current CSS.
3. Homepage: hero CTA, type, cards, article captions, quieter motion.
4. Article listing chrome + card presentation.
5. Article detail polish (CSS only).
6. Six service pages (CSS + font-family alignment).
7. Responsive + a11y + tests + content compare.
