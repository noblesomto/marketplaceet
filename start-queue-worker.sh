#!/bin/bash

# Laravel Queue Worker Startup Script
# This script starts the Laravel queue worker to process follower notifications

echo "╔════════════════════════════════════════════════════╗"
echo "║   Laravel Queue Worker - Follower Notifications   ║"
echo "╚════════════════════════════════════════════════════╝"
echo ""

# Get the directory of this script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$DIR"

# Check if already running
if pgrep -f "queue:work" > /dev/null; then
    echo "⚠️  Queue worker already running!"
    echo ""
    echo "Process info:"
    ps aux | grep "queue:work" | grep -v grep
    echo ""
    echo "To restart:"
    echo "  1. Kill existing: pkill -f 'queue:work'"
    echo "  2. Run this script again"
    exit 1
fi

# Check pending jobs
PENDING=$(php artisan tinker --execute="echo DB::table('jobs')->count();")
FAILED=$(php artisan tinker --execute="echo DB::table('failed_jobs')->count();")

echo "📊 Current Queue Status:"
echo "  • Pending Jobs: $PENDING"
echo "  • Failed Jobs: $FAILED"
echo ""

if [ "$PENDING" -gt 0 ]; then
    echo "✅ Will process $PENDING pending jobs..."
else
    echo "ℹ️  No pending jobs (will monitor for new jobs)"
fi
echo ""

# Ask for confirmation
read -p "Start queue worker? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Cancelled"
    exit 1
fi

# Create logs directory if it doesn't exist
mkdir -p storage/logs

echo ""
echo "🚀 Starting queue worker..."
echo "📝 Log file: storage/logs/queue-worker.log"
echo ""
echo "Commands:"
echo "  • Stop worker: pkill -f 'queue:work'"
echo "  • View logs: tail -f storage/logs/queue-worker.log"
echo "  • Check status: ps aux | grep queue:work"
echo ""

# Start queue worker in background
nohup php artisan queue:work database \
    --sleep=3 \
    --tries=3 \
    --max-time=3600 \
    --timeout=300 \
    >> storage/logs/queue-worker.log 2>&1 &

WORKER_PID=$!

# Wait a moment to check if it started
sleep 2

if ps -p $WORKER_PID > /dev/null; then
    echo "✅ Queue worker started successfully!"
    echo "   Process ID: $WORKER_PID"
    echo ""
    echo "📊 Monitoring for 10 seconds..."
    echo ""

    # Monitor for 10 seconds
    for i in {1..10}; do
        CURRENT_PENDING=$(php artisan tinker --execute="echo DB::table('jobs')->count();" 2>/dev/null)
        echo "   [$i/10] Pending jobs: $CURRENT_PENDING"
        sleep 1
    done

    echo ""
    echo "✅ Queue worker is running!"
    echo ""
    echo "💡 To keep it running permanently, use Supervisor:"
    echo "   See FOLLOWER_NOTIFICATION_FIX.md for setup instructions"

else
    echo "❌ Failed to start queue worker"
    echo "   Check storage/logs/queue-worker.log for errors"
    exit 1
fi
