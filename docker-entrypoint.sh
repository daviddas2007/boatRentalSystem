#!/bin/bash
set -e

echo "============================================"
echo "  BoatRentSystem - Starting Deployment..."
echo "============================================"

# Default PORT to 8080 if not set by Railway
export PORT="${PORT:-8080}"
echo ">> Port: $PORT"

# Ensure .env file exists (Laravel needs it)
touch /var/www/html/.env

# Check APP_KEY is set via environment variable
if [ -z "$APP_KEY" ]; then
    echo ">> WARNING: APP_KEY not set! Generating one..."
    php artisan key:generate --force
    echo ">> IMPORTANT: Copy the generated APP_KEY from .env and set it in Railway variables!"
fi

# Clear any stale caches first
echo ">> Clearing old caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Run database migrations
echo ">> Running database migrations..."
php artisan migrate --force || {
    echo "!! Migration failed — check DB connection variables"
    echo "!! DB_HOST=$DB_HOST DB_PORT=$DB_PORT DB_DATABASE=$DB_DATABASE"
}

# Seed database (only if SEED_DB=true)
if [ "$SEED_DB" = "true" ]; then
    echo ">> Seeding database with demo data..."
    php artisan db:seed --force || echo "!! Seeding failed or already seeded"
fi

# Create storage symlink
echo ">> Creating storage link..."
php artisan storage:link || true

# Cache configuration AFTER migrations
echo ">> Caching configuration..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "============================================"
echo "  BoatRentSystem is ready!"
echo "  Starting Laravel server on port: $PORT"
echo "============================================"

# Start Laravel's built-in server
exec php artisan serve --host=0.0.0.0 --port=$PORT
