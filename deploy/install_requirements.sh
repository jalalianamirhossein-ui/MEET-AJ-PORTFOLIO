#!/usr/bin/env bash
set -Eeuo pipefail

if [[ "${EUID}" -ne 0 ]]; then
  echo "Run this script with sudo: sudo bash deploy/install_requirements.sh"
  exit 1
fi

if [[ -r /etc/os-release ]]; then
  . /etc/os-release
else
  echo "Unable to identify the operating system."
  exit 1
fi

if [[ "${ID:-}" != "ubuntu" ]]; then
  echo "This script requires Ubuntu. Current system: ${ID:-unknown}"
  exit 1
fi

echo "Installing Git, Nginx, MySQL, and PHP 8.4..."
apt-get update
apt-get install -y git nginx mysql-server curl unzip ca-certificates software-properties-common

if ! apt-cache show php8.4-cli >/dev/null 2>&1; then
  add-apt-repository ppa:ondrej/php -y
  apt-get update
fi

apt-get install -y \
  php8.4 php8.4-cli php8.4-fpm php8.4-mysql php8.4-bcmath \
  php8.4-curl php8.4-gd php8.4-intl php8.4-mbstring php8.4-xml \
  php8.4-zip composer

php -r 'if (PHP_VERSION_ID < 80400) { fwrite(STDERR, "PHP 8.4 or newer is required.\n"); exit(1); }'

if ! composer --version | grep -qE 'Composer version 2\.'; then
  echo "Composer 2 is required. Check the installed Composer version."
  exit 1
fi

systemctl enable --now nginx mysql php8.4-fpm

echo
echo "Requirements installed successfully."
php -v | head -n 1
composer --version
