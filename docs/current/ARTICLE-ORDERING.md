# Article ordering

> Maintenance review: 2026-10-10. Public lists and the default admin list show the newest publication first.

The homepage, article library, matching search/tag results and default admin
article table use `Article::inDisplayOrder()` through `ArticleOrdering`.

1. Publication time (`published_at`) descending determines placement.
2. If publication timestamps are identical, the article inserted later (higher ID) appears first.

Historical classifications in `config/article-order.php` do not affect placement.
New articles need no priority-list change. Editing content or changing `updated_at`
does not promote an older article. Numeric `sort_order` remains a synchronized
legacy index and cannot override publication time. Drafts and future publications
remain hidden on public pages; publication scheduling still applies.

Publication dates are editorial facts, not spacing controls. Multiple articles
can legitimately be published on the same day. Oracle, Tomcat and Zabbix were
added to the repository on 2026-10-09; Grafana was added on 2026-10-10. Grafana's
original source incorrectly copied the 2026-10-09 technical review date into its
publication date. The source now publishes on 2026-10-10 and metadata records
review and publication separately. The historical version-review date stays unchanged.

Migration `2026_10_10_000054_use_chronological_article_order.php` repairs only the
known original Grafana import date, with source-file, date-provenance and schema
guards. It preserves manual publication dates, article content, status and
`updated_at`. It also synchronizes the legacy numeric index. Repeating it is safe.
It does not fabricate publication dates for other articles. Older imports without
schema dates use source-file modification time, recorded as `source_file_mtime`
in `seo_data.date_provenance`; those dates require an editorial record or backup
before correction. Dates previously changed by the retired random-date script
also require a known record or backup.

After deploying these files to an existing server:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

No bulk overwrite import is needed. To synchronize the legacy numeric index
after CMS additions, optionally run:

```bash
php scripts/update-article-order.php
```

That script changes only `sort_order`, checks all other fields inside a
transaction, and is safe to repeat. Public lists already read publication time
directly and do not depend on running it. Languages remain independently indexed.
