# 🚀 Production Update Guide - aps.sbvu.ac.in

**Deployment:** Cloudron  
**Domain:** https://aps.sbvu.ac.in  
**Version:** 1.2.0  
**Last Updated:** January 16, 2026

---

## 📋 Overview

This guide provides step-by-step instructions for updating the production Acute Pain Service application from **v1.1.3** to **v1.2.0** on Cloudron at aps.sbvu.ac.in.

**What's New in v1.2.0:**
- ✅ Master Data Management System (9 data types)
- ✅ Drag & drop reordering
- ✅ Specialty-based surgery filtering
- ✅ 4 new database tables
- ✅ Enhanced existing lookup tables
- ✅ 10,000+ lines of new code

---

## ⚠️ CRITICAL: Backup First!

**NEVER skip this step!** Always backup before any production update.

### Step 1: Backup Database

```bash
# SSH into your Cloudron server
ssh root@your-cloudron-server

# Navigate to app directory
cd /app/data

# Create backup with timestamp
mysqldump -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e > backup_v1.1.3_$(date +%Y%m%d_%H%M%S).sql

# Verify backup was created
ls -lh backup_*.sql
```

### Step 2: Backup Application Files

```bash
# Create backup of current application
cd /app
tar -czf data_backup_$(date +%Y%m%d_%H%M%S).tar.gz data/

# Or backup just the critical files
cp data/.env data/.env.backup
cp -r data/public/uploads data/uploads_backup
cp -r data/logs data/logs_backup
```

---

## 📥 Download and Deploy v1.2.0

### Option A: Via Git (Recommended)

```bash
# Navigate to app directory
cd /app/data

# Fetch latest changes
git fetch origin

# Check current branch
git branch

# Pull latest updates from deployment branch
git pull origin aps.sbvu.ac.in

# Verify version
grep "APP_VERSION" .env.production.sbvu
# Should show: APP_VERSION=1.2.0
```

### Option B: Manual Download

```bash
# Download release from GitHub
cd /tmp
wget https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.2.0.tar.gz
tar -xzf v1.2.0.tar.gz

# Backup current installation
cd /app
mv data data.backup.$(date +%Y%m%d)

# Copy new files
cp -r /tmp/acute-pain-service-1.2.0 /app/data

# Restore production config
cp data.backup.*/env.production.sbvu /app/data/.env

# Restore uploads and logs
cp -r data.backup.*/public/uploads/* /app/data/public/uploads/
cp -r data.backup.*/logs/* /app/data/logs/
```

---

## 🗄️ Database Migration

This is the **MOST IMPORTANT** step for v1.2.0 update!

### Step 1: Check Current Database Version

```bash
cd /app/data

# Check if new tables exist
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  -e "SHOW TABLES LIKE 'lookup_catheter_indications';"

# If it returns empty, you need to run migrations
# If it shows the table, migrations are already applied
```

### Step 2: Run Database Migrations

**Two migration files need to be imported in order:**

#### Migration 1: Create New Lookup Tables

**File:** `src/Database/migrations/013_create_new_lookup_tables.sql`

**What it does:**
- Creates `lookup_catheter_indications` table
- Creates `lookup_removal_indications` table
- Creates `lookup_sentinel_events` table
- Creates `lookup_specialties` table

**Import via Command Line:**
```bash
cd /app/data

mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  < src/Database/migrations/013_create_new_lookup_tables.sql
```

**Import via PhpMyAdmin:**
1. Log into PhpMyAdmin (if available on your Cloudron)
2. Select database: `a916f81cc97ef00e`
3. Click **"Import"** tab
4. Click **"Choose File"**
5. Navigate to: `/app/data/src/Database/migrations/013_create_new_lookup_tables.sql`
6. Click **"Go"** at the bottom
7. Wait for success message

#### Migration 2: Update Existing Tables

**File:** `src/Database/migrations/014_update_surgeries_with_specialties.sql`

**What it does:**
- Adds `specialty_id` foreign key to `lookup_surgeries` table
- Adds `sort_order` column to existing lookup tables
- Adds `deleted_at` column for soft deletes
- Renames `lookup_complications` to `lookup_red_flags`

**Import via Command Line:**
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  < src/Database/migrations/014_update_surgeries_with_specialties.sql
```

**Import via PhpMyAdmin:**
1. In PhpMyAdmin, select database: `a916f81cc97ef00e`
2. Click **"Import"** tab
3. Choose file: `014_update_surgeries_with_specialties.sql`
4. Click **"Go"**

### Step 3: Seed Master Data (Optional but Recommended)

**File:** `src/Database/seeders/MasterDataSeeder.sql`

**What it does:**
- Inserts 14 catheter indications
- Inserts 10 removal indications
- Inserts 8 sentinel events
- Inserts 15 specialties
- Links 50+ surgeries to specialties

**Import via Command Line:**
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  < src/Database/seeders/MasterDataSeeder.sql
```

**Import via PhpMyAdmin:**
1. Select database: `a916f81cc97ef00e`
2. Import: `src/Database/seeders/MasterDataSeeder.sql`

---

## ✅ Verify Migration Success

### Check New Tables Created

```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  -e "SHOW TABLES LIKE 'lookup_%';"
```

**Expected output (should show 9 tables):**
```
lookup_adjuvants
lookup_catheter_indications    ← NEW
lookup_comorbidities
lookup_drugs
lookup_red_flags
lookup_removal_indications     ← NEW
lookup_sentinel_events         ← NEW
lookup_specialties             ← NEW
lookup_surgeries
```

### Check Data Was Seeded

```bash
# Check catheter indications
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  -e "SELECT COUNT(*) as count FROM lookup_catheter_indications;"

# Should return: count = 14

# Check specialties
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  -e "SELECT COUNT(*) as count FROM lookup_specialties;"

# Should return: count = 15
```

---

## 🔧 Set Permissions

```bash
cd /app/data

# Set ownership (Cloudron user)
chown -R cloudron:cloudron /app/data

# Set directory permissions
find /app/data -type d -exec chmod 755 {} \;

# Set file permissions
find /app/data -type f -exec chmod 644 {} \;

# Make logs writable
chmod 755 logs
chmod 666 logs/*.log 2>/dev/null || true

# Make uploads/exports writable
chmod 755 public/uploads
chmod 755 public/exports

# Protect .env file
chmod 600 .env.production.sbvu 2>/dev/null || chmod 600 .env
```

---

## 🧪 Test the Update

### 1. Check Application Version

Visit: https://aps.sbvu.ac.in

- Should load without errors
- Login page should appear

### 2. Test Login

**Credentials:**
- Username: `admin` (or your admin username)
- Password: Your admin password

### 3. Navigate to Master Data

After login:
1. Click **"Settings"** in the sidebar
2. Scroll to **"Master Data"** section
3. You should see **9 cards** with icons:
   - 🩺 Catheter Indications
   - 📋 Removal Indications
   - 🚨 Sentinel Events
   - 🏥 Specialties
   - 🔪 Surgeries
   - 💊 Drugs
   - 💉 Adjuvants
   - 🩹 Comorbidities
   - ⚠️ Red Flags

### 4. Test a Master Data Type

1. Click on **"Specialties"** card
2. You should see a list of 15 specialties
3. Try searching: type "ortho" in search box
4. Should show "Orthopedics"

### 5. Test Patient Form with Specialty Filtering

1. Navigate to **"Patients"** → **"Add New"**
2. In the form, find **"Specialty"** dropdown
3. Select **"Orthopedics"**
4. The **"Surgery"** dropdown should auto-filter to show only orthopedic surgeries
5. Try selecting another specialty - surgery list should update

### 6. Test Catheter Form

1. Navigate to **"Catheters"** → **"Add New"**
2. Find **"Catheter Indication"** dropdown
3. Should show 14 options (not hardcoded anymore!)
4. Select "Post-operative analgesia"

---

## 🔍 Troubleshooting

### Issue 1: Tables Already Exist Error

**Error Message:**
```
Table 'lookup_catheter_indications' already exists
```

**Solution:**
This is normal! The migrations use `CREATE TABLE IF NOT EXISTS`. The tables are already created, which is fine.

### Issue 2: Specialty Dropdown Not Showing

**Symptoms:**
- Patient form doesn't show specialty dropdown
- Surgery dropdown shows all surgeries (not filtered)

**Solution:**
```bash
# Check if specialty_id column exists in lookup_surgeries
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  -e "DESCRIBE lookup_surgeries;"

# Should show 'specialty_id' column
# If not, re-run migration 014
```

### Issue 3: Master Data Page Shows Error

**Error:** 500 Internal Server Error when accessing `/masterdata`

**Solution:**
```bash
# Check logs
tail -50 /app/data/logs/error.log

# Check if MasterDataController exists
ls -la /app/data/src/Controllers/MasterDataController.php

# Check if config/masterdata.php exists
ls -la /app/data/config/masterdata.php

# If files missing, re-pull from git or re-extract release
```

### Issue 4: Drag & Drop Not Working

**Symptoms:**
- Can't reorder items in master data lists
- Drag handle (⋮⋮) not working

**Solution:**
1. Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
2. Check browser console for JavaScript errors (F12 → Console)
3. Ensure `sort_order` column exists in table

### Issue 5: Empty Dropdown in Forms

**Symptoms:**
- Catheter indication dropdown is empty
- Removal indication dropdown is empty

**Solution:**
```bash
# Import the seeder data
cd /app/data
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  < src/Database/seeders/MasterDataSeeder.sql
```

### Issue 6: Permission Denied Errors

**Error:** Can't write to logs or uploads

**Solution:**
```bash
# Fix permissions
chmod 755 /app/data/logs
chmod 755 /app/data/public/uploads
chmod 755 /app/data/public/exports
chown -R cloudron:cloudron /app/data
```

---

## 📊 Database Changes Summary

### New Tables (4)

| Table | Rows | Purpose |
|-------|------|---------|
| `lookup_catheter_indications` | 14 | Reasons for catheter insertion |
| `lookup_removal_indications` | 10 | Reasons for catheter removal |
| `lookup_sentinel_events` | 8 | Critical adverse events |
| `lookup_specialties` | 15 | Medical specialties |

### Modified Tables (5)

| Table | Changes |
|-------|---------|
| `lookup_surgeries` | Added `specialty_id` FK, `sort_order`, `deleted_at` |
| `lookup_drugs` | Added `sort_order`, `deleted_at` |
| `lookup_adjuvants` | Added `sort_order`, `deleted_at` |
| `lookup_comorbidities` | Added `sort_order`, `deleted_at` |
| `lookup_complications` | Renamed to `lookup_red_flags`, added columns |

### Total Database Size Increase

- **Before v1.2.0:** ~500 KB
- **After v1.2.0:** ~650 KB (+150 KB)
- **Additional Rows:** ~100 new entries

---

## 🔄 Rollback Procedure (If Needed)

If something goes wrong and you need to rollback:

### Step 1: Stop Application

```bash
# In Cloudron, stop the app via web interface
# OR via CLI:
cloudron restart --app aps.sbvu.ac.in
```

### Step 2: Restore Database

```bash
cd /app/data

# Find your backup
ls -lh backup_v1.1.3_*.sql

# Restore database
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  < backup_v1.1.3_YYYYMMDD_HHMMSS.sql
```

### Step 3: Restore Application Files

```bash
# Remove new version
cd /app
rm -rf data

# Restore old version
tar -xzf data_backup_YYYYMMDD_HHMMSS.tar.gz
```

### Step 4: Restart Application

```bash
cloudron restart --app aps.sbvu.ac.in
```

---

## 📚 Post-Update Tasks

### 1. Update Master Data (Optional)

After installation, you can customize master data:

1. Login as admin
2. Go to **Settings** → **Master Data**
3. Click on any data type (e.g., "Specialties")
4. Add, edit, or deactivate entries as needed

### 2. Test All Forms

- ✅ Patient Registration
- ✅ Catheter Insertion
- ✅ Drug Regime Entry
- ✅ Functional Outcomes
- ✅ Catheter Removal

### 3. Train Users (If Needed)

**New Features to Demonstrate:**
- Master data management interface
- Specialty-based surgery filtering
- New catheter indication dropdowns
- Drag & drop reordering

### 4. Monitor Logs

```bash
# Watch logs for errors
tail -f /app/data/logs/error.log

# Watch application logs
tail -f /app/data/logs/app.log
```

---

## 📞 Support

### Check Logs

```bash
# Application errors
tail -100 /app/data/logs/error.log

# Application activity
tail -100 /app/data/logs/app.log

# PHP errors
tail -100 /app/data/logs/php-errors.log
```

### Common Log Locations

```
/app/data/logs/error.log         - PHP and application errors
/app/data/logs/app.log           - Application activity log
/app/data/logs/php-errors.log    - PHP-specific errors
/app/data/logs/install.log       - Installation wizard log
```

### Getting Help

1. **Check Documentation:**
   - `IMPLEMENTATION_MASTER_DATA.md` - Complete feature guide
   - `FIXES_APPLIED.md` - Known issues and fixes
   - `CLOUDRON_DEPLOYMENT_GUIDE.md` - Cloudron specifics

2. **GitHub Issues:**
   - https://github.com/drjagan/acute-pain-service/issues

3. **Contact:**
   - Email: drjagan@gmail.com
   - Create issue on GitHub

---

## ✅ Update Checklist

Use this checklist to track your update progress:

### Pre-Update
- [ ] Backup database created
- [ ] Backup application files created
- [ ] Current version verified (should be v1.1.3)
- [ ] Downtime window scheduled (if needed)

### Update Process
- [ ] Application files updated (via git or download)
- [ ] Migration 013 imported (new lookup tables)
- [ ] Migration 014 imported (table updates)
- [ ] Seeder data imported (master data entries)
- [ ] Permissions set correctly
- [ ] Version check shows v1.2.0

### Testing
- [ ] Application loads without errors
- [ ] Login works
- [ ] Master Data page accessible
- [ ] 9 master data types visible
- [ ] Specialty filtering works in patient form
- [ ] Catheter indications dropdown populated
- [ ] Drag & drop reordering works
- [ ] Existing patient data intact
- [ ] Reports still generate

### Post-Update
- [ ] Monitor logs for 24 hours
- [ ] Train users on new features
- [ ] Update internal documentation
- [ ] Mark update as complete

---

## 🎉 Update Complete!

Once all checklist items are complete, your production instance at **https://aps.sbvu.ac.in** is successfully updated to **v1.2.0**!

**Key New Features Available:**
- ✅ Master Data Management at `/masterdata`
- ✅ 9 Data types with full CRUD
- ✅ Specialty-based surgery filtering
- ✅ Enhanced dropdown options
- ✅ Drag & drop reordering

**Next Version:** v1.3.0 (Coming soon with concurrent editing protection)

---

**Last Updated:** January 16, 2026  
**Guide Version:** 1.0  
**For:** Production deployment at aps.sbvu.ac.in
