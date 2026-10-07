# UniFi wireless mesh article

Route: `/articles/ubiquiti-unifi-wireless-mesh-network`.

The established bilingual importer, Blade reading layout, TOC, FAQ, SEO and
language preference are reused. The source has 23 sections: the requested 17
steps, enterprise recommendations, two checklists, eight FAQs, conclusion and
official references. The article is tagged Ubiquiti.

All five user-supplied PNGs are preserved at their specified resources paths and
copied byte-for-byte to public assets. Topology appears in architecture, roles
in parent setup, RF in backhaul design, troubleshooting in diagnostics and the
banner in the hero. ALT, title and captions switch with the locale, including
the server-rendered response. The hero caption receives a scoped column layout.

Ubiquiti Help Center sources were verified on 7 October 2026. The general
Adoption page conflicts with the dedicated mesh page about Mesh Connect on the
wired uplink AP; the article follows the dedicated role guide and explains the
conflict. User illustrations contain conceptual UI fields and RF examples;
nearby prose identifies these as illustrations rather than current GUI evidence.
No live UniFi hardware tests or throughput guarantees are claimed. The iperf3
test syntax was verified against ESnet documentation.

Validation:

- Focused PHPUnit run: 8 tests, 267 assertions passed, using SQLite in memory.
- Both locales verify all sections, ten troubleshooting cases, eight FAQs,
  source links, five images, localized attributes, TOC targets and discovery.
- Import rerun preserves subsequent CMS content edits.
- Existing image and Nginx article regression checks passed.
- JavaScript syntax and Git whitespace checks passed.
- Browser checks verified Persian hero, English/Persian switching for image
  attributes and prose, mobile topology loading and absence of horizontal
  overflow at the tested narrow viewport. Temporary viewport override reset.
- A broader bilingual run surfaced an existing MikroTik FAQ failure in
  BilingualEnterpriseArticleTest at line 91. That article was not changed.

Local DB backup before import: `.runtime/cms-before-unifi-mesh-20261007.sqlite`.
Only the scoped new migration/import was applied. Existing unrelated GPO and
brand-color work was preserved. No remote deployment was performed.
