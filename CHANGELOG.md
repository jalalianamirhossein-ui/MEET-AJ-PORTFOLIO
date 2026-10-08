# Changelog

## 2026-10-08 — Enterprise audit and hardening

- Fixed entity-encoded stored XSS caused by decoding sanitized article text during layout.
- Added production HTTPS enforcement using the configured origin; narrowed HSTS to the current host.
- Added compatible CSP framing/base/object restrictions, browser permissions policy, and no-store coverage for Filament/signed responses.
- Applied safe service-image uploads and removed sensitive transport messages from notification logs.
- Updated vendored Swiper 11.1.9 to patched 12.1.2; retained custom navigation icons and tested EN/FA sliders.
- Fixed SQLite literal-wildcard search and related-article limits; stopped loading full peer bodies for recommendations.
- Extracted worker/offline assets to `resources/static/`, published worker `cms-6`, and removed four unused homepage-generation methods.
- Standardized the service-worker test filename, replaced migration `eval` with JSON metadata parsing, and added regression tests, pinned CI checks and a redacted repository/history checker.
- Added audit/refactoring reports and operational entry-point documentation. No migration, public URL, production credential or production deployment was changed.
