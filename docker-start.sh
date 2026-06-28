#!/bin/bash
set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache

exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
