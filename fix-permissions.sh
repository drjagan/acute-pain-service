#!/bin/bash
# Fix File Permissions for Cloudron Deployment
# Run this script to fix common permission issues

echo "=========================================="
echo "Fixing File Permissions for Cloudron"
echo "=========================================="
echo ""

# Get current directory
APP_DIR="/app/data"

if [ ! -d "$APP_DIR" ]; then
    APP_DIR="$(pwd)"
fi

echo "Application directory: $APP_DIR"
echo ""

# Determine web server user
WEB_USER="cloudron"
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
fi

echo "Web server user: $WEB_USER"
echo ""

# Fix logs directory
echo "1. Fixing logs directory..."
if [ -d "$APP_DIR/logs" ]; then
    chmod 777 "$APP_DIR/logs"
    chown -R $WEB_USER:$WEB_USER "$APP_DIR/logs"
    chmod 666 "$APP_DIR/logs"/*.log 2>/dev/null
    echo "   ✓ Logs directory permissions fixed"
else
    mkdir -p "$APP_DIR/logs"
    chmod 777 "$APP_DIR/logs"
    chown $WEB_USER:$WEB_USER "$APP_DIR/logs"
    echo "   ✓ Logs directory created"
fi
echo ""

# Fix uploads directory
echo "2. Fixing uploads directory..."
if [ -d "$APP_DIR/public/uploads" ]; then
    chmod 777 "$APP_DIR/public/uploads"
    chown -R $WEB_USER:$WEB_USER "$APP_DIR/public/uploads"
    echo "   ✓ Uploads directory permissions fixed"
else
    mkdir -p "$APP_DIR/public/uploads"
    chmod 777 "$APP_DIR/public/uploads"
    chown $WEB_USER:$WEB_USER "$APP_DIR/public/uploads"
    echo "   ✓ Uploads directory created"
fi
echo ""

# Fix exports directory
echo "3. Fixing exports directory..."
if [ -d "$APP_DIR/public/exports" ]; then
    chmod 777 "$APP_DIR/public/exports"
    chown -R $WEB_USER:$WEB_USER "$APP_DIR/public/exports"
    echo "   ✓ Exports directory permissions fixed"
else
    mkdir -p "$APP_DIR/public/exports"
    chmod 777 "$APP_DIR/public/exports"
    chown $WEB_USER:$WEB_USER "$APP_DIR/public/exports"
    echo "   ✓ Exports directory created"
fi
echo ""

# Fix .env file
echo "4. Fixing .env file..."
if [ -f "$APP_DIR/.env" ]; then
    chmod 600 "$APP_DIR/.env"
    chown $WEB_USER:$WEB_USER "$APP_DIR/.env"
    echo "   ✓ .env file permissions fixed (600)"
else
    echo "   ⚠ .env file not found"
fi
echo ""

# Fix config directory
echo "5. Fixing config directory..."
if [ -d "$APP_DIR/config" ]; then
    chown -R $WEB_USER:$WEB_USER "$APP_DIR/config"
    chmod 755 "$APP_DIR/config"
    chmod 644 "$APP_DIR/config"/*.php
    echo "   ✓ Config directory permissions fixed"
fi
echo ""

# Create log files if they don't exist
echo "6. Creating log files..."
touch "$APP_DIR/logs/app.log"
touch "$APP_DIR/logs/error.log"
touch "$APP_DIR/logs/php-errors.log"
chmod 666 "$APP_DIR/logs"/*.log
chown $WEB_USER:$WEB_USER "$APP_DIR/logs"/*.log
echo "   ✓ Log files created/updated"
echo ""

# Summary
echo "=========================================="
echo "Summary"
echo "=========================================="
ls -ld "$APP_DIR/logs"
ls -l "$APP_DIR/logs"/*.log 2>/dev/null | head -5
ls -ld "$APP_DIR/public/uploads"
ls -ld "$APP_DIR/public/exports"
echo ""

echo "✓ Permissions fixed!"
echo ""
echo "Now test your application:"
echo "  Visit: https://aps.sbvu.ac.in"
echo "  Try creating a user again"
echo ""
echo "If still having issues, run:"
echo "  php debug-500-error.php"
echo "=========================================="
