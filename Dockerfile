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
# Stage 3: Base runtime (PHP extensions + env). Stable inputs → caches well.
# Kept separate from the app layers so the slow extension compile is cached
# while frontend/app stages are always rebuilt fresh (see CI no-cache-filter).
# ---------------------------------------------------------------------------
FROM serversideup/php:8.4-frankenphp AS php-base

LABEL maintainer="dotmavriq" \
      app="TEAL" \
      description="TEAL - Track Everything About Life"

# ---- Install additional PHP extensions (requires root) ----
# pgsql/pdo_pgsql: PostgreSQL
# gd:             Image processing (intervention/image)
# intl:           Unicode/i18n support
# bcmath:         Precision math (Laravel dependency)
# Note: pdo_pgsql, opcache, pcntl already included in serversideup image
USER root
RUN install-php-extensions \
    pgsql \
    gd \
    intl \
    bcmath \
    && echo "[production] PHP extensions installed:" \
    && php -m | sort
USER www-data

# ---- Set environment defaults ----
ENV APP_ENV=production \
    APP_DEBUG=false \
    OCTANE_SERVER=frankenphp \
    SERVER_NAME=":8080" \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=warning \
    AUTORUN_ENABLED=false

# ---------------------------------------------------------------------------
# Stage 4: Production image — app + built assets. These layers change every
# commit, so CI rebuilds this stage fresh (no-cache-filter) to guarantee the
# deployed code/assets always match source; php-base above stays cached.
# ---------------------------------------------------------------------------
FROM php-base AS production

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
USER root
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
USER www-data

# ---- Healthcheck ----
HEALTHCHECK --interval=15s --timeout=5s --retries=3 --start-period=20s \
    CMD php artisan octane:status --server=frankenphp || exit 1

EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]
