# =============================================================================
# TEAL Production Dockerfile
# Multi-stage build: Node (frontend) → Composer (PHP deps) → FrankenPHP runtime
# =============================================================================

# ---------------------------------------------------------------------------
# Stage 1: Build frontend assets with Vite
# ---------------------------------------------------------------------------
FROM node:22-alpine AS node-builder

WORKDIR /build

# Copy dependency manifests first for layer caching
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund \
    && echo "[node-builder] npm dependencies installed"

# Copy frontend source files needed for the build
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ resources/

# Build production assets
RUN npm run build \
    && echo "[node-builder] Vite build complete" \
    && ls -la public/build/ \
    && echo "[node-builder] Manifest:" \
    && cat public/build/manifest.json

# ---------------------------------------------------------------------------
# Stage 2: Install PHP dependencies with Composer
# ---------------------------------------------------------------------------
FROM composer:2 AS composer-builder

WORKDIR /build

# Copy dependency manifests
COPY composer.json composer.lock ./

# Install production dependencies only (no scripts — avoids needing artisan)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    && echo "[composer-builder] PHP dependencies installed" \
    && echo "[composer-builder] Vendor size:" \
    && du -sh vendor/

# ---------------------------------------------------------------------------
# Stage 3: Production runtime image
# ---------------------------------------------------------------------------
FROM serversideup/php:8.4-frankenphp AS production

LABEL maintainer="dotmavriq" \
      app="TEAL" \
      description="TEAL - Track Everything About Life"

# ---- Install additional PHP extensions ----
# pgsql/pdo_pgsql: PostgreSQL
# gd:             Image processing (intervention/image)
# intl:           Unicode/i18n support
# opcache:        Production PHP performance
# pcntl:          Process control for Octane graceful shutdown
# bcmath:         Precision math (Laravel dependency)
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    gd \
    intl \
    opcache \
    pcntl \
    bcmath \
    && echo "[production] PHP extensions installed:" \
    && php -m | sort

# ---- Set environment defaults ----
ENV APP_ENV=production \
    APP_DEBUG=false \
    OCTANE_SERVER=frankenphp \
    SERVER_NAME=":8080" \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=warning \
    AUTORUN_ENABLED=false

# ---- Set working directory ----
WORKDIR /var/www/html

# ---- Copy application source ----
COPY --chown=www-data:www-data . .

# ---- Copy built vendor directory from composer stage ----
COPY --from=composer-builder --chown=www-data:www-data /build/vendor ./vendor

# ---- Copy built frontend assets from node stage ----
COPY --from=node-builder --chown=www-data:www-data /build/public/build ./public/build

# ---- Run composer post-install scripts (package discovery, etc.) ----
RUN composer dump-autoload --optimize --no-dev \
    && echo "[production] Autoloader optimized"

# ---- Ensure storage directories and permissions ----
RUN mkdir -p \
    storage/app/public/covers \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && echo "[production] Storage directories created"

# ---- Copy entrypoint scripts ----
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
COPY docker/queue-entrypoint.sh /usr/local/bin/docker-queue-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh /usr/local/bin/docker-queue-entrypoint.sh

# ---- Healthcheck ----
HEALTHCHECK --interval=15s --timeout=5s --retries=3 --start-period=20s \
    CMD php artisan octane:status --server=frankenphp || exit 1

EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]
