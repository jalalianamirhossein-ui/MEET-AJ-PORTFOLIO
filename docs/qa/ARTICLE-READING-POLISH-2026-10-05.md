# Shared article reading design — 2026-10-05

Reviewed all 27 imported articles in Persian and English. Layout changes apply
at render time through `ArticlePresentation` and `article-reading.css`, rather
than rewriting the authored HTML or requiring a content re-import.

- Normalize every content section to the shared article layout; remove the
  inherited page-section padding that created large gaps in unclassified sections.
- Remove empty spacer paragraphs outside protected executable blocks.
- Use shorter paragraph/section spacing, topic-colored heading frames, callouts,
  tables and dark code boxes; the topic comes from the existing category/filter.
- Use Vazirmatn for Persian reading and system body text with Poppins headings
  for English. Keep technical blocks LTR and monospace in both languages.
- Center architecture headings and recognized ASCII flows, while maintaining
  readable text alignment and left-aligned executable commands.
- Turn both legacy question/answer pairs and existing FAQ cards into native
  details/summary controls. Answers remain in the document and FAQ schema.
  Native keyboard activation needs no JavaScript; language switching preserves
  disclosure state and updates questions/answers using existing data attributes.
- Add a consistent language label and accessible copy button to unwrapped code
  blocks. Existing code wrappers are reused.

Verification: 28 focused tests passed with 6,774 assertions, including all-article
localization/SEO, FAQ text parity, image delivery and SQL backup rendering. The
presentation audit covers both languages of all 27 articles, verifies exact
preservation of every complete `pre` block, FAQ counts and unchanged stored
content. The transform is also checked for repeatability.

Browser checks cover real FAQ opening, Enter-key closing, English/Persian
switching while a FAQ is open, centered flow headings and narrow-screen layout.
No RouterOS commands or other article runbooks were executed by this review.

Deployment after transferring the application/view and asset changes:

```bash
php artisan site:publish-assets
php artisan view:clear
```

No article database re-import is required for this design update. The new
stylesheet is loaded after the existing shared styles; future imports and new
articles use the same rendering layer.
