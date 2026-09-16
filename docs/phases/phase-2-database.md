# Phase 2 — schema, models and policies

Status: code written; executable database/policy tests pending completion of Composer installation. No migration success is claimed yet.

## Files changed

- Six `database/migrations/2026_09_15_*` migrations create users/password_reset_tokens, categories, articles, article_redirects, requests, sessions in dependency order.
- `app/Models/{User,Category,Article,ArticleRedirect,Request}.php` with relationships, localized uniqueness, publication constraints and transactional slug history.
- `app/Policies/{Article,Category,Request}Policy.php` restrict content to admin/editor and contacts to admin.
- `tests/TestCase.php`, `tests/Feature/ContentRulesTest.php`, `phpunit.xml` add disposable SQLite feature tests; MySQL/MariaDB is a separate required host check.

## Commands executed

- PHP `-l` over app, database, config, bootstrap, routes, scripts and tests: passed for all 35 current PHP files.
- PHP environment/original-source checks: all required extension/directory checks passed; 451 original hashes unchanged.
- Inspected official MariaDB release metadata for possible local MySQL-compatible validation. No server installed or started yet.

## Validation and blockers

- Syntax validation passed.
- Composite `(language, slug)` uniqueness and translation-group uniqueness defined; category and redirect foreign keys plus status/date indexes defined.
- DE publishing is rejected at model level; public scope is explicitly EN/FA, published and due. Editorial UI validation alone is not relied on.
- Model saves serialize route claims through Laravel's file cache lock and a database transaction; renamed slugs retain direct redirect history.
- Tests are written for publication/drafts, historical slug reservation, category FK/language behavior, admin/editor policies and redirect cascade. Execution pending dependencies; tests are not marked passed.
- MySQL/MariaDB engine-specific migration validation remains pending. SQLite tests cannot prove host collation/permissions/rewrite configuration.
