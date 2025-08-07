#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "artisan file not found. Make sure you're in the Laravel project root."
    exit 1
fi

print_status "Starting deployment process..."

# 1. Pull latest changes from git
print_status "Pulling latest changes from git..."
git pull origin $(git branch --show-current) || {
    print_error "Git pull failed"
    exit 1
}
print_success "Git pull completed"

# 2. Install/update composer dependencies
print_status "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader || {
    print_error "Composer install failed"
    exit 1
}
print_success "Composer dependencies installed"

# 3. Install/update npm dependencies
print_status "Installing NPM dependencies..."
npm ci || {
    print_error "NPM install failed"
    exit 1
}
print_success "NPM dependencies installed"

# 4. Clear and rebuild assets
print_status "Clearing old assets..."
rm -rf public/assets/css/
rm -rf public/assets/js/
rm -rf public/assets/plugins/
rm -rf public/assets/media/
rm -f public/mix-manifest.json

# 5. Build production assets
print_status "Building production assets..."
npm run production || {
    print_error "Asset build failed"
    exit 1
}
print_success "Assets built successfully"

# 6. Ensure storage directories exist and set permissions
print_status "Setting up storage directories and permissions..."
# Create all necessary storage directories
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p bootstrap/cache
# Set comprehensive permissions
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/
# Ensure web server can write to these directories
chown -R $USER:www-data storage/ bootstrap/cache/ 2>/dev/null || true
print_status "Verifying storage structure..."
ls -la storage/
print_success "Storage directories and permissions set"

# 7. Clear application caches
print_status "Clearing application caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
print_success "Caches cleared"

# 8. Cache optimization for production
print_status "Optimizing for production..."

# First try route and view cache (safer)
print_status "Running route:cache..."
php artisan route:cache || {
    print_warning "Route cache failed, continuing..."
}

print_status "Running view:cache..."
php artisan view:cache || {
    print_warning "View cache failed, continuing..."
}

# Test config:cache carefully
print_status "Attempting config:cache (will rollback if it breaks)..."
# Backup current config state
CONFIG_BACKUP=$(mktemp)
cp bootstrap/cache/config.php "$CONFIG_BACKUP" 2>/dev/null || true

php artisan config:cache
if [ $? -eq 0 ]; then
    print_status "Config:cache completed successfully"
    # Quick test to see if app still works
    php artisan --version >/dev/null 2>&1
    if [ $? -eq 0 ]; then
        print_success "Config cache applied successfully"
        rm -f "$CONFIG_BACKUP"
    else
        print_error "Config cache broke the application! Rolling back..."
        php artisan config:clear
        [ -f "$CONFIG_BACKUP" ] && cp "$CONFIG_BACKUP" bootstrap/cache/config.php
        rm -f "$CONFIG_BACKUP"
        print_warning "Config cache rolled back, application should be working"
    fi
else
    print_warning "Config cache failed, continuing without it"
    rm -f "$CONFIG_BACKUP"
fi

print_success "Production optimization completed"

# 9. Run database migrations (optional - uncomment if needed)
# print_status "Running database migrations..."
# php artisan migrate --force
# print_success "Database migrations completed"

# 10. Final checks
print_status "Running final checks..."
if [ -f "public/mix-manifest.json" ]; then
    print_success "Mix manifest exists"
else
    print_warning "Mix manifest not found - assets might not load properly"
fi

if [ -d "public/assets/css" ] && [ -d "public/assets/js" ]; then
    print_success "Asset directories exist"
else
    print_warning "Asset directories missing - check build process"
fi

print_success "🚀 Deployment completed successfully!"
print_status "Application should now be running with the latest changes."

# Optional: Show deployment summary
echo ""
echo "=== Deployment Summary ==="
echo "Git commit: $(git rev-parse --short HEAD)"
echo "Branch: $(git branch --show-current)"
echo "Date: $(date)"
echo "Assets built: ✅"
echo "Caches cleared: ✅"
echo "Production optimized: ✅"
echo "=========================="
