> **HISTORICAL / SUPERSEDED phase log.** Authoritative deploy procedure: [DEPLOYMENT.md](../current/DEPLOYMENT.md). Cutover is still not executed.

# Phase 6 — DirectAdmin deployment

Status: **documentation complete**. No production deploy was executed from this session.

## Implemented work

- `DEPLOYMENT.md` — host steps for PHP 8.4, MySQL/MariaDB, document root, permissions, migrate, caches, SSL, rollback.
- `.env.production.example` — `APP_ENV=production`, `APP_DEBUG=false`.
- File cache/session/queue so Supervisor/Redis/Node are not required.

## Files changed

- `DEPLOYMENT.md`
- `.env.production.example`
- `docs/historical/implementation-report.md`
- `docs/historical/final-site-qa-report.md`

## Commands executed (local only)

```text
php artisan optimize
php artisan route:list
```

No DirectAdmin UI, SSH-to-production, or live `mysql` client command succeeded against the hosting account in this session.

## Tests executed

None on the production host.

## Real results

Local application is deployable as a Composer project if the host matches PHP 8.4 + required extensions + MySQL/MariaDB. That match was not proven on DirectAdmin in this session.

## Blockers

- Production `.env` / `APP_KEY` / database password must be created on the host and never committed.
- Admin user must be created with `php artisan cms:create-user` on the host.
- `storage:link`, `storage/` and `bootstrap/cache` writability, and Apache document root must be set by the operator.
- Rollback plan depends on keeping original static files outside `public/` (they are still in the project root).
