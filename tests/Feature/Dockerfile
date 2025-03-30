# Use the official PHP 8.4 FPM image as the base image.
FROM php:8.4-fpm

# Install system dependencies.
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory.
WORKDIR /var/www

# Copy existing application directory contents.
COPY . .

# Install Laravel dependencies.
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions so that www-data can read and write.
RUN chown -R www-data:www-data /var/www

# Expose port 9000 for PHP-FPM.
EXPOSE 9000

# Run the PHP-FPM process.
CMD ["php-fpm"]
