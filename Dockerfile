# Single-stage build — avoids runtime library name mismatches between stages.
# Dev packages are left in the image (adds ~80 MB) but guarantees all shared
# libraries are present regardless of Debian point-release renaming.
FROM php:8.4-fpm-bookworm

# ── System dependencies ────────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip nginx supervisor \
    libpq-dev libpng-dev libonig-dev libxml2-dev \
    libzip-dev libfreetype6-dev libjpeg62-turbo-dev \
    libicu-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ── PHP extensions ─────────────────────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip opcache intl

RUN pecl install redis && docker-php-ext-enable redis

# ── PHP config ─────────────────────────────────────────────────────────────────
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

# ── Composer ───────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction --prefer-dist

COPY . .
RUN composer run-script post-autoload-dump --no-dev --no-interaction 2>/dev/null || true

# ── Nginx / Supervisor / Entrypoint ───────────────────────────────────────────
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# ── Permissions ────────────────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# .env is generated at runtime by entrypoint.sh from injected environment variables.
# Do NOT bake secrets into the image.

EXPOSE 7860

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -sf http://localhost:7860/api/v1/health || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
