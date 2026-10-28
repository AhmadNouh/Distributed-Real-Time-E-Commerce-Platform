FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        postgresql-dev \
        libzip-dev zip unzip \
        libpng-dev libjpeg-turbo-dev freetype-dev \
        git curl \
    && docker-php-ext-install pdo_pgsql zip gd opcache

RUN apk add --no-cache --virtual .builddeps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .builddeps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html