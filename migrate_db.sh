#!/bin/bash
set -e

# Detect container runtime
if command -v docker &> /dev/null; then
    RUNTIME="docker"
    COMPOSE="docker compose"
elif command -v podman &> /dev/null; then
    RUNTIME="podman"
    # Check for podman-compose or docker-compose availability
    if command -v podman-compose &> /dev/null; then
        COMPOSE="podman-compose"
    elif command -v docker-compose &> /dev/null; then
        COMPOSE="docker-compose"
    else
        echo "Error: Neither docker-compose nor podman-compose found."
        exit 1
    fi
else
    echo "Error: Neither docker nor podman found."
    exit 1
fi

echo "Using runtime: $RUNTIME"
echo "Using compose: $COMPOSE"

# Start the database container
echo "Starting PostgreSQL database..."
$COMPOSE -f docker-compose.frankenphp.yml up -d db

# Wait for DB to be healthy
echo "Waiting for database to be ready..."
MAX_RETRIES=30
RETRY_COUNT=0
# Try to use pg_isready inside the container
until $RUNTIME exec teal-db pg_isready -U teal -d teal > /dev/null 2>&1; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
        echo "Error: Database failed to become ready after ${MAX_RETRIES} attempts"
        exit 1
    fi
    echo "Waiting for database... (attempt $RETRY_COUNT/$MAX_RETRIES)"
    sleep 1
done
echo "Database is ready."

# Run the migration inside the app container (ensure app is running too)
echo "Starting application container..."
$COMPOSE -f docker-compose.frankenphp.yml up -d app

echo "Running migration script inside container..."
# Use compose exec which is more reliable for service names and handles TTY better in script context
$COMPOSE -f docker-compose.frankenphp.yml exec -T app php artisan app:migrate-sqlite-to-pgsql

echo "Migration completed."
