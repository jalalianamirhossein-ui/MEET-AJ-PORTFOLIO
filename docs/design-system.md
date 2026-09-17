# Design system — Meet AJ public site

**Authority:** Live overlay `assets/css/visual-upgrade.css`.  
**Visual DNA:** [article-visual-dna.md](article-visual-dna.md) (Article detail pages are the reference; they are not restyled from this file).  
**Date:** 2026-09-17

## Intent

One Infrastructure / DevOps language: premium, technical, editorial, restrained. Homepage is a personal portfolio story. Services are landings. Articles remain the reading document.

Cards are for **grouping**. They are not a default wrapper.

## Tokens (`:root`)

| Category | Tokens |
|----------|--------|
| Color | `--primary` / `--color-primary` `#2563eb`, `--primary-hover` `#1d4ed8`, `--color-primary-soft` `rgba(37,99,235,0.13)`, `--accent` `#0ea5e9`, `--color-text` `#1e293b`, `--color-text-muted` `#64748b`, `--color-background` `#f4f7fb`, `--color-surface` `#ffffff`, `--color-surface-muted` `#eef4ff`, `--color-surface-elevated` `#ffffff`, `--color-border` `#dce5f1`, `--color-success` `#059669`, `--color-warning` `#d97706`, `--color-danger` `#dc2626` |
| Type | Poppins + Vazirmatn. `--text-display` = Article H1 clamp. `--text-xs` … `--text-4xl` |
| Space | `--space-1` 4px through `--space-24` 96px |
| Radius | `--radius-control` 0.75rem, `--radius-panel` 1rem, `--radius-pill` 999px (kickers only) |
| Shadow | `--shadow-subtle` `0 4px 12px rgba(15,23,42,0.05)`, `--shadow-card` `0 18px 45px rgba(15,23,42,0.1)`, `--shadow-button` color-mix primary |
| Motion | `--duration-fast` 180ms, `--duration-slow` 560ms, `--ease-out` `cubic-bezier(0.22, 1, 0.36, 1)` |
| Layout | `--container` 56rem (reading), homepage catalog ~70rem, `--control-height` 2.95rem |

Green is success / availability only (hero “Available for Work”, form success). Skills use primary blue, never a rainbow of bar colors.

## Type hierarchy

Eyebrow → Display/H1 → H2 (bar) → H3 → Body → Small → Caption. Same family on Homepage, Services, Articles chrome, Contact, Footer.

## Components

- **Buttons:** primary, outline, ghost, danger. 12px radius, 700, 44px+ target, hover lift −2px, `:focus-visible`, `[aria-busy]`, disabled.
- **Cards (allowed):** service catalog preview, article teaser, quote/contact form, testimonial quote. One shadow recipe.
- **Not cards:** stats, resume items, skill rows, FAQ, SLA lines, about domain lists, process steps.
- **Filters:** pill buttons, keyboard + touch.
- **Nav / i18n:** glass listbox; DE only if `[data-de]` exists. Fullscreen mobile `#header.header-show`.
- **Quote form:** hidden until Request a Quote. Same field language as homepage contact.

## Motion

fade-up / fade-in on section enter (560ms). Micro 180ms. No bounce, no constant float. `prefers-reduced-motion: reduce` disables both.

## Out of scope

Article body HTML, article URLs, service prices/copy, form contracts, Filament admin, git.
