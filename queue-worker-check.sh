#!/bin/bash
WORKER_SCRIPT="$(cd "$(dirname "$0")" && pwd)/queue-worker.sh"
LOGFILE="$(cd "$(dirname "$0")" && pwd)/storage/logs/cron.log"

if ! pgrep -f "queue:work" > /dev/null 2>&1; then
    echo "[$(date)] Queue worker not running, starting..." >> "$LOGFILE"
    nohup bash "$WORKER_SCRIPT" > /dev/null 2>&1 &
    echo "[$(date)] Queue worker started (PID: $!)" >> "$LOGFILE"
fi
