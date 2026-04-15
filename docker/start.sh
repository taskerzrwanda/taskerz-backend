#!/bin/sh

echo "🚀 Starting Laravel app..."

# Clear old cache
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Run migrations on production
# echo "Running database migrations..."
# php artisan migrate --force

# Rebuild config cache
php artisan config:cache || true


echo "======================================="
echo "Starting PHP-FPM with Unix socket..."
echo "======================================="

# Ensure /var/run exists
mkdir -p /var/run

# Start PHP-FPM in foreground
php-fpm -F &
PHP_FPM_PID=$!

# Wait for PHP-FPM to start
sleep 3

echo "======================================="
echo "Checking PHP-FPM status..."
echo "======================================="

# Check if PHP-FPM is running
if ! ps aux | grep -v grep | grep php-fpm > /dev/null; then
    echo "❌ ERROR: PHP-FPM failed to start!"
    cat /var/log/php-fpm-error.log 2>/dev/null || echo "No PHP-FPM error logs"
    exit 1
fi

# Check if socket was created
if [ -S /var/run/php-fpm.sock ]; then
    echo "✅ PHP-FPM socket created: /var/run/php-fpm.sock"
    ls -la /var/run/php-fpm.sock
else
    echo "❌ ERROR: PHP-FPM socket not found!"
    exit 1
fi

echo "✅ PHP-FPM started successfully (PID: $PHP_FPM_PID)"

echo "======================================="
echo "Network configuration:"
echo "======================================="

# Show network info
echo "Listening ports (IPv4 and IPv6):"
netstat -tuln 2>/dev/null || ss -tuln 2>/dev/null || echo "netstat/ss not available"

echo ""
echo "IPv6 addresses:"
ip -6 addr show 2>/dev/null || echo "IPv6 info not available"

echo "======================================="
echo "Testing Nginx configuration..."
echo "======================================="

nginx -t

if [ $? -ne 0 ]; then
    echo "❌ ERROR: Nginx configuration test failed!"
    exit 1
fi

echo "✅ Nginx configuration is valid"

echo "======================================="
echo "Starting Nginx..."
echo "======================================="

# Start Nginx in foreground
exec nginx -g 'daemon off;'