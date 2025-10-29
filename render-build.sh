#!/usr/bin/env bash
# Exit on error
set -o errexit

# Install dependencies
composer install --no-dev --no-interaction --optimize-autoloader

# === ADD THESE TWO LINES ===
npm install
npm run build

# Generate application key if it doesn't exist
if [ -z "$APP_KEY" ]; then
  php artisan key:generate --force
fi

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Run database migrations
php artisan migrate --force

php artisan db:seed --force