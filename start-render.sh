#!/bin/sh
set -e

echo "Running Laravel migrations..."
php artisan migrate --force

echo "Linking storage..."
php artisan storage:link

# Output any errors to console
tail -f storage/logs/laravel.log &

echo "Testing database connection..."
php artisan db:show || echo "Database connection failed!"

# Start PHP-FPM in the background
echo "Starting PHP-FPM..."
php-fpm -D

# Start Nginx in the foreground (this keeps the container running)
echo "Starting Nginx..."
nginx -g 'daemon off;'