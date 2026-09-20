> **HISTORICAL.** Log of content-integrity repairs vs original HTML. Current compare-content result lives in [QA-MATRIX.md](../qa/QA-MATRIX.md) and [PROJECT-STATUS.md](../current/PROJECT-STATUS.md).

# Content integrity fixes

Repairs made by comparing Laravel output to original source files. No content was invented.

---

Page: All 23 articles (`article-body`)  
Issue: Nested `<article class="article-card">` inside the body made a naive `</article>` slice truncate headings and later sections (example: Ubuntu article h2 count 15 vs 9).  
Original source: `articles/*.html`  
Fix: Depth-matching `sliceArticleBody()` in `LegacyArticleImporter` and `CompareLegacyContent`. Re-imported with `--refresh`.  
Validation: `php artisan site:compare-content` → Failures: 0.  
Result: PASS

---

Page: Homepage article grid  
Issue: Card regex failed when attributes spanned newlines, so the importer could not require 23 homepage cards.  
Original source: `index.html` portfolio grid  
Fix: Offset-based parser in `homepageCards()`.  
Validation: Import refuses to run unless 23 HTML files exist; 23 cards imported.  
Result: PASS

---

Page: Article HTML in the database  
Issue: Symfony HTML sanitizer stripped `data-fa` / bilingual attributes.  
Original source: `articles/*.html`  
Fix: Store trusted original article-body HTML. Sanitizer is not a fail gate for import.  
Validation: Compare command counts `data-fa=` in source body vs imported body.  
Result: PASS

---

Page: Homepage, service pages (Blade conversion)  
Issue: Escaping every `@` to `@@` left `mailto:jalalian.amirhossein@@gmail.com` in the compiled HTML because Blade treats `@gmail` as a directive. JSON-LD `@type` was the original reason for escaping.  
Original source: `index.html`, `services/*.html`  
Fix: Wrap converted HTML in `@verbatim` / `@endverbatim`; close verbatim only around `@foreach` for the article grid. Rebuilt with `php artisan site:publish-assets --views`.  
Validation: `curl http://127.0.0.1:8000/` contains `mailto:jalalian.amirhossein@gmail.com` and `"@type": "Person"`. PHPUnit asserts no `@@gmail`.  
Result: PASS

---

Page: `/sitemap.xml`  
Issue: Homepage and service `lastmod` used `now()`, which invents a modification date.  
Original source: `index.html`, `services/*.html` file mtimes; article dates from JSON-LD or source file mtime  
Fix: `SitemapController` uses `filemtime` for home/services and `published_at` for articles. Importer no longer falls back to `now()`.  
Validation: Code review + re-import. Not a live crawler check of every lastmod value.  
Result: PASS (implementation); lastmod values were not independently crawled after the change

---

Page: German public pages  
Issue: None in source.  
Original source: No complete German article HTML.  
Fix: None. German remains unpublished. No `/de`, no `hreflang="de"`.  
Validation: `/de` and `/de/articles/*` return 404.  
Result: CONTENT_SOURCE_MISSING (German public content — expected)

---

---

Page: Filament interactive browser login  
Issue: Environment policy blocked entering the generated CMS password into the login form.  
Original source: n/a  
Fix: HTTP `actingAs` + Livewire list/forbidden tests; `cms:create-user` created local QA users in gitignored `.runtime/qa-users.json`.  
Validation: PHPUnit Gate + Livewire; browser login not completed.  
Result: BLOCKED (external automation restriction)

---

Page: Responsive matrix  
Issue: Not every public HTML page was measured at every requested viewport.  
Original source: existing CSS; no redesign.  
Fix: CDP overflow checks on homepage 320/375/768, network-design 320, articles index 1440, Ubuntu article 1440 — no horizontal overflow observed. Remaining page×viewport pairs were not executed.  
Validation: `document.documentElement.scrollWidth <= clientWidth + 2` where measured.  
Result: FAIL (matrix incomplete)

---

---

Page: Live SQLite `articles` table (visual-upgrade QA)  
Issue: `php artisan test` used `RefreshDatabase` against `database/database.sqlite` because PHPUnit 11 ignores `force="true"` on `<env>`. The suite migrated the file database, rolled back imported rows, and left **0** published articles. Homepage cards, `/articles`, and all 23 article URLs disappeared.  
Original source: `articles/*.html` (23 files) plus existing `articles:import-legacy --refresh`  
Fix: Re-imported 23 articles and 23 legacy redirects from those HTML files. Forced `DB_DATABASE=:memory:` in `tests/TestCase.php` before the application boots, and added `.env.testing` with `:memory:`. After the next PHPUnit run the file database still had 23 rows.  
Validation: `php artisan articles:import-legacy --refresh` → 23 imported, 23 redirects. HTTP 200 on all 23 `/articles/{slug}` and 301 on all 23 `/articles/{slug}.html`. `site:compare-content` Failures: 0.  
Result: PASS

---

Page: `/articles` listing chrome  
Issue: `PublicSiteTest` / `CmsOperationsTest` call `LegacySitePublisher::buildViews()`, which had been writing a fragment (filters + grid, no site header/footer, no `visual-upgrade.css`). Running tests regenerated that fragment and disconnected the listing from the portfolio.  
Original source: `index.html` header, nav, footer, and portfolio section  
Fix: `writeArticleIndex()` now copies skip-link, sidebar header, mobile menu toggle, and footer from `index.html`, points nav to `/#…` and `/articles`, and links `visual-upgrade.css?v=1107`. Regenerated with `site:publish-assets --views`.  
Validation: `/articles` HTML includes `#header`, 23 `.article-teaser` cards, and the overlay stylesheet. Browser snapshot shows primary nav + footer.  
Result: PASS

---

No other CONTENT_SOURCE_MISSING items were found for EN/FA homepage, six services, or 23 articles.
