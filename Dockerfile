# Dockerfile
FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev zip libonig-dev \
    && docker-php-ext-install pdo pdo_pgsql zip opcache

RUN pecl install excimer

# Enable OPcache for performance
COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy app
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader \
 && chown -R www-data:www-data /var/www

# Use non-root user
USER www-data

CMD ["php-fpm"]