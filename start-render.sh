#!/bin/sh
set -e

echo "=== Starting Virac RUMSYS ==="

# 1. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

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

# 5. Test DB
echo "Testing PostgreSQL connection..."
if php artisan db:show --verbose | grep -q "pgsql"; then
    echo "PostgreSQL connected!"
else
    echo "Database connection FAILED! Still using SQLite."
    php artisan db:show --verbose
    exit 1
fi

# 6. Start services
echo "Starting PHP-FPM..."
php-fpm -D

echo "Starting Nginx..."
exec nginx -g 'daemon off;'