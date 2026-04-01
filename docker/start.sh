#!/bin/sh

echo "🚀 Starting Laravel app..."

# Clear old cache
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Start PHP-FPM in foreground temporarily to check it starts
echo "Starting PHP-FPM..."
php-fpm -F &
PHP_FPM_PID=$!

# Wait for PHP-FPM to be ready
sleep 3

# Check if PHP-FPM is running
if ! ps | grep -v grep | grep php-fpm > /dev/null; then
    echo "ERROR: PHP-FPM failed to start!"
    exit 1
fi

echo "PHP-FPM started successfully"

# Start Nginx in foreground
echo "Starting Nginx..."
exec nginx -g 'daemon off;'