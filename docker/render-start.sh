#!/usr/bin/env bash
set -e

PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "Checking Vite assets..."
ls -la public/build || true
ls -la public/build/assets || true
test -f public/build/manifest.json

echo "Starting OCR Python service..."
/opt/ocr-venv/bin/python -m uvicorn app:app --host 127.0.0.1 --port 8010 --app-dir ocr_service &
OCR_PID="$!"
sleep 3
echo "OCR Python service started with PID ${OCR_PID}"

php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --force

apache2-foreground
