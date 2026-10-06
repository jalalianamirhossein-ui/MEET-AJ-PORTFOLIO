# MikroTik PBR Client package

Reviewed 2026-10-06. Article source: `resources/legacy/articles/mikrotik-pbr-client.html`; clean route: `/articles/mikrotik-pbr-client`.

- `MikroTikPBRClient-Setup-1.0.2-x64.msi` is the source for the public installer at `/downloads/mikrotik-pbr-client/MikroTikPBRClient-Setup-1.0.2-x64.msi`.
- `MikroTikPBRClient-Source.zip` is a private repository reference. The publisher does not expose it; keep it outside `public/`.
- Migration `2026_10_06_000021_add_mikrotik_pbr_client_article.php` imports this article. `config/article-order.php` places it first, followed by ping-triggered PBR and Linux Auditor.

Publish downloads with `php artisan site:publish-assets`. Review existing article changes with `php artisan articles:import-legacy --update-existing --slug=mikrotik-pbr-client --dry-run` before importing replacements.

The article test checks the published MSI against this source by SHA-256 and rejects a public source ZIP. Website checks do not establish Windows installer behavior or RouterOS compatibility.

See [articles](../../../../docs/current/ARTICLES.md) and [current status](../../../../docs/current/PROJECT-STATUS.md).
