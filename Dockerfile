FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libsqlite3-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure Apache & PHP
RUN a2enmod rewrite headers

# Configure Apache security settings
RUN sed -i 's/^ServerTokens .*/ServerTokens Prod/' /etc/apache2/conf-available/security.conf && \
    sed -i 's/^ServerSignature .*/ServerSignature Off/' /etc/apache2/conf-available/security.conf

# Set working directory
WORKDIR /var/www/html

# Configure PHP
RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory-limit.ini && \
    echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "error_reporting=E_ALL" >> /usr/local/etc/php/conf.d/error-reporting.ini && \
    echo "display_errors=On" >> /usr/local/etc/php/conf.d/error-display.ini

# Create test PHP file
RUN echo "<?php phpinfo(); ?>" > /var/www/html/info.php

# Create database directory and set permissions
RUN mkdir -p /var/www/html/database && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Copy and enable Apache configuration
COPY apache-config/httpd-vhosts.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default.conf

# Copy composer.json
COPY composer.json ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader

# Copy the rest of the application
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Expose port
EXPOSE 80

# Use the default entrypoint from the base image
CMD ["apache2-foreground"] 