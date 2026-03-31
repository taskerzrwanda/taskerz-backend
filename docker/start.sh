#!/bin/sh

echo "🚀 Starting Laravel app..."

# Clear old cache
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Optional: run migrations (if needed)
# php artisan migrate --force

# Start PHP-FPM
php-fpm -D

# Wait a bit
sleep 2

# Start Nginx
nginx -g 'daemon off;'