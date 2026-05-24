#!/bin/sh
set -e

# Run at container start before Supervisor takes over.
# This handles one-time setup that requires the full environment (APP_KEY, DB, etc.)

echo "[entrypoint] Running Laravel bootstrap tasks..."

# Storage symbolic link (safe to re-run)
php artisan storage:link --force 2>/dev/null || true

# Cache config/routes/views for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[entrypoint] Bootstrap complete. Starting Supervisor..."
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
