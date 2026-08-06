FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --no-dev --optimize

FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        libonig-dev libcurl4-openssl-dev libzip-dev unzip \
    && docker-php-ext-install mbstring curl zip pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor /app /var/www/html
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && cp .env.example .env \
    && php artisan key:generate --force \
    && php artisan config:clear

EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
