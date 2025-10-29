#!/bin/sh
set -e

echo "=== Starting Virac RUMSYS ==="

# 1. CLEAR CACHED CONFIG (CRITICAL!)
php artisan config:clear
php artisan cache:clear

# 2. Run migrations
echo "Running migrations..."
php artisan migrate --force

# 3. Storage link
echo "Linking storage..."
php artisan storage:link

# 4. Create logs
mkdir -p storage/logs
touch storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log

# 5. TEST DB WITH FRESH CONFIG
echo "Testing PostgreSQL connection..."
php artisan config:clear  # ENSURE FRESH ENV
if php artisan db:show --verbose; then
    echo "PostgreSQL connected!"
else
    echo "Database connection FAILED!"
    exit 1
fi

# 6. Start services
echo "Starting PHP-FPM..."
php-fpm -D

echo "Starting Nginx..."
exec nginx -g 'daemon off;'