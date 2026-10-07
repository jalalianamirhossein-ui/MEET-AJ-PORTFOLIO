# FortiGate SD-WAN article package

- `article.fa.md`: complete Persian editorial source, SEO, captions and official references.
- `article.fa.html`: standalone RTL reading preview with embedded styles.
- `article-body.fa.html`: HTML body for a site's article editor; adjust relative image URLs to the final asset locations.
- `base-config.fortios.conf`: the six ordered CLI blocks of the main static IPv4 scenario. Replace documentation WAN addressing with ISP assignments before use.
- `images/01` through `05`: five original technical illustrations, each in editable SVG and 1600 px PNG format. They are diagrams, not FortiOS screenshots.
- `images/contact-sheet.png`: editorial QA preview; not an extra article image.

Reference baseline: FortiOS 7.6.3, core SD-WAN syntax cross-checked against 7.4.6. Main mode is explicit SLA load balancing with source-destination hash; the weighted example applies only to the implicit rule. Example source uses two sequential probe servers, not simultaneous voting. Both-links-alive/SLA-failed fallback is documented.

Syntax and documented behavior were checked using official Fortinet documentation. No physical FortiGate or FortiOS VM was available, so device execution and live failover are not claimed. Acceptance tests in the article are required on the actual model and patch.

This package does not change the website database or publish to production. The existing CMS uses additional bilingual/localization metadata; this Persian editorial deliverable is ready to adapt or paste into that publishing workflow.

To rebuild HTML and PNGs, set `CODEX_ARTICLE_NODE_MODULES` to the bundled Node package directory returned by the workspace dependency tool, then run `node build.cjs`.
