# Essential enterprise Group Policy article — 2026-10-07

Added the bilingual Microsoft article at `/articles/10-essential-group-policies-windows-domain` through the existing importer and a scoped migration. Maintained builder: `scripts/build-essential-gpo-article.php`; output: `resources/legacy/articles/10-essential-group-policies-windows-domain.html`.

The 17 sections cover the ten policies, domain-root account-policy scope versus FGPP, Windows LAPS prerequisites and membership separation, Defender tamper limitations, role-specific WSUS scheduling, USB storage versus device installation, RDP access controls, audit collection, Office/Edge/PowerShell hardening, architecture, rollout, troubleshooting, summary and the final deployment checklist. Official Microsoft references accompany the relevant sections. Commands are editorial examples, not executed on a real Windows domain.

All five supplied PNGs are used. Banner export is exactly 1000×1000; the original 1254×1254 source is preserved in the editorial package. Four body illustrations retain 1536×1024 dimensions, explicit aspect-ratio attributes and lazy loading. Captions clarify schematic labels that differ from actual policy paths or command behavior. No global page redesign was made.

Local import followed a consistent SQLite `VACUUM INTO` backup at `storage/app/private/essential-gpo/before-20261007-180100.sqlite`; integrity was `ok`, with 32 pre-import articles. Only the new migration was run, not other pending work. Remote hosting was not modified.

Validation: PHP syntax passes; isolated SQLite in-memory feature test passes with 42 assertions for both locales, 17 sections, four body figures, summary/checklist rows, identical LTR command blocks, 1000×1000 banner, category, search, sitemap and legacy redirect. The existing ignored PHPUnit wrapper was used with an explicitly nonexistent APP_CONFIG_CACHE path and in-memory database environment.

Browser checks confirmed Persian RTL, live English switching, localized SEO title, correct hero image dimensions, and no document horizontal overflow at the default and narrow viewport. Screenshots: `storage/app/essential-gpo-desktop-fa.jpg`, `storage/app/essential-gpo-mobile-en.jpg`. A Laravel router was used for preview; a plain PHP document-root server initially served the retained static homepage and was replaced. Fresh preview origin: `http://127.0.0.1:8016`.

The repository-wide image checker reported five pre-existing/unrelated missing or different published MikroTik images. None concern this article; its five published image assets are present. Other concurrent workspace changes were preserved.

## Reference placement and FAQ correction

Moved all Microsoft reference links from the policy sections to a deduplicated final `references` section. Added eight article-specific bilingual questions and answers before the deployment checklist, with matching localized FAQPage schema and native disclosure rendering. The updated article has 19 sections. Focused test now passes with 52 assertions, including final reference placement, absence of inline reference blocks, eight FAQ disclosures and localized schema matching. A fresh SQLite backup preceded the scoped source update of article ID 33; publication date and status were preserved.
