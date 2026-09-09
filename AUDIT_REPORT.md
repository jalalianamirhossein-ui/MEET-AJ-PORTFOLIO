# Production Website Audit — Meet AJ Portfolio

Audit date: 2026-09-09

The repository is a static multilingual PWA: HTML documents, shared CSS/JavaScript,
and a PHP contact endpoint. It is not a React project, so the architecture review
applies to the document, style, behaviour, and server layers.

## Findings and remediation

| Issue | Location | Severity | Recommended fix | Status |
| --- | --- | --- | --- | --- |
| The off-canvas navigation remained in the keyboard tab order while visually closed. | `assets/js/main.js`, mobile header | High | Synchronize `inert`, `aria-hidden`, focus movement, Escape handling, and scroll lock with menu state. | Fixed |
| Mobile touch listeners duplicated native click handling and the Home link was intentionally prevented from navigating. | `assets/js/main.js` | High | Use one click path with `touch-action`, and always honor navigation links. | Fixed |
| Language switching replaced `textContent` on containers, removing nested icons and presentational spans; it also reloaded the page. | `assets/js/i18n.js`, hero and form markup | High | Translate direct text nodes only, preserve nested elements, emit a language-change event, and update animated text in place. | Fixed |
| Portfolio initialization waited for every lazy thumbnail before enabling filtering; the 24 PNGs total roughly 31 MB. | `assets/js/main.js`, `assets/img/portfolio` | High | Initialize layout immediately, reflow as images arrive, reserve a square media area, and cache images only on demand. | Fixed in code; source-image compression remains recommended. |
| The service worker prefetched the complete portfolio image set during install. | `sw.js` | High | Cache portfolio media through the existing on-demand static strategy rather than during installation. | Fixed |
| Service pages depended on unpinned third-party CDN CSS/JS, which weakens offline behavior and creates a network dependency. | `services/*.html` | Medium | Reuse local AOS and Bootstrap Icons assets. | Fixed |
| Service pages had no `main` landmark, skip link, canonical URL, Open Graph metadata, Twitter card, or sitemap entries. | `services/*.html`, `sitemap.xml` | High | Add landmarks, skip navigation, canonical/social metadata, and all six service URLs to the sitemap. | Fixed |
| The mobile hero reserved 400 px for an empty profile container, producing a large blank region before the primary content on phones. | `index.html`, `assets/css/main.css` | High | Hide the empty presentation container and use a viewport-safe, responsive hero inset. | Fixed |
| Service grids used fixed `minmax(300px, 1fr)` and `minmax(350px, 1fr)` tracks that could overflow a 320 px viewport. | `assets/css/services.css` | High | Use `minmax(min(100%, …), 1fr)`, wrap pricing/timeline content, and reduce mobile padding. | Fixed |
| The desktop sidebar disabled vertical scrolling, making lower navigation inaccessible on shorter laptop displays. | `assets/css/main.css` | Medium | Use a viewport-aware height with contained vertical scrolling. | Fixed |
| Sidebar navigation used low-contrast dark text on bright gradients. | `assets/css/main.css` | High | Use a dark, consistent shell with high-contrast text and active states. | Fixed |
| Filters were pointer-only list items. | `assets/js/main.js`, portfolio filters | Medium | Add button semantics, roving keyboard activation via Enter/Space, pressed state, and focus treatment. | Fixed |
| The preloader hid real content until `load`, and an extra no-op scroll listener ran every frame. | `assets/js/main.js`, `index.html` | Medium | Dismiss at DOM-ready with a short fail-safe, and remove the no-op listener. | Fixed |
| PWA manifest icon dimensions did not match the actual files, and landscape was prohibited. | `manifest.json` | Low | Correct icon dimensions and permit both orientations. | Fixed |
| Core styles referenced undefined legacy custom properties. | `assets/css/main.css` | Low | Provide compatibility aliases in the production baseline. | Fixed |
| The testimonial carousel advanced automatically with no persistent pause control and did not pause while keyboard focus entered the component. | `index.html`, `assets/js/main.js`, `assets/css/main.css` | Medium | Provide a 44 px pause/play control, pause on focus and document hiding, and honor reduced-motion preferences. | Fixed |
| Contact-form errors were announced but did not receive focus, leaving keyboard users without a clear recovery point. | `index.html`, `assets/js/main.js` | Medium | Make the error status focusable, focus it after an asynchronous failure, and retain native field-level validation. | Fixed |
| The shared focus outline used a yellow that is insufficiently distinct against white surfaces, and fixed mobile controls did not account for device safe-area insets. | `assets/css/main.css`, `assets/css/services.css` | Medium | Use a high-contrast blue outline on light surfaces, retain yellow on the dark sidebar, and add `env(safe-area-inset-*)` offsets. | Fixed |

## Remaining recommendations

1. Export the 24 1024×1024 portfolio thumbnails as appropriately sized WebP/AVIF derivatives. The source assets are large (about 1.1–2.0 MB each); code changes now defer them, but optimized derivatives would reduce transfer after a visitor opens more articles.
2. Consolidate the repeated inline FAQ/contact code shared by the six service pages into one external module in a follow-up maintenance pass. It is functionally stable, but centralizing it would reduce maintenance surface.
3. Add individual Open Graph/Twitter title and description metadata to the older article files that only have a canonical/title/description. The primary portfolio and all service pages now have complete social metadata.
4. Run a hosted Lighthouse/WebPageTest check after deployment to measure field-equivalent FCP/LCP/CLS; no browser automation session was available in this workspace for pixel-level screenshots.
