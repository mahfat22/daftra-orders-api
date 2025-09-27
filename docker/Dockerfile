# Use PHP 8.2 FPM as base image
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libgd-dev \
    libmagickwand-dev \
    zip \
    unzip \
    supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    xml \
    bcmath \
    zip \
    exif \
    pcntl \
    intl \
    gd

# Install PECL extensions
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www

# Copy supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create supervisor log directory and set permissions
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /var/www/storage/logs \
    && chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Expose port 9000 and start supervisord
EXPOSE 9000
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
