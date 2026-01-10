#!/bin/bash

##############################################################################
# Start PHP Development Server for APS Application
##############################################################################

PHP_BIN="/Applications/XAMPP/bin/php"
PORT=8000
PUBLIC_DIR="public"

echo "========================================="
echo "  APS Development Server"
echo "========================================="
echo ""

# Check if PHP exists
if [ ! -f "$PHP_BIN" ]; then
    echo "Error: PHP not found at $PHP_BIN"
    echo "Please update PHP_BIN path in this script"
    exit 1
fi

# Check PHP version
PHP_VERSION=$($PHP_BIN -v | head -n 1 | cut -d " " -f 2)
echo "PHP Version: $PHP_VERSION"
echo "Port: $PORT"
echo "Document Root: $PUBLIC_DIR"
echo ""
echo "Starting server..."
echo ""
echo "Access the application at:"
echo "  → http://localhost:$PORT"
echo ""
echo "Login credentials:"
echo "  Admin:     admin / admin123"
echo "  Attending: dr.sharma / admin123"
echo "  Resident:  dr.patel / admin123"
echo "  Nurse:     nurse.kumar / admin123"
echo ""
echo "Press Ctrl+C to stop the server"
echo "========================================="
echo ""

# Start server
$PHP_BIN -S localhost:$PORT -t $PUBLIC_DIR
