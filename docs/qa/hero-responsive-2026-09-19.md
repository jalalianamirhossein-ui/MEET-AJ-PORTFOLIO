# Hero visual verification — 2026-09-19

Verified in Chromium against the local Laravel site. English and Persian were switched using the actual language control, with the same Hero markup and photo.

| Viewport | EN | FA | Horizontal overflow | Minimum face-to-copy clearance |
| --- | --- | --- | --- | --- |
| 360 × 760 | Pass | Pass | 0 px | 81 px |
| 390 × 844 | Pass | Pass | 0 px | 125 px |
| 400 × 608 | Pass | Pass | 0 px | 60 px |
| 430 × 932 | Pass | Pass | 0 px | 171 px |

Screenshots were visually inspected for the face, badge, name, role and both CTAs. The face bounds were conservatively estimated from the original 1920 × 1080 photo (x=770–1155, y=16–515), then mapped through the rendered `object-fit: cover` crop. All copy begins below that region. Both CTAs remain visible in all four viewports. The 400 × 608 Hero grows to approximately 667 px so the transition can follow the content without clipping it.

The photo height is independent of text height, and its physical horizontal position stays at 52% in both languages. The lower scrim begins below the face. The final 64 px fade matches the About section's #f8fbff top edge; its pale blue gradient and restrained cyan glows preserve white reading surfaces.

Existing public-site and production-audit regression suites: **16 tests passed, 626 assertions**. Public CSS matches its editable source. Homepage stylesheet cache version was updated in both the source HTML and generated Blade view.
