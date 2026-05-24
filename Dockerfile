# ──────────────────────────────────────────────────────────────────────────────
# Stage 1: Builder — install Composer dependencies
# ──────────────────────────────────────────────────────────────────────────────
FROM php:8.3-fpm AS builder

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip libpq-dev libpng-dev libonig-dev libxml2-dev \
    libzip-dev libfreetype6-dev libjpeg62-turbo-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip opcache

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction --prefer-dist

COPY . .
RUN composer run-script post-autoload-dump --no-dev --no-interaction 2>/dev/null || true

# ──────────────────────────────────────────────────────────────────────────────
# Stage 2: Production runtime
# ──────────────────────────────────────────────────────────────────────────────
FROM php:8.3-fpm AS production

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor curl \
    libpq5 libpng16-16 libonig5 libzip4 libfreetype6 libjpeg62-turbo \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

WORKDIR /var/www/html
COPY --from=builder --chown=www-data:www-data /app /var/www/html

COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -sf http://localhost/api/v1/health || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
