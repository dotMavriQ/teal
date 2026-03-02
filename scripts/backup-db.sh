#!/usr/bin/env bash
# Backup TEAL PostgreSQL database with timestamp
# Usage: ./scripts/backup-db.sh [label]

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="$PROJECT_DIR/backups"
LABEL="${1:-manual}"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
FILENAME="teal_${TIMESTAMP}_${LABEL}.sql"

mkdir -p "$BACKUP_DIR"

# Try podman exec first, then docker exec, then direct pg_dump
if podman exec teal-db pg_dump -U teal -d teal --no-owner --no-acl > "$BACKUP_DIR/$FILENAME" 2>/dev/null; then
    :
elif docker exec teal-db pg_dump -U teal -d teal --no-owner --no-acl > "$BACKUP_DIR/$FILENAME" 2>/dev/null; then
    :
elif PGPASSWORD=secret pg_dump -h 127.0.0.1 -U teal -d teal --no-owner --no-acl > "$BACKUP_DIR/$FILENAME" 2>/dev/null; then
    :
else
    # Last resort: dump each table as INSERT statements via PHP
    cd "$PROJECT_DIR"
    php artisan tinker --execute="
        \$tables = ['users','movies','books','shows','episodes','anime','comics','comic_issues','shelves','book_shelf','migrations','sessions','cache','cache_locks'];
        \$out = '';
        foreach (\$tables as \$t) {
            try {
                \$rows = DB::table(\$t)->get();
                if (\$rows->isEmpty()) continue;
                \$cols = array_keys((array)\$rows[0]);
                \$colList = implode(', ', array_map(fn(\$c) => '\"' . \$c . '\"', \$cols));
                foreach (\$rows as \$row) {
                    \$vals = array_map(function(\$v) {
                        if (\$v === null) return 'NULL';
                        return \"'\" . str_replace(\"'\", \"''\", (string)\$v) . \"'\";
                    }, array_values((array)\$row));
                    \$out .= \"INSERT INTO \\\"\$t\\\" (\$colList) VALUES (\" . implode(', ', \$vals) . \");\n\";
                }
            } catch (\Exception \$e) {}
        }
        file_put_contents('$BACKUP_DIR/$FILENAME', \$out);
    " 2>/dev/null

    if [ ! -s "$BACKUP_DIR/$FILENAME" ]; then
        rm -f "$BACKUP_DIR/$FILENAME"
        echo "ERROR: Failed to backup database." >&2
        exit 1
    fi
fi

SIZE=$(du -h "$BACKUP_DIR/$FILENAME" | cut -f1)
echo "Backup saved: backups/$FILENAME ($SIZE)"

# Keep only the 20 most recent backups
cd "$BACKUP_DIR"
ls -t teal_*.sql 2>/dev/null | tail -n +21 | xargs -r rm --
