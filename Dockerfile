# ===== Builder Stage =====
# This stage installs dependencies
FROM composer:2 as builder

# Set the working directory
WORKDIR /app

# Copy composer files and install dependencies for production
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Copy the rest of the application code
COPY . .


# ===== Final Stage =====
# This stage builds the final, smaller production image
FROM php:8.1-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install required system packages and PHP extensions
RUN apk add --no-cache \
        freetype-dev \
        busybox-cron \
        libjpeg-turbo-dev \
        libpng-dev \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip mbstring

# Copy application code and dependencies from the builder stage
COPY --from=builder /app .

# Set correct permissions for storage and logs
# The user 'www-data' is the default user for php-fpm
RUN mkdir -p uploads logs backups \
    && chown -R www-data:www-data uploads logs backups \
    && chmod -R 775 uploads logs backups

# Expose port 9000 and start php-fpm server
EXPOSE 9000