FROM php:8.4-cli

# Laravel + MySQL に必要な拡張と、カバレッジ計測用の pcov
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev libicu-dev \
    && docker-php-ext-install pdo_mysql zip intl bcmath \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

EXPOSE 8000

CMD ["sh", "-c", "[ -d vendor ] || composer install; php artisan serve --host=0.0.0.0 --port=8000"]
