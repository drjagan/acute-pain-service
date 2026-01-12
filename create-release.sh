#!/bin/bash

# Script to create GitHub Release for v1.1.2
# Usage: ./create-release.sh

set -e

echo "==========================================="
echo "Creating GitHub Release for v1.1.2"
echo "==========================================="
echo ""

# Check if gh is installed
if ! command -v gh &> /dev/null; then
    echo "Error: GitHub CLI (gh) is not installed"
    echo "Install it from: https://cli.github.com/"
    exit 1
fi

# Check if authenticated
if ! gh auth status &> /dev/null; then
    echo "Error: Not authenticated with GitHub"
    echo "Run: gh auth login"
    exit 1
fi

# Check if we're in the right directory
if [ ! -f "VERSION" ]; then
    echo "Error: VERSION file not found"
    echo "Please run this script from the project root directory"
    exit 1
fi

# Verify version
VERSION=$(cat VERSION)
if [ "$VERSION" != "1.1.2" ]; then
    echo "Warning: VERSION file shows $VERSION, expected 1.1.2"
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo "Step 1: Checking if tag v1.1.2 exists..."
if ! git tag -l | grep -q "^v1.1.2$"; then
    echo "Error: Tag v1.1.2 not found"
    echo "Available tags:"
    git tag -l
    exit 1
fi
echo "✓ Tag v1.1.2 exists"
echo ""

echo "Step 2: Checking if tag is pushed to remote..."
if ! git ls-remote --tags origin | grep -q "refs/tags/v1.1.2"; then
    echo "Error: Tag v1.1.2 not found on remote"
    echo "Push it with: git push origin v1.1.2"
    exit 1
fi
echo "✓ Tag v1.1.2 is on remote"
echo ""

echo "Step 3: Checking if release already exists..."
if gh release view v1.1.2 &> /dev/null; then
    echo "Release v1.1.2 already exists!"
    echo ""
    gh release view v1.1.2
    echo ""
    read -p "Delete and recreate? (y/N) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Deleting existing release..."
        gh release delete v1.1.2 -y
        echo "✓ Deleted"
    else
        echo "Exiting without changes"
        exit 0
    fi
fi
echo "✓ Ready to create release"
echo ""

echo "Step 4: Creating GitHub Release..."
gh release create v1.1.2 \
    --title "Version 1.1.2 - Production Deployment Package" \
    --notes-file RELEASE_NOTES_v1.1.2.md \
    --verify-tag

echo ""
echo "==========================================="
echo "✓ Release created successfully!"
echo "==========================================="
echo ""

echo "Step 5: Verifying download URL..."
sleep 2
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.1.2.tar.gz)

if [ "$HTTP_CODE" = "200" ]; then
    echo "✓ Download URL is working (HTTP $HTTP_CODE)"
else
    echo "⚠ Download URL returned HTTP $HTTP_CODE"
    echo "  It may take a few moments for the URL to become active"
fi
echo ""

echo "Release URL:"
gh release view v1.1.2 --web 2>&1 | grep -v "Opening" || echo "https://github.com/drjagan/acute-pain-service/releases/tag/v1.1.2"
echo ""

echo "Download URL:"
echo "https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.1.2.tar.gz"
echo ""

echo "Test with:"
echo "  curl -I https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.1.2.tar.gz"
echo ""

echo "==========================================="
echo "All done! 🎉"
echo "==========================================="
