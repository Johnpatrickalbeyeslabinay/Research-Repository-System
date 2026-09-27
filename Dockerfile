FROM php:8.4-fpm

WORKDIR /var/www

# System dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    curl \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.js 22
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# Copy application
COPY . /var/www

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# Install frontend dependencies and build
RUN npm ci \
    && npm run build

# Laravel permissions
RUN chown -R www-data:www-data \
    /var/www/storage \
    /var/www/bootstrap/cache

# Nginx configuration
COPY docker/nginx.conf /etc/nginx/sites-available/default

EXPOSE 10000

CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan view:cache && php-fpm -D && nginx -g 'daemon off;'"]
