#!/usr/bin/env bash
set -e

echo "=== ParSEC Startup ==="
echo "PORT: $PORT"

# Cache config first (uses env vars directly, no DB needed)
echo "[1/6] Caching config..."
php artisan config:cache

echo "[2/6] Caching routes..."
php artisan route:cache

echo "[3/6] Caching views..."
php artisan view:cache

# Run migrations — retry up to 3 times (DB might not be ready instantly)
echo "[4/6] Running migrations..."
for i in 1 2 3; do
    php artisan migrate --force && break || {
        echo "Migration attempt $i failed, retrying in 5s..."
        sleep 5
    }
done

# Storage link (ignore if already exists)
echo "[5/6] Linking storage..."
php artisan storage:link --force 2>/dev/null || true

# Seed slots if none exist
echo "[6/6] Checking seed data..."
php artisan db:seed --class=SlotSeeder --force 2>/dev/null || true

echo "=== Starting server on 0.0.0.0:$PORT ==="
exec php artisan serve --host=0.0.0.0 --port="$PORT"
