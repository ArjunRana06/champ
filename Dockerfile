FROM php:8.2-cli

# install system dependencies
RUN apt-get update && apt-get install -y \
    unzip \
    zip \
    curl \
    git

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# copy project
COPY . .

# install dependencies
RUN composer install --no-dev --optimize-autoloader

# expose port
EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000
