#!/bin/bash
set -euo pipefail

# ── Oracle Cloud Free Tier — Laravel Deployment Script ──
# Run this on a fresh Oracle Cloud Ubuntu 22.04/24.04 VM.
# Usage: chmod +x setup.sh && sudo ./setup.sh

REPO_URL="https://github.com/ArjunRana06/champ.git"
APP_DIR="/var/www/html/ai-study-assistant"
DB_NAME="ai_study_assistant"
DB_USER="laravel"
DB_PASS="$(openssl rand -base64 24)"
APP_KEY=""
DOMAIN="${1:-localhost}"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}[1/12] System update & prerequisites${NC}"
export DEBIAN_FRONTEND=noninteractive
apt-get update && apt-get upgrade -y
apt-get install -y software-properties-common curl git unzip zip \
    nginx certbot python3-certbot-nginx mysql-server-8.0 \
    cron supervisor redis-server

echo -e "${GREEN}[2/12] PHP 8.3 + extensions${NC}"
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y php8.3-{fpm,cli,common,mysql,mbstring,xml,curl,gd,bcmath,zip,intl,readline,tokenizer,pgsql,redis} \
    php8.3-{imagick}

echo -e "${GREEN}[3/12] Composer${NC}"
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

echo -e "${GREEN}[4/12] Node.js${NC}"
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

echo -e "${GREEN}[5/12] MySQL database${NC}"
mysql <<SQL
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo -e "${GREEN}[6/12] Clone application${NC}"
git clone ${REPO_URL} ${APP_DIR}
cd ${APP_DIR}

echo -e "${GREEN}[7/12] Environment configuration${NC}"
cp .env.example .env
# Generate APP_KEY
php artisan key:generate --force

# Read the generated key
APP_KEY=$(grep ^APP_KEY .env | cut -d= -f2-)

# Write production .env
cat > .env <<ENVEOF
APP_NAME=AI-Study-Assistant-for-Students
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=https://${DOMAIN}

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS=ranaarjun9814@gmail.com
MAIL_FROM_NAME="AI Study Assistant for Students"

OPENROUTER_API_KEY=
OPENROUTER_MODEL=meta-llama/llama-3.3-70b-instruct:free
OPENROUTER_EMBEDDING_MODEL=openai/text-embedding-3-small
ENVEOF

chown -R www-data:www-data .env

echo -e "${GREEN}[8/12] Install PHP dependencies${NC}"
composer install --no-dev --optimize-autoloader

echo -e "${GREEN}[9/12] Build frontend assets${NC}"
npm install && npm run build

echo -e "${GREEN}[10/12] Permissions & storage${NC}"
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo -e "${GREEN}[11/12] Nginx virtual host${NC}"
cat > /etc/nginx/sites-available/${DOMAIN} <<NGINXEOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    root ${APP_DIR}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 50M;
}
NGINXEOF

ln -sf /etc/nginx/sites-available/${DOMAIN} /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

echo -e "${GREEN}[12/12] Queue worker (supervisor)${NC}"
mkdir -p /var/log/laravel

cat > /etc/supervisor/conf.d/laravel-worker.conf <<SUPERVISOREOF
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/laravel/worker.log
stopwaitsecs=3600
SUPERVISOREOF

cat > /etc/supervisor/conf.d/laravel-scheduler.conf <<SUPERVISOREOF
[program:laravel-scheduler]
process_name=%(program_name)s
command=php ${APP_DIR}/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/laravel/scheduler.log
SUPERVISOREOF

supervisorctl reread && supervisorctl update && supervisorctl start all

# ── Migrate ──
php artisan migrate --force
php artisan storage:link

echo ""
echo -e "${YELLOW}══════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Deployment complete!${NC}"
echo -e "${YELLOW}══════════════════════════════════════════════════${NC}"
echo ""
echo -e "  Website:    ${GREEN}http://${DOMAIN}${NC}"
echo -e "  DB name:    ${DB_NAME}"
echo -e "  DB user:    ${DB_USER}"
echo -e "  DB pass:    ${DB_PASS}"
echo ""
echo -e "${YELLOW}  IMPORTANT:${NC}"
echo -e "  1. Set your OPENROUTER_API_KEY in ${APP_DIR}/.env"
echo -e "  2. Run: sudo certbot --nginx -d ${DOMAIN}  (for HTTPS)"
echo -e "  3. Configure firewall: sudo ufw allow 22,80,443/tcp"
echo ""
