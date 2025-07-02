#!/bin/bash

# TEC Sunday School Audio App - Production Deployment Script
# Usage: ./deploy.sh

set -e

echo "🚀 Starting TEC Sunday School Audio App deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "artisan file not found. Please run this script from the Laravel project root."
    exit 1
fi

# 1. Environment Setup
print_status "Setting up production environment..."
if [ ! -f ".env" ]; then
    if [ -f ".env.production" ]; then
        cp .env.production .env
        print_status "Copied .env.production to .env"
    else
        print_error ".env file not found. Please create one based on .env.production"
        exit 1
    fi
fi

# 2. Install/Update Dependencies
print_status "Installing production dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Generate Application Key (if needed)
if ! grep -q "APP_KEY=base64:" .env; then
    print_status "Generating application key..."
    php artisan key:generate --force
fi

# 4. Database Setup
print_status "Setting up database..."
php artisan migrate --force

# 5. Clear and Cache Everything
print_status "Clearing old caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

print_status "Building production caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Optimize Autoloader
print_status "Optimizing autoloader..."
composer dump-autoload --optimize

# 7. Set Permissions
print_status "Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/views
chmod -R 775 storage/framework/cache

# 8. Create symbolic link for storage (if needed)
if [ ! -L "public/storage" ]; then
    print_status "Creating storage symbolic link..."
    php artisan storage:link
fi

# 9. Sync audio files
print_status "Syncing audio files from Bunny.net..."
php artisan music:sync --force

# 10. Final checks
print_status "Running final checks..."

# Check if .env is properly configured
if grep -q "APP_ENV=local" .env; then
    print_warning "APP_ENV is still set to 'local'. Consider changing to 'production'"
fi

if grep -q "APP_DEBUG=true" .env; then
    print_warning "APP_DEBUG is still set to 'true'. Consider changing to 'false' for production"
fi

# Check file permissions
if [ ! -w "storage/logs" ]; then
    print_error "storage/logs is not writable"
    exit 1
fi

print_status "Deployment completed successfully! 🎉"
print_status "Your TEC Sunday School Audio App is ready for production."

echo ""
echo "📋 Post-deployment checklist:"
echo "   1. Update your web server configuration"
echo "   2. Set up SSL certificate"
echo "   3. Configure backup strategy"
echo "   4. Set up monitoring"
echo "   5. Test all functionality"
echo ""
echo "🔗 Access your app at: $(grep APP_URL .env | cut -d '=' -f2)"
