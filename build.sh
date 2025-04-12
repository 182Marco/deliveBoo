#!/usr/bin/env bash

set -e

echo "▶️ Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "▶️ Installing JS dependencies..."
npm install && npm run build

echo "▶️ Setting Laravel permissions..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
