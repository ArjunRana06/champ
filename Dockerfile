FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    unzip zip curl git libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev nodejs npm

RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf

RUN composer install --no-dev --optimize-autoloader && \
    npm install && npm run build

RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]
