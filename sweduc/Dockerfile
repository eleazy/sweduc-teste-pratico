FROM php:8.2-apache

# Install modules
RUN apt-get update && apt-get install -y \
    git \
    libicu-dev \
    default-mysql-client \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install iconv \
    && docker-php-ext-install intl \
    && docker-php-ext-install opcache \
    && docker-php-ext-install fileinfo \
    && docker-php-ext-install exif \
    && docker-php-ext-install zip \
    && docker-php-ext-install calendar \
    && docker-php-ext-configure gd --with-freetype --with-webp --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN a2enmod rewrite
RUN service apache2 restart

ENV APACHE_DOCUMENT_ROOT /app/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY ./docker/php-fpm/php.production.ini "$PHP_INI_DIR/php.ini"

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy project
COPY --chown=www-data:www-data ./ /app
WORKDIR /app

# Install dependencies
RUN composer install \
    -o \
    --working-dir=/app \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist
