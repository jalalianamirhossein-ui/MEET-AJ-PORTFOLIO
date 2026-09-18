# Design system — Meet AJ public site

**Authority:** [design-system/meet-aj/MASTER.md](../../design-system/meet-aj/MASTER.md). Live cascade last overlay `assets/css/site-modules.css?v=1832` after `visual-upgrade.css?v=1710`.  
**Visual DNA:** [ARTICLE-VISUAL-DNA.md](ARTICLE-VISUAL-DNA.md) (Article detail pages are the reference; they are not restyled from this file).  
**Date:** 2026-09-18  
**Current status:** [PROJECT-STATUS.md](PROJECT-STATUS.md)

ui-ux-pro-max catalogs were read directly (Python is not installed, so `search.py` was not executed): Portfolio/Personal + B2B Service; Swiss Modernism 2.0 + Editorial Grid; primary `#2563EB`; Poppins + Vazirmatn; 150–200ms micro-interaction and 400–560ms reveal. frontend-design: Article DNA is the distinctive language — not a SaaS card kit, not cream/terracotta, not glass everywhere.

## Intent

One Infrastructure / DevOps language: premium, technical, editorial, restrained. Homepage is a personal portfolio story. Services are landings. Articles remain the reading document.

Cards are for **grouping**. They are not a default wrapper.

## Tokens (`:root`)

| Category | Tokens |
|----------|--------|
| Color | `--primary` / `--color-primary` `#2563eb`, `--primary-hover` `#1d4ed8`, `--color-primary-soft` `rgba(37,99,235,0.13)`, `--accent` `#0ea5e9`, `--color-text` `#1e293b`, `--color-text-muted` `#64748b`, `--color-background` `#f4f7fb`, `--color-surface` `#ffffff`, `--color-surface-muted` `#eef4ff`, `--color-surface-elevated` `#ffffff`, `--color-border` `#dce5f1`, `--color-success` `#16a34a`, `--color-warning` `#d97706`, `--color-danger` `#dc2626`, `--footer-bg` `#2563eb` |
| Type | Poppins + Vazirmatn. `--text-display` = Article H1 clamp. `--text-xs` … `--text-4xl` |
| Space | `--space-1` 4px through `--space-24` 96px |
| Radius | `--radius-control` 0.75rem, `--radius-panel` 1rem, `--radius-pill` 999px (kickers/filters/tags only) |
| Shadow | `--shadow-subtle` `0 4px 12px rgba(15,23,42,0.05)`, `--shadow-card` `0 18px 45px rgba(15,23,42,0.1)`, `--shadow-button` color-mix primary |
| Motion | `--duration-fast` 180ms, `--duration-slow` 560ms, `--ease-out` `cubic-bezier(0.22, 1, 0.36, 1)` |
| Layout | `--container` 56rem (reading), homepage catalog ~70rem, `--control-height` 2.75–2.95rem |

| Topic accents | Fallback palette `--topic-microsoft` `#2563eb`, `--topic-linux` `#15803d`, `--topic-mikrotik` `#c2410c`, `--topic-vmware` `#6d28d9`, `--topic-security` `#be123c`, `--topic-devops` `#0e7490`, `--topic-other` `#a16207`. Live cards/filters/badges set `--topic` from `Category::accentColor()` (editable `categories.accent_color` or that fallback). |

Green is success / availability on the hero (“Available for Work”) and form success. Homepage **Expertise** categories additionally use pastel greens/teals/oranges on title pills only — not full-column fills. Skill **meters** (the Skills section) still use per-group `--skill-accent`, not a rainbow of unrelated bar colours.

## Type hierarchy

Eyebrow → Display/H1 → H2 (bar, weight 700) → H3 → Body → Small → Caption. Same family on Homepage, Services, Articles chrome, Contact, Footer.

Article library teasers are **H3** (after section H2). Homepage skill/value/cert titles still use **H4** in `index.html` (WARN — heading skip).

## Components

- **Buttons:** primary, outline, ghost, danger. 12px radius, 700, 44px+ target, hover lift −2px, `:focus-visible`, `[aria-busy]`, disabled. Hero CTAs are solid, not glass. Icons are Bootstrap Icons, never `→` or emoji.
- **Cards (allowed):** service catalog preview, article teaser, quote/contact form, testimonial quote. One shadow recipe.
- **Not cards:** stats, resume items, skill rows, FAQ, SLA lines, about domain lists, process steps, contact methods.
- **Homepage Expertise / تخصص‌ها:** five columns with pastel category accents (Infrastructure `#15803d`, Networking `#2563eb`, DevOps `#6d28d9`, Monitoring `#0f766e`, Security `#c2410c`). Title is a tinted pill + icon, not a filled card. Skill rows use a 3px `border-inline-start` (left in LTR, right in RTL).
- **Filters:** pill buttons, keyboard + touch, min-height 44px.
- **Nav / i18n:** solid listbox (no glass); DE only if `[data-de]` exists. Fullscreen mobile `#header.header-show` with icy-blue chrome (`visual-upgrade.css` + `site-modules.css`). Closed header is `hidden` + `inert`. Open menu inerts `main` / `footer` / skip-link (`main.js?v=1412`). Hamburger `inset-inline-start`; language `inset-inline-end`.
- **Quote form:** hidden until Request a Quote. Same field language as homepage contact. Placeholders: `data-en-placeholder` / `data-fa-placeholder` applied by `i18n.js?v=1403`.

## Motion

fade-up / fade-in on section enter (560ms desktop / 400ms ≤768). Micro 180ms, −2px. Expertise titles fade/slide up and skill rows stagger (`expertise-rise`, 250ms hover). No bounce, no `scale(1.1)`, no rainbow social hover. `prefers-reduced-motion: reduce` disables AOS, hover lift, menu stagger, and Expertise reveal.

## Out of scope

Article body HTML, article URLs, service prices/copy, form contracts, git. Filament admin is native Filament 5 with a **White + Red** overlay (`#be123c` / `#fff1f2` / `#7f1d1d`); the public site stays `#2563eb`.
