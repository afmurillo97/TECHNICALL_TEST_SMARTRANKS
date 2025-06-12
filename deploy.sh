#!/bin/bash

# Exit on error
set -e

# Configuration
DEPLOY_PATH="/home/admin-tt/htdocs/www.technical-test.site"
BACKUP_PATH="/home/admin-tt/backups"
LOG_DIR="$DEPLOY_PATH/storage/logs"
LOG_FILE="$LOG_DIR/deploy.log"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Environment configuration
APP_ENV="production"
PHP_VERSION="8.1"
NODE_VERSION="18"

# Function to set permissions
set_permissions() {
    local path="$1"
    log "Setting permissions for $path"
    
    # Set ownership
    chown -R admin-tt:www-data "$path"
    
    # Set directory permissions
    find "$path" -type d -exec chmod 775 {} \;
    
    # Set file permissions
    find "$path" -type f -exec chmod 664 {} \;
    
    # Set special permissions for storage and cache
    if [ -d "$path/storage" ]; then
        chmod -R 775 "$path/storage"
        chmod -R 775 "$path/bootstrap/cache"
    fi
}

# Ensure log directory exists and is writable
mkdir -p "$LOG_DIR"
set_permissions "$LOG_DIR"

# Logging function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Error handling
trap 'log "Error occurred on line $LINENO. Command: $BASH_COMMAND"' ERR

# Create backup
create_backup() {
    log "Creating backup..."
    if [ -d "$DEPLOY_PATH" ]; then
        tar -czf "$BACKUP_PATH/backup_$TIMESTAMP.tar.gz" -C "$DEPLOY_PATH" .
        log "Backup created: backup_$TIMESTAMP.tar.gz"
    else
        log "No existing deployment found to backup"
    fi
}

# Install/Update dependencies
install_dependencies() {
    log "Installing/Updating dependencies..."
    
    # Composer dependencies
    if [ -f "$DEPLOY_PATH/composer.json" ]; then
        log "Installing Composer dependencies..."
        cd "$DEPLOY_PATH"
        composer install --no-dev --optimize-autoloader 2>&1 | tee -a "$LOG_FILE"
    fi
    
    # NPM dependencies and build
    if [ -f "$DEPLOY_PATH/package.json" ]; then
        log "Installing NPM dependencies..."
        cd "$DEPLOY_PATH"
        npm ci --production 2>&1 | tee -a "$LOG_FILE"
        npm run build 2>&1 | tee -a "$LOG_FILE"
    fi
}

# Generate application key
generate_key() {
    log "Generating application key..."
    if [ -f "$DEPLOY_PATH/.env" ]; then
        if ! grep -q "^APP_KEY=" "$DEPLOY_PATH/.env"; then
            cd "$DEPLOY_PATH"
            php artisan key:generate --force 2>&1 | tee -a "$LOG_FILE"
        fi
    fi
}

# Configure Swagger documentation
configure_swagger() {
    log "Configuring Swagger documentation..."
    if [ -f "$DEPLOY_PATH/.env" ]; then
        cd "$DEPLOY_PATH"
        
        # Update Swagger configuration in .env
        sed -i 's/L5_SWAGGER_GENERATE_ALWAYS=true/L5_SWAGGER_GENERATE_ALWAYS=false/' .env
        sed -i 's/L5_SWAGGER_UI_PERSIST_AUTHORIZATION=false/L5_SWAGGER_UI_PERSIST_AUTHORIZATION=true/' .env
        
        # Generate Swagger documentation
        php artisan l5-swagger:generate 2>&1 | tee -a "$LOG_FILE"
        
        # Set proper permissions for Swagger documentation
        if [ -d "$DEPLOY_PATH/storage/api-docs" ]; then
            chmod -R 755 "$DEPLOY_PATH/storage/api-docs"
            chown -R www-data:www-data "$DEPLOY_PATH/storage/api-docs"
        fi
    fi
}

# Run database migrations and seeders
run_migrations() {
    log "Running database migrations..."
    if [ -f "$DEPLOY_PATH/artisan" ]; then
        cd "$DEPLOY_PATH"
        php artisan migrate --force 2>&1 | tee -a "$LOG_FILE"
        
        # Run seeders if needed (usually only in development)
        if [ "$APP_ENV" = "development" ]; then
            php artisan db:seed 2>&1 | tee -a "$LOG_FILE"
        fi
    fi
}

# Clear application cache
clear_cache() {
    log "Clearing application cache..."
    if [ -f "$DEPLOY_PATH/artisan" ]; then
        cd "$DEPLOY_PATH"
        php artisan config:clear 2>&1 | tee -a "$LOG_FILE"
        php artisan cache:clear 2>&1 | tee -a "$LOG_FILE"
        php artisan view:clear 2>&1 | tee -a "$LOG_FILE"
        php artisan route:clear 2>&1 | tee -a "$LOG_FILE"
    fi
}

# Optimize application
optimize_application() {
    log "Optimizing application..."
    if [ -f "$DEPLOY_PATH/artisan" ]; then
        cd "$DEPLOY_PATH"
        php artisan optimize 2>&1 | tee -a "$LOG_FILE"
        php artisan config:cache 2>&1 | tee -a "$LOG_FILE"
        php artisan route:cache 2>&1 | tee -a "$LOG_FILE"
    fi
}

# Deploy new version
deploy() {
    log "=== Starting deployment process ==="
    log "Current directory: $(pwd)"
    log "Deploy path: $DEPLOY_PATH"
    log "Log file: $LOG_FILE"
    
    # Create necessary directories if they don't exist
    mkdir -p "$DEPLOY_PATH"
    mkdir -p "$BACKUP_PATH"
    mkdir -p "$LOG_DIR"
    
    # Create backup before deployment
    create_backup
    
    # Pull latest changes (if using git)
    if [ -d "$DEPLOY_PATH/.git" ]; then
        log "Pulling latest changes..."
        cd "$DEPLOY_PATH"
        git pull origin main 2>&1 | tee -a "$LOG_FILE"
    fi
    
    # Run deployment tasks
    install_dependencies
    generate_key
    configure_swagger
    run_migrations
    clear_cache
    optimize_application
    set_permissions "$DEPLOY_PATH"
    
    # Restart PHP-FPM
    log "Restarting PHP-FPM..."
    systemctl --user restart php8.2-fpm || true

    # Restart Nginx
    log "Restarting Nginx..."
    systemctl --user restart nginx || true

    log "=== Deployment process completed ==="
}

# Main execution
deploy 