> **HISTORICAL / SUPERSEDED.**
> **Original date:** 2026-09-16.
> **Original purpose:** UI/UX browser QA pass, including the 10-viewport matrix that has not been re-measured since.
> **Superseded by:** [../qa/VISUAL-QA.md](../qa/VISUAL-QA.md), [../qa/RESPONSIVE-QA.md](../qa/RESPONSIVE-QA.md), [../qa/ACCESSIBILITY-QA.md](../qa/ACCESSIBILITY-QA.md).
> Authoritative per-URL QA: [../qa/QA-MATRIX.md](../qa/QA-MATRIX.md). Current status: [../current/PROJECT-STATUS.md](../current/PROJECT-STATUS.md).

# Final UI/UX QA — Meet AJ

Date: 2026-09-16 (navigation + language switcher pass)  
Origin: `http://127.0.0.1:8000`  
Overlay: `assets/css/visual-upgrade.css?v=1116` (after `rtl.css`)  
Language chrome: `assets/css/lang-toggle.css?v=1115`, `assets/js/i18n.js?v=1115`, `assets/js/main.js?v=1115`  
Source of truth: browser-rendered site.

PHPUnit: **30 tests, 584 assertions, 1 skipped (MySQL), 0 failures**  
`php artisan site:compare-content`: **Failures: 0**  
`php artisan optimize:clear`: ran locally.

Git was not initialized, committed, or pushed.

---

## Navigation + language switcher (this pass)

### Language switcher changes

Replaced the old `EN | FA` pill (gradient, glow, 36px, hover-rotate) with a compact glass control:

- Button shows the **active** language (`EN` / `FA`)
- Click opens a 180ms listbox with EN and FA
- Active option uses a primary inset bar (inline-start)
- 44px minimum height
- Desktop: mounted in the sidebar `.logo-section`
- Mobile: floating glass control, opposite the menu button
- Closes on outside click and Escape; arrow keys move between options
- **DE is not shown.** Public pages have no `[data-de]`. `GET /de` remains 404. German is still CMS-draft only.

### Navigation button changes

Sidebar / menu links keep the existing items (Home, About, Resume, Services, Articles, Testimonials, Contact). Skills was not added — that link was never in the live nav.

- Quiet default, light glass hover, **stronger active** (fill + inset bar)
- No translate/scale hover
- `focus-visible` uses `--focus-ring`
- Active is visually stronger than hover

### Glass UI

Restrained glass: translucent fill, `backdrop-filter: blur(16–22px)` with an opaque fallback, 1px slate border, soft shadow. Readable on the hero photo (dark floating switcher) and on the light desktop sidebar (light switcher).

### Mobile menu changes

The drawer is no longer a 300px off-canvas strip.

When open (`< 1200px`):

- `#header` is `position: fixed; inset: 0`
- Measured **375×812** cover: `375.2×812` at `(0,0)` (subpixel width)
- Dark technical surface + subtle grid
- Top: logo, language switcher, close (☰ morphs to ×)
- Middle: existing primary nav
- Bottom: existing social/phone links (no invented contact copy)
- `body.menu-open` / `html.menu-open` lock scroll (`overflow: hidden`)
- Escape closes; focus returns to `#menu-toggle`
- Closed header is `hidden` + `inert` (out of tab order)
- Opening animation ~320ms; nav items stagger 40ms; `prefers-reduced-motion` disables both

### Accessibility

- Switcher: `aria-haspopup="listbox"`, `aria-expanded`, `aria-controls`, options `aria-selected`
- Menu: `aria-controls="header"`, `aria-expanded`, bilingual `aria-label` Open/Close
- Focus trap while the fullscreen menu is open (Tab / Shift+Tab)
- 44×44 menu button and 44px language control at 320px
- Reduced motion: no stagger, no chevron spin, no shell fade

### Responsive

`document.documentElement.scrollWidth - clientWidth`:

| Viewport | Overflow | Notes |
|----------|----------|--------|
| 320×800 | 0 | 44px controls; EN+FA only |
| 375×812 | 0 | Fullscreen menu + closed chrome |
| 768×1024 | 0 | Still uses fullscreen menu (`<1200`) |
| 1440×900 | 0 | Sidebar + in-logo switcher |
| 1920×1080 | 0 | Hamburger `display: none` |

Also exercised 390 conceptually with neighbors; 1024/1366 follow the same 1200 breakpoint.

### Animation

- Language menu: 180ms fade/translate
- Hamburger bars: 200ms to × (bars only, button does not spin)
- Fullscreen shell: 320ms fade
- Nav items: 280ms fade+translateY, 40ms stagger
- Reduced motion: functional, no decorative motion

### EN / FA

- EN LTR: switcher left on mobile, close right; sidebar LTR
- FA RTL: switcher right, close left; nav labels right-aligned with icons on the inline-end
- DE: omitted (not available on the page)

### Screenshots tested

| File | What |
|------|------|
| `nav-en-desktop.png` | EN sidebar, active Home, EN control |
| `nav-lang-dropdown-en.png` | EN/FA listbox, no DE |
| `nav-en-mobile-closed.png` | Floating EN control + hero |
| `nav-en-mobile-menu.png` | Fullscreen EN menu, ×, social footer |
| `nav-fa-mobile-menu.png` | Fullscreen FA menu, RTL |

### Remaining issues (navigation)

| ID | Severity | Problem | Status |
|----|----------|---------|--------|
| N01 | Low | Desktop language listbox can overlay the first social row | Remaining |
| N02 | Low | Browser screenshot canvas still shows unused white beside emulated viewports | QA artifact |
| N03 | Low | Service detail pages have the switcher but no `#header` fullscreen menu (source layout) | By design |
| N04 | — | German remains unpublished | By design |

---

## Prior pass — articles, cards, testimonials

The remainder of this file is the earlier 2026-09-16 article/card QA. Overlay at that time was `visual-upgrade.css?v=1108`. PHPUnit counts in that pass were 23 tests / 510 assertions; the live suite is now 30 / 584.

PHPUnit then: **23 tests, 510 assertions, 1 skipped (MySQL), 0 failures**  
`php artisan site:compare-content` then: **Failures: 0**

Lighthouse was not run in that pass either.

---

## Visual issues found

| ID | Severity | Problem | Location | Fix | Validation | Status |
|----|----------|---------|----------|-----|------------|--------|
| V01 | High | Article filters looked like raw glass pills (gradient, scale, Segoe UI) | `#portfolio .portfolio-filters` | Overlay toolbar: surface, 44px targets, solid `--primary` active, no gradient/scale | Desktop + 320px EN/FA screenshots; computed `h=44`, active `rgb(37,99,235)` | Fixed |
| V02 | High | Card titles/excerpts still fought overlay positioning; CTA was a 44×44 icon | `.article-teaser` | Persistent caption, line-clamp, text CTA “More Details” / «جزئیات بیشتر» | Titles readable without hover on desktop, tablet, 320px | Fixed |
| V03 | Medium | Uneven card heights broke the grid | `#portfolio .isotope-container` | CSS grid `align-items: stretch`, equal image `14rem` / `16:10`, title 2-line / excerpt 3-line clamp | First-row cards `480px` at 1440 | Fixed |
| V04 | Medium | Testimonials used Segoe UI and hover accent wash | `#testimonials .testimonial-item` | Poppins/Vazirmatn, quiet surface, no hover color shift; pause/play kept | EN/FA screenshots; pause toggles `aria-pressed` when `prefers-reduced-motion: no-preference` | Fixed |
| V05 | Medium | Service pages still felt like a second system | `body.service-page` | Overlay type, radius, focus, 44px controls; original layout kept | `/services/network-design.html` EN + FA | Fixed |
| V06 | Low | 4-up grid at 1440 cramped titles | `.isotope-container` | `minmax(19rem, 1fr)` | 1440 EN articles screenshot | Fixed |
| V07 | Low | Load-more still used glass gradient | `.load-more-btn` | Solid `--primary` | Visual on homepage/listing | Fixed |

---

## Article filter issues

| ID | Severity | Problem | Location | Fix | Validation | Status |
|----|----------|---------|----------|-----|------------|--------|
| F01 | High | Active filter not obvious | `.filter-active` | Solid `#2563eb` fill, white label | Microsoft filter screenshot | Fixed |
| F02 | High | Keyboard/ARIA incomplete in markup | `ul.portfolio-filters` | `role="group"`, each `li` is `role="button"` `tabindex="0"` `aria-pressed`; Enter/Space in `main.js` | Snapshot interactive refs; keyboard handler present | Fixed |
| F03 | Medium | Touch targets below 44px / hover scale | `li` padding 16px 32px + transform | `min-height: 44px`, no transform | 320px: all six filters `h=44` | Fixed |
| F04 | Medium | Isotope hide vs CSS grid left filtered cards visible | `.portfolio-item` | `.is-filtered-out { display:none }` after `arrange` | Counts: All 6 (load-more), Microsoft 5, Linux 6, MikroTik 6, VMware 3, Others 3 | Fixed |
| F05 | Low | RTL filters mirrored as a CSS afterthought | `html[dir=rtl] .portfolio-filters` | `direction: rtl`; Vazirmatn; active chip on the inline-start | FA 1440 + 320 screenshots | Fixed |

Filter labels unchanged (All Articles, Microsoft, Linux Articles, MikroTik, Vmware, Other Articles).

---

## Article card issues

| ID | Severity | Problem | Location | Fix | Validation | Status |
|----|----------|---------|----------|-----|------------|--------|
| C01 | High | Information hidden until hover | `.portfolio-info` | Caption outside thumbnail; always visible | Cards show image, category, title, excerpt, CTA at rest | Fixed |
| C02 | Medium | Variable heights | `.article-teaser` | Stretch + line-clamp + fixed image height | Equal first-row height | Fixed |
| C03 | Medium | CTA not a readable action | `.details-link` icon | `.article-teaser-cta` text button, 44px min-height | EN “More Details”, FA «جزئیات بیشتر» | Fixed |
| C04 | Low | RTL CTA/preview order could inherit LTR grid | `.portfolio-links` | `html[dir=rtl] .article-teaser .portfolio-links { direction: rtl }` | FA card row | Fixed |
| C05 | Low | Generic “Article” / «مقاله» category on most cards | presentation `category_label_*` | Left as source HTML overlay label (not invented) | — | Remaining (source) |

---

## Testimonial issues

| ID | Severity | Problem | Location | Fix | Validation | Status |
|----|----------|---------|----------|-----|------------|--------|
| T01 | Medium | Segoe UI + generic overlay cards | `.testimonial-item h3/h4` | Inherit `--default-font`; quiet border/shadow | EN/FA screenshots | Fixed |
| T02 | Medium | Pause control hidden under reduced motion | `[data-swiper-autoplay-toggle]` | Kept: JS hides toggle and stops autoplay when `prefers-reduced-motion: reduce` (correct). Visible when motion allowed | Automation browser defaulted to reduce; after `no-preference`, `display:inline-flex`, click sets `aria-pressed` | Fixed (by design) |
| T03 | Low | Hover recolored author to accent | `:hover h3` | Overlay keeps primary text color | Computed styles | Fixed |

Quote copy was not rewritten or translated.

---

## EN/FA issues

| ID | Severity | Problem | Location | Fix | Validation | Status |
|----|----------|---------|----------|-----|------------|--------|
| L01 | High | FA article H1 showed «مقاله» instead of the source title | `hero_title_fa` via `LegacyArticleImporter::attr()` | Parser now reads attributes on the same opening tag; re-imported 23 articles from `articles/*.html` | Ubuntu article H1 = «تنظیم تاریخ، ساعت و Time Zone در Ubuntu Server»; compare-content 0 failures | Fixed |
| L02 | High | RTL cards/filters used accidental `text-align:center` from `rtl.css` | overlay after rtl | `text-align: right` + Vazirmatn; no `letter-spacing` on Persian headings | FA homepage/articles | Fixed |
| L03 | Medium | English needed Poppins, Persian Vazirmatn | `body` / `html[dir=rtl]` | Overlay fonts; category `text-transform: none` in RTL | Computed `font-family` | Fixed |
| L04 | Low | Service “Back to Services” stays English in FA | service chrome | Existing source string; not invented | FA service screenshot | Remaining (source) |

---

## Responsive issues

| ID | Severity | Problem | Location | Fix | Validation | Status |
|----|----------|---------|----------|-----|------------|--------|
| R01 | High | Filtered/hidden cards + Isotope absolute layout caused clipped captions | `#portfolio` | CSS grid, `position:static`, `.is-filtered-out` | 1440/768/320 | Fixed |
| R02 | Medium | Mobile filters as a single overflowing row | `<575px` | Wrap; `flex: 1 1 calc(50% - gap)` | 320px FA: 2-col, overflow 0 | Fixed |
| R03 | — | Screenshot canvas showed a white remainder beside emulated viewports | Browser tool | Not page overflow; CDP `scrollWidth - clientWidth` | 320, 375, 414, 768, 1024, 1440, 1920 = **0** on home/articles/service | Validated |

Additional viewports 390 / 1280 / 1366 were not every-page screenshots; neighbors 375/414 and 1024/1440/1920 had overflow 0.

HTTP 200: `/`, `/articles`, two article details, all 6 `/services/*.html`.

---

## Accessibility issues

| ID | Severity | Problem | Location | Fix | Validation | Status |
|----|----------|---------|----------|-----|------------|--------|
| A01 | High | Filters not exposed as buttons | `li` | `role="button"`, `tabindex="0"`, `aria-pressed` | Snapshot | Fixed |
| A02 | Medium | 44px targets | filters, CTA, preview, pause, load-more | `--control-height: 44px` | Computed heights | Fixed |
| A03 | Medium | Focus rings missing on polish components | filters, CTA, carousel | `outline: var(--focus-ring)` on `:focus-visible` | CSS overlay | Fixed |
| A04 | — | Skip-link still peeks ~2px (`top:-40px`) | global | Pre-existing; left intact | — | Remaining (existing) |

Skip links, article copy buttons, and service FAQ disclosure were not removed.

---

## Performance issues

| ID | Severity | Problem | Location | Fix | Validation | Status |
|----|----------|---------|----------|-----|------------|--------|
| P01 | — | No new JS frameworks, CDNs, or animation libraries added | overlay CSS + existing `main.js` | — | Asset list unchanged except cache `v=1108` | Pass |
| P02 | Low | Lighthouse not executed | CI/local | — | — | Remaining (not measured) |
| P03 | — | Broken images | hero, profile, cards | — | `naturalWidth>0`; 0 broken imgs at 375 | Pass |

---

## Fixes implemented

1. **Article filters** — design-system toolbar (no gradient, 44px, obvious active, keyboard + ARIA).
2. **Article cards** — persistent category/title/excerpt/CTA; equal-height grid; text CTA.
3. **Filter behavior** — `.is-filtered-out` so CSS grid and Isotope agree; all six filters return the correct sets (23 total).
4. **Testimonials** — same type/radius/shadow/surface as the rest of Meet AJ; pause/play retained.
5. **EN/FA** — overlay after `rtl.css`; Vazirmatn/RTL for Persian, Poppins/LTR for English.
6. **Importer** — `attr()` no longer picks up a preceding `data-fa="مقاله"`; FA hero/card titles restored from source HTML.
7. **Services** — token overlay only; URLs remain `/services/{slug}.html`.
8. **Cache** — `visual-upgrade.css` / `main.js` `?v=1108`; views published from `index.html`.
9. **Admin test 500** — `UserResource` moved to `app/Filament/Resources/Users/` and hidden from navigation (`shouldRegisterNavigation = false`) because Filament 5 was not registering `filament.admin.resources.users.index` (sidebar called `route()` and 500’d). PHPUnit green. User CRUD remains a remaining admin item.

Did not change: article body HTML, SEO URLs, DB schema, contact API, PWA, Filament panel architecture (beyond User resource path/nav).

---

## Remaining issues

| ID | Severity | Problem | Location | Fix | Validation | Status |
|----|----------|---------|----------|-----|------------|--------|
| X01 | Medium | Filament Users resource routes still do not appear in `route:list`; nav hidden so `/admin` does not 500 | `Users\UserResource` | Needs a dedicated Filament 5 route-registration fix | `route:list` has articles/categories/requests only | Remaining |
| X02 | Low | Card category label is still the source word “Article” / «مقاله» on most teasers | presentation | Do not invent Microsoft/Linux badges | Visual | Remaining (source) |
| X03 | Low | Service back-link English on FA | service pages | Source has no FA string | FA service screenshot | Remaining (source) |
| X04 | Low | Skip-link 2px peek | `.skip-link` | Existing a11y pattern | — | Remaining (existing) |
| X05 | Low | Lighthouse not run | — | Run on deploy host | — | Remaining |
| X06 | Low | 390 / 1280 / 1366 not every-page screenshots | viewports | Neighbors overflow 0 | CDP | Remaining (coverage gap) |
| X07 | — | German `/de` unpublished | public | No DE source; 404 by design | PHPUnit | Remaining (by design) |
| X08 | — | Filament interactive login/click-through | `/admin` | Historical BLOCKED (password policy / human) | — | Remaining |

---

## Viewport matrix (overflow)

`document.documentElement.scrollWidth - clientWidth` (CDP). 0 = no horizontal scroll.

| Viewport | Home | /articles | Article detail | Service |
|----------|------|-----------|----------------|---------|
| 320×800 | 0 | 0 | 0 (FA) | 0 |
| 375×812 | 0 | — | — | — |
| 414×896 | — | — | — | 0 |
| 768×1024 | 0 | — | — | — |
| 1024×768 | — | — | — | 0 |
| 1440×900 | 0 | 0 | — | — |
| 1920×1080 | — | 0 | — | 0 |

---

## Functional QA

- Filters: Microsoft 5, Linux 6, MikroTik 6, VMware 3, Others 3, All (load-more batch 6) — **pass**
- Active filter class + `aria-pressed` — **pass**
- Language switcher EN ↔ FA, `dir` ltr/rtl — **pass**
- Article links `/articles/{slug}` — **pass**
- Service URLs `/services/{slug}.html` — **pass** (bare `/services/network-design` is 404 by existing route `.*\.html`)
- Testimonial pause/play — **pass** when motion is allowed; hidden under reduced motion
- Contact forms — not resubmitted this pass; PHPUnit contact tests green
- Console: no broken images on sampled homepage; Lighthouse not run
