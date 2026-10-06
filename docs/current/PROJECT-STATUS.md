# Project status — Meet AJ

**Current local verification: 2026-10-06.** Evidence: [structure and documentation audit](../qa/STRUCTURE-DOCUMENTATION-AUDIT-2026-10-06.md). Historical reports describe their own dates.

## Application

Laravel 13.31.0, Filament 5.8.2, Livewire 4.4.5 and PHPUnit 11.5.56 are locked dependencies. PHP 8.4.25 runs locally. Blade and CSS/JavaScript serve the public site; Filament serves `/admin`. No frontend build, Redis, worker or scheduler is required. Node is optional for frontend and documentation checks. SQLite is local; deployment targets MySQL/MariaDB. Production was not inspected.

## Local snapshot

| Item | Count |
|---|---|
| Maintained article HTML sources / local article rows | 28 / 28 |
| Rows with paired FA/EN localization metadata | 27 |
| Article redirects | 28 |
| Categories | 19 |
| Tags / article-tag links | 24 / 50 |
| Services / visible catalog entries | 13 / 12 |
| Testimonials / homepage content sections | 9 / 7 |
| Users / requests | 0 / 0 |
| Migration files / local ledger records | 24 / 25 |

All 24 current migration files report Ran. The ledger contains a historical record in addition to current files. Counts describe this environment, not every installation. Localization metadata presence does not prove translation completeness.

## Current behavior and maintenance

Homepage copy, service catalog, testimonials and articles are CMS-backed. Search/tag filtering, related articles, shared FA/EN preference, clean canonicals, contact CSRF/honeypot/throttling and admin policies remain implemented. German is draft-only; retired service detail routes return 404. Editorial ordering comes from `config/article-order.php`: MikroTik PBR Client, ping-triggered PBR, then Linux Auditor lead the Enterprise list.

Recent content migrations update testimonial attribution, contact headings and intro, add the MikroTik PBR Client article and move its download to the article end. Article packages contain builders and runbooks outside the document root; selected Bash/MSI downloads are published explicitly. The NetBox PDF source is absent from this checkout.

## Verification and limitations

`site:compare-content` reported **27 failures** on 2026-10-06, primarily source/database drift; the earlier zero-failure result is historical. Updating stored article bodies is a separate editorial operation: preview `articles:import-legacy --update-existing --dry-run`, back up and review CMS edits before applying. This documentation task did not replace database content.

Frontend scroll-reveal tests passed (4/4). Current PHPUnit evidence and local filesystem bootstrap limitations are recorded in the dated audit. Remote deployment, SMTP, real MySQL, infrastructure examples, Lighthouse/load tests and browser PWA installation/offline behavior were not checked in this review. No local CMS user exists for interactive login.

Start with [the documentation index](../README.md), [directory map](PROJECT-STRUCTURE.md), [articles](ARTICLES.md), [testing](TESTING.md) and [deployment](DEPLOYMENT.md).

## Organized images

The 2026-10-06 image follow-up organizes 75 maintained website pictures into separate banner, article-body, avatar, brand, icon and screenshot folders. The path migration preserves CMS edits and publication timestamps. Legacy URLs redirect to the new assets; upload directories are separated by purpose. [Folder guide](IMAGES.md) and [image verification](../qa/IMAGE-ORGANIZATION-2026-10-06.md). Twelve focused tests pass (439 assertions); 30 public pages render with valid image paths. Earlier broad-suite/content drift remains as documented above.
