#!/bin/bash

# Kill all processes when script exits (Ctrl+C)
trap 'kill 0' EXIT

echo "Starting PHP server, Vite/Tailwind, and Queue worker..."

php artisan serve --port=8030 &
npm run dev &
php artisan queue:work &

wait
