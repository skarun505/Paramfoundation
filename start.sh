#!/usr/bin/env bash

echo "=== ParSEC Startup ==="
echo "PORT=$PORT"
echo "APP_ENV=$APP_ENV"
echo "DB_HOST=$DB_HOST"
echo "DB_DATABASE=$DB_DATABASE"

# ------------------------------------------------------------------
# 1. Clear any cached config that might be stale
# ------------------------------------------------------------------
echo "[1/7] Clearing stale cache..."
php artisan config:clear  2>/dev/null || true
php artisan route:clear   2>/dev/null || true
php artisan view:clear    2>/dev/null || true

# ------------------------------------------------------------------
# 2. Ensure storage/logs is writable
# ------------------------------------------------------------------
echo "[2/7] Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ------------------------------------------------------------------
# 3. Run migrations with retry (DB may need a moment to be ready)
# ------------------------------------------------------------------
echo "[3/7] Running migrations..."
MIGRATED=false
for i in 1 2 3 4 5; do
    if php artisan migrate --force; then
        MIGRATED=true
        echo "Migrations succeeded on attempt $i"
        break
    else
        echo "Migration attempt $i failed — waiting 5s..."
        sleep 5
    fi
done

if [ "$MIGRATED" = false ]; then
    echo "ERROR: All migration attempts failed. Check DB credentials."
    echo "DB_HOST=$DB_HOST  DB_DATABASE=$DB_DATABASE  DB_USERNAME=$DB_USERNAME"
fi

# ------------------------------------------------------------------
# 4. Storage symlink
# ------------------------------------------------------------------
echo "[4/7] Linking storage..."
php artisan storage:link --force 2>/dev/null || true

# ------------------------------------------------------------------
# 5. Seed slots if table is empty
# ------------------------------------------------------------------
echo "[5/7] Seeding slots..."
SLOT_COUNT=$(php artisan tinker --execute="echo \App\Models\Slot::count();" 2>/dev/null | tail -1 || echo "0")
echo "Current slot count: $SLOT_COUNT"
if [ "$SLOT_COUNT" = "0" ]; then
    php artisan db:seed --class=SlotSeeder --force 2>/dev/null || true
fi

# ------------------------------------------------------------------
# 6. Cache for production performance
# ------------------------------------------------------------------
echo "[6/7] Caching config/routes/views..."
php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true

# ------------------------------------------------------------------
# 7. Start server
# ------------------------------------------------------------------
echo "[7/7] Starting Laravel on 0.0.0.0:$PORT ..."
exec php artisan serve --host=0.0.0.0 --port="$PORT"
