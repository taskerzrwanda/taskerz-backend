FROM php:8.4-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    curl \
    zip \
    unzip \
    git \
    mysql-client \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    net-tools

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm ci && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create necessary directories
RUN mkdir -p /var/run /var/log/nginx

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/start.sh /start.sh

# Make start script executable
RUN chmod +x /start.sh

# Expose port 80
EXPOSE 80

# Start the application
CMD ["/start.sh"]