#!/usr/bin/env bash
# Production deploy helper — run from project root after git pull
set -euo pipefail

echo "→ Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "→ Installing Node dependencies..."
if command -v npm >/dev/null 2>&1; then
    npm ci
    npm run build
else
    echo "ERROR: npm is required to build CSS/JS (public/build is not in git)."
    exit 1
fi

if [ ! -f public/build/manifest.json ]; then
    echo "ERROR: public/build/manifest.json missing after npm run build."
    exit 1
fi

echo "→ Running migrations..."
php artisan migrate --force

echo "→ Clearing caches..."
php artisan config:clear
php artisan view:clear
php artisan cache:clear

echo "→ Optimizing..."
php artisan config:cache
php artisan view:cache
php artisan route:cache

echo "✓ Deploy complete. Hard-refresh the browser (Cmd+Shift+R)."
