> **HISTORICAL phase log.** Authoritative procedure: [../current/DEPLOYMENT.md](../current/DEPLOYMENT.md) (moved from the repository root during the 2026-09-17 documentation reorganisation). Cutover is still not executed.

# Phase 06 — DirectAdmin deployment

**Phase:** 06
**Date:** 2026-09-16
**Status:** **PASS** for documentation · deployment itself **BLOCKED / NOT TESTED**

## Objective

Write a deployment procedure that a DirectAdmin shared-hosting account can actually follow, without assuming SSH, Composer, Node, Redis or Supervisor.

## Changes

- Deployment guide covering PHP 8.4 selection, MySQL/MariaDB creation, document root, permissions, migration, caches, SSL, post-deploy checks and rollback.
- `.env.production.example` with `APP_ENV=production` and `APP_DEBUG=false`.
- File cache, file sessions and `QUEUE_CONNECTION=sync` so no extra services are required.

## Files changed

- `DEPLOYMENT.md` (now `docs/current/DEPLOYMENT.md`)
- `.env.production.example`
- `docs/historical/implementation-report.md`
- `docs/historical/final-site-qa-report.md`

## Commands executed

```text
php artisan optimize
php artisan route:list
```

No DirectAdmin UI action, SSH session, or live `mysql` client command was executed against the hosting account.

## Tests

None on the production host.

## Results

The application is deployable as a Composer project **if** the host provides PHP 8.4, the required extensions and MySQL/MariaDB. That match has never been proven on DirectAdmin.

## Blockers

- Production `.env`, `APP_KEY` and database password must be created on the host and never committed.
- An admin user must be created on the host with `php artisan cms:create-user`.
- `storage:link`, writability of `storage/` and `bootstrap/cache`, and the Apache document root must be set by the operator.
- Rollback depends on the original static files staying outside `public/` — they still are.
