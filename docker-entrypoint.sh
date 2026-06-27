#!/bin/bash
set -e

PORT=${PORT:-80}
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80\b/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

echo "Apache starting on port ${PORT}..."
exec apache2-foreground
