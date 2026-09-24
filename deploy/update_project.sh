#!/usr/bin/env bash
set -Eeuo pipefail

read -r -p "Project path [/var/www/meetaj]: " PROJECT_DIR
PROJECT_DIR="${PROJECT_DIR:-/var/www/meetaj}"
read -r -p "Branch name [main]: " BRANCH_NAME
BRANCH_NAME="${BRANCH_NAME:-main}"

[[ -d "${PROJECT_DIR}/.git" ]] || {
  echo "This path is not a valid Git repository: ${PROJECT_DIR}"
  exit 1
}

cd "${PROJECT_DIR}"

if [[ -n "$(git status --porcelain)" ]]; then
  echo "Local changes detected. Operation stopped to prevent overwriting them."
  git status --short
  exit 1
fi

git checkout "${BRANCH_NAME}"
git pull --ff-only origin "${BRANCH_NAME}"

composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan site:publish-assets --views
php artisan filament:assets
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

systemctl reload php8.4-fpm
systemctl reload nginx

echo
echo "Update completed successfully. Current revision:"
git log -1 --oneline
