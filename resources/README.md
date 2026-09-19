# Frontend and content sources

| Directory | Purpose |
|-----------|---------|
| `assets/` | Editable site assets, copied to `public/assets/` |
| `css/` | Filament admin stylesheet, published with `filament:assets` |
| `downloads/` | Visitor downloads; the NetBox guide retains its `/docs/` URL |
| `static/` | Manifest, preloaders, and static language-toggle fragment |
| `legacy/` | Original HTML used for content import, comparison, and view generation; former endpoints and templates are retained as references |
| `views/` | Active Blade views and partials |

Run `php artisan site:publish-assets` after editing public asset sources and `php artisan filament:assets` after editing the admin CSS. The optional `--views` flag rebuilds `views/home.blade.php` and `views/articles/index.blade.php` from `legacy/index.html` through `App\Services\LegacySitePublisher`; edit the generator or its source for persistent changes to generated markup. Other Blade views are maintained directly.

`legacy/views/` is outside Laravel's configured view path. The old `legacy/forms/` PHP files are not live endpoints. `/forms/*` routes use Laravel controllers. The old `legacy/sw.js`, `legacy/robots.txt`, and `legacy/sitemap.xml` are reference files; the application generates their current public equivalents.

See [the project directory map](../docs/current/PROJECT-STRUCTURE.md) for placement and deployment rules.
