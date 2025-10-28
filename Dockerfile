# 1. Start with the official PHP 8.2 FPM image
FROM php:8.2-fpm

# 2. Switch to the root user to install packages
USER root

# 3, 4, 5. Install OS dependencies, PHP extensions, Node.js, and Google Chrome
RUN apt-get update && apt-get install -y \
    # Packages from 3a & 3b
    nginx \
    curl \
    gnupg \
    libpq-dev \
    # Add libzip-dev for zip PHP extension
    libzip-dev \
    # Packages for Chrome install (5)
    wget \
    ca-certificates \
    # Add git and unzip for Composer
    git \
    unzip \
    # Clean up apt cache *before* installing PHP extensions
    && rm -rf /var/lib/apt/lists/* \
    \
    # 4. Install PHP extensions
    # Add 'zip' for Composer
    && docker-php-ext-install pdo_pgsql bcmath zip \
    \
    # Install Node.js 20.x (LTS)
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    \
    # 5. Set up Google Chrome repo
    && mkdir -p -m 755 /etc/apt/keyrings \
    && wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | gpg --dearmor -o /etc/apt/keyrings/google-chrome.gpg \
    && echo "deb [arch=amd64 signed-by=/etc/apt/keyrings/google-chrome.gpg] http://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google-chrome.list \
    \
    # Install Chrome and its dependencies
    && apt-get update \
    && apt-get install -y google-chrome-stable \
    libnss3 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libdbus-1-3 \
    libatspi2.0-0 \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxext6 \
    libxfixes3 \
    libxi6 \
    libxrandr2 \
    libxtst6 \
    libgbm1 \
    libpango-1.0-0 \
    libcairo2 \
    libasound2 \
    # Final cleanup
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 6. Set the working directory
WORKDIR /var/www/html

# 7. Copy your application code into the server
COPY . .

# 7a. Make our new start script executable
RUN chmod +x /var/www/html/start-render.sh

# 8. Set the correct file permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 8a. Create database directory and empty SQLite file for build process
RUN mkdir -p /var/www/html/database && \
    touch /var/www/html/database/database.sqlite && \
    chown -R www-data:www-data /var/www/html/database

# 9. Install Composer dependencies
USER www-data
# Set APP_ENV to prevent database operations during build
ENV APP_ENV=production
ENV DB_CONNECTION=sqlite
# Skip discovery to avoid database queries during composer install
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts
# Run package discovery separately with proper environment
RUN php artisan package:discover --ansi || true

# 10. Install NPM dependencies and build your assets
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome-stable
# Set npm cache to a directory www-data can write to
ENV npm_config_cache=/tmp/.npm
RUN npm install && npm run build

# 11. Cache Laravel's config and routes
RUN php artisan config:cache
RUN php artisan route:cache

# 12. Copy Nginx configuration and expose port
USER root
COPY nginx.conf /etc/nginx/sites-available/default
EXPOSE 80

# 13. Set the start script as the entry point
CMD ["/var/www/html/start-render.sh"]