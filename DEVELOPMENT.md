# Development and verification

Use PHP 8.4+, Composer 2 and PDO SQLite locally. See [README](README.md) for PHP extensions. Node 22+ is optional for verification; the website has no npm build.

For a new checkout only:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan site:publish-assets --views
php artisan filament:assets
php artisan storage:link
php artisan serve
```

Preserve existing `.env`, key, database and editorial changes. Seeding/importing can synchronize content; review the [import guidance](docs/current/ARTICLES.md) before running against an existing CMS. Do not run `migrate:fresh` on an operational database.

```bash
composer validate --strict
composer audit --locked
php artisan test
php artisan test -- --filter=EnterpriseHardeningTest
php artisan route:list
php artisan view:cache
php artisan view:clear
node --test tests/Frontend/*.test.cjs
node scripts/check-images.cjs
node scripts/check-documentation.cjs
node scripts/check-repository-security.cjs --history
git diff --check
```

The custom Artisan test command forwards PHPUnit options after `--`. The default suite forces SQLite `:memory:` and array mail/session/cache, so it does not use production records or send notifications. `phpunit.mysql.xml` is opt-in and destructive: use a disposable dedicated test database only. Windows can use `.runtime/php84/php.exe` and `.runtime/composer.phar` if not on PATH; sandbox ACLs may require the approved host runtime for PHPUnit and permission checks.

CI runs on pushes/PRs with read-only repository permissions and pinned action commits. The workflow has been reviewed locally; its first remote execution remains pending. It does not deploy. npm audit/build are inapplicable without a package manifest. Static frontend versions/advisories need separate review.

Keep secrets out of source/logs. The repository checker uses a few high-confidence signatures, skips binary/large current files, and is not a complete secret detector. If it flags a file, inspect it privately; never paste the matched value into an issue or report. For missing assets publish sources again; for stale config/views clear the respective cache; for mail failures inspect the safe exception class and protected SMTP configuration. Detailed coverage and limits: [testing guide](docs/current/TESTING.md) and [audit report](docs/SECURITY-AUDIT-REPORT.md).
