# Fix Master Data 404 Error

**Issue:** `/masterdata` URLs return 404 error on production (aps.sbvu.ac.in)  
**Status:** Master Data works on localhost but not on production

---

## 🔍 Most Likely Causes

The 404 error is most likely because **Master Data files weren't fully copied** during deployment.

---

## ✅ Quick Fix - Copy Missing Files

### Step 1: Run Diagnostic Script

```bash
# On production server
cd /app/data
php diagnose-masterdata-404.php
```

This will tell you exactly which files are missing.

---

### Step 2: Re-copy Master Data Files

```bash
# On production server

# If you still have /tmp/acute-pain-service
cd /tmp/acute-pain-service

# If not, clone again
cd /tmp
git clone --branch aps.sbvu.ac.in --depth 1 https://github.com/drjagan/acute-pain-service.git
cd acute-pain-service

# Copy MasterDataController
cp src/Controllers/MasterDataController.php /app/data/src/Controllers/

# Copy masterdata config
cp config/masterdata.php /app/data/config/

# Copy master data views (entire directory)
cp -r src/Views/masterdata /app/data/src/Views/

# Copy BaseLookupModel
cp src/Models/BaseLookupModel.php /app/data/src/Models/

# Copy lookup models
cp src/Models/LookupCatheterIndication.php /app/data/src/Models/
cp src/Models/LookupRemovalIndication.php /app/data/src/Models/
cp src/Models/LookupSentinelEvent.php /app/data/src/Models/
cp src/Models/LookupSpecialty.php /app/data/src/Models/

# Set permissions
chmod 644 /app/data/src/Controllers/MasterDataController.php
chmod 644 /app/data/config/masterdata.php
chmod 755 /app/data/src/Views/masterdata
chmod 644 /app/data/src/Views/masterdata/*.php
chmod 644 /app/data/src/Models/*.php
```

---

### Step 3: Verify Files Copied

```bash
# Check MasterDataController
ls -lh /app/data/src/Controllers/MasterDataController.php
# Should show file size around 28KB

# Check masterdata views
ls -la /app/data/src/Views/masterdata/
# Should show: index.php, list.php, form.php, manage-children.php

# Check config
ls -lh /app/data/config/masterdata.php
# Should show file size around 16KB

# Check models
ls -lh /app/data/src/Models/BaseLookupModel.php
# Should show file size around 11KB
```

---

### Step 4: Test Master Data

```bash
# Test with curl
curl -I https://aps.sbvu.ac.in/masterdata

# Should show: HTTP/1.1 200 OK (not 404)
```

Or visit in browser:
- https://aps.sbvu.ac.in/masterdata

---

## 🔍 Alternative Causes

If the quick fix above doesn't work, check these:

### Issue 1: .htaccess Missing or Incorrect

**Check:**
```bash
cat /app/data/public/.htaccess | head -20
```

**Should contain:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirect all requests to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>
```

**Fix:**
```bash
cp /tmp/acute-pain-service/public/.htaccess /app/data/public/
chmod 644 /app/data/public/.htaccess
```

---

### Issue 2: Apache mod_rewrite Not Enabled

**Check:**
```bash
apachectl -M | grep rewrite
# Should show: rewrite_module (shared)
```

**Fix (if not enabled):**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

### Issue 3: Wrong Document Root

**Check Apache config:**
```bash
# On Cloudron, check if document root is correct
# Should be: /app/data/public
```

**Cloudron should handle this automatically**, but verify the app is configured correctly.

---

### Issue 4: PHP Autoloading Issues

**Check if classes can be loaded:**
```bash
cd /app/data
php -r "require 'config/config.php'; echo 'Config loaded OK\n';"
php -r "require 'src/Controllers/MasterDataController.php'; echo 'Controller loaded OK\n';"
```

If errors appear, check for syntax errors or missing dependencies.

---

## 📋 Complete File Checklist

These files **must exist** for Master Data to work:

### Controllers (1 file):
- ✅ `/app/data/src/Controllers/MasterDataController.php` (28KB)

### Models (5 files):
- ✅ `/app/data/src/Models/BaseLookupModel.php` (11KB)
- ✅ `/app/data/src/Models/LookupCatheterIndication.php`
- ✅ `/app/data/src/Models/LookupRemovalIndication.php`
- ✅ `/app/data/src/Models/LookupSentinelEvent.php`
- ✅ `/app/data/src/Models/LookupSpecialty.php`

### Views (4 files):
- ✅ `/app/data/src/Views/masterdata/index.php`
- ✅ `/app/data/src/Views/masterdata/list.php`
- ✅ `/app/data/src/Views/masterdata/form.php`
- ✅ `/app/data/src/Views/masterdata/manage-children.php`

### Config (1 file):
- ✅ `/app/data/config/masterdata.php` (16KB)

### Public (1 file):
- ✅ `/app/data/public/.htaccess`

---

## 🔧 Comprehensive Re-Copy Script

If you want to be absolutely sure all files are copied:

```bash
# On production server

# 1. Backup current state
cd /app/data
tar -czf /tmp/backup_before_masterdata_fix_$(date +%Y%m%d-%H%M%S).tar.gz src/ config/

# 2. Clone fresh from GitHub
cd /tmp
rm -rf acute-pain-service
git clone --branch aps.sbvu.ac.in --depth 1 https://github.com/drjagan/acute-pain-service.git
cd acute-pain-service

# 3. Copy ALL src files (safe - preserves existing)
cp -r src/* /app/data/src/

# 4. Copy config files (safe - preserves .env)
cp config/masterdata.php /app/data/config/

# 5. Verify critical files
echo "Checking MasterDataController..."
ls -lh /app/data/src/Controllers/MasterDataController.php

echo "Checking masterdata views..."
ls -la /app/data/src/Views/masterdata/

echo "Checking masterdata config..."
ls -lh /app/data/config/masterdata.php

# 6. Set permissions
find /app/data/src -type f -exec chmod 644 {} \;
find /app/data/src -type d -exec chmod 755 {} \;
chmod 644 /app/data/config/masterdata.php

# 7. Test
curl -I https://aps.sbvu.ac.in/masterdata
```

---

## 🎯 Quick Diagnostic One-Liner

```bash
# Run this to check all files at once
cd /app/data && \
echo "Controller: $(ls -lh src/Controllers/MasterDataController.php 2>&1 | awk '{print $5}')" && \
echo "Config: $(ls -lh config/masterdata.php 2>&1 | awk '{print $5}')" && \
echo "Views: $(ls src/Views/masterdata/*.php 2>&1 | wc -l) files" && \
echo "Models: $(ls src/Models/Lookup*.php 2>&1 | wc -l) files" && \
echo ".htaccess: $(ls -lh public/.htaccess 2>&1 | awk '{print $5}')"
```

**Expected output:**
```
Controller: 28K
Config: 16K
Views: 4 files
Models: 4 files
.htaccess: 1.5K
```

---

## 🔍 Check Error Logs

If still getting 404, check Apache error logs:

```bash
# Cloudron logs location (adjust if different)
tail -50 /app/data/logs/app.log

# Or Apache error log
tail -50 /var/log/apache2/error.log
```

Look for errors like:
- "Controller not found"
- "Method not found"
- "Class not found"

---

## ✅ Final Verification

After fixing, test all Master Data URLs:

```bash
# Test each URL
curl -I https://aps.sbvu.ac.in/masterdata
curl -I https://aps.sbvu.ac.in/masterdata/specialties
curl -I https://aps.sbvu.ac.in/masterdata/catheter_indications
curl -I https://aps.sbvu.ac.in/masterdata/removal_indications
curl -I https://aps.sbvu.ac.in/masterdata/sentinel_events

# All should return: HTTP/1.1 200 OK
```

Or test in browser - these should all work:
- https://aps.sbvu.ac.in/masterdata
- https://aps.sbvu.ac.in/masterdata/specialties
- https://aps.sbvu.ac.in/masterdata/surgeries
- https://aps.sbvu.ac.in/masterdata/drugs

---

## 📝 Summary

**Most likely cause:** MasterDataController and related files not copied during initial deployment.

**Quick fix:**
1. Run diagnostic: `php diagnose-masterdata-404.php`
2. Re-copy master data files from GitHub clone
3. Set correct permissions
4. Test URLs

**If that doesn't work:**
1. Check .htaccess exists
2. Check mod_rewrite enabled
3. Check error logs for specific errors

---

**The files are in GitHub - just need to be copied to `/app/data/` correctly!** 🔧
