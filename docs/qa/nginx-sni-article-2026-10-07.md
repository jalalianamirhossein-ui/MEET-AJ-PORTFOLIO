# Nginx SNI reverse-proxy article

The new article is imported through the existing `LegacyArticleImporter` at
`/articles/nginx-reverse-proxy-multiple-domains-single-ip-443`.
English and Persian share this canonical route and the established language
cookie and browser switch. There are 26 requested sections, plus eight FAQs
and official references. Existing Blade layout, breadcrumbs, TOC, code-copy
controls, table styles, article cards and related-article selection are reused.

The five supplied PNGs are used as the banner and illustrations for architecture,
SNI, root-cause analysis and NAT. The generator copies them to public assets.
Complete final and HTTP bootstrap configurations are downloadable under
`public/downloads/nginx-reverse-proxy-multiple-domains-single-ip-443/`.

## Verification

- Article-specific feature tests verify both locales, SEO/schema, all sections,
  TOC targets, matching code, images, downloads, discovery, redirects and
  preservation of subsequent CMS edits on migration rerun.
- Persian header/banner and architecture image verified in the in-app browser.
  English switching and mobile overflow checked. Preview screenshots are in
  `storage/app/nginx-sni-desktop-fa.png` and `storage/app/nginx-sni-mobile-en.png`.
- Official Nginx, Certbot, Let's Encrypt, curl, OpenSSL, RFC, Grafana and GitLab
  documentation checked. The Atlassian reference could not be fetched by the
  web tool; application-specific Jira settings should be checked for the target
  Jira release.
- A Linux/Nginx runtime is not installed in this workspace. No live backend,
  certificate issuance or target Linux `nginx -t` execution is claimed. The
  article includes required target-side syntax, connectivity and TLS tests.
- Broader layout regression run found two failures outside this article:
  `ArticlePresentationTest` for the existing MikroTik firewall source and
  `GroupPolicyMsiArticleTest` for its expected image path. No changes were made
  to those articles to suppress the failures.

## Local database recovery

The initial feature-test execution used cached application configuration and
rebuilt `.runtime/cms.sqlite` instead of using the intended in-memory database.
This was an agent execution error. `phpunit.xml` now forces a separate nonexistent
configuration-cache path so tests cannot load the local site's cached database
connection.

The affected database was preserved at
`storage/app/private/nginx-sni-recovery/after-initial-test.sqlite`. The latest
available full backup was restored using SQLite's backup API:
`storage/app/private/image-reorganization/before-20261006-131724-0901f2.sqlite`.
Pending migrations were then applied and the new article imported.

Recovery verified 31 articles, 7 homepage content rows, 13 services and 9
testimonials, with SQLite integrity_check returning `ok`. This verifies structural
recovery, not preservation of any CMS edits made after the 6 October backup.
Those later edits cannot be guaranteed. No remote production database was used.
