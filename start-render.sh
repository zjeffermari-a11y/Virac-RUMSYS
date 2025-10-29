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
echo "Laravel config (after cache clear):"
echo "Current DB Connection: $(php artisan tinker --execute='echo config("database.default");')"

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

# 4. Run migrations (fresh to fix schema)
echo "Running migrations..."
# Change this line back:
php artisan migrate --force


# 5. Storage link
echo "Linking storage..."
php artisan storage:link || echo "Storage already linked"

# 6. Cache config for better performance (with actual runtime env vars)
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Set proper permissions
chown -R www-data:www-data storage bootstrap/cache

# 8. Start PHP-FPM
echo "Starting PHP-FPM..."
php-fpm -D

# 9. Start Nginx (foreground to keep container running)
echo "Starting Nginx..."
echo "=== Application ready at port 80 ==="
exec nginx -g 'daemon off;'