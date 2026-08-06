FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git libonig-dev libcurl4-openssl-dev libzip-dev zlib1g-dev libsqlite3-dev unzip \
    && docker-php-ext-install mbstring curl zip pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN chmod -R 775 storage bootstrap/cache \
    && composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --optimize-autoloader \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/app/qrcodes \
    && cp .env.example .env \
    && php artisan package:discover --ansi \
    && php artisan key:generate --force \
    && php artisan config:clear

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]