#!/bin/sh
# =============================================================================
# TEAL Queue Worker Entrypoint
# Waits for the app to be ready, then starts processing queued jobs
# =============================================================================
set -e

log()   { echo "[teal-queue] $(date '+%Y-%m-%d %H:%M:%S') $*"; }
warn()  { echo "[teal-queue] $(date '+%Y-%m-%d %H:%M:%S') WARN: $*" >&2; }
fatal() { echo "[teal-queue] $(date '+%Y-%m-%d %H:%M:%S') FATAL: $*" >&2; exit 1; }

log "Starting TEAL queue worker (PID $$)..."

# ---- Validate environment ----
for var in APP_KEY DB_HOST DB_DATABASE DB_USERNAME DB_PASSWORD; do
    eval val=\$$var
    if [ -z "$val" ]; then
        fatal "Missing required environment variable: $var"
    fi
done

# ---- Wait for database ----
log "Waiting for database..."
MAX_RETRIES=60
RETRY=0
DB_READY=false

while [ "$RETRY" -lt "$MAX_RETRIES" ]; do
    if php artisan db:show --json 2>/dev/null | head -1 | grep -q '{'; then
        DB_READY=true
        break
    fi
    RETRY=$((RETRY + 1))
    log "Database not ready, attempt $RETRY/$MAX_RETRIES..."
    sleep 2
done

if [ "$DB_READY" = "false" ]; then
    fatal "Database not reachable after $MAX_RETRIES attempts"
fi
log "Database connection established"

# ---- Wait for migrations (check that jobs table exists) ----
log "Waiting for migrations to complete..."
RETRY=0
MIGRATED=false

while [ "$RETRY" -lt 30 ]; do
    if php artisan queue:monitor default 2>/dev/null; then
        MIGRATED=true
        break
    fi
    RETRY=$((RETRY + 1))
    log "Migrations not yet complete, attempt $RETRY/30..."
    sleep 3
done

if [ "$MIGRATED" = "false" ]; then
    fatal "Timed out waiting for migrations"
fi
log "Migrations confirmed, jobs table accessible"

# ---- Start queue worker ----
log "============================================"
log "Queue worker starting"
log "  Queue:      default"
log "  Tries:      3"
log "  Timeout:    90s"
log "  Max time:   3600s"
log "  Memory:     128MB"
log "  Backoff:    10,30,60s"
log "============================================"

exec php artisan queue:work \
    --queue=default \
    --sleep=3 \
    --tries=3 \
    --timeout=90 \
    --max-time=3600 \
    --memory=128 \
    --backoff=10,30,60
