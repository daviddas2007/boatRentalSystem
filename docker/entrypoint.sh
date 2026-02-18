#!/usr/bin/env sh
set -e

cd /var/www/html

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

if [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

exec "$@"
