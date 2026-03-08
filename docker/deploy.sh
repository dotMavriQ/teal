#!/bin/bash
# =============================================================================
# TEAL Deployment Script for gerty
# Usage: ./docker/deploy.sh [first-run|update|status|logs|backup|rollback]
# =============================================================================
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="/opt/teal/backups"
COMPOSE="docker compose"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()   { echo -e "${GREEN}[deploy]${NC} $*"; }
warn()  { echo -e "${YELLOW}[deploy]${NC} $*"; }
error() { echo -e "${RED}[deploy]${NC} $*" >&2; }
info()  { echo -e "${BLUE}[deploy]${NC} $*"; }

# ---- Pre-flight checks ----
preflight() {
    log "Running pre-flight checks..."

    # Check Docker
    if ! command -v docker &>/dev/null; then
        error "Docker is not installed"
        exit 1
    fi
    log "  Docker: $(docker --version | cut -d' ' -f3)"

    # Check compose
    if ! $COMPOSE version &>/dev/null; then
        error "Docker Compose is not available"
        exit 1
    fi
    log "  Compose: $($COMPOSE version --short 2>/dev/null || $COMPOSE version | grep -oP '\d+\.\d+\.\d+')"

    # Check external network
    if ! docker network inspect web &>/dev/null; then
        warn "  Docker network 'web' does not exist — creating it..."
        docker network create web
        log "  Network 'web' created"
    else
        log "  Network 'web': exists"
    fi

    # Check env file
    if [ ! -f "$PROJECT_DIR/.env.production" ]; then
        error ".env.production not found!"
        error "Copy .env.production.example to .env.production and fill in secrets"
        exit 1
    fi
    log "  .env.production: found"

    # Validate critical env vars
    local missing=""
    for var in APP_KEY DB_PASSWORD; do
        val=$(grep "^${var}=" "$PROJECT_DIR/.env.production" | cut -d'=' -f2-)
        if [ -z "$val" ]; then
            missing="$missing $var"
        fi
    done
    if [ -n "$missing" ]; then
        error "Missing values in .env.production:$missing"
        exit 1
    fi
    log "  Environment: validated"

    log "Pre-flight checks passed"
}

# ---- Commands ----

cmd_first_run() {
    log "=== TEAL First-Time Deployment ==="
    preflight

    # Create backup directory
    mkdir -p "$BACKUP_DIR"

    # Build and start
    log "Building images..."
    cd "$PROJECT_DIR"
    $COMPOSE build --no-cache

    log "Starting services..."
    $COMPOSE up -d

    log "Waiting for services to be healthy..."
    sleep 10

    # Show status
    cmd_status

    log "=== First-time deployment complete ==="
    info "Visit: https://teal.dotmavriq.life"
    info "Logs:  $COMPOSE logs -f"
}

cmd_update() {
    log "=== TEAL Update Deployment ==="
    preflight

    cd "$PROJECT_DIR"

    # Pull latest code
    log "Pulling latest code..."
    git pull origin main

    # Backup database before update
    cmd_backup

    # Rebuild
    log "Building updated image..."
    $COMPOSE build app

    # Rolling restart: bring up new containers
    log "Restarting app and queue..."
    $COMPOSE up -d --force-recreate --no-deps app queue

    log "Waiting for health check..."
    sleep 15

    cmd_status

    log "=== Update deployment complete ==="
}

cmd_status() {
    log "=== TEAL Service Status ==="
    cd "$PROJECT_DIR"

    echo ""
    $COMPOSE ps
    echo ""

    # Check each service
    for svc in app db queue; do
        container="teal-$svc"
        if docker inspect --format='{{.State.Health.Status}}' "$container" 2>/dev/null | grep -q "healthy"; then
            log "  $container: healthy"
        elif docker inspect --format='{{.State.Status}}' "$container" 2>/dev/null | grep -q "running"; then
            warn "  $container: running (health check pending)"
        else
            error "  $container: NOT RUNNING"
        fi
    done

    echo ""
    # Show recent logs
    info "Recent app logs (last 10 lines):"
    $COMPOSE logs --tail=10 app 2>/dev/null || true
}

cmd_logs() {
    cd "$PROJECT_DIR"
    $COMPOSE logs -f "${2:-}"
}

cmd_backup() {
    log "=== Database Backup ==="
    mkdir -p "$BACKUP_DIR"
    TIMESTAMP=$(date +%Y%m%d-%H%M%S)
    BACKUP_FILE="$BACKUP_DIR/teal-${TIMESTAMP}.sql.gz"

    if docker exec teal-db pg_dump -U teal teal 2>/dev/null | gzip > "$BACKUP_FILE"; then
        SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
        log "Backup saved: $BACKUP_FILE ($SIZE)"

        # Keep only last 10 backups
        ls -t "$BACKUP_DIR"/teal-*.sql.gz 2>/dev/null | tail -n +11 | xargs -r rm
        KEPT=$(ls "$BACKUP_DIR"/teal-*.sql.gz 2>/dev/null | wc -l)
        log "Backups retained: $KEPT"
    else
        error "Backup failed!"
        rm -f "$BACKUP_FILE"
        return 1
    fi
}

cmd_rollback() {
    log "=== Rollback ==="
    cd "$PROJECT_DIR"

    LATEST_BACKUP=$(ls -t "$BACKUP_DIR"/teal-*.sql.gz 2>/dev/null | head -1)
    if [ -z "$LATEST_BACKUP" ]; then
        error "No backups found in $BACKUP_DIR"
        exit 1
    fi

    warn "This will restore from: $LATEST_BACKUP"
    warn "All current data will be OVERWRITTEN."
    read -p "Continue? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        log "Rollback cancelled"
        exit 0
    fi

    log "Stopping app and queue..."
    $COMPOSE stop app queue

    log "Restoring database..."
    gunzip -c "$LATEST_BACKUP" | docker exec -i teal-db psql -U teal teal

    log "Restarting services..."
    $COMPOSE up -d app queue

    log "=== Rollback complete ==="
}

# ---- Main ----
case "${1:-help}" in
    first-run)  cmd_first_run ;;
    update)     cmd_update ;;
    status)     cmd_status ;;
    logs)       cmd_logs "$@" ;;
    backup)     cmd_backup ;;
    rollback)   cmd_rollback ;;
    help|*)
        echo "TEAL Deployment Script"
        echo ""
        echo "Usage: $0 <command>"
        echo ""
        echo "Commands:"
        echo "  first-run   Initial deployment (build + start everything)"
        echo "  update      Pull latest code, backup DB, rebuild, restart"
        echo "  status      Show service health and recent logs"
        echo "  logs [svc]  Tail logs (optionally for specific service: app, db, queue)"
        echo "  backup      Backup PostgreSQL database"
        echo "  rollback    Restore from latest backup (interactive)"
        echo "  help        Show this help"
        ;;
esac
