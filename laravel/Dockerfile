# Build stage
FROM php:8.4-fpm-alpine AS base

# Set working directory
WORKDIR /var/www/html

# Use faster Alpine mirrors and install system dependencies
RUN set -eux; \
    # Use multiple mirrors for better reliability
    echo "https://dl-cdn.alpinelinux.org/alpine/v3.22/main" > /etc/apk/repositories; \
    echo "https://dl-cdn.alpinelinux.org/alpine/v3.22/community" >> /etc/apk/repositories; \
    echo "http://mirror.leaseweb.com/alpine/v3.22/main" >> /etc/apk/repositories; \
    echo "http://mirror.leaseweb.com/alpine/v3.22/community" >> /etc/apk/repositories; \
    # Update package index with timeout
    apk update --timeout 30; \
    # Install build dependencies for PECL
    apk add --no-cache --timeout 60 \
        autoconf \
        gcc \
        g++ \
        make \
        linux-headers; \
    # Install PHP dependencies
    apk add --no-cache --timeout 60 \
        postgresql-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev; \
    # Install general utilities
    apk add --no-cache --timeout 60 \
        git \
        curl \
        zip \
        unzip \
        bash \
        supervisor

# Install PHP extensions
RUN set -eux; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache

# Install Redis extension separately
RUN set -eux; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    rm -rf /tmp/pear; \
    # Remove build dependencies to reduce image size
    apk del autoconf gcc g++ make linux-headers

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www/html

# Production stage
FROM base AS production

# Copy custom PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Install dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-progress

# Set permissions
RUN chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Switch to www user
USER www

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]

# Development stage
FROM base AS development

# Copy custom PHP configuration for development
COPY docker/php/php-dev.ini /usr/local/etc/php/conf.d/custom.ini

# Install Xdebug for development (use latest version for PHP 8.4 support)
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Set permissions
RUN chown -R www:www /var/www/html

# Switch to www user
USER www

EXPOSE 9000

CMD ["php-fpm"]
