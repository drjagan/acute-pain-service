#!/bin/bash
# Quick fix for missing base files causing Master Data 404 errors

echo "=== Fixing Missing Base Files ==="
echo ""

# Check if we're in the right directory
if [ ! -d "/app/data/src" ]; then
    echo "Error: Must run on production server with /app/data directory"
    echo "Run this: scp fix-missing-base-files.sh cloudron@aps.sbvu.ac.in:/tmp/"
    echo "Then: ssh cloudron@aps.sbvu.ac.in 'bash /tmp/fix-missing-base-files.sh'"
    exit 1
fi

# Check if GitHub clone exists
if [ ! -d "/tmp/acute-pain-service" ]; then
    echo "GitHub clone not found. Cloning..."
    cd /tmp
    git clone --branch aps.sbvu.ac.in --depth 1 https://github.com/drjagan/acute-pain-service.git
    if [ $? -ne 0 ]; then
        echo "Error: Failed to clone repository"
        exit 1
    fi
else
    echo "GitHub clone found. Pulling latest..."
    cd /tmp/acute-pain-service
    git pull origin aps.sbvu.ac.in
fi

cd /tmp/acute-pain-service

echo ""
echo "1. Copying BaseController.php..."
if [ -f "src/Controllers/BaseController.php" ]; then
    cp src/Controllers/BaseController.php /app/data/src/Controllers/
    chmod 644 /app/data/src/Controllers/BaseController.php
    echo "   ✓ BaseController.php copied"
else
    echo "   ✗ BaseController.php not found in repository!"
fi

echo ""
echo "2. Copying BaseModel.php..."
if [ -f "src/Models/BaseModel.php" ]; then
    cp src/Models/BaseModel.php /app/data/src/Models/
    chmod 644 /app/data/src/Models/BaseModel.php
    echo "   ✓ BaseModel.php copied"
else
    echo "   ✗ BaseModel.php not found in repository!"
fi

echo ""
echo "3. Verifying files exist..."
if [ -f "/app/data/src/Controllers/BaseController.php" ]; then
    SIZE=$(stat -f%z /app/data/src/Controllers/BaseController.php 2>/dev/null || stat -c%s /app/data/src/Controllers/BaseController.php 2>/dev/null)
    echo "   ✓ BaseController.php exists ($SIZE bytes)"
else
    echo "   ✗ BaseController.php NOT FOUND"
fi

if [ -f "/app/data/src/Models/BaseModel.php" ]; then
    SIZE=$(stat -f%z /app/data/src/Models/BaseModel.php 2>/dev/null || stat -c%s /app/data/src/Models/BaseModel.php 2>/dev/null)
    echo "   ✓ BaseModel.php exists ($SIZE bytes)"
else
    echo "   ✗ BaseModel.php NOT FOUND"
fi

echo ""
echo "4. Testing Master Data URL..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://aps.sbvu.ac.in/masterdata)
if [ "$HTTP_CODE" = "200" ]; then
    echo "   ✓ Master Data working! (HTTP $HTTP_CODE)"
elif [ "$HTTP_CODE" = "302" ]; then
    echo "   ✓ Master Data accessible (redirecting, HTTP $HTTP_CODE)"
else
    echo "   ✗ Still getting HTTP $HTTP_CODE (not working yet)"
fi

echo ""
echo "=== Fix Complete ==="
echo ""
echo "Test in browser: https://aps.sbvu.ac.in/masterdata"
