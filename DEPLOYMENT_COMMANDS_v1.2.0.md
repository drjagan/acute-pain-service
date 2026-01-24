# Production Deployment Commands - v1.2.0
**Target:** aps.sbvu.ac.in (Cloudron)  
**Date:** January 24, 2026

---

## Quick Start - Copy & Paste Commands

### 1. Connect to Server
```bash
ssh cloudron@aps.sbvu.ac.in
```

### 2. Navigate to App Directory
```bash
cd /app/code
```

### 3. Create Backup Directory
```bash
BACKUP_DIR="/app/data/backups/v1.2.0-$(date +%Y%m%d-%H%M%S)"
mkdir -p $BACKUP_DIR
echo "Backup directory: $BACKUP_DIR"
```

### 4. Backup Database
```bash
mysqldump -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e > $BACKUP_DIR/database_backup.sql

# Verify backup
ls -lh $BACKUP_DIR/database_backup.sql
```

### 5. Backup Application Files
```bash
tar -czf $BACKUP_DIR/app_code.tar.gz /app/code
ls -lh $BACKUP_DIR/app_code.tar.gz
```

### 6. Pull Latest Code
```bash
cd /app/code
git fetch origin
git pull origin aps.sbvu.ac.in

# Verify version
cat VERSION
```

### 7. Run Migration 013 (Create New Lookup Tables)
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < database/migrations/013_create_new_lookup_tables.sql
```

**Verify:**
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  -e "SHOW TABLES LIKE 'lookup_%';" a916f81cc97ef00e
```

**Expected Output:** Should show 9 tables including the 4 new ones:
- lookup_catheter_indications ✨ NEW
- lookup_removal_indications ✨ NEW  
- lookup_sentinel_events ✨ NEW
- lookup_specialties ✨ NEW

### 8. Run Migration 014 (Update Surgeries with Specialties)
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < database/migrations/014_update_surgeries_with_specialties.sql
```

**Verify:**
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  -e "DESCRIBE lookup_surgeries;" a916f81cc97ef00e
```

**Expected:** Should show `specialty_id` column (INT, nullable, with FK)

### 9. Seed Master Data (Optional but Recommended)
```bash
# Check if seeder exists
ls -la database/seeders/MasterDataSeeder.sql

# Run seeder
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < database/seeders/MasterDataSeeder.sql
```

**Verify data counts:**
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
- specialties: ~20
- catheter_indications: ~18
- removal_indications: ~7
- sentinel_events: ~30
- surgeries: ~75
- drugs: ~16
- adjuvants: ~12
- comorbidities: ~36
- red_flags: ~19

### 10. Clear Cache
```bash
# Clear PHP sessions
rm -rf /tmp/php_sessions/*

# Clear application cache (if exists)
rm -rf /app/data/cache/*
```

### 11. Restart Application
**Via Cloudron Dashboard:**
1. Go to https://my.sbvu.ac.in
2. Find "Acute Pain Service" app
3. Click "Restart" button

**OR via command line (if you have Apache access):**
```bash
sudo systemctl restart apache2
```

---

## Verification Commands

### Database Structure Check
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e << 'EOF'
SHOW TABLES LIKE 'lookup_%';
DESCRIBE lookup_surgeries;
DESCRIBE lookup_specialties;
DESCRIBE lookup_catheter_indications;
EOF
```

### Check Specialty-Surgery Relationships
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  -e "SELECT s.name AS specialty, COUNT(su.id) AS surgery_count 
      FROM lookup_specialties s 
      LEFT JOIN lookup_surgeries su ON s.id = su.specialty_id 
      GROUP BY s.id, s.name 
      ORDER BY surgery_count DESC;" a916f81cc97ef00e
```

### Check Version
```bash
cat /app/code/VERSION
```

Should output: `1.2.0`

---

## Web-Based Verification (Open in Browser)

After deployment, verify these URLs:

1. **Homepage**  
   https://aps.sbvu.ac.in  
   ✓ Should load without errors  
   ✓ Check version in footer (1.2.0)

2. **Master Data Dashboard**  
   https://aps.sbvu.ac.in/masterdata  
   ✓ Should display all 9 master data types

3. **Individual Master Data Types:**
   - https://aps.sbvu.ac.in/masterdata/specialties
   - https://aps.sbvu.ac.in/masterdata/catheter_indications
   - https://aps.sbvu.ac.in/masterdata/removal_indications
   - https://aps.sbvu.ac.in/masterdata/sentinel_events
   - https://aps.sbvu.ac.in/masterdata/surgeries
   - https://aps.sbvu.ac.in/masterdata/drugs
   - https://aps.sbvu.ac.in/masterdata/adjuvants
   - https://aps.sbvu.ac.in/masterdata/comorbidities
   - https://aps.sbvu.ac.in/masterdata/red_flags

4. **Test CRUD Operations:**
   - Create new specialty
   - Edit existing specialty
   - Test drag & drop reordering
   - Export to CSV
   - Soft delete and restore

5. **Test Specialty Filtering:**
   - Navigate to patient registration form
   - Select a specialty from dropdown
   - Verify surgery dropdown filters to show only that specialty's surgeries

---

## Rollback Commands (If Needed)

### Restore Database from Backup
```bash
# List available backups
ls -lh /app/data/backups/

# Restore (replace YYYYMMDD-HHMMSS with actual backup timestamp)
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < /app/data/backups/v1.2.0-YYYYMMDD-HHMMSS/database_backup.sql
```

### Rollback Code to Previous Version
```bash
cd /app/code

# View recent commits
git log --oneline -10

# Reset to previous commit (replace HASH with actual commit hash)
git reset --hard HASH

# Verify
cat VERSION
```

### Drop New Tables (Nuclear Option)
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e << 'EOF'
DROP TABLE IF EXISTS lookup_catheter_indications;
DROP TABLE IF EXISTS lookup_removal_indications;
DROP TABLE IF EXISTS lookup_sentinel_events;
DROP TABLE IF EXISTS lookup_specialties;
ALTER TABLE lookup_surgeries DROP COLUMN IF EXISTS specialty_id;
EOF
```

---

## Post-Deployment Checklist

- [ ] Database backup created and verified
- [ ] Application backup created and verified
- [ ] Git pulled successfully (version 1.2.0)
- [ ] Migration 013 executed successfully
- [ ] Migration 014 executed successfully
- [ ] All 9 lookup tables exist
- [ ] Seeder data loaded (optional)
- [ ] Application restarted
- [ ] Homepage loads without errors
- [ ] Master data dashboard accessible
- [ ] All 9 master data types visible
- [ ] CRUD operations tested
- [ ] Specialty filtering tested in patient forms
- [ ] No PHP errors in logs
- [ ] No JavaScript console errors
- [ ] Performance acceptable (<2 second page loads)

---

## Support & Documentation

- **Full Deployment Guide:** INSTALL.md
- **Migration Reference:** MIGRATION_QUICK_REFERENCE.md
- **Release Notes:** documentation/releases/RELEASE_NOTES_v1.2.0.md
- **Troubleshooting:** documentation/troubleshooting/MASTER_DATA_FIXES.md

---

## Database Credentials (Quick Reference)

```
Host:     mysql
Port:     3306
Database: a916f81cc97ef00e
User:     a916f81cc97ef00e
Password: 33050ba714a937bf69970570779e802c33b9faa11e4864d4
```

**MySQL Connect:**
```bash
mysql -h mysql -u a916f81cc97ef00e -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 a916f81cc97ef00e
```

---

**Deployment prepared by:** Claude Code  
**Date:** January 24, 2026  
**Version:** 1.2.0 - Master Data Management System
