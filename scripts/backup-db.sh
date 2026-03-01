#!/usr/bin/env bash
# Backup TEAL PostgreSQL database with timestamp
# Usage: ./scripts/backup-db.sh [label]
# Example: ./scripts/backup-db.sh pre-test

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="$PROJECT_DIR/backups"
LABEL="${1:-manual}"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
FILENAME="teal_${TIMESTAMP}_${LABEL}.sql"

mkdir -p "$BACKUP_DIR"

# Dump from the containerized PostgreSQL
if podman exec teal-db pg_dump -U teal -d teal --no-owner --no-acl > "$BACKUP_DIR/$FILENAME" 2>/dev/null; then
    SIZE=$(du -h "$BACKUP_DIR/$FILENAME" | cut -f1)
    echo "Backup saved: backups/$FILENAME ($SIZE)"
else
    echo "ERROR: Failed to backup database. Is teal-db container running?" >&2
    exit 1
fi

# Keep only the 20 most recent backups
cd "$BACKUP_DIR"
ls -t teal_*.sql 2>/dev/null | tail -n +21 | xargs -r rm --
