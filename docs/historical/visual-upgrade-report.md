> **HISTORICAL.** Overlay implementation report. Later UI QA: [final-ui-qa.md](final-ui-qa.md). Authoritative overall QA: [QA-MATRIX.md](../qa/QA-MATRIX.md).

# Meet AJ — Visual upgrade report

Date: 2026-09-16  
Origin: `http://127.0.0.1:8000`  
Mode: Additive polish on the migrated Laravel site. Identity, copy, URLs, Filament, contact contracts, and PWA cache name were preserved. Git was not initialized, committed, or pushed.

## 1. Before / after design problems

| ID | Before | After |
| --- | --- | --- |
| A01 | Live SQLite had 0 articles during parts of QA; grid empty | 23 articles re-imported from `articles/*.html` |
| A02 | `/articles` was a filter fragment without site chrome | Same listing content inside the existing sidebar + footer |
| A03 | Card titles/excerpts were hover-only overlays, often clipped | Persistent `.article-teaser` caption under the thumbnail |
| A04 | Hero primary CTA was glass Apple-blue on the photo | Solid `--primary` `#2563eb` with white label |
| A05 | Conflicting tokens (Roboto, red unused footer, rainbow header) | Overlay: Poppins, white sidebar, footer token aliased to primary |
| A06–A08 | Ad-hoc type, noisy lifts, title scale, float animations | Tokenized radius/shadow/space; hover lift ≤4px; reduced-motion rules |
| A09 | Service pages used a second system | Same pages; Poppins overlay + quieter cards/focus; copy unchanged |
| A10 | Article detail already strong | CSS-only hero/body rhythm; article HTML untouched |

Design audit: `docs/design-audit.md`.

## 2. Changes implemented

- Additive `assets/css/visual-upgrade.css` (published to `public/assets/css/`).
- Article cards: caption is a sibling of the image, not inside overflowing `.portfolio-content`.
- Homepage and listing use CSS grid for the article row so Isotope absolute positioning cannot clip captions.
- Article listing generator now emits site chrome so PHPUnit `buildViews()` cannot regress A02.
- Tests no longer wipe the live sqlite file.

Not done (on purpose): no Tailwind/shadcn, no new CDNs, no content rewrite, no URL/schema/Filament changes.

## 3. Files changed

| File | Role |
| --- | --- |
| `docs/design-audit.md` | Phase 1 audit |
| `docs/content-integrity-fixes.md` | Restores from original HTML |
| `docs/visual-upgrade-report.md` | This report |
| `assets/css/visual-upgrade.css` | Design tokens + overlay |
| `public/assets/css/visual-upgrade.css` | Published copy |
| `resources/views/components/article-card.blade.php` | Teaser markup |
| `resources/views/home.blade.php` | Overlay link + generated grid include |
| `resources/views/articles/index.blade.php` | Chrome + overlay (generated) |
| `resources/views/articles/show.blade.php` | Overlay cache-bust |
| `resources/views/services/*.blade.php` | Overlay + `service-page` (generated from HTML) |
| `index.html` | Overlay cache-bust `v=1107` |
| `services/*.html` | Overlay cache-bust `v=1107` |
| `app/Services/LegacySitePublisher.php` | Durable listing chrome |
| `tests/TestCase.php` | Force sqlite `:memory:` for PHPUnit |
| `.env.testing` | Laravel testing env (`:memory:`) |

Article bodies, SEO URLs, and `routes/web.php` were not rewritten.

## 4. Design system changes

Reused existing `--primary` `#2563eb`, `--surface`, `--text-primary`. Added:

- Spacing `--space-1` … `--space-16`
- Radius `--radius-sm` … `--radius-xl`
- Shadows `--shadow-sm` / `--md` / `--lg`
- `--container` 1140px, `--measure` 40rem, `--control-height` 44px
- `--text-on-primary`, `--heading-font` / `--default-font` Poppins + Vazirmatn
- `--footer-bg` aliased to primary (live blue footer; unused red token not activated)
- Hover: ≤4px lift; focus-visible: `--focus-ring`; disabled: opacity 0.5
- `prefers-reduced-motion`: no decorative motion

## 5. Responsive results

CDP check: `document.documentElement.scrollWidth <= clientWidth + 2`.

| Page | Viewport | Overflow |
| --- | --- | --- |
| `/` | 320×800 | none |
| `/` | 375×812 | none |
| `/` | 1440×900 | none |
| `/articles` | 768×1024 | none |
| `/services/network-design.html` | 375×812 | none |
| `/services/network-design.html` | 1920×1080 | none |

**Not measured** after the final restore: 390×844, 414×896, 1024×768, 1280×800, 1366×768 on every public URL. The requested 10-viewport × all-pages matrix is **incomplete**. Sampled sizes showed no horizontal overflow. Mobile nav toggle is present at 375 and 768.

## 6. Accessibility results

Preserved: skip links, 44px control height, form labels, honeypot `website`, EN/FA toggle, article copy buttons, FAQ buttons, `aria-current` on listing Articles.

Overlay: persistent card titles (no hover-only information), solid hero CTA contrast, `prefers-reduced-motion`, focus-visible outline on index/article/service links.

Skip-link remains the existing homepage pattern (`top: -40px` until focus). It was not removed.

Keyboard/screen-reader pass was **not** a full WCAG 2.2 audit. Filament browser login remains previously blocked.

## 7. Performance impact

- One extra CSS file; no new JS libraries, no background video, no new webfont hosts beyond fonts already linked.
- Images still lazy-loaded on cards; hero still uses existing `hero-bg.jpg`.
- PWA still `meet-aj-v2.0.0-cms-3` (`sw.js` 200).
- LCP/CLS/INP were not re-measured with Lighthouse. No evidence of a large JS/CSS regression from this overlay.

## 8. Content integrity results

`php artisan site:compare-content` → **Failures: 0** (homepage, 6 services, articles index, 23 article bodies).

Restores from original sources (no invented copy):

1. 23 articles re-imported after PHPUnit emptied the live sqlite file.
2. `/articles` chrome restored from `index.html` via the publisher.

Details: `docs/content-integrity-fixes.md`.

## 9. Test results

```
PHPUnit 11.5.56 / PHP 8.4.25
Tests: 23, Assertions: 510, Skipped: 1
OK, but some tests were skipped
```

Skipped: `MysqlSchemaTest` (MySQL profile is `phpunit.mysql.xml`).

`php artisan route:list` still shows **29** routes. Public routes unchanged: `/`, `/index.html` 301, `/articles`, `/articles/{slug}`, `/articles/{slug}.html`, `/services/{page}`, contact token/store, sitemap, robots.

After tests, live sqlite still had **23** articles (TestCase `:memory:` isolation).

## 10. Remaining issues

- Full 10-viewport × every public page matrix not executed (see §5).
- Article card row heights still vary with title length.
- Hero title can sit on a bright part of the photo; text-shadow helps, contrast is not perfect on every crop.
- Service pages remain a separate quote layout (original HTML preserved).
- Lighthouse LCP/CLS/INP not re-run.
- Full keyboard-only + screen-reader pass not re-run.
- Card filter “Windows CMD…” preview alt still falls back to `"Article"` for one item (pre-existing presentation data, not rewritten).

## Public page results

Legend: **PASS** means the checks listed were run. **FAIL** means a required check was not completed or failed.

| Page | HTTP | Content compare | Visual screenshot | Result |
| --- | --- | --- | --- | --- |
| `/` homepage | 200 | PASS | Desktop 1440 + mobile 375; 23 teasers; solid CTA `rgb(37,99,235)` | **PASS** |
| `/index.html` | 301 → `/` | n/a | n/a | **PASS** |
| `/articles` | 200 | PASS | Chrome + 23 captioned cards (768 and earlier 1440) | **PASS** |
| `/services/network-design.html` | 200 | PASS | Desktop + mobile 375 | **PASS** |
| `/services/devops-automation.html` | 200 | PASS | Desktop | **PASS** |
| `/services/monitoring-security.html` | 200 | PASS | Desktop | **PASS** |
| `/services/system-administration.html` | 200 | PASS | Desktop | **PASS** |
| `/services/technical-consulting.html` | 200 | PASS | Desktop | **PASS** |
| `/services/virtualization-solutions.html` | 200 | PASS | Desktop | **PASS** |
| `/articles/ubuntu-date-time-settings` | 200 | PASS | Desktop article hero/TOC/code | **PASS** |
| Other 22 article canonical URLs | 200 each | PASS each | Template sampled via Ubuntu + listing cards | **PASS** (HTTP + content; visual by template) |
| 23 `/articles/{slug}.html` | 301 each | n/a | n/a | **PASS** |
| `/sitemap.xml` | 200, 30 `<loc>`, 23 article paths, no legacy `.html` article locs in compare | n/a | n/a | **PASS** |
| `/robots.txt` | 200 | n/a | n/a | **PASS** |
| `/manifest.json` | 200 | n/a | n/a | **PASS** |
| `/sw.js` | 200 | n/a | n/a | **PASS** |
| `/forms/get-csrf-token.php` | 200 | n/a | Homepage form labels + honeypot present | **PASS** |
| Viewport matrix (all pages × 10 sizes) | n/a | n/a | Sampled only | **FAIL** (incomplete) |

Homepage contact section, six service quote forms, footer, and EN/FA controls were present in accessibility snapshots. Contact POST contract was not re-posted in this visual pass; PHPUnit still covers token + persistence.
