#!/bin/sh
set -e

echo "Running Laravel startup tasks..."

# Clear stale caches from previous builds
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache config/routes/views with live Render environment variables
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate --force

echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf