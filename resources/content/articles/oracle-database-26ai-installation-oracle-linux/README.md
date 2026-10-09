# Oracle Database 26ai enterprise article

Slug: `oracle-database-26ai-installation-oracle-linux`.

[English](article.en.md) and [Persian](article.fa.md) use identical executable blocks. The existing CMS imports the bilingual [HTML source](../../../legacy/articles/oracle-database-26ai-installation-oracle-linux.html); it does not render these Markdown files directly. [Metadata](metadata.json) records bilingual SEO, edition/platform, review date and official references.

The article uses the existing Linux theme, language control, code blocks, responsive tables, table of contents, related articles, FAQ and JSON-LD. Publication is registered by the slug-specific migration, tag mapping and curated ordering. Repeated migration execution preserves CMS editorial changes.

Edit paired prose/shared commands in [the article builder](../../../../scripts/build-oracle-article.py), then run from the repository root:

```powershell
python scripts/build-oracle-article.py
php artisan articles:import-legacy --update-existing --slug=oracle-database-26ai-installation-oracle-linux
php vendor/phpunit/phpunit/phpunit --filter OracleEnterpriseArticleTest
node scripts/check-images.cjs
node scripts/check-documentation.cjs
```

The builder regenerates Markdown, HTML, metadata and ten Linux configuration templates; it mirrors templates to `public/downloads/` and existing images to `public/assets/`. Templates must retain LF line endings. The import command updates only this article and should be used intentionally after an editorial review.

[Image manifest](image-manifest.json) records exact dimensions, SHA-256, sizes and generation methods. [Image prompts](image-prompts.json) record banner provenance and deterministic diagram specifications. [The image builder](../../../../scripts/build-oracle-images.py) reproduces the five diagrams and exports a supplied native generated banner at 1000 × 1000; use `--banner-source` with that original PNG.

The [delivery and QA report](../../../../docs/qa/oracle-database-26ai-article-2026-10-09.md) records validation and limitations. Oracle installation, SQL/RMAN operations, service execution, TLS and recovery have not been run against a real Oracle Linux database host. Confirm entitlement, current MOS certification/RU, storage, wallets and every environment-specific value before operational deployment.
