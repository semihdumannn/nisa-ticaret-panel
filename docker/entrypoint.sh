#!/bin/sh
set -e

# ─── Helper: strip CR/LF/TAB/spaces from a variable value ────────────────────
strip() { printf '%s' "$1" | tr -d '\r\n\t' | sed 's/^[[:space:]]*//;s/[[:space:]]*$//'; }

echo "[entrypoint] Generating .env from environment variables..."

# ── FIREBASE CREDENTIALS ───────────────────────────────────────────────────────
# FIREBASE_CREDENTIALS_B64: base64-encoded service account JSON (set in HF Spaces secrets)
_FIREBASE_CREDENTIALS_PATH=""
if [ -n "${FIREBASE_CREDENTIALS_B64}" ]; then
    _FIREBASE_CREDENTIALS_PATH="/var/www/html/storage/app/firebase-credentials.json"
    echo "${FIREBASE_CREDENTIALS_B64}" | base64 -d > "${_FIREBASE_CREDENTIALS_PATH}"
    chmod 600 "${_FIREBASE_CREDENTIALS_PATH}"
fi

# ── APP_URL ────────────────────────────────────────────────────────────────────
# SPACE_HOST (auto-injected by HF Spaces) can contain \r — strip it.
# SetRequestForConsole passes APP_URL straight into Symfony Request::create()
# which throws "Invalid URI: Host is malformed" on any non-ASCII char in host.
if [ -n "${APP_URL}" ]; then
    _APP_URL="$(strip "${APP_URL}")"
elif [ -n "${SPACE_HOST}" ]; then
    _APP_URL="https://$(strip "${SPACE_HOST}")"
else
    _APP_URL="http://localhost:7860"
fi

# ── REDIS_URL ──────────────────────────────────────────────────────────────────
# Upstash supplies REDIS_HOST with a "tls://" prefix (e.g. tls://host.upstash.io).
# Strip that prefix to build a clean rediss:// URL for PhpRedis TLS.
_REDIS_HOST_CLEAN="$(printf '%s' "${REDIS_HOST:-127.0.0.1}" | sed 's|^[a-z]*://||' | tr -d '\r\n\t')"

if [ -n "${REDIS_URL}" ]; then
    _REDIS_URL="$(strip "${REDIS_URL}")"
elif [ "${_REDIS_HOST_CLEAN}" != "127.0.0.1" ] && [ -n "${REDIS_PASSWORD}" ]; then
    _REDIS_USER="${REDIS_USERNAME:-default}"
    _REDIS_URL="rediss://${_REDIS_USER}:$(strip "${REDIS_PASSWORD}")@${_REDIS_HOST_CLEAN}:${REDIS_PORT:-6379}"
else
    _REDIS_URL=""
fi

# ── Write .env ─────────────────────────────────────────────────────────────────
cat > /var/www/html/.env << ENVEOF
APP_NAME="Nisa Ticaret"
APP_ENV=${APP_ENV:-production}
APP_KEY=$(strip "${APP_KEY}")
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${_APP_URL}

LOG_CHANNEL=stderr
LOG_LEVEL=${LOG_LEVEL:-error}

DB_CONNECTION=pgsql
DB_HOST=$(strip "${DB_HOST}")
DB_PORT=${DB_PORT:-5432}
DB_DATABASE=$(strip "${DB_DATABASE}")
DB_USERNAME=$(strip "${DB_USERNAME}")
DB_PASSWORD=$(strip "${DB_PASSWORD}")
DB_SSLMODE=require

CACHE_STORE=${CACHE_STORE:-file}
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
FILAMENT_FILESYSTEM_DISK=${FILAMENT_FILESYSTEM_DISK:-local}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-redis}
SESSION_DRIVER=${SESSION_DRIVER:-file}
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none

REDIS_URL=${_REDIS_URL}
REDIS_HOST=${_REDIS_HOST_CLEAN}
REDIS_USERNAME=${REDIS_USERNAME:-default}
REDIS_PASSWORD=$(strip "${REDIS_PASSWORD}")
REDIS_PORT=${REDIS_PORT:-6379}
REDIS_SCHEME=tls

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

FIREBASE_PROJECT_ID=${FIREBASE_PROJECT_ID:-}
FIREBASE_CREDENTIALS=${_FIREBASE_CREDENTIALS_PATH}
ENVEOF

echo "[entrypoint] APP_URL   → ${_APP_URL}"
echo "[entrypoint] REDIS_URL → ${_REDIS_URL}"

# ── Override process environment with cleaned values ──────────────────────────
# phpdotenv does NOT override existing process env vars, so if HF Spaces
# injected APP_URL/REDIS_URL with stray whitespace, PHP would still read the
# dirty value from the environment instead of the clean value from .env.
# Exporting the cleaned values here ensures every child process (artisan,
# php-fpm workers) sees the sanitised strings.
export APP_URL="${_APP_URL}"
export REDIS_URL="${_REDIS_URL}"
export REDIS_HOST="${_REDIS_HOST_CLEAN}"

# ─── Bootstrap ────────────────────────────────────────────────────────────────
echo "[entrypoint] Running Laravel bootstrap tasks..."

php artisan storage:link   --force                  2>/dev/null || true
php artisan config:cache                            2>&1 || echo "[entrypoint] WARN: config:cache failed (non-fatal)"
php artisan route:cache                             2>&1 || echo "[entrypoint] WARN: route:cache failed (non-fatal)"
php artisan view:cache                              2>&1 || echo "[entrypoint] WARN: view:cache failed (non-fatal)"
php artisan migrate --force --no-interaction        2>&1 || echo "[entrypoint] WARN: migrate failed (non-fatal)"
php artisan db:seed --class=RolePermissionSeeder --force 2>&1 || echo "[entrypoint] WARN: RolePermissionSeeder failed (non-fatal)"

# One-time cleanup: detach firebase_uid / phone from spurious non-admin accounts
# so AdminUserSeeder can update the real admin without hitting unique constraints.
if [ -n "${ADMIN_EMAIL}" ]; then
    if [ -n "${ADMIN_FIREBASE_UID}" ]; then
        php artisan tinker --execute="App\Models\User::where('firebase_uid','${ADMIN_FIREBASE_UID}')->where('email','!=','${ADMIN_EMAIL}')->update(['firebase_uid'=>null]);" 2>/dev/null || true
    fi
    if [ -n "${ADMIN_PHONE}" ]; then
        php artisan tinker --execute="App\Models\User::where('phone','${ADMIN_PHONE}')->where('email','!=','${ADMIN_EMAIL}')->update(['phone'=>null]);" 2>/dev/null || true
    fi
fi

php artisan db:seed --class=AdminUserSeeder --force 2>&1 || echo "[entrypoint] WARN: AdminUserSeeder failed (non-fatal)"
php artisan db:seed --class=PriceStockSeeder --force 2>&1 || echo "[entrypoint] WARN: PriceStockSeeder failed (non-fatal)"

echo "[entrypoint] Fixing storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "[entrypoint] Bootstrap complete. Starting Supervisor..."
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
