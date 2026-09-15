#!/bin/sh
#!/bin/sh

# Clear and rebuild caches on startup
php artisan config:clear
php artisan route:clear
php artisan view:clear

set -e

echo "Running Laravel startup tasks..."

# Cache config/routes/views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
