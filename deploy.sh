#!/usr/bin/env bash

# Ploi deployment script for PW Online Status
# Usage: run from the project root on the server

set -e

echo "🚀 Starting deployment..."

# Ensure we're in the project root
if [ ! -f "artisan" ]; then
    echo "❌ artisan not found. Please run this script from the project root."
    exit 1
fi

# Pull the latest code (optional: Ploi usually does this before running the script)
# git pull origin main

echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

echo "📦 Installing Node dependencies..."
npm ci

echo "🔑 Ensuring application key exists..."
if [ -z "${APP_KEY}" ]; then
    if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then
        php artisan key:generate --quiet
    fi
fi

echo "⚙️  Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🗄️  Running migrations..."
php artisan migrate --force --no-interaction

echo "🎨 Building frontend assets..."
npm run build

echo "🧹 Optimising storage..."
php artisan storage:link --quiet || true

echo "✅ Deployment complete!"
