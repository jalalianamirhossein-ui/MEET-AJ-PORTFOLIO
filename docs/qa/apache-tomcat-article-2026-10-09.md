# Apache Tomcat Linux installation and security article — delivery and QA

Reviewed on 9 October 2026. The new article uses the existing MEET AJ article architecture and shared visual components. No production configuration, credentials or operational database was changed.

## Architecture review

Reviewed the recently published Redis, MongoDB and Nginx SNI articles, their builders, metadata, migration registration and feature tests before creating this package. Existing conventions used here:

- Editorial `article.en.md`, `article.fa.md` and `metadata.json` under a slug directory.
- Importable HTML under `resources/legacy/articles`, with bilingual `data-en`/`data-fa`, `article-localizations`, existing hero/body classes, navigation and JSON-LD.
- One English database row with both translations. The shared clean route uses the existing language cookie/control. English and Persian do not have separate paths; `?lang=` redirects to the canonical path.
- Existing Laravel article cards, Linux theme, related articles, code presentation, FAQ accordions, mobile TOC, SEO/schema and asset publication conventions.
- Versioned content migration through `LegacyArticleImporter`, preserving later CMS edits on repeated publication.
- No new package dependency, page component, stylesheet, route or unrelated article change.

## Version and technical review

The official [Tomcat 11 download page](https://tomcat.apache.org/download-11.cgi) and [version matrix](https://tomcat.apache.org/whichversion.html) listed **11.0.26** as latest stable in the supported 11 branch on the review date. Tomcat 11 requires Java 17 or later; the example uses a maintained distribution OpenJDK **21** build on Ubuntu **24.04 LTS**, with RHEL-family alternatives.

Official references consulted include Tomcat RUNNING, migration/upgrade guidance, security considerations/advisories, connector/Host/Server/Valve configuration, deployment and JULI logging; Apache release verification; the Ubuntu 24.04 systemd manuals; Nginx proxy, SSL, response-header and real-IP documentation; Let's Encrypt/Certbot; Red Hat OpenJDK and firewall guidance; Java 21 launcher/jcmd manuals. The OpenJDK project URL returned HTTP 403 to the research tool; it remains a valid reference link, while Java commands were checked against the available Java 21 and distribution references.

The article covers all 13 requested chapters, an authored prerequisites section, operational acceptance, eight bilingual FAQ entries and references/downloads: **17 authored sections**. It contains **34 Bash blocks** and five downloadable configurations. English and Persian executable blocks are identical.

Technical choices include root-owned binaries/configuration/WARs; separate CATALINA_HOME and CATALINA_BASE; narrowly writable state/log directories; foreground JVM tracking by systemd; no Java SecurityManager; disabled shutdown listener; no AJP; no deployed default/admin apps; loopback backend; overwritten forwarded headers; exact loopback RemoteIpValve trust; access logging; certificate bootstrap and renewal hooks; controlled upgrades and rollback. JVM and application-specific limits/exceptions are explained.

## Created and modified files

All paths below are relative to `D:\MEET AJ PORTFOLIO`.

| File | Purpose |
| --- | --- |
| [article.en.md](../../resources/content/articles/apache-tomcat-linux-installation-security-hardening/article.en.md) | Complete English editorial source |
| [article.fa.md](../../resources/content/articles/apache-tomcat-linux-installation-security-hardening/article.fa.md) | Complete Persian editorial source |
| [metadata.json](../../resources/content/articles/apache-tomcat-linux-installation-security-hardening/metadata.json) | Version, review date, bilingual SEO/title/description/keywords/FAQ |
| [image-generation.json](../../resources/content/articles/apache-tomcat-linux-installation-security-hardening/image-generation.json) | Built-in generator provenance and all four exact prompts |
| [CMS HTML](../../resources/legacy/articles/apache-tomcat-linux-installation-security-hardening.html) | Existing importer source |
| [build-tomcat-article.py](../../scripts/build-tomcat-article.py) | Reproducible bilingual builder and public-asset mirroring |
| [publication migration](../../database/migrations/2026_10_09_000048_add_apache_tomcat_article.php) | Imports this article only; rollback preserves editorial content |
| [ApacheTomcatArticleTest.php](../../tests/Feature/ApacheTomcatArticleTest.php) | Bilingual rendering/publication/asset/link/preservation checks |
| [ArticleTagAssigner.php](../../app/Services/ArticleTagAssigner.php) | Modified: adds the Tomcat tag and this slug's Linux/Ubuntu/Nginx/Tomcat assignment |
| [this report](apache-tomcat-article-2026-10-09.md) | Delivery manifest, validation and limitations |

Five configuration files exist in both `resources/content/articles/apache-tomcat-linux-installation-security-hardening/` and `public/downloads/apache-tomcat-linux-installation-security-hardening/`:

- `tomcat.service`
- `server.xml`
- `logging.properties`
- `nginx-bootstrap.conf`
- `nginx-tomcat.conf`

## Generated images

Generated with the built-in image_gen tool, inspected for technical connections and English labels, then resized to the requested exact dimensions and losslessly compressed as PNG. All prompts are preserved in `image-generation.json`; no unrelated image or placeholder was reused.

| Filename | Source directory | Dimensions | Bytes |
| --- | --- | --- | --- |
| apache-tomcat-linux-security-banner.png | resources/assets/img/articles/banners | 1000 × 1000 | 424812 |
| apache-tomcat-production-architecture.png | resources/assets/img/articles/content | 1920 × 1080 | 682529 |
| apache-tomcat-systemd-service.png | resources/assets/img/articles/content | 1920 × 1080 | 775475 |
| apache-tomcat-security-hardening.png | resources/assets/img/articles/content | 1920 × 1080 | 714643 |

Identical public copies exist under `public/assets/img/articles/banners/` and `public/assets/img/articles/content/`. Public image mirrors are ignored by Git as in the existing project; the maintained resource images and builder/publication process supply them. Each content figure has English and Persian alt text; the existing hero component supplies localized article-title alt text.

## URLs and publication

English: [article](https://meetaj.ir/articles/apache-tomcat-linux-installation-security-hardening).

Persian: [مقاله](https://meetaj.ir/articles/apache-tomcat-linux-installation-security-hardening), selected through the site's existing language preference/control.

The migration registers the article for the normal deployment process. An isolated SQLite `:memory:` import confirmed publication, homepage/library/search/tag inclusion, related articles, sitemap, internal routes and legacy redirects. No migration or import was executed against the existing CMS or production database. These URLs describe the final route; live remote publication was not asserted.

## Validation results

- Initial Tomcat feature tests: **3 tests / 189 assertions passed**.
- Final selected tests (`ApacheTomcatArticleTest`, `ArticleContentReviewTest`, `ArticlePresentationTest`): **9 tests / 6811 assertions passed**, including all 17 authored sections and no automatic filler.
- Full PHP suite: **163 tests / 23552 assertions; 1 failure, 1 skip**. Failure: `LinuxSecurityAuditorArticleTest::test_auditor_stays_first_in_linux_and_follows_editorial_priority_in_the_full_library` expects the auditor first in Linux filtering, but the concurrently edited `config/article-order.php` promotes the Oracle article first. Tomcat does not modify this ordering file and is excluded from that test's curated scenario. The MySQL-only schema test was skipped under the default isolated SQLite suite. Concurrent Oracle files/changes were preserved.
- Existing frontend suite: **6 tests passed**.
- Image audit: **111 maintained images / 331 references / 0 errors** in the shared checkout at verification.
- Final documentation-link audit: **148 Markdown documents / 870 local links / 0 broken links** in the shared checkout at verification.
- Repository security/history audit: **840 inventoried files / 703 text files / 0 errors** at verification; this is the existing limited-signature checker.
- Composer strict validation passed; existing article routes confirmed; changed PHP files and Python builder syntax checked.
- All **34 Bash blocks passed `bash -n`**; `server.xml` parsed successfully. Parsing does not execute Linux deployment commands.
- `git diff --check` passed; only Git's Windows line-ending notices appeared.
- Headless Chrome QA at **1440 × 1000** and **390 × 844**, in both languages: no horizontal overflow, missing assets or JS errors; image loading, listing, title/meta language switching, intact code and eight FAQs passed. Screenshots were visually inspected after waiting for the existing preloader to dismiss.

Ignored local evidence exists under `storage/app/tomcat-qa/`: bilingual rendered article/listing HTML, `results.json`, hero/systemd/FAQ/listing screenshots. JUnit files: `storage/app/tomcat-focused.xml`, `storage/app/tomcat-final-focused.xml`, `storage/app/tomcat-full.xml`. Preview rendering explicitly rejects any database other than SQLite `:memory:`.

## Limits and remaining deployment work

The article integration and images are complete. The one shared-suite Oracle ordering failure remains outside this task. Live publication requires the normal reviewed deployment and migration/asset publication. Linux installation, systemd sandboxing, Nginx configuration, ACME issuance, SELinux/AppArmor and the real WAR must be exercised in staging on the actual target environment before enterprise production acceptance. This Windows workspace does not claim a live Linux runtime test or a production rollout.
