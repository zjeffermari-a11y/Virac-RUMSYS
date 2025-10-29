#!/bin/sh
set -e

echo "=== Starting Virac RUMSYS ==="

# Clear cached config to use Render env vars
php artisan config:clear
php artisan cache:clear

# Run migrations with PostgreSQL
echo "Running migrations..."
php artisan migrate --force

# Storage link
echo "Linking storage..."
php artisan storage:link

# Create logs directory
mkdir -p storage/logs
touch storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log

# Test DB connection
echo "Testing PostgreSQL connection..."
if php artisan db:show; then
    echo "✅ PostgreSQL connected!"
else
    echo "❌ Database connection FAILED!"
    exit 1
fi

# Start services
echo "Starting PHP-FPM..."
php-fpm -D

echo "Starting Nginx..."
exec nginx -g 'daemon off;'