#!/bin/sh
set -e # Exit immediately if a command exits with a non-zero status.

echo "Running Laravel migrations..."
php artisan migrate --force

echo "Linking storage..."
php artisan storage:link

# Execute the original start script from the base Docker image
echo "Starting Nginx and PHP-FPM..."
/usr/bin/start.shs