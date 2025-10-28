# 1. Start with the official PHP 8.2 FPM image
FROM php:8.2-fpm

# 2. Switch to the root user to install packages
USER root

# 3. Install Node.js (Using Node 20 LTS)
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 3a. Install Nginx and other basic dependencies
RUN apt-get update && apt-get install -y nginx curl gnupg \
    && rm -rf /var/lib/apt/lists/*

# 3b. Install PostgreSQL development libraries (REQUIRED for pdo_pgsql)
RUN apt-get update && apt-get install -y libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# 4. Install PHP extensions for PostgreSQL and general Laravel use
RUN docker-php-ext-install pdo_pgsql bcmath

# 5. Install Google Chrome and its dependencies (for Browsershot)
RUN apt-get update && apt-get install -y wget gnupg ca-certificates \
    && mkdir -p -m 755 /etc/apt/keyrings \
    && wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | gpg --dearmor -o /etc/apt/keyrings/google-chrome.gpg \
    && echo "deb [arch=amd64 signed-by=/etc/apt/keyrings/google-chrome.gpg] http://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google-chrome.list \
    && apt-get update \
    && apt-get install -y google-chrome-stable libnss3 libatk1.0-0 libatk-bridge2.0-0 libcups2 libdrm2 libdbus-1-3 libatspi2.0-0 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxtst6 libgbm1 libpango-1.0-0 libcairo2 libasound2 \
    && rm -rf /var/lib/apt/lists/*

# 6. Set the working directory
WORKDIR /var/www/html

# 7. Copy your application code into the server
COPY . .

# 7a. Make our new start script executable
RUN chmod +x /var/www/html/start-render.sh

# 8. Set the correct file permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Install Composer dependencies
USER www-data
RUN composer install --no-interaction --no-dev --optimize-autoloader

# 10. Install NPM dependencies and build your assets
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome-stable
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