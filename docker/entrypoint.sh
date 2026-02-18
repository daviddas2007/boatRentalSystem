#!/usr/bin/env sh
set -e

cd /var/www/html

PORT="${PORT:-8080}"
sed -ri "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

if [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

exec "$@"
