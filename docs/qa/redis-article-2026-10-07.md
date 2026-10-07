# Redis production deployment article — QA, 7 October 2026

- Local URL: `http://127.0.0.1:8000/articles/redis-installation-configuration-replication`.
- Persian and English editorial sources: `resources/content/articles/redis-installation-configuration-replication/article.fa.md` and `article.en.md`.
- Reproducible builder: `scripts/build-redis-article.py`; CMS source: `resources/legacy/articles/redis-installation-configuration-replication.html`.
- All 23 requested topics, plus authored introduction/prerequisites/conclusion, eight FAQ items and reference/download section: 28 authored sections.
- Local CMS import is complete. Migration `2026_10_07_000047_add_redis_production_article.php` imports only this slug and preserves later CMS edits on reruns. No remote production deployment was performed.
- Source metadata includes Persian/English title, description, keywords and FAQ. Existing site machinery renders canonical, Article and FAQ structured data, social metadata, language direction and translated image alt text.
- Seven public templates are mirrored from editorial source: primary/replica baseline fragments, ACL bootstrap, TLS overlay, cache memory profile, Sentinel monitor template and Prometheus scrape fragment. Real secrets are not included. Secret substitution is mandatory; the Sentinel fragment is explicitly not a complete secured Sentinel deployment.

## Version and documentation verification

Redis Open Source **8.10.2**, published 17 September 2026, was verified as latest stable through the official [release page](https://github.com/redis/redis/releases/tag/8.10.2) and [official release archive](https://download.redis.io/releases/). Redis Open Source lifecycle was checked separately from Redis Software and Redis Cloud.

The [8.10.2 tagged redis.conf](https://raw.githubusercontent.com/redis/redis/8.10.2/redis.conf) and [Redis 8.10 command reference](https://redis.io/docs/latest/commands/redis-8-10-commands/) were consulted before writing command/configuration examples. Official Redis references checked include APT installation, configuration, ACL, ACL LIST/WHOAMI/DRYRUN, TLS, replication, REPLICAOF, INFO, persistence, BGSAVE, LASTSAVE, WAIT, WAITAOF, Sentinel and Sentinel clients, Cluster, eviction, CLI, Linux administration, latency, SLOWLOG, Pub/Sub and distributed locks. Links appear beside the relevant article sections.

Ubuntu **26.04 LTS** was verified as the newest LTS in [Canonical's release cycle](https://ubuntu.com/about/release-cycle). The worked deployment remains **24.04 LTS** as requested; the article instructs readers to recheck repository support, codename, candidate package, paths and actual service unit on newer LTS releases. It does not invent an exact APT build string for 8.10.2 or claim the ordinary Ubuntu repository always provides the newest upstream release.

Redis Exporter and Prometheus references use the upstream [Redis Exporter project](https://github.com/oliver006/redis_exporter) and [official Prometheus configuration documentation](https://prometheus.io/docs/prometheus/latest/configuration/configuration/). Exporter ACL/collector compatibility is a separate target-side acceptance check.

## Images

Generated with the built-in `image_gen` tool. Originals are retained at their generated locations. Export uses only delivery-size resampling; semantic corrections were performed through ImageGen, not by drawing over generated artwork.

| Asset | Delivered dimensions |
| --- | --- |
| `resources/assets/img/articles/banners/redis-production-banner.png` | 1000 × 1000 |
| `resources/assets/img/articles/content/redis-production-architecture.png` | 1920 × 1080 |
| `resources/assets/img/articles/content/redis-replication-architecture.png` | 1920 × 1080 |
| `resources/assets/img/articles/content/redis-sentinel-high-availability.png` | 1920 × 1080 |
| `resources/assets/img/articles/content/redis-monitoring-architecture.png` | 1920 × 1080 |

Public counterparts are in `public/assets/img/articles/banners/` and `public/assets/img/articles/content/`. All five images were inspected. Production artwork was corrected to state Sentinel adds failover, and the Sentinel artwork was corrected from chained replication to a primary branching directly to two replicas, all on port 6379. Images use English text only, navy/black datacenter artwork and Redis red accents. Monitoring dashboard numbers are explicitly illustrative.

Prompts: `resources/content/articles/redis-installation-configuration-replication/image-prompts.json`; final technical correction prompts: `image-revisions.json` in that directory. Native and delivered dimensions, selected originals and sizes are recorded in `image-manifest.json`. Landscape generation returned 1672 × 941 artwork and was resampled to the requested export dimensions; it is not claimed to have been natively rendered at 1920 × 1080.

## Validation

- **11 tests, 1130 assertions passed:** RedisProductionArticleTest, ArticleFaqTranslationTest and CategoryFilterColorsTest.
- Article tests cover FA/EN rendering, metadata/schema, unchanged code through localization, effective sections and TOC anchors, Redis tags/category, all image dimensions/alts, source/download parity, library/home/sitemap visibility, legacy redirect and migration reruns preserving CMS edits.
- **Bash syntax passed:** `bash -n` for `bootstrap-acl.sh`. Git Bash's MSYS object creation is blocked inside this Windows sandbox; the same read-only syntax check succeeded outside the sandbox after automatic approval. The script was not executed against the host.
- **PHP syntax passed:** article migration and ArticleTagAssigner.
- Standard PHPUnit invocation encounters the documented Windows sandbox issue where `is_readable(vendor/autoload.php)` is false although PHP can require it. The ignored `storage/app/run-redis-tests.php` directly requires autoload and creates an ignored configuration retaining all tracked PHPUnit isolation settings, including SQLite `:memory:` and a nonexistent config-cache path. No vendor or tracked PHPUnit configuration was modified.
- The local SQLite database was backed up before import with SQLite's backup API; integrity_check returned `ok`. Backup: `storage/app/private/redis-article/before-20261007T192617Z.sqlite`. Tests used an isolated in-memory database.
- Browser QA covered desktop FA/EN, translated headings, actual SEO metadata and narrow layouts (approximately 380 and 272 CSS pixels in the browser's current scaling). No page-level horizontal overflow was observed. Default viewport restored after testing; final article is left in Persian.
- Screenshots: `C:/Users/Victus/.codex/visualizations/2026/10/07/01a117ac-79be-7dd1-99a0-762deab8eae2/redis-desktop-fa.png`, `redis-desktop-en.png`, `redis-mobile-fa.png`, `redis-mobile-en.png`.

## Validation limits

No Linux Redis runtime was available for a real primary/replica integration test. WSL listing was denied in this environment; no target Linux installation was attempted. This report confirms source/documentation review, Bash parsing and website integration, not live Redis startup, TLS handshakes, Ubuntu APT package availability, replication, failover or recovery on the target machines. The article provides the target-side expected output and acceptance steps for those checks.

Concurrent workspace edits to MongoDB, MikroTik assets, homepage layout, image mappings and other unrelated files were preserved. Only the Redis additions to the shared tag catalog and slug mapping belong to this task.
