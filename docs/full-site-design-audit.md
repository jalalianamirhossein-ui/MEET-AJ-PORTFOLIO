# Full-site design audit — Meet AJ

**Date:** 2026-09-16  
**Source of truth:** application code (`index.html`, Blade, `assets/css`, Filament), not older phase reports.  
**Status:** Implemented 2026-09-16. Overlay `visual-upgrade.css` v1120. See [design-system.md](design-system.md) and [final-project-qa-report.md](final-project-qa-report.md).

## Verdict of the current visual system

The Laravel migration preserved the original iPortfolio-derived homepage plus a separate `services.css` world for quote pages. Overlay `visual-upgrade.css` already unified articles, About, and navigation, but **six visual languages still coexist**:

| Surface | Visual language | Problem |
|---------|-----------------|---------|
| Hero | Photo overlay, typed roles, glass CTAs | Strong, but role hierarchy is weaker than the name |
| About | Custom infrastructure diagram | Premium; already rebuilt |
| Stats / Skills / Resume | Template progress bars, dense resume lists | Rainbow/skill colors, cramped type |
| Services index | Bootstrap service-item + catalog overlay | Cards almost unified |
| Service detail | `services.css` form-and-card stack | Reads like an admin quote sheet |
| Articles | Overlay toolbar + teasers | Largely unified |
| Contact / Footer | Extra intro, mixed card chrome | Footer already aliased to primary but still busy |
| Nav / i18n | Glass switcher + fullscreen menu | Recently rebuilt; keep |

## Hierarchy

Most homepage sections have `h2` + paragraph (title + description) but **no eyebrow**. Service details start with an icon + H1 then jump into pricing. Resume dumps long institute names at small sizes.

Plan: one section-header pattern (eyebrow optional via existing `h2` scale, title, description). Service pages get a true hero → overview → included → pricing → process → SLA → add-ons → FAQ → quote CTA.

## Typography

- LTR: Poppins (overlay). RTL: Vazirmatn.
- Body ~16px overlay; some resume/service lists drop below 14px.
- Service pages did not load Poppins/Vazirmatn (Bootstrap-icon page + `services.css` system font).

Plan: shared `--font-display / --font-heading / --font-body` and `--text-*` scale. Service landing loads the same fonts.

## Color

Brand primary is `#2563eb` (also Filament). Accent `#0ea5e9`. Footer was historically red, overlay aliases it to primary. Skills/progress and some hover states still introduce unrelated greens/oranges.

Plan: blue is the only brand accent. Green = success/availability only.

## Spacing / grid

`--space-*` exists in the overlay but `main.css` and `services.css` still use ad-hoc padding. Containers 1140px vs service `container` padding.

Plan: section vertical rhythm `clamp(3rem, 6vw, 5rem)`; card padding `--space-6`; control height 44px.

## Components

Buttons: hero `btn-modern`, catalog `btn-primary`, service `btn-success` (green submit), contact native-ish. Cards: at least four radius/shadow recipes.

Plan: primary / outline / ghost / danger. Cards share radius-lg, 1px border, shadow-sm, hover lift 4px.

## Navigation / language

Already glass + fullscreen mobile (v1116). DE hidden unless `[data-de]`. Keep; do not regress.

## Hero

Name is large; rotating typed string carries the role. Existing factual role is “Network and IT Infrastructure Specialist”. Typed items already include DevOps Engineer. Plan: static role line from existing About copy; keep typed specializations; keep CTAs.

## Get to Know Me

Rebuilt in a prior pass (asymmetric copy + Infrastructure Core). Keep structure; only token-align.

## Stats / Skills / Resume

Stats: four existing counters (49 / 31 / 4160 / 12) — do not invent. Skills: unify bar color to primary, 2-col → 1-col. Resume: CSS timeline, no copy change.

## Services

Index: six DB cards — keep. Detail: rebuild as landing pages using `features`, `process`, `faq`, `presentation` exclusions/deliverables/SLA/addons, existing prices. **Do not invent process steps.** Form must not dominate: CTA first, form revealed.

## Articles

Index overlay is acceptable. Detail: chrome only; body HTML untouched.

## Testimonials / Contact / Footer

Testimonials overlay exists. Contact: two-column desktop. Footer: same primary, quieter icons.

## Accessibility / motion

Focus ring token exists. Service FAQ uses click-only buttons (OK if keyboard + aria). Form always `show` — fail. Reduced motion: extend to new landing animations.

## Out of scope for invention

No new services, prices, testimonials, years, German public pages, or article rewrites.

## Implementation order

1. Central tokens in `visual-upgrade.css`
2. Service landing Blade + CSS
3. Homepage section/card/button/skill/resume/contact/footer overlay
4. Hero static role
5. QA: compare-content, PHPUnit, browser matrix
