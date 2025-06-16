FROM php:8.4-cli-alpine

RUN apk add --no-cache tini
ENTRYPOINT ["/sbin/tini", "-s", "--"]

RUN apk add --no-cache \
        icu-dev libzip-dev oniguruma-dev zlib-dev git unzip \
    && docker-php-ext-install intl zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.* ./
RUN composer install --no-scripts --no-autoloader --no-interaction
COPY . .
RUN composer dump-autoload --optimize \
    && composer run-script post-install-cmd
