# 1. Start with Render's official PHP 8.2 Nginx image
FROM render/php:8.2-fpm-nginx

# 2. Switch to the root user to install packages
USER root

# 3. Install Node.js (Using Node 20 LTS as a more modern default)
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 4. Install PHP extensions for PostgreSQL and general Laravel use (bcmath)
RUN docker-php-ext-install pdo_pgsql bcmath

# 5. Install Google Chrome and its dependencies (for Browsershot/Puppeteer)
#    This also cleans up apt-get cache files to reduce image size.
RUN apt-get update && apt-get install -y wget gnupg \
    && wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add - \
    && echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google-chrome.list \
    && apt-get update \
    && apt-get install -y google-chrome-stable libnss3 libatk1.0-0 libatk-bridge2.0-0 libcups2 libdrm2 libdbus-1-3 libatspi2.0-0 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxtst6 libgbm1 libpango-1.0-0 libcairo2 libasound2 \
    && rm -rf /var/lib/apt/lists/*

# 6. Set the working directory
WORKDIR /var/www/html

# 7. Copy your application code into the server
COPY . .

RUN chmod +x /var/www/html/start-render.sh

# 8. Set the correct file permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Install Composer dependencies
#    Run as the 'www-data' user for better security
USER www-data
RUN composer install --no-interaction --no-dev --optimize-autoloader

# 10. Install NPM dependencies and build your assets
#     We also set the env vars here so they are present during 'npm install'
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome-stable
RUN npm install && npm run build

# 11. Cache Laravel's config and routes
RUN php artisan config:cache
RUN php artisan route:cache

# 12. Switch back to root user. The base image's start script expects to run as root.
USER root