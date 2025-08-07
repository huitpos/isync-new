#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default values
SKIP_GIT=false
SKIP_COMPOSER=false
SKIP_NPM=false
RUN_MIGRATIONS=false
BRANCH=""

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

# Function to show help
show_help() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Laravel Deployment Script"
    echo ""
    echo "Options:"
    echo "  --skip-git        Skip git pull"
    echo "  --skip-composer   Skip composer install"
    echo "  --skip-npm        Skip npm install and build"
    echo "  --migrate         Run database migrations"
    echo "  --branch BRANCH   Git branch to pull (default: current branch)"
    echo "  --help            Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0                          # Full deployment"
    echo "  $0 --skip-git               # Skip git pull"
    echo "  $0 --migrate                # Deploy with migrations"
    echo "  $0 --branch develop         # Deploy specific branch"
    echo "  $0 --skip-composer --skip-npm  # Skip dependencies"
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --skip-git)
            SKIP_GIT=true
            shift
            ;;
        --skip-composer)
            SKIP_COMPOSER=true
            shift
            ;;
        --skip-npm)
            SKIP_NPM=true
            shift
            ;;
        --migrate)
            RUN_MIGRATIONS=true
            shift
            ;;
        --branch)
            BRANCH="$2"
            shift 2
            ;;
        --help)
            show_help
            exit 0
            ;;
        *)
            print_error "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "artisan file not found. Make sure you're in the Laravel project root."
    exit 1
fi

print_status "Starting deployment process..."

# 1. Git operations
if [ "$SKIP_GIT" = false ]; then
    if [ -n "$BRANCH" ]; then
        print_status "Switching to branch: $BRANCH"
        git checkout "$BRANCH" || {
            print_error "Failed to checkout branch: $BRANCH"
            exit 1
        }
    fi
    
    print_status "Pulling latest changes from git..."
    git pull origin $(git branch --show-current) || {
        print_error "Git pull failed"
        exit 1
    }
    print_success "Git pull completed"
else
    print_warning "Skipping git operations"
fi

# 2. Composer dependencies
if [ "$SKIP_COMPOSER" = false ]; then
    print_status "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader || {
        print_error "Composer install failed"
        exit 1
    }
    print_success "Composer dependencies installed"
else
    print_warning "Skipping composer install"
fi

# 3. NPM dependencies and build
if [ "$SKIP_NPM" = false ]; then
    print_status "Installing NPM dependencies..."
    npm ci || {
        print_error "NPM install failed"
        exit 1
    }
    print_success "NPM dependencies installed"

    # Clear and rebuild assets
    print_status "Clearing old assets..."
    rm -rf public/assets/css/
    rm -rf public/assets/js/
    rm -rf public/assets/plugins/
    rm -rf public/assets/media/
    rm -f public/mix-manifest.json

    # Build production assets
    print_status "Building production assets..."
    npm run production || {
        print_error "Asset build failed"
        exit 1
    }
    print_success "Assets built successfully"
else
    print_warning "Skipping NPM operations"
fi

# 4. Clear application caches
print_status "Clearing application caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
print_success "Caches cleared"

# 5. Run migrations if requested
if [ "$RUN_MIGRATIONS" = true ]; then
    print_status "Running database migrations..."
    php artisan migrate --force || {
        print_error "Database migrations failed"
        exit 1
    }
    print_success "Database migrations completed"
fi

# 6. Cache optimization for production
print_status "Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Production optimization completed"

# 7. Set proper permissions
print_status "Setting proper permissions..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
# Ensure web server can write to these directories
chown -R $USER:www-data storage/ bootstrap/cache/ 2>/dev/null || true
print_success "Permissions set"

# 8. Final checks
print_status "Running final checks..."
if [ "$SKIP_NPM" = false ]; then
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
fi

print_success "🚀 Deployment completed successfully!"
print_status "Application should now be running with the latest changes."

# Show deployment summary
echo ""
echo "=== Deployment Summary ==="
echo "Git commit: $(git rev-parse --short HEAD)"
echo "Branch: $(git branch --show-current)"
echo "Date: $(date)"
if [ "$SKIP_GIT" = false ]; then
    echo "Git operations: ✅"
else
    echo "Git operations: ⏭️ (skipped)"
fi
if [ "$SKIP_COMPOSER" = false ]; then
    echo "Composer install: ✅"
else
    echo "Composer install: ⏭️ (skipped)"
fi
if [ "$SKIP_NPM" = false ]; then
    echo "Assets built: ✅"
else
    echo "Assets built: ⏭️ (skipped)"
fi
echo "Caches cleared: ✅"
echo "Production optimized: ✅"
if [ "$RUN_MIGRATIONS" = true ]; then
    echo "Database migrations: ✅"
fi
echo "=========================="
