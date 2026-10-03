FROM php:8.3-fpm-alpine AS php

RUN apk add --no-cache oniguruma \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS oniguruma-dev \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY bootstrap.php ./
COPY config ./config
COPY src ./src
COPY routes ./routes
COPY templates ./templates
COPY database ./database
COPY public ./public
COPY docker/php-entrypoint.sh /usr/local/bin/app-entrypoint

RUN composer dump-autoload --no-dev --optimize \
    && mkdir -p var/smarty/compile var/smarty/cache \
    && chown -R www-data:www-data var \
    && chmod +x /usr/local/bin/app-entrypoint

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]

FROM caddy:2-alpine AS caddy

COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY --from=php /var/www/html/public /var/www/html/public
