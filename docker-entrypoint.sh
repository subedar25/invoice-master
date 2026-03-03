#!/bin/bash
set -e

# Fix permissions and ownership for all files and folders (needed because volumes override Dockerfile permissions)
echo "Setting proper permissions and ownership..."

# Change to application directory
cd /var/www/html

# Set ownership for all files and folders
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data . 2>/dev/null || true
fi

# Set all directories to 755
find . -type d -exec chmod 755 {} \; 2>/dev/null || true

# Set all files to 644
find . -type f -exec chmod 644 {} \; 2>/dev/null || true

# Special permissions for SSL private keys (must be 600 for security)
if [ -d ssl ]; then
    find ssl -type f -name "*.key" -exec chmod 600 {} \; 2>/dev/null || true
fi

# Ensure storage and bootstrap cache directories are writable (775 for dirs, 664 for files)
find storage -type d -exec chmod 775 {} \; 2>/dev/null || true
find storage -type f -exec chmod 664 {} \; 2>/dev/null || true
find bootstrap/cache -type d -exec chmod 775 {} \; 2>/dev/null || true
find bootstrap/cache -type f -exec chmod 664 {} \; 2>/dev/null || true

# Set executable permissions for scripts
find . -type f \( -name "*.sh" -o -name "artisan" \) -exec chmod 755 {} \; 2>/dev/null || true

echo "Permissions and ownership set."

# Wait for database to be ready
echo "Waiting for database connection..."
timeout=30
counter=0

# Try to connect to database
until php -r "
try {
    \$pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null || [ $counter -ge $timeout ]; do
    echo "Waiting for database... ($counter/$timeout)"
    sleep 1
    counter=$((counter+1))
done

# Check if we can connect
if php -r "
try {
    \$pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; then
    echo "Database connection established!"
    
    # Run migrations
    echo "Running database migrations..."
    php artisan migrate --force || echo "Migration failed or already run"
    
    # Run seeders
    echo "Running database seeders..."
    php artisan db:seed --class=ModuleAndPermissionSeeder --force || echo "ModuleAndPermissionSeeder failed or already run"
    php artisan db:seed --class=SuperAdminSeeder --force || echo "SuperAdminSeeder failed or already run"
else
    echo "Warning: Could not connect to database. Skipping migrations and seeders."
    echo "Make sure your .env file has correct database credentials."
    echo "You can run them manually later with:"
    echo "  docker-compose exec app php artisan migrate --force"
    echo "  docker-compose exec app php artisan db:seed --class=ModuleAndPermissionSeeder"
    echo "  docker-compose exec app php artisan db:seed --class=SuperAdminSeeder"
fi

# Check and build frontend assets if needed
echo "Checking frontend assets..."
if [ ! -f public/build/manifest.json ]; then
    echo "Vite manifest not found. Building frontend assets..."
    
    # Verify Node.js and npm are available
    if command -v node >/dev/null 2>&1 && command -v npm >/dev/null 2>&1; then
        echo "Node.js version: $(node --version)"
        echo "npm version: $(npm --version)"
        
        # Install npm dependencies if node_modules doesn't exist
        if [ ! -d node_modules ]; then
            echo "Installing npm dependencies..."
            npm install || echo "npm install failed, continuing..."
        fi
        
        # Build frontend assets
        echo "Building frontend assets with Vite..."
        npm run build || echo "npm build failed, continuing..."
        
        # Verify build was successful
        if [ -f public/build/manifest.json ]; then
            echo "Frontend assets built successfully!"
        else
            echo "Warning: Frontend build may have failed. manifest.json not found."
        fi
    else
        echo "Warning: Node.js or npm not found. Cannot build frontend assets."
        echo "Frontend assets should be built during Docker build or manually."
    fi
else
    echo "Frontend assets already built."
fi

# Clear and rebuild caches
echo "Building application caches..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start Apache
echo "Starting Apache..."
exec apache2-foreground
