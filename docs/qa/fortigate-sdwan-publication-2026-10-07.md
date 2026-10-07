# FortiGate article publication

The maintained source existed at `resources/legacy/articles/fortigate-sd-wan-load-balancing-failover.html`, but there was no deployment migration and no row in the local SQLite database. Adding an HTML source alone does not make an article visible in the Laravel library.

Migration `2026_10_07_000037_add_fortigate_sdwan_article` imports only this slug, gives a newly imported article its Fortinet category, and preserves existing CMS content and publication state on rerun. The tag assigner explicitly maps the slug to Fortinet.

Verification: `FortiGateArticlePublicationTest` passed with 2 tests and 26 assertions against isolated in-memory SQLite. It covers library discovery, article rendering, all ten image files, legacy redirect, category/tag and preservation of edited draft content on rerun. PHP lint passed. Windows sandbox PHP readability checks prevented the initial PHPUnit invocation; the same focused tests passed outside that sandbox.

The local SQLite database was backed up with SQLite's backup API to `storage/app/private/fortigate-publication/before-import.sqlite`, then only this migration was applied locally. The article is published locally and database integrity_check returned `ok`.

No remote server was accessed or deployed. After deploying the source, migration and assets to the server, back up the production database and run from the project root:

```bash
php artisan migrate --force
php artisan site:publish-assets
php artisan optimize:clear
```

The deployment updater already runs migrations and publishes assets. The new migration makes this article participate in that workflow. If the source was manually imported previously, the migration preserves that article rather than replacing CMS edits.
