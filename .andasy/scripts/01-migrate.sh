#!/usr/bin/env bash
# Apply any pending migrations before the app boots.
# --force is required because prod APP_ENV blocks interactive migrate.
# `bash -e` in entrypoint.sh means a failing migration aborts the boot —
# preferable to booting on a schema the code doesn't match.
/usr/bin/php /var/www/html/artisan migrate --force --no-ansi
