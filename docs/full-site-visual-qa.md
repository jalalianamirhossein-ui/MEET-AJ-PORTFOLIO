# Full-site visual QA — Meet AJ

**Date:** 2026-09-17  
**Overlay:** `assets/css/visual-upgrade.css?v=1306`  
**Git:** not initialized / not committed.

## Method

Mandatory skill order was executed:

1. **frontend-design** — Article DNA direction (editorial, left-aligned, one blue accent, photo as the memorable element).
2. **using-ui-stack / Laravel** — Blade + CSS component families (buttons, pills, grouping cards). No Tailwind/shadcn added.
3. **responsive-testing** — 375 / 428 / 768 / 1280 / 1536 overflow + screenshots.
4. **accessibility-auditing** — aria snapshots, skip link, FAQ `aria-expanded`, Escape on mobile menu, 44px menu/arrow targets, form labels.
5. **visual-qa-testing** — implement → render → screenshot → fix → re-render. Screenshot-visible issues were fixed even when DOM overflow was 0.

Article pages are the DNA reference and were not redesigned.

## Stage results

| Stage | Rendered? | Result | Notes |
|-------|-----------|--------|-------|
| A Homepage | Yes 1280/768/375 | PASS | White hero name, editorial about map, skills lists, resume timeline, catalog cards as grouping |
| B Services index | Homepage `#services` | PASS | There is no `/services` route (404 by design). Catalog lives on the homepage |
| C Six service details | Yes all 6 heroes | PASS | Flattened blocks; form hidden until CTA; prices unchanged |
| D Articles index | Yes 1280 | PASS | Compact category pills, Article H2 bar |
| E Nav / language | Desktop + mobile | PASS | Sidebar desktop; fullscreen mobile menu; EN/FA |
| F Contact | Yes 1280 | PASS | Info flattened; form remains one grouping card |
| G Mobile | 375/428/768 | PASS overflow 0 | Menu Escape closes; service blocks inset from language chip |

## Visual problems found and fixed this pass

1. Hero first name washed gray on the photo — solid white + shadow (both name lines).
2. About “Infrastructure Core” was a dark dashboard card that clipped node labels — restyled to Article surface + inset nodes.
3. About map animations ignored reduced-motion — grid/flow/pulse now respect `prefers-reduced-motion`.
4. SLA/add-on lists used a 2-column grid that left a leftover third item — single editorial column.
5. Testimonial prev/next sat on top of quotes — arrows moved under the carousel (44×44).
6. Floating EN overlapped service section kickers while scrolling — service blocks inset 5rem.
7. Overlay cache stale in the browser — bumped to `v=1306` and copied to `public/`.

## Remaining issues (not blockers)

- Cursor’s screenshot panel is narrower than emulated 1280/1440, so the right column can look cropped in captures even when `scrollWidth - clientWidth === 0`.
- Typed line still uses “I'm a” + “IT Consultant” (existing copy).
- Articles index heading jump h2 → h4 is pre-existing; Article detail was not redesigned.
- At 375 the EN chip still sits close to the FAQ kicker; content is readable and overflow is 0.

## Console / network

No JS errors collected on homepage (`window.__qaErrors` empty). Overlay loaded as `visual-upgrade.css?v=1306`. Service form stays hidden until Request a Quote; after click, labeled fields (Full Name, Email, Phone, Service, Subject, Project Details) appear in the accessibility tree.
