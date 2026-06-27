# Use the official PHP 8.1 FPM image as a base
FROM php:8.1-fpm-alpine as base

# Set the working directory in the container
WORKDIR /var/www/html

# Install system dependencies needed for extensions
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip

# --- Development Stage ---
FROM base as dev

# Copy composer from the official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install development-specific dependencies
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del $PHPIZE_DEPS