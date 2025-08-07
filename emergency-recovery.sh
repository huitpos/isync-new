#!/bin/bash

# Emergency recovery script for when config:cache breaks the site

echo "🚨 Emergency Laravel Recovery Script"
echo "===================================="

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ artisan file not found. Make sure you're in the Laravel project root."
    exit 1
fi

echo "🔧 Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "🔧 Clearing compiled files..."
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
rm -f bootstrap/cache/services.php

echo "🔧 Setting proper permissions..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

echo "✅ Recovery completed!"
echo "🌐 Your Laravel application should now be working again."
echo ""
echo "💡 Tips to prevent this:"
echo "   - Check your .env file for invalid values"
echo "   - Make sure all config files have proper syntax"
echo "   - Test config:cache on staging first"
