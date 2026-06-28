FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    unzip zip curl git libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev nodejs npm

RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader && \
    npm install && npm run build

RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

USER www-data

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
