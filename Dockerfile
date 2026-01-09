FROM php:8.2-fpm-alpine AS base

RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    supervisor \
    nginx

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    zip \
    gd \
    intl \
    mbstring \
    opcache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

FROM base AS test-app

WORKDIR /var/www/html

RUN composer create-project laravel/laravel . --prefer-dist --no-interaction --no-dev

RUN composer require cslant/laravel-telegram-git-notifier --with-all-dependencies

RUN mkdir -p storage/app/vendor/tg-notifier/jsons \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
