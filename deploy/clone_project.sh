#!/usr/bin/env bash
set -Eeuo pipefail

read -r -p "Git repository URL: " REPOSITORY_URL
read -r -p "Branch name: " BRANCH_NAME
read -r -p "Install path [/var/www/meetaj]: " PROJECT_DIR
PROJECT_DIR="${PROJECT_DIR:-/var/www/meetaj}"

[[ -n "${REPOSITORY_URL}" ]] || { echo "Repository URL cannot be empty."; exit 1; }
[[ -n "${BRANCH_NAME}" ]] || { echo "Branch name cannot be empty."; exit 1; }

if [[ -e "${PROJECT_DIR}" ]]; then
  if [[ -n "$(find "${PROJECT_DIR}" -mindepth 1 -maxdepth 1 -print -quit 2>/dev/null)" ]]; then
    echo "Destination exists and is not empty: ${PROJECT_DIR}"
    exit 1
  fi
else
  mkdir -p "${PROJECT_DIR}"
fi

git clone --branch "${BRANCH_NAME}" --single-branch "${REPOSITORY_URL}" "${PROJECT_DIR}"
cd "${PROJECT_DIR}"
composer install --no-dev --optimize-autoloader

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

echo
echo "Project cloned from branch $(git branch --show-current) to:"
echo "${PROJECT_DIR}"
