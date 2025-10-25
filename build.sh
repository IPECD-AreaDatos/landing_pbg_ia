#!/bin/bash

# Install dependencies
composer install --no-dev --optimize-autoloader

# Create necessary directories in /tmp
mkdir -p /tmp/cache
mkdir -p /tmp/views

# Clear and cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --show
fi

echo "Laravel build completed successfully!"