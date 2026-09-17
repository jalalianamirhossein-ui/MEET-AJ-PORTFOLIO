# Article Visual DNA — Meet AJ

**Status:** REFERENCE ONLY. Article detail pages must not be redesigned.  
**Sources:** live `/articles/{slug}` (desktop 1440, mobile 375) + `assets/css/articles.css`.  
**Inspected representatives:** Linux SSH guide (theme-linux), Microsoft CMD article (theme-microsoft), MikroTik article (theme-mikrotik).  
**Date:** 2026-09-17  
**Overlay at last DNA check:** `visual-upgrade.css?v=1310`

Homepage hero fill must stay scoped to `body.index-page`. Article H1 is `#1e293b` with no text-shadow (verified on `/articles/enable-ssh-linux-complete-guide`: computed `rgb(30, 41, 59)`).

## 1. What works well

- One calm canvas (`#f4f7fb`) with a single white reading surface. The page is a **document**, not a dashboard.
- Headings do the grouping. H2 uses a 0.3rem accent bar + 2px rule. Sections do not sit in nested cards.
- Blue is the site identity. Topic themes (green Linux, orange MikroTik, indigo VMware) are **article-only** and must not leak into Homepage or Services.
- Buttons are 12px radius, 700 weight, 2.95rem tall, lift −2px. They are not pills.
- Inner cards exist only when content is a method, step, or test — grouping, not decoration.
- Motion is one language: 560ms enter, 180ms hover, `cubic-bezier(0.22, 1, 0.36, 1)`.
- Persian (Vazirmatn, RTL) and English (Poppins, LTR) share the same scale and spacing.

## 2. Typography system

| Role | Spec (from live Article CSS) | Use on other pages |
|------|------------------------------|--------------------|
| Eyebrow / category | 0.76rem, 700, uppercase, 0.05em, pill | Kickers, filters, service category |
| Display / H1 | `clamp(2rem, 3.35vw, 3.5rem)`, 700, lh 1.18, ls −0.025em | Hero name, service title (service H1 may use a slightly larger vw so short titles do not look weak) |
| H2 | `clamp(1.45rem, 2.6vw, 2rem)`, 700, bar + underline | Section titles site-wide |
| H3 | `clamp(1.2rem, 2.1vw, 1.5rem)`, 700 | Domain names, resume role, FAQ |
| Body | 16px, lh 1.85 in reading column; muted 1.7 | About, overview, contact copy |
| Small / caption | 0.82–0.9rem, `#64748b` | Dates, metadata, prices unit |

Do not copy Article H1 `max-width: 21ch` onto Homepage or Services. That measure is for long technical titles.

## 3. Color system

Canonical (default / Microsoft theme — this is the **site** DNA):

| Token | Hex / value | Role |
|-------|-------------|------|
| Primary | `#2563eb` | Accents, buttons, links |
| Primary strong / hover | `#1d4ed8` | Hover, strong text |
| Accent | `#0ea5e9` / `#38bdf8` | Gradient bar only |
| Background | `#f4f7fb` | Page canvas |
| Surface muted | `#eef4ff` | Hero wash, nested highlight |
| Surface | `#ffffff` | Reading surface, grouping card |
| Text | `#1e293b` | Headings and body |
| Muted | `#64748b` | Supporting copy |
| Border | `#dce5f1` | Rules, inputs |
| Code | `#0f172a` / `#e2e8f0` | Article code only |
| Success | `#16a34a` | Success / available — not skills |
| Warning | `#d97706` | Warnings |
| Danger | `#dc2626` | Errors |

Topic greens/oranges/purples stay inside `.article-page.theme-*`.

## 4. Spacing system

Prefer 4 / 8 / 12 / 16 / 24 / 32 / 48 / 64 / 80 / 96.

Article rhythm:

- Section gap inside the reading column: `clamp(1.75rem, 3.5vw, 2.75rem)`
- Reading column padding: `clamp(1.15rem, 3.2vw, 2.5rem)`
- Content width: `56rem`
- Measure for excerpts: `46rem`
- Control height: `2.95rem` (≈ 47px, satisfies 44px)

Homepage/Services should use the same **scale**, not the same 56rem reading column on every layout. Portfolio catalog may be ~70rem. Service story column may stay 56rem.

## 5. Component system

**Buttons:** 0.75rem radius, 0.7rem 1rem padding, 700, primary shadow `0 7px 16px color-mix(primary, transparent 78%)`. Outline is white + `#dce5f1`. Back control is 0.7rem, subtle shadow.

**Cards:** Article uses **one** elevated reading card. Nested 0.8rem cards only for discrete procedures. Do not wrap every list, stat, or FAQ in a card.

**Pills:** category / kicker only (`border-radius: 999px`). Buttons are not pills.

**Inputs:** 12px, white, `#dce5f1` border, `#1e293b` text.

**Icons:** Bootstrap Icons, 28px-class marks, primary blue. No emoji as UI.

**Code blocks:** navy, ~0.8rem radius — Article-only. Do not put navy panels on service quote forms.

**Metadata:** breadcrumb + category pill + date. Compact, not a toolbar.

## 6. Responsive behavior

- 320–414: single column, full-width CTAs, hamburger nav, no horizontal overflow.
- 768: two-column service hero (title / price).
- 1024+: process may become a row; still editorial, not a dashboard grid with connecting lines.
- Language switcher must not cover back controls.

## 7. Animation behavior

- Enter: 560ms fade/translate 14px.
- Hover: 180ms translateY(−2px).
- Respect `prefers-reduced-motion: reduce`.
- No bounce, no perpetual float, no parallax on content.

## 8. Global standards (extract, do not copy layout)

1. Canvas `#f4f7fb`, text `#1e293b`, primary `#2563eb`.
2. H2 bar + 2px rule.
3. 12px buttons / 16px grouping radius.
4. One shadow recipe: `0 18px 45px rgba(15,23,42,0.1)` — only on true grouping surfaces.
5. Pills for categories only.
6. Forms look like the reading surface, not an admin panel.
7. Green = success / availability only.
8. RTL/LTR share tokens; Persian uses Vazirmatn and 700 (not 800).
9. Cards communicate grouping. Typography, rules, and numbers do the rest.
10. Service pages are landings. Article pages remain the reading document.
