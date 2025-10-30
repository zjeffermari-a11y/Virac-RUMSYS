#!/bin/sh
set -e

echo "=== Starting Virac RUMSYS ==="

# 1. CLEAR any stale cached config from build time
echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 2. Verify environment variables are loaded
echo "=== Environment Variables Check ==="
echo "DB_CONNECTION env var: ${DB_CONNECTION:-NOT SET}"
echo "DB_HOST env var: ${DB_HOST:-NOT SET}"
echo "DB_DATABASE env var: ${DB_DATABASE:-NOT SET}"
echo "DB_USERNAME env var: ${DB_USERNAME:-NOT SET}"
echo ""

# 3. Test database connection
echo "Testing PostgreSQL connection..."
if php artisan db:show; then
    echo "✓ PostgreSQL connected successfully!"
else
    echo "✗ Database connection FAILED!"
    echo "Showing environment for debugging:"
    php artisan config:show database
    exit 1
fi

# 4. Run migrations
echo "Running migrations and seeders..."
php artisan migrate:fresh --seed --force

echo "Checking migration status..."
php artisan migrate:status

# 5. **SEED DATABASE IF EMPTY**
echo "Checking if database needs seeding..."
# Check if users table exists first
TABLE_EXISTS=$(php artisan tinker --execute="try { DB::table('users')->count(); echo 'EXISTS'; } catch (\Exception \$e) { echo 'NOT_EXISTS'; }" 2>/dev/null || echo "NOT_EXISTS")

if [ "$TABLE_EXISTS" = "NOT_EXISTS" ]; then
    echo "⚠️  Users table doesn't exist. Running migrations again..."
    php artisan migrate:fresh --force
    echo "✓ Migrations completed!"
fi

# 6. Storage link
echo "Linking storage..."
php artisan storage:link || echo "Storage already linked"

# 7. Cache config for better performance (with actual runtime env vars)
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Set proper permissions
chown -R www-data:www-data storage bootstrap/cache

# 9. Start PHP-FPM
echo "Starting PHP-FPM..."
php-fpm -D

# 10. Start Nginx (foreground to keep container running)
echo "Starting Nginx..."
echo "=== Application ready at port 80 ==="
exec nginx -g 'daemon off;'