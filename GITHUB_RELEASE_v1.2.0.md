# 🎯 Acute Pain Service v1.2.0 - Master Data Management System

**Major Feature Release** with complete Master Data Management System, new lookup tables, and enhanced admin capabilities.

---

## ✨ Highlights

### Master Data Management Dashboard
- **Centralized Interface**: Manage all 9 lookup data types from `/masterdata`
- **Drag & Drop Reordering**: Intuitive UI for organizing data
- **CSV Export**: Download any data type for analysis
- **Soft Delete**: Preserve data integrity with restore capability
- **Specialty Filtering**: Smart surgery filtering based on medical specialty

### New Database Tables (4)
- 🩺 **Catheter Indications** - Reasons for catheter insertion (18 items)
- 📋 **Removal Indications** - Reasons for catheter removal (7 items)
- 🚨 **Sentinel Events** - Critical complications tracking (30 items)
- 🏥 **Specialties** - Medical specialties with surgery relationships (20 items)

### Enhanced Tables (5)
All existing lookup tables now have:
- `sort_order` column for custom ordering
- `deleted_at` column for soft deletes
- Specialty relationships for surgeries
- Active/inactive toggling

---

## 🚀 Quick Installation

### New Installation
```bash
wget https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.2.0.tar.gz
tar -xzf v1.2.0.tar.gz
cd acute-pain-service-1.2.0

# Use installation wizard at: http://your-domain/install/
# Or import database/aps_database_complete.sql via PhpMyAdmin
```

### Upgrade from v1.1.3
```bash
# 1. Backup database
mysqldump -u root -p aps_database > backup_v1.1.3.sql

# 2. Run migrations
mysql -u root -p aps_database < src/Database/migrations/013_create_new_lookup_tables.sql
mysql -u root -p aps_database < src/Database/migrations/014_update_surgeries_with_specialties.sql

# 3. Optional: Seed master data
mysql -u root -p aps_database < src/Database/seeders/MasterDataSeeder.sql
```

---

## 📊 What's New

### Master Data Features
- **CRUD Operations**: Create, Read, Update, Delete with validation
- **Search & Filter**: Real-time search across all master data
- **Bulk Export**: CSV export for all 9 data types
- **Active/Inactive Toggle**: Enable/disable entries without deletion
- **Duplicate Prevention**: Automatic validation for unique names
- **Restore Deleted**: Recover soft-deleted entries

### Code Additions
- `MasterDataController.php` (28KB) - Complete CRUD logic
- `BaseLookupModel.php` (11KB) - 15+ reusable methods
- 4 specialized lookup models
- 4 master data views (index, list, form, manage-children)
- `config/masterdata.php` - Configuration for all types

### Documentation
- **CODING_STANDARDS.md** (14KB) - PSR-12 compliance guide
- **MASTER_DATA_SYSTEM.md** (16KB) - Implementation details
- **MASTER_DATA_FIXES.md** (8.7KB) - Troubleshooting guide
- **RELEASE_NOTES_v1.2.0.md** (20KB) - Complete release notes

---

## 🐛 Bug Fixes

### Session Handling (v1.2.1)
- Fixed session permission errors on XAMPP/localhost
- Added fallback session storage path (`storage/sessions/`)
- Graceful error handling for non-writable session directories

### Security
- Removed test credentials from login page
- Improved CSRF protection on all forms
- SQL injection prevention with prepared statements

---

## 📈 Statistics

**Code Changes:**
- 41 files changed
- 10,785+ lines added
- 4 new database tables
- 5 enhanced tables
- 150+ seeded records

**Performance:**
- Page load times: <2 seconds
- Drag & drop: Instant response
- CSV export: <1 second for 100 rows
- Search: Real-time (as-you-type)

---

## 🔄 Database Migrations

### Migration 013: Create New Lookup Tables
Creates 4 new tables:
- `lookup_catheter_indications`
- `lookup_removal_indications`
- `lookup_sentinel_events`
- `lookup_specialties`

All with consistent schema: `id`, `name`, `active`, `sort_order`, `deleted_at`, timestamps

### Migration 014: Update Surgeries with Specialties
- Adds `specialty_id` foreign key to `lookup_surgeries`
- Adds `sort_order` to drugs and adjuvants
- Adds `deleted_at` for soft deletes

### Seeder: MasterDataSeeder.sql
Populates all 9 lookup tables with sample data (optional but recommended)

---

## 🔗 URLs & Access

**Master Data Dashboard:**
- Navigate to **Settings → Master Data** in the app
- Direct URL: `https://your-domain/masterdata`

**Individual Data Types:**
- Catheter Indications: `/masterdata/catheter_indications`
- Removal Indications: `/masterdata/removal_indications`
- Sentinel Events: `/masterdata/sentinel_events`
- Specialties: `/masterdata/specialties`
- Surgeries: `/masterdata/surgeries`
- Drugs: `/masterdata/drugs`
- Adjuvants: `/masterdata/adjuvants`
- Comorbidities: `/masterdata/comorbidities`
- Red Flags: `/masterdata/red_flags`

---

## ⚠️ Breaking Changes

**None!** v1.2.0 is fully backward compatible with v1.1.3.

All existing features continue to work. New features are additive.

---

## 🎯 Deployment Notes

### Production Deployment (Cloudron)
For Cloudron deployments at aps.sbvu.ac.in:
- See `DEPLOYMENT_COMMANDS_v1.2.0.md` for step-by-step guide
- See `deploy-v1.2.0-to-production.sh` for automated deployment
- Backup created automatically before deployment

### Localhost/Development
- Works on XAMPP, WAMP, MAMP
- Session fixes included for localhost environments
- Test script: `test_v1.2_localhost.php`

### Requirements
- PHP 7.4+ (8.0+ recommended)
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite
- 50MB disk space minimum

---

## 📚 Documentation

**Complete Release Notes:**
- `documentation/releases/RELEASE_NOTES_v1.2.0.md` (20KB)

**Implementation Guide:**
- `documentation/development/MASTER_DATA_SYSTEM.md` (16KB)

**Troubleshooting:**
- `documentation/troubleshooting/MASTER_DATA_FIXES.md` (8.7KB)

**Coding Standards:**
- `documentation/development/CODING_STANDARDS.md` (14KB)

**Deployment:**
- `DEPLOYMENT_COMMANDS_v1.2.0.md` - Production commands
- `deploy-v1.2.0-to-production.sh` - Automated script
- `DEPLOY_NOW.md` - Quick start guide

---

## 🙏 Acknowledgments

This release includes comprehensive codebase cleanup, reorganized documentation, and production deployment guides for seamless upgrades.

**Contributors:**
- Dr. Jagan Mohan R - Project Lead & Development

---

## 🔗 Links

- **Repository**: https://github.com/drjagan/acute-pain-service
- **Issues**: https://github.com/drjagan/acute-pain-service/issues
- **Documentation**: `/documentation/` folder
- **Production**: https://aps.sbvu.ac.in (SBVU deployment)

---

## 📝 Next Steps

After installation:
1. Access Master Data Dashboard: `/masterdata`
2. Review and customize lookup data for your institution
3. Add specialties relevant to your hospital
4. Link surgeries to appropriate specialties
5. Train staff on new features

---

**Full changelog**: https://github.com/drjagan/acute-pain-service/compare/v1.1.3...v1.2.0

**Release Date**: January 24, 2026  
**Version**: 1.2.0  
**License**: MIT
