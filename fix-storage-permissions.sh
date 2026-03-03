#!/bin/bash
# Fix Laravel storage and log permissions (run on server: /var/www/html or your app root)
# Run as root or with sudo.

APP_ROOT="${1:-/var/www/html}"
WEB_USER="${2:-www-data}"

echo "Fixing permissions for: $APP_ROOT (web user: $WEB_USER)"

# Create log directory if missing
mkdir -p "$APP_ROOT/storage/logs"
mkdir -p "$APP_ROOT/storage/framework/cache"
mkdir -p "$APP_ROOT/storage/framework/sessions"
mkdir -p "$APP_ROOT/storage/framework/views"
mkdir -p "$APP_ROOT/bootstrap/cache"

# Ownership: web server user must own storage and bootstrap/cache
chown -R "$WEB_USER:$WEB_USER" "$APP_ROOT/storage" "$APP_ROOT/bootstrap/cache"

# Directories writable by owner and group
chmod -R 775 "$APP_ROOT/storage" "$APP_ROOT/bootstrap/cache"

# Ensure log file can be created (optional: touch and chown)
touch "$APP_ROOT/storage/logs/laravel.log" 2>/dev/null && chown "$WEB_USER:$WEB_USER" "$APP_ROOT/storage/logs/laravel.log"

echo "Done. If using SELinux, you may also need: chcon -R -t httpd_sys_rw_content_t $APP_ROOT/storage"
