# Meet AJ — Design System MASTER

**Status:** SOURCE OF TRUTH for public site + Filament branding.  
**Date:** 2026-09-21  
**Stack:** Laravel 13 + Blade + Livewire 4 + Filament 5. **Not** React, Vue, Next, Inertia, or Tailwind on the public site.  
**Visual reference:** live Article detail pages (`assets/css/articles.css`). Do not restyle article HTML bodies.

`search.py` could not be executed: Python is not installed on this workstation. Catalog files were read directly:

| Catalog | Match used |
|---------|------------|
| `products.csv` | #11 Portfolio/Personal + #5 B2B Service |
| `styles.csv` | Swiss Modernism 2.0 (#50) + Editorial Grid / Magazine (#66). **Not** Glassmorphism as a style. |
| `colors.csv` | Resume/CV Builder + Conference (#2563EB primary, navy text, success green) |
| `typography.csv` | #2 Modern Professional — **Poppins** headings. Body stays Poppins + **Vazirmatn** for FA (existing bilingual system). Do not switch to Inter. |
| `motion.csv` | Subtle Hover Micro-interaction (150–200ms, ≤2px lift) + Subtle Scroll Reveal (8–16px, 300–400ms). No GSAP; keep existing AOS. |
| `ux-guidelines.csv` | Reduced motion, 44px touch on iOS-class controls, breakpoint testing |
| `stacks/laravel.csv` | Blade components, Form Requests, CSRF, policies. **Ignore** Tailwind/Vite/Inertia rows — this project vendors CSS. |

Dials applied by brief (not by CLI): variance **4** (balanced editorial), motion **4** (subtle + one scroll language), density **4** (spacious, not dashboard).

## 1. Principles

1. One canvas, one accent, one motion language.
2. Article pages are the document. Homepage is a portfolio story. Services are landings. Admin is Filament.
3. Cards group. Typography, rules, and numbers structure.
4. Green is success/availability only — except **inside** `.article-page.theme-*` topic themes.
5. Glass is allowed only on the floating language control if a solid fallback exists. No glass buttons, cards, or hero CTAs.
6. Icons: Bootstrap Icons (SVG font). Never emoji characters or “emoji” icon names for professional stats.
7. Buttons are 12px radius, 700 weight, ≥44px tall. They are not pills.
8. Pills are kickers, categories, and filters only.
9. RTL/LTR share tokens. Persian uses Vazirmatn at 700, not 800.
10. Filament stays native Filament 5. The public site uses blue; the admin uses a restrained White + Red overlay — do not rebuild the admin as a custom theme.

## 2. Color tokens

```css
--color-primary: #2563eb;
--color-primary-hover: #1d4ed8;
--color-primary-soft: rgba(37, 99, 235, 0.13);
--color-accent: #0ea5e9;          /* H2 bar gradient only */
--color-text: #1e293b;
--color-text-muted: #64748b;
--color-background: #f4f7fb;
--color-surface: #ffffff;
--color-surface-muted: #eef4ff;
--color-border: #dce5f1;
--color-success: #16a34a;
--color-warning: #d97706;
--color-danger: #dc2626;
--color-on-primary: #ffffff;
--footer-bg: #2563eb;
```

**Forbidden as site chrome:** iOS `#007aff`, purple `#8a2be2`, Instagram rainbow hovers, WhatsApp green on nav, glass white-on-white.

## 3. Typography

| Role | Spec |
|------|------|
| Family EN | Poppins 400/600/700 |
| Family FA | Vazirmatn 400/500/600/700 |
| Kicker | 0.76rem, 700, uppercase, 0.05em |
| H1 / display | `clamp(2rem, 3.35vw, 3.5rem)`, 700, lh 1.18, ls −0.025em |
| H2 | `clamp(1.45rem, 2.6vw, 2rem)`, 700, 0.3rem accent bar + 2px rule |
| H3 | `clamp(1.2rem, 2.1vw, 1.5rem)`, 700 |
| Body | 16px, lh 1.7 public / 1.85 reading column |
| Caption | 0.82–0.9rem, muted |

Do not copy Article H1 `max-width: 21ch` onto Homepage or Services.

## 4. Spacing

Scale: 4 / 8 / 12 / 16 / 24 / 32 / 48 / 64 / 80 / 96 (`--space-1` … `--space-24`).

- Section vertical: `clamp(3.5rem, 6vw, 5.5rem)`
- Reading column: 56rem
- Homepage catalog: 70rem
- Control height: 2.75–2.95rem (minimum 44px for touch controls)

## 5. Radius

| Token | Value | Use |
|-------|-------|-----|
| `--radius-control` | 0.75rem (12px) | Buttons, inputs, menu toggle |
| `--radius-panel` | 1rem | Grouping surfaces, quote form |
| `--radius-pill` | 999px | Kickers, filters, tags only |
| `--radius-sm` | 8px | Nested article procedure cards only |

No 24px / 30px “Apple” pills on CTAs.

## 6. Shadows

| Token | Value | Use |
|-------|-------|-----|
| `--shadow-subtle` | `0 4px 12px rgba(15,23,42,0.05)` | Sidebar, inputs |
| `--shadow-card` | `0 18px 45px rgba(15,23,42,0.1)` | True grouping surfaces |
| `--shadow-button` | `0 7px 16px color-mix(primary, transparent 78%)` | Primary buttons |

No 32/64 black shadows. No colored brand-glow on social hover.

## 7. Buttons

- Primary: `#2563eb`, white type, `--shadow-button`, hover `#1d4ed8` + `translateY(-2px)` in 180ms.
- Outline (on light): white fill, `#dce5f1` border, navy type.
- Outline (on photo): solid `rgba(15,23,42,0.72)`, white type, **no** backdrop-filter.
- Ghost: no fill, primary type.
- Danger: `#dc2626` for destructive admin only.
- Focus: `2px solid #2563eb` offset 2px.
- Loading: `[aria-busy="true"]` opacity 0.7, no extra spinner library.
- Disabled: 0.45 opacity, `pointer-events: none`.

## 8. Icons

Bootstrap Icons only. Decorative icons `aria-hidden="true"`. Icon-only controls need `aria-label`. Size 1em–1.25em in text; 28px marks in empty states. No emoji glyphs. Stats “satisfied customers” uses `bi-people`, not `bi-emoji-smile`.

## 9. Motion

| Event | Duration | Easing | Displacement |
|-------|----------|--------|--------------|
| Hover / focus | 180ms | `cubic-bezier(0.22, 1, 0.36, 1)` | −2px |
| Section enter (AOS) | 560ms desktop / 400ms ≤768 | same | 14px |
| Stagger | 40–80ms per item, max ~8 | — | — |
| Mobile menu items | 200ms + 40ms stagger | same | 10px |

`prefers-reduced-motion: reduce` disables AOS, menu item animation, hover translate, and blink cursors. No bounce, no `scale(1.1)`, no perpetual float, no parallax on copy.

## 10. Responsive

| Width | Behavior |
|-------|----------|
| 320–414 | One column, full-width CTAs, hamburger, no overflow |
| 768 | Two-column about / service hero |
| 1024+ | Sidebar nav (xl), process may be a row |
| 1280–1920 | Catalog 70rem, reading 56rem, do not stretch full bleed |

Safe-area padding on mobile menu. Language control must not cover back links.

## 11. Laravel / Blade / Livewire / Filament

- Public UI: Blade + `visual-upgrade.css` overlay after `main.css` / `articles.css` / `services.css` / `rtl.css`.
- Livewire is **admin only** (Filament). Do not add Livewire to public pages for decoration.
- Filament: native tables/forms; White + Red brand overlay (`#be123c`, `#fff1f2`, `#7f1d1d`) in `resources/css/filament-admin.css`; keep the public blue palette separate.
- Forms keep existing CSRF / honeypot / validation / rate limit.

## Anti-patterns (do not introduce)

Random gradients, neon, glass cards, SaaS pricing tables, Tailwind, extra icon packs, GSAP, emoji, fake German, invented prices.
