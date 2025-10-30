#!/bin/sh
set -e

echo "=== Starting Virac RUMSYS ==="

# 1. CLEAR any stale cached config from build time
echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 2. Verify environment variables
echo "=== Environment Variables Check ==="
echo "DB_CONNECTION env var: ${DB_CONNECTION:-NOT SET}"
echo "DB_HOST env var: ${DB_HOST:-NOT SET}"
echo "DB_DATABASE env var: ${DB_DATABASE:-NOT SET}"
echo ""

# 3. Test database connection
echo "Testing PostgreSQL connection..."
if php artisan db:show; then
    echo "✓ PostgreSQL connected successfully!"
else
    echo "✗ Database connection FAILED!"
    exit 1
fi

# 4. Run migrations
echo "=== Running Migrations ==="
php artisan migrate --force


# 7. Cache config
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Set permissions
chown -R www-data:www-data storage bootstrap/cache

# 9. Start PHP-FPM
echo "Starting PHP-FPM..."
php-fpm -D

# 10. Start Nginx
echo "Starting Nginx..."
echo "=== Application ready at port ${PORT} ==="
exec nginx -g 'daemon off;'