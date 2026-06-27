#!/bin/bash
WORKER_NAME="ai-study-assistant-worker"
ARTISAN="/data/html/AI Study Assistant for Students/artisan"
LOG="/data/html/AI Study Assistant for Students/storage/logs/worker-watchdog.log"

if ! pgrep -f "artisan queue:work" > /dev/null; then
    echo "$(date): Worker not running, starting..." >> "$LOG"
    nohup php "$ARTISAN" queue:work --sleep=3 --tries=3 --max-time=3600 >> "$LOG" 2>&1 &
    echo "$(date): Worker started with PID $!" >> "$LOG"
fi
