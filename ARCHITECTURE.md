# Application architecture

Laravel 13.31.0, PHP 8.4+, Filament 5.8.3 and Livewire 4.4.5 render Blade pages and a session-authenticated CMS. Local development uses SQLite; deployment targets MySQL/MariaDB. File sessions/cache and synchronous optional mail are configured; no worker, Redis, scheduler, SPA or npm build is required.

Browser → `public/index.php` → global proxy/HTTPS/header middleware → web CSRF/session middleware → controllers and validated requests → Eloquent/scopes/policies → Blade. Filament supplies its own session/CSRF/auth middleware; policies protect CMS resources and private Livewire widgets.

Article markup is normalized once before sanitization, then laid out without further entity decoding. Structured homepage links pass a scheme allowlist. Image uploads use MIME-derived random filenames and reject unvalidated existing paths. The service worker excludes private/signed traffic and respects no-store.

Editable frontend assets and worker/offline sources live in `resources/`; the publisher copies them into `public/`. Import sources and article tooling stay outside the document root. Tracked public download files deliberately retain their visitor URLs. Migration filenames and article slugs were preserved.

See [the detailed directory/request map](docs/current/ARCHITECTURE.md), [refactoring report](docs/PROJECT-REFACTORING-REPORT.md), and [vendor inventory](resources/assets/vendor/DEPENDENCIES.md). Root documents are entry points; `docs/current/` retains detailed operational guides, and dated QA/archive documents retain historical evidence.
