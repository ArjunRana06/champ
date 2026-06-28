#!/bin/bash
set -e

cd /var/www/html

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        cat > .env <<EOF
APP_NAME=AI-Study-Assistant
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ai-study-assistant-sf50.onrender.com
LOG_LEVEL=warning
DB_CONNECTION=pgsql
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
EOF
    fi
    php artisan key:generate --force
fi

mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache

exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
