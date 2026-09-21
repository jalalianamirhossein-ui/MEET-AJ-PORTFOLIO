#!/usr/bin/env bash

# Meet AJ — local/deployment refresh sequence
# Usage:
#   bash scripts/refresh-project.sh
#   ALLOW_DESTRUCTIVE_DB_RESET=1 bash scripts/refresh-project.sh
set -Eeuo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

if ! command -v php >/dev/null 2>&1; then
  echo "ERROR: php was not found in PATH." >&2
  exit 1
fi

if [[ ! -f artisan ]]; then
  echo "ERROR: artisan was not found. Run this script from the project checkout." >&2
  exit 1
fi

step() {
  printf '\n==> %s\n' "$1"
  shift
  "$@"
}

on_error() {
  echo "\nERROR: command failed at line ${BASH_LINENO[0]}. Refresh stopped." >&2
}
trap on_error ERR

echo "Project: $PROJECT_ROOT"

step "Clear existing Laravel caches" php artisan optimize:clear
step "Run pending migrations" php artisan migrate --force
step "Seed the database" php artisan db:seed --force
step "Create the storage symlink" php artisan storage:link
step "Publish Filament assets" php artisan filament:assets
step "Cache Laravel configuration, routes, and views" php artisan optimize

if [[ "${ALLOW_DESTRUCTIVE_DB_RESET:-0}" != "1" ]]; then
  cat >&2 <<'WARNING'

WARNING: migrate:fresh is destructive: it drops every application table.
To run the requested database reset, re-run with:

  ALLOW_DESTRUCTIVE_DB_RESET=1 bash scripts/refresh-project.sh

The remaining non-destructive steps were completed successfully.
WARNING
  exit 0
fi

step "Reset and seed the database (DESTRUCTIVE)" php artisan migrate:fresh --seed --force
step "Create or update the CMS user (interactive)" php artisan cms:create-user
step "Refresh article ordering" php scripts/update-article-order.php
step "Synchronize article tags" php artisan articles:sync-tags
step "Clear final Laravel caches" php artisan optimize:clear

echo
echo "Refresh completed successfully."
