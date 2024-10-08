FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /var/www

RUN chown -R www-data:www-data /var/www

USER www-data

EXPOSE 8000
