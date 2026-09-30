#!/usr/bin/env bash
set -Eeuo pipefail

read -r -p "Project path [/var/www/meetaj]: " PROJECT_DIR
PROJECT_DIR="${PROJECT_DIR:-/var/www/meetaj}"

[[ -f "${PROJECT_DIR}/.env.production.example" ]] || {
  echo ".env.production.example was not found in the project path."
  exit 1
}

PROJECT_DIR="$(cd -- "${PROJECT_DIR}" && pwd -P)"
if [[ -e "${PROJECT_DIR}/.env" || -L "${PROJECT_DIR}/.env" ]]; then
  echo "An existing .env must be preserved, including its APP_KEY. Use the update procedure instead."
  exit 1
fi

read -r -p "Site domain (for example, example.com): " APP_DOMAIN
read -r -p "Database name: " DB_DATABASE
read -r -p "Database username: " DB_USERNAME
read -r -s -p "Database password: " DB_PASSWORD
echo
read -r -p "Notification email (optional): " CONTACT_EMAIL

[[ -n "${APP_DOMAIN}" && -n "${DB_DATABASE}" && -n "${DB_USERNAME}" ]] || {
  echo "Domain, database name, and database username are required."
  exit 1
}

cd "${PROJECT_DIR}"
MEETAJ_SETUP_APP_URL="https://${APP_DOMAIN}" \
MEETAJ_SETUP_DB_DATABASE="${DB_DATABASE}" \
MEETAJ_SETUP_DB_USERNAME="${DB_USERNAME}" \
MEETAJ_SETUP_DB_PASSWORD="${DB_PASSWORD}" \
MEETAJ_SETUP_CONTACT_NOTIFICATION_EMAIL="${CONTACT_EMAIL}" \
  php scripts/write-production-env.php "${PROJECT_DIR}"
unset DB_PASSWORD
php artisan config:clear
php artisan key:generate --force

chmod 640 .env
chown www-data:www-data .env
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

php artisan storage:link || true
php artisan migrate --force
php artisan site:publish-assets --views
php artisan filament:assets
php artisan articles:import-legacy
php artisan services:import-legacy
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo
echo ".env and the project database were configured successfully."
echo "Create an admin user with: php artisan cms:create-user"
