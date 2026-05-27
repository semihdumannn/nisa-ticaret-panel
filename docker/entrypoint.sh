#!/bin/sh
set -e

# ─── Generate .env from injected environment variables ────────────────────────
# HuggingFace Spaces (and Docker in general) injects secrets as env vars.
# We write them into a .env file so Laravel picks them up before config:cache.
echo "[entrypoint] Generating .env from environment variables..."

# Build APP_URL: prefer the injected secret, fall back to the HF Space hostname,
# then fall back to localhost. SPACE_HOST is injected automatically by HF Spaces.
if [ -n "${APP_URL}" ]; then
    _APP_URL="${APP_URL}"
elif [ -n "${SPACE_HOST}" ]; then
    _APP_URL="https://${SPACE_HOST}"
else
    _APP_URL="http://localhost:7860"
fi

cat > /var/www/html/.env << ENVEOF
APP_NAME="Nisa Ticaret"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${_APP_URL}

LOG_CHANNEL=stderr
LOG_LEVEL=${LOG_LEVEL:-error}

DB_CONNECTION=pgsql
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT:-5432}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
DB_SSLMODE=require

CACHE_STORE=${CACHE_STORE:-redis}
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
FILAMENT_FILESYSTEM_DISK=${FILAMENT_FILESYSTEM_DISK:-local}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-redis}
SESSION_DRIVER=${SESSION_DRIVER:-redis}
SESSION_LIFETIME=120

REDIS_HOST=${REDIS_HOST:-127.0.0.1}
REDIS_PASSWORD=${REDIS_PASSWORD:-null}
REDIS_PORT=${REDIS_PORT:-6379}
REDIS_SCHEME=${REDIS_SCHEME:-tls}

MAIL_MAILER=log
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS:-noreply@nisaticaret.com}
MAIL_FROM_NAME="Nisa Ticaret"

SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS:-localhost}
CORS_ALLOWED_ORIGINS=${CORS_ALLOWED_ORIGINS:-*}

SCOUT_DRIVER=database

HORIZON_NAME="Nisa Ticaret"
HORIZON_PREFIX=nisa_ticaret_horizon:

IYZICO_API_KEY=${IYZICO_API_KEY:-}
IYZICO_SECRET_KEY=${IYZICO_SECRET_KEY:-}
IYZICO_BASE_URL=${IYZICO_BASE_URL:-https://sandbox-api.iyzipay.com}
ENVEOF

echo "[entrypoint] APP_URL resolved to: ${_APP_URL}"

# ─── Bootstrap ────────────────────────────────────────────────────────────────
echo "[entrypoint] Running Laravel bootstrap tasks..."

php artisan storage:link --force 2>/dev/null || true

# config:cache, route:cache, view:cache are optimisations — failures must not
# crash the container (Symfony 7 strict URI validation can reject the URL in
# certain environments). The app falls back to reading files directly.
php artisan config:cache  2>&1 || echo "[entrypoint] WARN: config:cache failed (non-fatal)"
php artisan route:cache   2>&1 || echo "[entrypoint] WARN: route:cache failed (non-fatal)"
php artisan view:cache    2>&1 || echo "[entrypoint] WARN: view:cache failed (non-fatal)"

php artisan migrate --force --no-interaction 2>&1 || echo "[entrypoint] WARN: migrate failed (non-fatal)"

echo "[entrypoint] Bootstrap complete. Starting Supervisor..."
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
