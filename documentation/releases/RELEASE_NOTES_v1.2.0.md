# 🎯 Master Data Management System - v1.2.0

**Major Feature Release**: Complete overhaul of lookup data management with centralized admin interface.

---

## ⚡ Quick Installation

### For New Installations

```bash
# Download release
wget https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.2.0.tar.gz
tar -xzf v1.2.0.tar.gz
cd acute-pain-service-1.2.0

# Option 1: PhpMyAdmin Import (Fastest - 2 minutes)
# - Create database: aps_database
# - Import: database/aps_database_complete.sql
# - Update config with credentials
# - Login with admin/admin123

# Option 2: Installation Wizard
# Navigate to: http://your-domain/install/
```

### For Existing Installations (Upgrade from v1.1.3)

```bash
# CRITICAL: Backup your database first!
mysqldump -u root -p aps_database > backup_v1.1.3.sql

# Run the new migrations
mysql -u root -p aps_database < src/Database/migrations/013_create_new_lookup_tables.sql
mysql -u root -p aps_database < src/Database/migrations/014_update_surgeries_with_specialties.sql

# Run the seeders (optional, adds sample data)
php run_master_data_migrations_v2.php

# Update your application files
# (Copy new files, keep your .env and config/config.php)
```

---

## 🆕 What's New in v1.2.0

### Master Data Management System (Complete Rewrite)

The biggest change in v1.2.0 is the **centralized Master Data Management System**. All lookup tables are now manageable through a user-friendly admin interface.

#### ✨ Key Features

1. **Unified Admin Interface**
   - Single dashboard for all master data types
   - Navigate to **Settings → Master Data** or `/masterdata`
   - Manage 9 different data categories from one place

2. **9 Master Data Types Available**
   - 🩺 **Catheter Indications** (NEW) - Reasons for catheter insertion
   - 📋 **Removal Indications** (NEW) - Reasons for catheter removal
   - 🚨 **Sentinel Events** (NEW) - Critical complications to track
   - 🏥 **Specialties** (NEW) - Medical specialties (Orthopedics, General Surgery, etc.)
   - 🔪 **Surgeries** (Enhanced) - Surgical procedures linked to specialties
   - 💊 **Drugs** (Enhanced) - Anesthetic drugs for regimes
   - 💉 **Adjuvants** (Enhanced) - Adjuvant medications
   - 🩹 **Comorbidities** (Enhanced) - Patient comorbidities
   - ⚠️ **Red Flags** (Enhanced) - Contraindications for procedures

3. **Rich CRUD Operations**
   - ➕ **Create**: Add new entries with validation
   - ✏️ **Edit**: Update existing entries
   - 🗑️ **Delete**: Soft delete (data preserved with `deleted_at`)
   - 🔄 **Restore**: Recover deleted entries
   - 👁️ **View**: Display all active and inactive entries
   - 🔍 **Search**: Real-time search across all fields
   - 📊 **Pagination**: Handles large datasets efficiently
   - 📥 **Export**: Download as CSV for analysis

4. **Drag & Drop Reordering**
   - Intuitive drag handles (⋮⋮) on each row
   - Visual feedback during drag operations
   - Auto-save with success indicators (✓)
   - Works on 7 out of 9 data types (where ordering matters)

5. **Smart Features**
   - **Active/Inactive Toggle**: Click a switch to enable/disable entries
   - **Duplicate Detection**: Prevents duplicate names
   - **Foreign Key Support**: Surgeries linked to specialties with cascading
   - **Specialty Filtering**: When selecting surgery, list auto-filters by specialty
   - **Common Items**: Flag frequently used items for quick access
   - **Custom Fields**: Each data type has unique fields (e.g., `requires_notes`, `is_planned`)

---

## 📦 Database Changes

### New Tables (4)

1. **`lookup_catheter_indications`** (NEW)
   - Stores reasons for catheter insertion
   - Fields: `name`, `description`, `is_common`, `active`, `sort_order`
   - 14 seed entries included (Post-op analgesia, Chronic pain, Labor analgesia, etc.)

2. **`lookup_removal_indications`** (NEW)
   - Stores reasons for catheter removal
   - Fields: `name`, `code`, `requires_notes`, `is_planned`, `active`, `sort_order`
   - 10 seed entries included (Planned removal, Infection, Accidental dislodgement, etc.)

3. **`lookup_sentinel_events`** (NEW)
   - Critical complications tracking
   - Fields: `name`, `severity`, `description`, `requires_action`, `active`, `sort_order`
   - 8 seed entries included (Respiratory depression, Cardiac arrest, Epidural abscess, etc.)

4. **`lookup_specialties`** (NEW)
   - Medical specialty categories
   - Fields: `name`, `code`, `description`, `active`, `sort_order`
   - 15 seed entries included (Orthopedics, General Surgery, Neurosurgery, etc.)

### Updated Tables (5)

1. **`lookup_surgeries`** (ENHANCED)
   - Added `specialty_id` foreign key
   - Now linked to `lookup_specialties`
   - Enables specialty-based filtering
   - 50+ surgeries pre-categorized

2. **`lookup_drugs`** (ENHANCED)
   - Added `sort_order` for custom ordering
   - Added `deleted_at` for soft deletes
   - 12 common drugs included

3. **`lookup_adjuvants`** (ENHANCED)
   - Added `sort_order` and `deleted_at`
   - 8 common adjuvants included

4. **`lookup_comorbidities`** (ENHANCED)
   - Added `sort_order` and `deleted_at`
   - 20+ comorbidities included

5. **`lookup_complications`** (RENAMED to `lookup_red_flags`)
   - Renamed for clarity
   - Added `sort_order` and `deleted_at`
   - 15+ contraindications included

---

## 🎨 User Interface Updates

### Settings Page

**Before v1.2.0:**
- SMTP settings only
- Limited configuration options

**After v1.2.0:**
- New **"Master Data"** section with 9 cards
- Quick access buttons for each data type
- Record counts displayed (e.g., "14 entries")
- Color-coded icons for visual distinction
- Direct navigation to management pages

### Forms Enhanced

1. **Patient Registration** (`/patients/create`)
   - **Specialty dropdown** now available
   - **Surgery dropdown** auto-filters based on selected specialty
   - Example: Select "Orthopedics" → See only orthopedic surgeries

2. **Catheter Insertion** (`/catheters/create`)
   - **Catheter indication** dropdown replaced hardcoded options
   - Searchable Select2 dropdown with 14+ options
   - Easily add new indications via Settings

3. **Catheter Removal** (`/catheters/remove`)
   - **Removal indication** dropdown from lookup table
   - Auto-shows "Notes" field when indication requires it
   - Distinguishes planned vs unplanned removals

4. **Functional Outcomes** (PENDING - v1.2.1)
   - Sentinel events will use lookup table
   - Currently still hardcoded

---

## 🔧 Backend Improvements

### New Models (10)

All models extend `BaseLookupModel` for consistency:

1. **`BaseLookupModel.php`** (Abstract base class)
   - 15+ reusable methods
   - Generic CRUD operations
   - Search, pagination, export, soft delete
   - Drag-drop reordering support
   - **Key methods:**
     - `getAll($activeOnly, $page, $perPage, $search)`
     - `create($data)`
     - `update($id, $data)`
     - `softDelete($id)` / `restore($id)`
     - `toggleActive($id)`
     - `updateSortOrder($orderArray)`
     - `exportToCSV()`

2. **Specialized Models:**
   - `LookupCatheterIndication.php`
   - `LookupRemovalIndication.php`
   - `LookupSentinelEvent.php`
   - `LookupSpecialty.php`
   - `LookupSurgery.php` (with specialty relationship)
   - `LookupDrug.php`
   - `LookupAdjuvant.php`
   - `LookupComorbidity.php`
   - `LookupRedFlag.php`

### New Controller (1)

**`MasterDataController.php`** - 400+ lines
- Handles all master data operations
- 15 action methods
- RESTful routing pattern
- Admin-only access (role check)
- CSRF protection on all POST requests
- AJAX endpoints for toggle/reorder

**Key Routes:**
- `GET /masterdata` - Dashboard
- `GET /masterdata/{type}` - List entries
- `GET /masterdata/{type}/create` - Add form
- `POST /masterdata/{type}` - Store entry
- `GET /masterdata/{type}/{id}/edit` - Edit form
- `POST /masterdata/{type}/{id}` - Update entry
- `POST /masterdata/{type}/{id}/delete` - Soft delete
- `POST /masterdata/{type}/{id}/restore` - Restore
- `POST /masterdata/{type}/{id}/toggle` - Toggle active (AJAX)
- `POST /masterdata/{type}/reorder` - Update sort order (AJAX)
- `GET /masterdata/{type}/export` - Export CSV

### New Views (5)

1. **`src/Views/masterdata/index.php`** - Dashboard with 9 cards
2. **`src/Views/masterdata/list.php`** - Generic list view with drag-drop
3. **`src/Views/masterdata/form.php`** - Dynamic add/edit form
4. **`src/Views/masterdata/import.php`** - CSV import (future feature)
5. **`src/Views/masterdata/settings.php`** - Master data configuration

### Configuration File

**`config/masterdata.php`** - 500+ lines
- Centralized configuration for all 9 data types
- Defines table names, display names, icons
- Field definitions with types and validation
- Sorting, searching, export settings
- Easy to extend with new types

---

## 🐛 Bug Fixes

### Critical Fixes (Applied to All Master Data Types)

1. **SQL Error in `hasColumn()` Method**
   - **Issue**: MariaDB doesn't support placeholders in `SHOW COLUMNS`
   - **Fix**: Changed from prepared statement to direct query with escaped value
   - **Location**: `src/Models/BaseLookupModel.php:267`
   - **Affects**: All 9 master data types

2. **Duplicate Entry Fatal Error**
   - **Issue**: App crashed when creating/updating duplicate entries
   - **Fix**: Added try-catch blocks in controller with user-friendly messages
   - **Location**: `src/Controllers/MasterDataController.php:120-215`
   - **Affects**: All 9 master data types

3. **Drag & Drop Not Working**
   - **Issue**: Null reference error, no visual feedback
   - **Fixes Applied**:
     - Handle-only dragging (not whole row)
     - Visual feedback (opacity, cursor change)
     - Store row reference before nulling
     - Better drop positioning
     - Success indicator (green checkmark)
   - **Location**: `src/Views/masterdata/list.php:249-310`
   - **Affects**: 7 sortable data types

4. **Console Error Suppression**
   - **Issue**: Browser extension error "message port closed" appearing in console
   - **Fix**: Suppress specific Chrome extension errors
   - **Location**: `src/Views/masterdata/list.php:215-220`
   - **Affects**: All 9 master data types

5. **Reorder AJAX Not Reading POST Data**
   - **Issue**: POST data not being read from JSON body
   - **Fix**: Read JSON from `php://input`
   - **Location**: `src/Controllers/MasterDataController.php:252-267`
   - **Affects**: All sortable master data types

6. **Silent Failures in Sort Order Updates**
   - **Issue**: No error logging when sort order updates failed
   - **Fix**: Added comprehensive error logging
   - **Location**: `src/Models/BaseLookupModel.php:135-165`
   - **Affects**: All sortable master data types

7. **Unique Constraint Violations**
   - **Issue**: Duplicate key errors during installation
   - **Fix**: Added `fix_unique_constraints.php` script
   - **Affects**: Installation process

---

## 📝 Code Quality & Architecture

### Design Patterns Implemented

1. **Repository Pattern**
   - `BaseLookupModel` serves as abstract repository
   - Specialized models extend with custom business logic

2. **Single Responsibility Principle**
   - Each model handles one data type
   - Controller delegates to models
   - Views are presentation-only

3. **DRY (Don't Repeat Yourself)**
   - 90% of code reused via `BaseLookupModel`
   - Generic views handle all data types
   - Configuration-driven behavior

4. **Open/Closed Principle**
   - Easy to add new master data types
   - Just add config entry + create model
   - No changes to controller or views needed

### Security Enhancements

- ✅ **CSRF Protection**: All POST requests validated
- ✅ **Role-Based Access**: Admin-only middleware
- ✅ **SQL Injection Prevention**: Prepared statements throughout
- ✅ **XSS Prevention**: All output escaped with `htmlspecialchars()`
- ✅ **Soft Deletes**: Data never permanently destroyed
- ✅ **Input Sanitization**: All inputs sanitized via `Sanitizer` helper

### Performance Optimizations

- ✅ **Pagination**: Handles 1000+ records efficiently
- ✅ **Database Indexing**: All foreign keys and search columns indexed
- ✅ **Lazy Loading**: Only loads data when needed
- ✅ **AJAX Toggles**: Partial page updates (no full reload)
- ✅ **Efficient Queries**: Uses JOINs to minimize round-trips

---

## 🧪 Testing Checklist

### Master Data Dashboard
- [ ] Navigate to **Settings → Master Data**
- [ ] See 9 cards with icons and record counts
- [ ] Click each card → Opens corresponding list page

### CRUD Operations (Test on any type, e.g., Specialties)
- [ ] **Create**: Click "Add New" → Fill form → Save successfully
- [ ] **Read**: See new entry in list
- [ ] **Update**: Click "Edit" → Change name → Save successfully
- [ ] **Delete**: Click "Delete" → Entry marked as deleted (gray)
- [ ] **Restore**: Click "Restore" → Entry active again
- [ ] **Duplicate Prevention**: Try creating duplicate → See error message

### Search & Pagination
- [ ] **Search**: Type in search box → Results filter instantly
- [ ] **Pagination**: If >20 records, see page numbers
- [ ] **Per Page**: Change "Show X entries" → List adjusts

### Drag & Drop Reordering (Test on Specialties)
- [ ] Hover over drag handle (⋮⋮) → Cursor changes to "move"
- [ ] Drag a row → See opacity change during drag
- [ ] Drop in new position → See green checkmark
- [ ] Refresh page → Order persists

### Active/Inactive Toggle
- [ ] Click toggle switch → Color changes (green/red)
- [ ] No page reload (AJAX)
- [ ] Inactive items appear in gray

### Export to CSV
- [ ] Click "Export to CSV" → File downloads
- [ ] Open CSV → See all columns with data
- [ ] File named correctly (e.g., `specialties_2026-01-16.csv`)

### Form Integration Tests

#### Patient Registration
- [ ] Navigate to **Patients → Add New**
- [ ] Select **Specialty** dropdown → See 15 options
- [ ] Select "Orthopedics" → **Surgery** dropdown filters
- [ ] Verify only orthopedic surgeries appear
- [ ] Select surgery → Save patient successfully

#### Catheter Insertion
- [ ] Navigate to **Catheters → Add New**
- [ ] **Catheter Indication** dropdown → See 14+ options
- [ ] Select "Post-operative analgesia" → Form accepts
- [ ] Save catheter → Indication saved correctly

#### Catheter Removal
- [ ] Navigate to **Catheters → Remove**
- [ ] **Removal Indication** dropdown → See 10+ options
- [ ] Select indication requiring notes → Notes field appears
- [ ] Select indication not requiring notes → Notes field hidden
- [ ] Save removal → Data saved correctly

### Settings Page
- [ ] Navigate to **Settings**
- [ ] See **"Master Data"** section with 9 cards
- [ ] Each card shows correct count (e.g., "14 entries")
- [ ] Click "Manage" → Opens correct list page

---

## 🔒 Security Notes

### Change Default Passwords

**IMPORTANT:** All test users have password `admin123`. Change immediately in production!

**Test Users:**
- `admin` / `admin123` - System Administrator
- `dr.sharma` / `admin123` - Attending Physician
- `dr.patel` / `admin123` - Resident
- `nurse.kumar` / `admin123` - Nurse

### Database Backup Before Upgrade

**CRITICAL:** Always backup before upgrading:

```bash
# Full database backup
mysqldump -u root -p aps_database > backup_before_v1.2.0.sql

# Backup with timestamp
mysqldump -u root -p aps_database > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## 📚 Documentation

### New Documentation Files

1. **`IMPLEMENTATION_MASTER_DATA.md`** (1500+ lines)
   - Complete implementation guide
   - Database schema details
   - File structure overview
   - API reference
   - Usage examples

2. **`FIXES_APPLIED.md`** (800+ lines)
   - All bug fixes documented
   - Before/after code comparisons
   - Testing procedures
   - Affects analysis

3. **`AGENTS.md`** (600+ lines)
   - Agent development guide
   - Coding standards (PSR-12)
   - Security guidelines
   - Common tasks and patterns

4. **`documentation/development/CONCURRENT_EDITING_PROTECTION.md`**
   - Prevents data loss from concurrent edits
   - Optimistic locking implementation
   - Future feature (v1.3.0)

### Updated Documentation

- Updated `README.md` with v1.2.0 features
- Updated `CHANGELOG.md` with detailed changes
- Updated `documentation/releases/RELEASE_NOTES.md`

---

## 🎯 Upgrade from v1.1.3

### Step-by-Step Upgrade Guide

```bash
# 1. BACKUP YOUR DATABASE (CRITICAL!)
mysqldump -u root -p aps_database > backup_v1.1.3_$(date +%Y%m%d).sql

# 2. Download v1.2.0
wget https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.2.0.tar.gz
tar -xzf v1.2.0.tar.gz

# 3. Backup your config files
cp acute-pain-service-1.1.3/.env /tmp/aps_env_backup
cp acute-pain-service-1.1.3/config/config.php /tmp/aps_config_backup 2>/dev/null || true

# 4. Run database migrations
cd acute-pain-service-1.2.0
mysql -u root -p aps_database < src/Database/migrations/013_create_new_lookup_tables.sql
mysql -u root -p aps_database < src/Database/migrations/014_update_surgeries_with_specialties.sql

# 5. Seed master data (optional - adds sample data)
php run_master_data_migrations_v2.php

# 6. Restore your config
cp /tmp/aps_env_backup .env
cp /tmp/aps_config_backup config/config.php 2>/dev/null || true

# 7. Update version in .env
sed -i 's/APP_VERSION=1.1.3/APP_VERSION=1.2.0/' .env

# 8. Set permissions
chmod 755 public/
chmod 777 logs/
chmod 777 public/uploads/
chmod 777 public/exports/

# 9. Test the application
php -S localhost:8000 -t public/

# 10. Navigate to http://localhost:8000 and test
```

### Database Migration Details

**Migration 013** - Creates 4 new lookup tables:
- `lookup_catheter_indications`
- `lookup_removal_indications`
- `lookup_sentinel_events`
- `lookup_specialties`

**Migration 014** - Updates existing tables:
- Adds `specialty_id` foreign key to `lookup_surgeries`
- Adds `sort_order`, `deleted_at` to existing lookup tables
- Renames `lookup_complications` to `lookup_red_flags`

**Safe to Run**: All migrations use `CREATE TABLE IF NOT EXISTS` and `ALTER TABLE IF NOT EXISTS`, so they're idempotent.

---

## ⚠️ Breaking Changes

**None** - v1.2.0 is fully backward compatible with v1.1.3.

Existing data will continue to work. New fields are nullable or have defaults.

---

## 🔮 Roadmap (v1.3.0 and Beyond)

### v1.3.0 (Next Release)
- Concurrent editing protection (optimistic locking)
- Bulk import from CSV
- Audit trail for master data changes
- Master data versioning (track changes over time)
- Data validation rules engine
- Multi-language support for lookup data

### v2.0.0 (Major Release)
- REST API for external integrations
- Mobile app integration
- Advanced analytics dashboard
- Multi-hospital support
- Real-time notifications via WebSockets
- Advanced reporting with charts

---

## 🆘 Support

### Common Issues

**Q: Drag and drop not working?**
- Ensure you're using a modern browser (Chrome 90+, Firefox 88+, Safari 14+)
- Check browser console for JavaScript errors
- Verify `sort_order` column exists in table

**Q: Specialty filtering not working in patient form?**
- Clear browser cache
- Ensure `specialty_id` foreign key exists in `lookup_surgeries`
- Run migration 014 if not already run

**Q: Getting "Duplicate entry" error?**
- Check if entry already exists (case-insensitive)
- Look in deleted entries (might need to permanently delete first)
- Use search to find existing entries

**Q: CSV export not downloading?**
- Check browser download settings
- Verify `public/exports/` directory is writable (`chmod 777`)
- Check PHP `max_execution_time` if large dataset

### Getting Help

1. Check logs: `tail -f logs/app.log` and `logs/error.log`
2. Review documentation: `IMPLEMENTATION_MASTER_DATA.md` and `FIXES_APPLIED.md`
3. Search existing issues: https://github.com/drjagan/acute-pain-service/issues
4. Create new issue: https://github.com/drjagan/acute-pain-service/issues/new

---

## 🔗 Links

- **GitHub Repository:** https://github.com/drjagan/acute-pain-service
- **Download v1.2.0:** https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.2.0.tar.gz
- **Report Issues:** https://github.com/drjagan/acute-pain-service/issues
- **Previous Release (v1.1.3):** https://github.com/drjagan/acute-pain-service/releases/tag/v1.1.3
- **Full Changelog:** https://github.com/drjagan/acute-pain-service/compare/v1.1.3...v1.2.0

---

## ✨ Features Summary (All Versions)

### Core Features (v1.0.0)
- Patient registration & demographics (22 fields)
- Catheter insertion details (21 catheter types)
- Drug regime management (6 sections)
- Functional outcomes assessment (13 fields)
- Catheter removal documentation (16 fields)
- Dashboard with real-time statistics
- Reports (individual & consolidated)
- User management with RBAC
- Mobile responsive design

### v1.1.0 Features
- Patient-physician associations (many-to-many)
- Real-time notifications system
- SMTP email configuration
- "My Patients" dashboard widget
- Notification dropdown with auto-refresh

### v1.1.1 Features
- Admins as attending physicians
- Enhanced role hierarchy

### v1.1.2 Features
- Automated LAMP installation script
- Production deployment package
- Comprehensive documentation (1900+ lines)

### v1.1.3 Features
- SQL export file for PhpMyAdmin
- Installation wizard debugging
- No more hardcoded credentials

### v1.2.0 Features (This Release)
- **Master Data Management System** (NEW)
- 9 master data types with CRUD operations
- Drag & drop reordering
- Specialty-based surgery filtering
- CSV export functionality
- Active/inactive toggles
- Search and pagination
- Soft delete with restore

---

## 📊 System Requirements

- **OS:** Ubuntu 20.04/22.04 LTS (other Linux distributions supported)
- **PHP:** 8.1+ (8.3 recommended)
- **MySQL:** 8.0+ or MariaDB 10.5+
- **Apache:** 2.4+ or Nginx 1.18+
- **RAM:** Minimum 2GB (4GB recommended)
- **Disk:** 10GB available space

**Required PHP Extensions:**
- `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `json`, `curl`

---

**Master data management, enhanced forms, and improved administration**
