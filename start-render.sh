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

echo "Checking migration status..."
php artisan migrate:status

# 5. SEED DATABASE IF EMPTY
echo "=== Seeding database ==="
php artisan db:seed --force --verbose
echo "✓ Seeding completed!"

# Get user count, strip all whitespace and non-numeric chars
USER_COUNT=$(php artisan tinker --execute="echo DB::table('users')->count();" 2>&1 | tr -d '\n\r\t ' | grep -o '[0-9]*' | head -1)

# Default to 0 if extraction failed
if [ -z "$USER_COUNT" ]; then
    USER_COUNT=0
fi

echo "User count detected: $USER_COUNT"

# Numeric comparison (safer than string comparison)
if [ "$USER_COUNT" -eq 0 ] 2>/dev/null; then
    echo "⚠️  Database has 0 users! Starting seed..."
    php artisan db:seed --force --verbose
    
    # Verify
    NEW_COUNT=$(php artisan tinker --execute="echo DB::table('users')->count();" 2>&1 | tr -d '\n\r\t ' | grep -o '[0-9]*' | head -1)
    echo "✓ Seeding complete! Database now has $NEW_COUNT users."
else
    echo "✓ Database has $USER_COUNT users. Skipping seed."
fi

# 6. Storage link
echo "Linking storage..."
php artisan storage:link || true

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
echo "=== Application ready at port 80 ==="
exec nginx -g 'daemon off;'