# 🚀 Deploy v1.2.0 to Production NOW

**Quick deployment guide for aps.sbvu.ac.in**

---

## ✅ Pre-Deployment Status

✓ Production branch `aps.sbvu.ac.in` is ready  
✓ All v1.2.0 code merged and pushed to GitHub  
✓ Migration files ready (013, 014)  
✓ Seeder file ready (MasterDataSeeder.sql)  
✓ Deployment scripts created and tested  

---

## 📋 Two Ways to Deploy

### Option 1: Use the Full Deployment Script (Recommended)
```bash
./deploy-v1.2.0-to-production.sh
```

This interactive script will:
- Run pre-deployment checks
- Show backup plan
- Display step-by-step deployment instructions
- Provide verification commands
- Include rollback procedures

### Option 2: Use Quick Command Reference
Open `DEPLOYMENT_COMMANDS_v1.2.0.md` for copy-paste ready commands

---

## 🎯 Quick Start (3 Minutes)

### Step 1: SSH into Server
```bash
ssh cloudron@aps.sbvu.ac.in
```

### Step 2: Quick Deployment (Copy all at once)
```bash
# Navigate to app
cd /app/code

# Create backup
BACKUP_DIR="/app/data/backups/v1.2.0-$(date +%Y%m%d-%H%M%S)"
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e > $BACKUP_DIR/database_backup.sql

# Pull latest code
git pull origin aps.sbvu.ac.in

# Run migrations
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/013_create_new_lookup_tables.sql

mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/014_update_surgeries_with_specialties.sql

# Seed data (optional but recommended)
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/seeders/MasterDataSeeder.sql

# Verify
cat VERSION
```

### Step 3: Restart App
1. Go to https://my.sbvu.ac.in
2. Find "Acute Pain Service"
3. Click "Restart"

### Step 4: Test
Open https://aps.sbvu.ac.in/masterdata

---

## 📊 What's Being Deployed

### New Features
- Master Data Management Dashboard (`/masterdata`)
- 4 new lookup tables:
  - Catheter Indications (18 items)
  - Removal Indications (7 items)
  - Sentinel Events (30 items)
  - Specialties (20 items)
- Specialty-based surgery filtering
- Drag & drop reordering
- CSV export for all master data
- Soft delete with restore

### Database Changes
- Migration 013: Creates 4 new lookup tables
- Migration 014: Adds specialty_id to surgeries table
- Seeder: Populates all tables with 150+ sample records

### Files Added/Modified
- 41 files changed
- 10,785 lines of code added
- MasterDataController.php (28KB)
- BaseLookupModel.php (11KB)
- 4 new views
- config/masterdata.php

---

## ✅ Verification Checklist

After deployment, verify:

- [ ] Homepage loads (https://aps.sbvu.ac.in)
- [ ] Version shows 1.2.0
- [ ] Master Data dashboard accessible (/masterdata)
- [ ] All 9 types display correctly
- [ ] Can create new specialty
- [ ] Can edit existing item
- [ ] Drag & drop works
- [ ] CSV export works
- [ ] Specialty filtering in patient forms works

---

## 🔄 Rollback (if needed)

```bash
# Restore database
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < $BACKUP_DIR/database_backup.sql

# Rollback code
cd /app/code
git reset --hard 967b10e  # Previous commit before deployment scripts
```

---

## 📞 Support

- Full guide: `DEPLOYMENT_COMMANDS_v1.2.0.md`
- Release notes: `documentation/releases/RELEASE_NOTES_v1.2.0.md`
- Troubleshooting: `documentation/troubleshooting/MASTER_DATA_FIXES.md`

---

## 🎉 Ready to Deploy!

Everything is prepared and tested. The deployment should take about 3-5 minutes.

**Last commit:** eb30970 - Fix migration and seeder paths in deployment scripts  
**Branch:** aps.sbvu.ac.in  
**Target:** https://aps.sbvu.ac.in

---

**Prepared:** January 24, 2026  
**Version:** 1.2.0 - Master Data Management System
