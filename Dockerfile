# 1. Start with the official PHP 8.2 FPM image
FROM php:8.2-fpm

# 2. Switch to the root user to install packages
USER root

# 3, 4, 5. Install OS dependencies, PHP extensions, Node.js, and Google Chrome
RUN apt-get update && apt-get install -y \
    nginx \
    curl \
    gnupg \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    wget \
    ca-certificates \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    \
    # Install PHP extensions
    && docker-php-ext-install pdo_pgsql bcmath zip intl \
    \
    # Install Node.js
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    \
    # Install Chrome
    && mkdir -p -m 755 /etc/apt/keyrings \
    && wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | gpg --dearmor -o /etc/apt/keyrings/google-chrome.gpg \
    && echo "deb [arch=amd64 signed-by=/etc/apt/keyrings/google-chrome.gpg] http://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google-chrome.list \
    && apt-get update \
    && apt-get install -y google-chrome-stable \
        libnss3 libatk1.0-0 libatk-bridge2.0-0 libcups2 libdrm2 \
        libdbus-1-3 libatspi2.0-0 libxcomposite1 libxcursor1 \
        libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 \
        libxtst6 libgbm1 libpango-1.0-0 libcairo2 libasound2 \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 6. Set the working directory
WORKDIR /var/www/html

# 7. Copy your application code into the server
COPY . .

# 7a. Make our new start script executable
RUN chmod +x /var/www/html/start-render.sh

# 8. Set the correct file permissions for the entire application
RUN chown -R www-data:www-data /var/www/html

# 9. Install Composer dependencies
USER www-data
# Set APP_ENV to prevent database operations during build
ENV APP_ENV=production

# ADD THIS LINE TO FIX SQLITE MIGRATIONS
RUN composer require doctrine/dbal

# Skip discovery to avoid database queries during composer install
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# CRITICAL: Delete cached service providers before running ANY artisan commands
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php

# Now run package discovery to rebuild the cache without dev packages
RUN php artisan package:discover --ansi

# 10. Install NPM dependencies and build your assets
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome-stable
# Set npm cache to a directory www-data can write to
ENV npm_config_cache=/tmp/.npm
RUN npm install && npm run build

# 11. Cache Laravel's config and routes
RUN php artisan route:cache

# 12. Copy Nginx configuration and expose port
USER root
COPY nginx.conf /etc/nginx/sites-available/default
EXPOSE 80

# 13. Set the start script as the entry point
CMD ["/var/www/html/start-render.sh"]