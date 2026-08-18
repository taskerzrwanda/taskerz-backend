#!/usr/bin/env bash
# Configure OPcache for production. Runs before caches.sh and before php-fpm starts.
# validate_timestamps=0 is safe because the image is rebuilt on each deploy.

set -e

PHP_VER=$(/usr/bin/php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>/dev/null || echo "")

if [ -z "$PHP_VER" ]; then
    echo "OPcache config: could not detect PHP version, skipping."
    exit 0
fi

CONF_DIR="/etc/php/${PHP_VER}/fpm/conf.d"

if [ ! -d "$CONF_DIR" ]; then
    echo "OPcache config: ${CONF_DIR} not present, skipping."
    exit 0
fi

cat > "${CONF_DIR}/99-opcache-tuned.ini" <<'EOF'
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=128
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.fast_shutdown=1
EOF

echo "OPcache configured for PHP ${PHP_VER} at ${CONF_DIR}/99-opcache-tuned.ini"
