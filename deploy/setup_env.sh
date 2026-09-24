#!/usr/bin/env bash
set -Eeuo pipefail

read -r -p "Project path [/var/www/meetaj]: " PROJECT_DIR
PROJECT_DIR="${PROJECT_DIR:-/var/www/meetaj}"

[[ -f "${PROJECT_DIR}/.env.production.example" ]] || {
  echo ".env.production.example was not found in the project path."
  exit 1
}

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
if [[ -e .env ]]; then
  read -r -p ".env already exists. Replace it? [y/N]: " REPLACE_ENV
  [[ "${REPLACE_ENV}" =~ ^[Yy]$ ]] || { echo "Operation cancelled."; exit 0; }
  cp .env ".env.backup.$(date +%Y%m%d%H%M%S)"
fi

cp .env.production.example .env

APP_URL="https://${APP_DOMAIN}"
php artisan key:generate --force

escape_sed_value() {
  printf '%s' "$1" | sed 's/[\\&|]/\\&/g'
}

APP_URL_ESCAPED="$(escape_sed_value "${APP_URL}")"
DB_DATABASE_ESCAPED="$(escape_sed_value "${DB_DATABASE}")"
DB_USERNAME_ESCAPED="$(escape_sed_value "${DB_USERNAME}")"
DB_PASSWORD_ESCAPED="$(escape_sed_value "${DB_PASSWORD}")"
CONTACT_EMAIL_ESCAPED="$(escape_sed_value "${CONTACT_EMAIL}")"

sed -i \
  -e "s|^APP_URL=.*|APP_URL=${APP_URL_ESCAPED}|" \
  -e "s|^DB_HOST=.*|DB_HOST=127.0.0.1|" \
  -e "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE_ESCAPED}|" \
  -e "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME_ESCAPED}|" \
  -e "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD_ESCAPED}|" \
  -e "s|^CONTACT_NOTIFICATION_EMAIL=.*|CONTACT_NOTIFICATION_EMAIL=${CONTACT_EMAIL_ESCAPED}|" \
  .env

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
