# Design system — Meet AJ public site

**Authority:** AUTHORITATIVE description of the live overlay, not a separate product.  
**Date:** 2026-09-16  
**Implementation:** `assets/css/visual-upgrade.css` (`?v=1120`), loaded after `main.css` / `articles.css` / `services.css`. No Tailwind, React, Vue, or extra animation libraries.

## Intent

One Infrastructure / DevOps portfolio language: premium, technical, blue-accent, restrained motion. The overlay unifies homepage, six service landings, articles chrome, contact, and footer without rewriting article HTML or inventing copy.

## Tokens (`:root`)

| Category | Tokens |
|----------|--------|
| Color | `--color-primary` `#2563eb`, `--color-primary-hover` `#1d4ed8`, `--color-primary-soft` `#dbeafe`, `--color-accent` `#0ea5e9`, `--color-success` `#059669`, `--color-danger` `#dc2626`, `--color-warning` `#d97706`, `--color-text` / `--color-text-muted`, `--color-background`, `--color-surface`, `--color-surface-elevated`, `--color-border`, `--color-glass` |
| Type | `--font-display` / `--font-heading` / `--font-body` = Poppins + Vazirmatn; `--text-xs` … `--text-display` |
| Space | `--space-1` (4px) through `--space-24` (96px) |
| Radius | `--radius-sm` … `--radius-xl`, `--radius-pill` |
| Shadow | `--shadow-subtle`, `--shadow-card`, `--shadow-elevated`, `--shadow-floating` |
| Motion | `--duration-fast` 150ms, `--duration-normal` 220ms, `--duration-slow` 400ms, `--ease-out` |
| Control | `--control-height` 44px, `--focus-ring`, `--container` 1140px, `--measure` 40rem |

Green is reserved for success / availability (hero “Available for Work”, form success toast). Skill bars use primary blue only.

## Type hierarchy

Section pattern: optional **kicker** → **title** → **description** → content. Body 15–17px desktop, 14–16px mobile. Persian uses Vazirmatn under `html[dir=rtl]`.

## Components

- **Buttons:** `.btn-primary`, `.btn-outline` / `.btn-outline-primary`, `.btn-ghost`, `.btn-secondary`, `.btn-danger`. States: hover, `:focus-visible`, `:disabled`, `[aria-busy]`. Minimum 44×44px.
- **Cards:** radius-lg, 1px border, shadow-subtle, hover lift 4px (disabled under `prefers-reduced-motion`).
- **Filters:** real `<button type="button" data-filter>` inside the articles/portfolio list.
- **Service landing:** `.service-hero`, `.service-block`, `.service-include-grid`, `.service-process`, `.service-faq`, `.service-quote`. Quote form is `[hidden]` until CTA.
- **Nav / i18n:** glass listbox (`lang-toggle.css`); DE only if `[data-de]` exists. Fullscreen mobile `#header.header-show`.

## Breakpoints (controlled)

Mobile-first overlays use 575 / 767 / 991 / 1199. Visual QA matrix: 320, 375, 390, 414, 768, 1024, 1280, 1366, 1440, 1920.

## Out of scope

Filament admin uses its own primary `#2563eb` and `resources/css/filament-admin.css`. Do not restyle Filament internals from this overlay.
