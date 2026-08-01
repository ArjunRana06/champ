#!/bin/bash
cd "$(dirname "$0")"
php artisan queue:work --sleep=3 --tries=3 --max-time=72000 >> storage/logs/worker.log 2>&1
