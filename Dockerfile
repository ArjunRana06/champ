FROM php:8.3.7-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    unzip zip curl git

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000
