#!/usr/bin/env bash
# =============================================================================
# TEAL Production Pull-Deploy (runs ON gerty)
# =============================================================================
# Invoked by CI over SSH after the image is built + pushed to GHCR:
#   TEAL_TAG=<commit-sha> ./docker/deploy-prod.sh
#
# DATA SAFETY IS THE PRIME DIRECTIVE. This script:
#   - acts on the `app` and `queue` services ONLY, by explicit name
#   - NEVER runs: down / down -v / volume rm / --renew-anon-volumes
#   - NEVER touches the `db` service or the teal-db-data volume
#   - takes + verifies a DB backup before anything goes live (no backup = abort)
#   - HALTS if a pending migration looks destructive (DROP/TRUNCATE/DELETE)
#   - rolls back to the previous image if the new app fails its health check
# =============================================================================
set -euo pipefail

# ---- Config ----
PROJECT_DIR="${PROJECT_DIR:-/opt/teal/app}"
BACKUP_DIR="${BACKUP_DIR:-/opt/teal/backups}"
TEAL_IMAGE="${TEAL_IMAGE:-ghcr.io/dotmavriq/teal}"
TEAL_TAG="${TEAL_TAG:?TEAL_TAG (commit sha) must be set}"
KEEP_BACKUPS="${KEEP_BACKUPS:-10}"
HEALTH_TIMEOUT="${HEALTH_TIMEOUT:-120}"
COMPOSE="docker compose"

export TEAL_IMAGE TEAL_TAG
IMAGE_REF="${TEAL_IMAGE}:${TEAL_TAG}"

log()   { echo "[deploy-prod] $(date '+%H:%M:%S') $*"; }
warn()  { echo "[deploy-prod] $(date '+%H:%M:%S') WARN: $*" >&2; }
fatal() { echo "[deploy-prod] $(date '+%H:%M:%S') FATAL: $*" >&2; exit 1; }

cd "$PROJECT_DIR" || fatal "Project dir $PROJECT_DIR not found"

# ---- 0. Guard: never let this script be used to tear down data ----
case " $* " in
    *" down "*|*" -v "*|*"volume"*) fatal "Refusing: destructive arg passed to deploy script" ;;
esac

# ---- 1. Pre-flight ----
log "Pre-flight checks (image: $IMAGE_REF)..."
[ -f .env ]            || fatal ".env missing in $PROJECT_DIR"
[ -f .env.production ] || fatal ".env.production missing in $PROJECT_DIR"
docker network inspect web >/dev/null 2>&1 || fatal "Docker network 'web' missing"

db_health="$(docker inspect --format '{{.State.Health.Status}}' teal-db 2>/dev/null || echo missing)"
[ "$db_health" = "healthy" ] || fatal "teal-db is not healthy (status: $db_health) — aborting before touching anything"
log "  teal-db: healthy"

# Capture the currently-running image so we can roll back to it.
PREV_IMAGE="$(docker inspect --format '{{.Config.Image}}' teal-app 2>/dev/null || echo '')"
log "  current app image: ${PREV_IMAGE:-<none>}"

# ---- 2. Backup the database (NO BACKUP = NO DEPLOY) ----
log "Backing up production database..."
mkdir -p "$BACKUP_DIR"
TS="$(date +%Y%m%d-%H%M%S)"
BACKUP_FILE="$BACKUP_DIR/teal-${TS}.sql.gz"

if ! docker exec teal-db pg_dump -U teal teal | gzip > "$BACKUP_FILE"; then
    rm -f "$BACKUP_FILE"
    fatal "pg_dump failed — aborting deploy, prod left untouched"
fi
gzip -t "$BACKUP_FILE" 2>/dev/null || { rm -f "$BACKUP_FILE"; fatal "Backup failed gzip integrity check"; }
# Sanity: dump must contain the key tables, not an empty/error dump.
if ! gunzip -c "$BACKUP_FILE" | grep -q 'CREATE TABLE public.books'; then
    rm -f "$BACKUP_FILE"
    fatal "Backup missing expected tables — aborting deploy"
fi
log "  backup OK: $BACKUP_FILE ($(du -h "$BACKUP_FILE" | cut -f1))"

# Rotate: keep the most recent $KEEP_BACKUPS.
ls -t "$BACKUP_DIR"/teal-*.sql.gz 2>/dev/null | tail -n +"$((KEEP_BACKUPS + 1))" | xargs -r rm -f
log "  backups retained: $(ls "$BACKUP_DIR"/teal-*.sql.gz 2>/dev/null | wc -l)"

# ---- 3. Pull the new image (app + queue share it) ----
log "Pulling new image..."
$COMPOSE pull app queue

# ---- 4. Destructive-migration guard ----
# Render the SQL of pending migrations WITHOUT executing it, then scan for
# data-destroying statements. Schema-only drops (index/constraint) are allowed.
log "Scanning pending migrations for destructive statements..."
PRETEND_SQL="$(docker run --rm \
    --network teal-internal \
    -e DB_HOST=db -e DB_PORT=5432 -e APP_ENV=production \
    -v "$PROJECT_DIR/.env:/var/www/html/.env:ro" \
    --entrypoint php "$IMAGE_REF" \
    artisan migrate --pretend --no-ansi 2>&1)" || {
        warn "migrate --pretend could not run cleanly; output was:"
        echo "$PRETEND_SQL" >&2
        fatal "Could not verify migration safety — aborting before going live"
    }

if echo "$PRETEND_SQL" | grep -iqE 'drop table|drop column|truncate|delete from|alter table .*drop '; then
    echo "$PRETEND_SQL" >&2
    fatal "DESTRUCTIVE migration detected in pending changes — HALTING. Backup is at $BACKUP_FILE. Run it manually & deliberately if intended."
fi
log "  no destructive migrations pending (additive changes will auto-apply)"

# ---- 5. Go live: recreate ONLY app + queue ----
log "Recreating app + queue with $IMAGE_REF (db untouched)..."
$COMPOSE up -d --no-deps app queue

# ---- 6. Health gate (rollback on failure) ----
log "Waiting for teal-app to become healthy (timeout ${HEALTH_TIMEOUT}s)..."
elapsed=0
while [ "$elapsed" -lt "$HEALTH_TIMEOUT" ]; do
    status="$(docker inspect --format '{{.State.Health.Status}}' teal-app 2>/dev/null || echo missing)"
    if [ "$status" = "healthy" ]; then
        log "=== Deploy OK — teal-app healthy on $IMAGE_REF ==="
        docker image prune -f >/dev/null 2>&1 || true
        exit 0
    fi
    [ "$status" = "unhealthy" ] && break
    sleep 4; elapsed=$((elapsed + 4))
done

warn "teal-app did not become healthy (last status: ${status:-unknown})"
if echo "$PREV_IMAGE" | grep -q "^${TEAL_IMAGE}:"; then
    PREV_TAG="${PREV_IMAGE##*:}"
    warn "Rolling back to previous image: $PREV_IMAGE"
    TEAL_TAG="$PREV_TAG" $COMPOSE up -d --no-deps app queue
    fatal "Deploy failed health check — rolled back to $PREV_IMAGE. DB untouched; backup at $BACKUP_FILE"
else
    fatal "Deploy failed health check and no GHCR previous image to auto-roll-back to (was: ${PREV_IMAGE:-none}). DB untouched; backup at $BACKUP_FILE. Investigate manually."
fi
