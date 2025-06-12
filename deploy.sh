#!/bin/bash

# Exit on error
set -e

# Configuration
DEPLOY_PATH="/home/admin-tt/htdocs/www.technical-test.site"
LOG_DIR="$DEPLOY_PATH/storage/logs"
LOG_FILE="$LOG_DIR/deploy.log"

# Create log directory if it doesn't exist
mkdir -p "$LOG_DIR"

# Simple logging function with Bogota timezone
echo_log() {
    echo "[$(TZ='America/Bogota' date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Set permissions function
set_permissions() {
    local path="$1"
    echo_log "Setting permissions for $path"
    
    # Only change permissions if we have write access
    if [ -w "$path" ]; then
        # Set directory permissions
        find "$path" -type d -exec chmod 775 {} \; 2>/dev/null || true
        
        # Set file permissions
        find "$path" -type f -exec chmod 664 {} \; 2>/dev/null || true
        
        # Set special permissions for storage and cache
        if [ -d "$path/storage" ]; then
            chmod -R 775 "$path/storage" 2>/dev/null || true
            chmod -R 775 "$path/bootstrap/cache" 2>/dev/null || true
        fi
    else
        echo_log "Warning: No write access to $path, skipping permission changes"
    fi
}

# Start deployment
echo_log "Starting deployment process..."

# Navigate to project directory
cd "$DEPLOY_PATH" || {
    echo_log "Failed to change to project directory"
    exit 1
}

# Set initial permissions for log directory
set_permissions "$LOG_DIR"

# Check for .env file
if [ ! -f .env ]; then
    echo_log "Creating .env file from example..."
    if [ -f .env.example ]; then
        cp .env.example .env
        set_permissions ".env"
    else
        echo_log "Error: .env.example not found"
        exit 1
    fi
fi

# Ensure vendor and node_modules directories exist and are writable
echo_log "Setting up dependency directories..."
mkdir -p vendor node_modules
chmod -R 775 vendor node_modules 2>/dev/null || true

# Install Composer dependencies
echo_log "Installing Composer dependencies..."
# First, ensure composer cache directory is writable
mkdir -p ~/.composer/cache
chmod -R 775 ~/.composer/cache
composer install --no-dev --optimize-autoloader 2>&1 | tee -a "$LOG_FILE"

# Install NPM dependencies and build assets
echo_log "Installing NPM dependencies and building assets..."
# First, ensure npm cache is clean and writable
mkdir -p ~/.npm
chmod -R 775 ~/.npm
npm cache clean --force 2>&1 | tee -a "$LOG_FILE"

# Remove existing node_modules if it exists
rm -rf node_modules 2>/dev/null || true

# Install dependencies locally
npm ci --no-global 2>&1 | tee -a "$LOG_FILE"

# Install vite locally
npm install vite --save-dev 2>&1 | tee -a "$LOG_FILE"

# Build assets using local vite
echo_log "Building assets..."
npx vite build 2>&1 | tee -a "$LOG_FILE"

# Generate application key if not exists
if ! grep -q "^APP_KEY=" .env; then
    echo_log "Generating application key..."
    php artisan key:generate --force 2>&1 | tee -a "$LOG_FILE"
fi

# Configure Swagger
echo_log "Configuring Swagger documentation..."
# Ensure storage directory is writable
chmod -R 775 storage 2>/dev/null || true
php artisan l5-swagger:generate 2>&1 | tee -a "$LOG_FILE"

# Run database migrations
echo_log "Running database migrations..."
php artisan migrate --force 2>&1 | tee -a "$LOG_FILE"

# Clear and cache configuration
echo_log "Clearing and caching configuration..."
php artisan config:clear 2>&1 | tee -a "$LOG_FILE"
php artisan config:cache 2>&1 | tee -a "$LOG_FILE"

# Clear and cache routes
echo_log "Clearing and caching routes..."
php artisan route:clear 2>&1 | tee -a "$LOG_FILE"
php artisan route:cache 2>&1 | tee -a "$LOG_FILE"

# Clear and cache views
echo_log "Clearing and caching views..."
php artisan view:clear 2>&1 | tee -a "$LOG_FILE"
php artisan view:cache 2>&1 | tee -a "$LOG_FILE"

# Optimize the application
echo_log "Optimizing the application..."
php artisan optimize 2>&1 | tee -a "$LOG_FILE"

# Set permissions for storage and cache directories
echo_log "Setting permissions for storage and cache directories..."
if [ -d "storage" ]; then
    chmod -R 775 storage 2>/dev/null || true
fi
if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache 2>/dev/null || true
fi

# Restart PHP-FPM (using systemd service)
echo_log "Restarting PHP-FPM..."
systemctl --user restart php8.2-fpm || true

# Restart Nginx (using systemd service)
echo_log "Restarting Nginx..."
systemctl --user restart nginx || true

echo_log "Deployment completed successfully!" 