#!/bin/bash

echo "🚀 Starting Laravel build for Vercel..."

# Install PHP dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Create necessary directories
mkdir -p /tmp/storage/framework/cache
mkdir -p /tmp/storage/framework/sessions  
mkdir -p /tmp/storage/framework/views
mkdir -p /tmp/storage/logs
mkdir -p public/build

# Set permissions
chmod -R 775 /tmp/storage

# Generate app key if needed
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --show --no-interaction
fi

# Clear caches
php artisan config:clear --no-interaction
php artisan route:clear --no-interaction  
php artisan view:clear --no-interaction

# Generate static version for production
echo "📄 Generating static HTML..."
php artisan config:cache --no-interaction

# Test that the app works
echo "🧪 Testing Laravel app..."
php artisan --version

echo "✅ Laravel build completed successfully!"