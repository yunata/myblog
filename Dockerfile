FROM php:8.3-apache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

WORKDIR /var/www/html
