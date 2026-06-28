#!/bin/bash
set -e

cd /var/www/html

# Create .env if it doesn't exist
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
    php artisan key:generate --force || true
fi

# Create all required storage directories
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache

# Create storage symlink if not present
php artisan storage:link --force 2>/dev/null || true

# Run database migrations (safe for production with --force)
php artisan migrate --force

# Clear stale compiled views and config cache
php artisan view:clear
php artisan config:clear

exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
