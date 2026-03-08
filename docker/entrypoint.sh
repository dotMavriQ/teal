#!/bin/sh
# =============================================================================
# TEAL Docker Entrypoint
# Handles startup validation, migrations, caching, and Octane launch
# =============================================================================
set -e

# ---- Logging helpers ----
log()   { echo "[teal-entrypoint] $(date '+%Y-%m-%d %H:%M:%S') $*"; }
warn()  { echo "[teal-entrypoint] $(date '+%Y-%m-%d %H:%M:%S') WARN: $*" >&2; }
fatal() { echo "[teal-entrypoint] $(date '+%Y-%m-%d %H:%M:%S') FATAL: $*" >&2; exit 1; }

log "Starting TEAL application (PID $$)..."
log "PHP version: $(php -v | head -1)"
log "Environment: ${APP_ENV:-unknown}"

# ---- Step 1: Validate critical environment variables ----
log "Validating environment..."
MISSING=""
for var in APP_KEY DB_HOST DB_DATABASE DB_USERNAME DB_PASSWORD; do
    eval val=\$$var
    if [ -z "$val" ]; then
        MISSING="$MISSING $var"
    fi
done
if [ -n "$MISSING" ]; then
    fatal "Missing required environment variables:$MISSING"
fi
log "Environment validation passed"

# ---- Step 2: Verify PHP extensions ----
log "Verifying PHP extensions..."
REQUIRED_EXTENSIONS="pdo_pgsql pgsql gd intl pcntl"
MISSING_EXT=""
for ext in $REQUIRED_EXTENSIONS; do
    if ! php -m 2>/dev/null | grep -qi "^${ext}$"; then
        MISSING_EXT="$MISSING_EXT $ext"
    fi
done
if [ -n "$MISSING_EXT" ]; then
    fatal "Missing PHP extensions:$MISSING_EXT"
fi
log "All required PHP extensions present"

# ---- Step 3: Ensure storage directory structure ----
log "Ensuring storage directories..."
mkdir -p \
    storage/app/public/covers \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Fix permissions (may not be able to chown as non-root, that's fine)
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache
fi
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
log "Storage directories ready"

# ---- Step 4: Create storage:link symlink ----
log "Creating storage symlink..."
php artisan storage:link --force 2>/dev/null && log "Storage symlink created" || warn "Storage symlink already exists or failed (non-critical)"

# ---- Step 5: Wait for database connectivity ----
log "Waiting for database at ${DB_HOST}:${DB_PORT:-5432}..."
MAX_RETRIES=30
RETRY=0
DB_READY=false

while [ "$RETRY" -lt "$MAX_RETRIES" ]; do
    if php artisan db:show --json 2>/dev/null | head -1 | grep -q '{'; then
        DB_READY=true
        break
    fi
    RETRY=$((RETRY + 1))
    log "Database not ready, attempt $RETRY/$MAX_RETRIES (retrying in 2s)..."
    sleep 2
done

if [ "$DB_READY" = "false" ]; then
    fatal "Database not reachable after $MAX_RETRIES attempts (${DB_HOST}:${DB_PORT:-5432})"
fi
log "Database connection established"

# ---- Step 6: Run migrations ----
log "Running database migrations..."
# Note: --isolated requires cache_locks table which may not exist on first run
# Use --force only (safe since we're the only instance during startup)
if php artisan migrate --force 2>&1; then
    log "Migrations completed successfully"
else
    MIGRATE_EXIT=$?
    fatal "Migration failed with exit code $MIGRATE_EXIT"
fi

# ---- Step 7: Cache configuration for production performance ----
if [ "${APP_ENV}" = "production" ]; then
    log "Caching configuration for production..."

    php artisan config:cache \
        && log "Config cached" \
        || warn "Config cache failed (non-critical)"

    php artisan route:cache \
        && log "Routes cached" \
        || warn "Route cache failed (non-critical)"

    php artisan view:cache \
        && log "Views cached" \
        || warn "View cache failed (non-critical)"

    php artisan event:cache \
        && log "Events cached" \
        || warn "Event cache failed (non-critical)"

    log "Production caching complete"
else
    log "Skipping caching (non-production environment)"
fi

# ---- Step 8: Final pre-flight checks ----
log "Running pre-flight checks..."

# Verify the app can bootstrap
if php artisan about --json 2>/dev/null | head -1 | grep -q '{'; then
    log "Application bootstrap: OK"
else
    warn "Application bootstrap check returned unexpected output (may still work)"
fi

# Verify build assets exist
if [ -f "public/build/manifest.json" ]; then
    log "Vite manifest: OK"
else
    warn "Vite manifest not found at public/build/manifest.json — assets may not load"
fi

# Log a summary
log "============================================"
log "TEAL ready to start"
log "  Server:   FrankenPHP (Octane)"
log "  Port:     8080"
log "  Workers:  ${OCTANE_WORKERS:-4}"
log "  Env:      ${APP_ENV}"
log "  URL:      ${APP_URL:-not set}"
log "  DB:       ${DB_HOST}:${DB_PORT:-5432}/${DB_DATABASE}"
log "============================================"

# ---- Step 9: Launch Octane (exec replaces shell for proper signal handling) ----
exec php artisan octane:start \
    --server=frankenphp \
    --host=0.0.0.0 \
    --port=8080 \
    --workers="${OCTANE_WORKERS:-4}" \
    --max-requests="${OCTANE_MAX_REQUESTS:-1000}" \
    --log-level="${LOG_LEVEL:-warning}"
