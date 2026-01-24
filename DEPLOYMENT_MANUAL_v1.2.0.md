# Manual Deployment Guide - v1.2.0 (No Git Required)

**Target:** aps.sbvu.ac.in (Cloudron)  
**Method:** Manual file upload + database migrations  
**Time:** 10-15 minutes  
**Date:** January 24, 2026

---

## ✅ Pre-Deployment Checklist

- [ ] SSH access to aps.sbvu.ac.in
- [ ] Database credentials handy
- [ ] Current .env file backed up
- [ ] Database backed up
- [ ] Ready to deploy v1.2.0

---

## 🚀 Deployment Steps

### Step 1: SSH into Cloudron Server

```bash
ssh cloudron@aps.sbvu.ac.in
# OR
ssh root@45659acf-49d3-4ade-9a80-984c72816b55
```

---

### Step 2: Navigate to Application Directory

```bash
cd /app/data
pwd
# Should output: /app/data
```

**⚠️ IMPORTANT:** Your application is in `/app/data/`, NOT `/app/code/`

---

### Step 3: Create Backup

```bash
# Create backup directory
BACKUP_DIR="/app/data/backups/v1.2.0-$(date +%Y%m%d-%H%M%S)"
mkdir -p $BACKUP_DIR
echo "Backup directory: $BACKUP_DIR"

# Backup database
mysqldump -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e > $BACKUP_DIR/database_backup.sql

# Verify database backup
ls -lh $BACKUP_DIR/database_backup.sql
# Should show file size (e.g., 500K)

# Backup current .env file (CRITICAL!)
cp /app/data/.env $BACKUP_DIR/.env.backup

# Backup current application files
tar -czf $BACKUP_DIR/app_files_backup.tar.gz \
  --exclude='logs/*' \
  --exclude='public/uploads/*' \
  --exclude='public/exports/*' \
  --exclude='storage/sessions/*' \
  /app/data

# Verify backup
ls -lh $BACKUP_DIR/
```

---

### Step 4: Download v1.2.0 from GitHub

**IMPORTANT:** Download the `aps.sbvu.ac.in` branch (NOT the v1.2.0 tag!)  
The production branch includes `.env.production.sbvu` with your credentials and Cloudron-specific files.

**Note:** The branch name contains dots which causes issues with GitHub's archive system.  
**Solution:** Use `git clone` method (faster and more reliable).

```bash
# Clone the production branch
cd /tmp
git clone --branch aps.sbvu.ac.in --depth 1 https://github.com/drjagan/acute-pain-service.git

# Navigate into the cloned directory
cd acute-pain-service

# Verify you have production files
ls -la .env.production.sbvu
# Should show the file exists

ls -la CLOUDRON_DEPLOYMENT_GUIDE.md
# Should exist

# You now have all files from aps.sbvu.ac.in branch ready!
```

**Alternative methods:** See `DOWNLOAD_PRODUCTION_BRANCH.md` if git is not available.

---

### Step 5: Copy New Files to /app/data

```bash
cd /tmp/acute-pain-service

# Copy source code
cp -r src /app/data/

# Copy config files
cp -r config /app/data/

# Copy documentation
cp -r documentation /app/data/

# Copy VERSION file
cp VERSION /app/data/

# Copy storage structure
cp -r storage /app/data/ 2>/dev/null || mkdir -p /app/data/storage/sessions

# IMPORTANT: Set up .env from production template
cp .env.production.sbvu /app/data/.env

# Verify .env was created
ls -la /app/data/.env
# Should show file with 600 permissions
```

---

### Step 6: Verify .env File Integrity

```bash
cd /app/data

# Check .env has correct database credentials
cat .env | grep "DB_HOST"
# Should show: DB_HOST=mysql

cat .env | grep "DB_NAME"  
# Should show: DB_NAME=a916f81cc97ef00e

cat .env | grep "APP_VERSION"
# Should show: APP_VERSION=1.2.0 (or will be updated next)

# If .env was overwritten, restore from backup:
# cp $BACKUP_DIR/.env.backup .env
```

---

### Step 7: Update Version in .env

```bash
cd /app/data

# Update APP_VERSION to 1.2.0
sed -i 's/^APP_VERSION=.*/APP_VERSION=1.2.0/' .env

# Verify
cat .env | grep APP_VERSION
# Should show: APP_VERSION=1.2.0
```

---

### Step 8: Run Database Migration 013

```bash
cd /app/data

# Run migration 013: Create new lookup tables
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/013_create_new_lookup_tables.sql

# Check for errors
echo $?
# Should show: 0 (success)
```

**Verify Migration 013:**
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  -e "SHOW TABLES LIKE 'lookup_%';" a916f81cc97ef00e
```

**Expected Output** (9 tables):
```
lookup_adjuvants
lookup_catheter_indications      ← NEW
lookup_comorbidities
lookup_drugs
lookup_red_flags
lookup_removal_indications       ← NEW
lookup_sentinel_events           ← NEW
lookup_specialties               ← NEW
lookup_surgeries
```

---

### Step 9: Run Database Migration 014

```bash
cd /app/data

# Run migration 014: Update surgeries with specialties
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/014_update_surgeries_with_specialties.sql

# Check for errors
echo $?
# Should show: 0 (success)
```

**Verify Migration 014:**
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  -e "DESCRIBE lookup_surgeries;" a916f81cc97ef00e
```

**Expected:** Should show `specialty_id` column (INT, nullable, FK)

---

### Step 10: Seed Master Data (Optional but Recommended)

```bash
cd /app/data

# Check if seeder exists
ls -la src/Database/seeders/MasterDataSeeder.sql

# Run seeder
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/seeders/MasterDataSeeder.sql
```

**Verify Data Counts:**
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e << 'EOF'
SELECT 'specialties' AS table_name, COUNT(*) AS count FROM lookup_specialties
UNION ALL SELECT 'catheter_indications', COUNT(*) FROM lookup_catheter_indications
UNION ALL SELECT 'removal_indications', COUNT(*) FROM lookup_removal_indications
UNION ALL SELECT 'sentinel_events', COUNT(*) FROM lookup_sentinel_events
UNION ALL SELECT 'surgeries', COUNT(*) FROM lookup_surgeries
UNION ALL SELECT 'drugs', COUNT(*) FROM lookup_drugs
UNION ALL SELECT 'adjuvants', COUNT(*) FROM lookup_adjuvants
UNION ALL SELECT 'comorbidities', COUNT(*) FROM lookup_comorbidities
UNION ALL SELECT 'red_flags', COUNT(*) FROM lookup_red_flags;
EOF
```

**Expected Counts:**
```
specialties              20
catheter_indications     18
removal_indications       7
sentinel_events          30
surgeries                75
drugs                    16
adjuvants                12
comorbidities            36
red_flags                19
```

---

### Step 11: Set Permissions

```bash
cd /app/data

# Set directory permissions
find /app/data -type d -exec chmod 755 {} \;

# Set file permissions
find /app/data -type f -exec chmod 644 {} \;

# Make storage/sessions writable
chmod 777 storage/sessions

# Make logs writable
chmod 755 logs
chmod 666 logs/*.log 2>/dev/null || true

# Make uploads/exports writable
chmod 755 public/uploads
chmod 755 public/exports

# Protect .env file
chmod 600 .env
```

---

### Step 12: Clean Up

```bash
# Remove cloned repository
rm -rf /tmp/acute-pain-service

# Verify cleanup
ls /tmp/acute-pain-service 2>/dev/null || echo "Cleanup successful"
```

---

### Step 13: Verify Deployment

```bash
cd /app/data

# Check version
cat VERSION
# Should show: 1.2.0

# Check database tables count
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  -e "SELECT COUNT(*) AS total_tables FROM information_schema.tables WHERE table_schema = 'a916f81cc97ef00e';" a916f81cc97ef00e
# Should show: 20+ tables

# Check new MasterDataController exists
ls -lh src/Controllers/MasterDataController.php
# Should show file size ~28KB

# Check master data views exist
ls -l src/Views/masterdata/
# Should show: index.php, list.php, form.php, manage-children.php
```

---

### Step 14: Restart Application (Optional)

**Via Cloudron Dashboard:**
1. Go to https://my.sbvu.ac.in
2. Find "Acute Pain Service" app
3. Click "Restart" button

**Via Command Line (if you have access):**
```bash
sudo systemctl restart apache2
```

---

## ✅ Post-Deployment Verification

### Test in Browser

1. **Homepage**: https://aps.sbvu.ac.in
   - ✅ Should load without errors
   - ✅ Check footer shows "Version 1.2.0"

2. **Login**: https://aps.sbvu.ac.in/auth/login
   - ✅ No test credentials box (removed in v1.2.0)
   - ✅ Can login with your credentials

3. **Master Data Dashboard**: https://aps.sbvu.ac.in/masterdata
   - ✅ Should display all 9 master data types
   - ✅ Click on "Specialties" → should show 20 items
   - ✅ Click on "Catheter Indications" → should show 18 items

4. **Test CRUD Operations**:
   - ✅ Create new specialty
   - ✅ Edit existing specialty
   - ✅ Drag & drop to reorder
   - ✅ Export to CSV
   - ✅ Soft delete and restore

5. **Test Specialty Filtering**:
   - ✅ Go to patient registration form
   - ✅ Select a specialty from dropdown
   - ✅ Verify surgery dropdown filters correctly

---

## 🔄 Rollback Procedure (If Needed)

If something goes wrong:

```bash
# 1. Restore database
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < $BACKUP_DIR/database_backup.sql

# 2. Restore application files
cd /app/data
rm -rf src config documentation storage
tar -xzf $BACKUP_DIR/app_files_backup.tar.gz -C / --strip-components=3

# 3. Restore .env
cp $BACKUP_DIR/.env.backup .env

# 4. Verify
cat VERSION
# Should show old version (1.1.3 or earlier)

# 5. Restart application
# Via Cloudron dashboard
```

---

## 📊 Deployment Checklist

- [ ] SSH into server
- [ ] Navigate to /app/data
- [ ] Create backup directory
- [ ] Backup database
- [ ] Backup .env file
- [ ] Backup application files
- [ ] Download v1.2.0 from GitHub
- [ ] Extract archive
- [ ] Copy new files to /app/data
- [ ] Verify .env file integrity
- [ ] Update version in .env
- [ ] Run migration 013
- [ ] Verify 4 new tables created
- [ ] Run migration 014
- [ ] Verify specialty_id column added
- [ ] Seed master data (optional)
- [ ] Verify data counts
- [ ] Set permissions
- [ ] Clean up temp files
- [ ] Verify VERSION file
- [ ] Restart application
- [ ] Test homepage
- [ ] Test login
- [ ] Test master data dashboard
- [ ] Test CRUD operations
- [ ] Test specialty filtering

---

## 🎯 Summary of What Changed

**From the Git-Based Commands:**
- ❌ WRONG: `cd /app/code`
- ✅ CORRECT: `cd /app/data`

- ❌ WRONG: `git pull origin aps.sbvu.ac.in`
- ✅ CORRECT: Manual download and copy

**Benefits of Manual Method:**
- ✅ No risk of overwriting .env
- ✅ No git setup required
- ✅ Clear control over what gets updated
- ✅ Easier to verify each step
- ✅ Safer for production

---

## 📝 Next Time You Deploy

**For future updates (e.g., v1.3.0):**

1. Repeat this process with new version tag
2. OR set up git in `/app/data` for faster deployments
3. Always backup before deploying
4. Always verify .env file after copying files
5. Test thoroughly before considering deployment complete

---

## 📞 Troubleshooting

### Issue: "Table already exists" error

**Solution:**
```bash
# Skip migration 013 if tables already exist
# Just run migration 014
```

### Issue: ".env file missing database credentials"

**Solution:**
```bash
# Restore from backup
cp $BACKUP_DIR/.env.backup /app/data/.env

# Verify
cat /app/data/.env | grep DB_HOST
```

### Issue: "Permission denied" on storage/sessions

**Solution:**
```bash
chmod 777 /app/data/storage/sessions
```

### Issue: "MasterDataController not found"

**Solution:**
```bash
# Re-copy src directory
cd /tmp/acute-pain-service-1.2.0
cp -r src /app/data/

# Verify
ls -la /app/data/src/Controllers/MasterDataController.php
```

---

## ✅ Success!

If all verification steps pass, **v1.2.0 is successfully deployed!** 🎉

Your Acute Pain Service application now has:
- ✅ Master Data Management System
- ✅ 4 new lookup tables
- ✅ Enhanced existing tables
- ✅ Drag & drop reordering
- ✅ CSV export
- ✅ Specialty-based surgery filtering

**Access at:** https://aps.sbvu.ac.in/masterdata

---

**Deployment Method:** Manual (No Git)  
**Target:** aps.sbvu.ac.in (Cloudron)  
**Application Path:** /app/data  
**Date:** January 24, 2026  
**Version Deployed:** 1.2.0
