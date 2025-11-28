FROM php:8.3-fpm

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    zip \
    git \
    curl \
    vim \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_mysql gd zip \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure PHP-FPM port
RUN sed -i 's/listen = .*/listen = 9002/' /usr/local/etc/php-fpm.d/zz-docker.conf

# Copy composer files first
COPY composer.json composer.lock ./

# Install dependencies FIRST (vendor folder)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Now copy the full project
COPY . .

# Fix permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 9002
CMD ["php-fpm"]
