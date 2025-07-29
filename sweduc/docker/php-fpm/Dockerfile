FROM php:8-fpm

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install modules
RUN apt-get update && apt-get install -y \
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
    && docker-php-ext-configure gd --with-freetype --with-webp --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

CMD ["php-fpm"]
