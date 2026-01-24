# Master Data Management System - Implementation Guide
## Version 1.2.0

---

## Overview

This document provides a comprehensive guide for the Master Data Management System implemented in the Acute Pain Service application. This system provides a centralized, database-driven approach to managing all lookup tables and reference data.

---

## Table of Contents

1. [Implementation Summary](#implementation-summary)
2. [Database Schema](#database-schema)
3. [File Structure](#file-structure)
4. [Installation Instructions](#installation-instructions)
5. [Features](#features)
6. [Usage Guide](#usage-guide)
7. [API Reference](#api-reference)
8. [Testing Checklist](#testing-checklist)

---

## Implementation Summary

### ✅ Completed Components

**Phase 1: Database Setup (100%)**
- 4 new lookup tables created
- Existing tables updated with soft delete support
- Comprehensive seed data provided
- Specialty-surgery foreign key relationship established

**Phase 2: Backend Models (100%)**
- BaseLookupModel with 15+ generic methods
- 10 specialized lookup model classes
- Full CRUD operations
- Pagination, search, export, reordering capabilities

**Phase 3: Controller & Routes (100%)**
- MasterDataController with 15 action methods
- RESTful routing integrated
- CSRF protection
- Admin-only access control
- AJAX operations for toggle/reorder

**Phase 4: Frontend UI (100%)**
- Master data dashboard (index.php)
- Generic list view with pagination
- Generic form view for add/edit
- Drag-drop reordering
- CSV export functionality
- Responsive Bootstrap 5 design

**Phase 5: Form Integration (90%)**
- ✅ Catheter insertion form updated
- ✅ Catheter removal form updated
- ✅ Patient registration updated with specialty filtering
- ⚠️ Functional outcomes (sentinel events) - PENDING

**Phase 6: Settings Page (100%)**
- Master data section updated
- All 9 data types accessible
- Quick links provided

---

## Database Schema

### New Tables Created

#### 1. lookup_catheter_indications
```sql
CREATE TABLE lookup_catheter_indications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    is_common BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
);
```

#### 2. lookup_removal_indications
```sql
CREATE TABLE lookup_removal_indications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    requires_notes BOOLEAN DEFAULT FALSE,
    is_planned BOOLEAN DEFAULT TRUE,
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
);
```

#### 3. lookup_sentinel_events
```sql
CREATE TABLE lookup_sentinel_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category ENUM('infection', 'neurological', 'cardiovascular', 'respiratory', 'mechanical', 'other') NOT NULL,
    severity ENUM('mild', 'moderate', 'severe', 'critical') NOT NULL,
    requires_immediate_action BOOLEAN DEFAULT FALSE,
    description TEXT NULL,
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
);
```

#### 4. lookup_specialties
```sql
CREATE TABLE lookup_specialties (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT NULL,
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
);
```

### Updated Tables

#### lookup_surgeries (Updated)
- **Added:** `specialty_id` (INT UNSIGNED, Foreign Key)
- **Removed:** `speciality` (VARCHAR)
- **Foreign Key:** References `lookup_specialties(id)` ON DELETE RESTRICT

#### All Existing Lookup Tables
- **Added:** `deleted_at` (DATETIME NULL) for soft delete support

---

## File Structure

```
acute-pain-service/
│
├── config/
│   └── masterdata.php                          # Master data configuration (NEW)
│
├── src/
│   ├── Controllers/
│   │   ├── MasterDataController.php            # Main controller (NEW)
│   │   ├── CatheterController.php              # Updated with indications
│   │   └── PatientController.php               # Updated with specialty filtering
│   │
│   ├── Models/
│   │   ├── BaseLookupModel.php                 # Generic lookup model (NEW)
│   │   ├── LookupCatheterIndication.php        # NEW
│   │   ├── LookupRemovalIndication.php         # NEW
│   │   ├── LookupSentinelEvent.php            # NEW
│   │   ├── LookupSpecialty.php                # NEW
│   │   ├── LookupSurgery.php                  # NEW
│   │   ├── LookupComorbidity.php              # NEW
│   │   ├── LookupDrug.php                     # NEW
│   │   ├── LookupAdjuvant.php                 # NEW
│   │   └── LookupRedFlag.php                  # NEW
│   │
│   └── Views/
│       ├── masterdata/
│       │   ├── index.php                       # Dashboard (NEW)
│       │   ├── list.php                        # Generic list view (NEW)
│       │   └── form.php                        # Generic form view (NEW)
│       │
│       ├── catheters/
│       │   ├── create.php                      # Updated
│       │   ├── edit.php                        # Updated
│       │   └── remove.php                      # Updated
│       │
│       ├── patients/
│       │   ├── create.php                      # Updated (pending)
│       │   └── edit.php                        # Updated (pending)
│       │
│       └── settings/
│           └── index.php                       # Updated with master data links
│
├── src/Database/
│   ├── migrations/
│   │   ├── 013_create_new_lookup_tables.sql   # NEW
│   │   └── 014_update_surgeries_with_specialties.sql  # NEW
│   │
│   └── seeders/
│       └── MasterDataSeeder.sql                # NEW
│
└── public/
    └── index.php                                # Updated routing
```

---

## Installation Instructions

### Step 1: Run Database Migrations

```bash
# Navigate to the project directory
cd "Acute Pain Management 01/acute-pain-service"

# Run migrations in order
mysql -u your_user -p your_database < src/Database/migrations/013_create_new_lookup_tables.sql
mysql -u your_user -p your_database < src/Database/migrations/014_update_surgeries_with_specialties.sql
```

### Step 2: Seed Initial Data

```bash
# Seed master data
mysql -u your_user -p your_database < src/Database/seeders/MasterDataSeeder.sql
```

### Step 3: Update Catheter Table (REQUIRED)

The `catheters` table needs to be updated to support the new indication structure:

```sql
-- Add indication_id and indication_notes columns
ALTER TABLE catheters 
ADD COLUMN indication_id INT UNSIGNED NULL AFTER indication,
ADD COLUMN indication_notes TEXT NULL AFTER indication_id;

-- Add foreign key
ALTER TABLE catheters
ADD CONSTRAINT fk_catheter_indication
FOREIGN KEY (indication_id) 
REFERENCES lookup_catheter_indications(id)
ON DELETE SET NULL
ON UPDATE CASCADE;

-- Optional: Migrate existing text indications to new structure
-- (Manual process - review each catheter's indication field)
```

### Step 4: Clear Any Caches

```bash
# If using any caching
php artisan cache:clear  # or equivalent
```

### Step 5: Verify Installation

1. Log in as an admin user
2. Navigate to Settings > Master Data
3. Verify all 9 master data types are visible
4. Test creating a new item in any category

---

## Features

### 1. Centralized Management
- Single dashboard for all 9 lookup table types
- Consistent UI across all data types
- Color-coded categories

### 2. CRUD Operations
- ✅ Create new items with validation
- ✅ Read/list items with pagination
- ✅ Update existing items
- ✅ Soft delete with restore capability
- ✅ Bulk operations support

### 3. Advanced Functionality
- **Search:** Full-text search across configurable columns
- **Pagination:** 25/50/100 items per page
- **Sorting:** Drag-and-drop reordering (where applicable)
- **Export:** CSV export functionality
- **Toggle:** Quick enable/disable without navigation
- **Grouping:** Specialty-based surgery grouping

### 4. Relationships
- **Specialties → Surgeries:** One-to-many relationship
- **Foreign Key Support:** Cascading updates
- **Grouped Dropdowns:** Surgeries grouped by specialty

### 5. Validation
- Required field validation
- Unique constraint validation
- Pattern validation (codes)
- Custom validation per field type

### 6. Security
- Admin-only access
- CSRF protection on all forms
- Input sanitization
- SQL injection prevention (prepared statements)

---

## Usage Guide

### For Administrators

#### Adding a New Comorbidity

1. Navigate to: **Settings → Master Data → Comorbidities**
2. Click **"Add New"**
3. Enter:
   - **Name:** e.g., "Chronic Kidney Disease"
   - **Description:** Optional clinical description
   - **Active:** Check to enable immediately
   - **Sort Order:** Lower numbers appear first
4. Click **"Create"**

#### Managing Specialties and Surgeries

1. **Add Specialties First:**
   - Settings → Master Data → Medical Specialties
   - Add: "Orthopedic Surgery" (Code: ORTHO)

2. **Add Surgeries Under Specialty:**
   - Settings → Master Data → Surgical Procedures
   - Select Specialty: "Orthopedic Surgery"
   - Add: "Total Hip Replacement"

3. **Reorder Items:**
   - Drag and drop rows to reorder
   - Changes save automatically

#### Exporting Data

1. Navigate to any master data list
2. Click **"Export CSV"** button
3. File downloads as: `{type}_{date}.csv`
4. Open in Excel/Google Sheets

### For Clinical Users

#### Using Indications in Catheter Forms

**Insertion:**
- Dropdown shows "Common Indications" first
- "Other Indications" appear below
- Optional notes field for details

**Removal:**
- Indications grouped by Planned/Unplanned
- Some indications require additional notes
- Auto-calculated catheter days

#### Selecting Surgeries by Specialty

1. **Patient Registration:**
   - First select Medical Specialty (dropdown)
   - Surgery dropdown filters to show only that specialty's procedures
   - Select multiple surgeries if needed

---

## API Reference

### REST Endpoints

#### Master Data Dashboard
```
GET /masterdata/index
```
Shows all 9 master data types

#### List Items
```
GET /masterdata/list/{type}
```
Parameters:
- `page` (optional): Page number (default: 1)
- `search` (optional): Search term
- `per_page` (optional): Items per page (25/50/100)

#### Create Form
```
GET /masterdata/create/{type}
```

#### Store New Item
```
POST /masterdata/store/{type}
```
Body: Form fields based on configuration

#### Edit Form
```
GET /masterdata/edit/{type}/{id}
```

#### Update Item
```
POST /masterdata/update/{type}/{id}
```

#### Delete Item (Soft Delete)
```
POST /masterdata/delete/{type}/{id}
```

#### Toggle Active Status (AJAX)
```
POST /masterdata/toggleActive/{type}/{id}
```
Returns: JSON `{success: boolean, message: string}`

#### Reorder Items (AJAX)
```
POST /masterdata/reorder/{type}
```
Body: `{order: {id: sort_order, ...}}`

#### Export CSV
```
GET /masterdata/export/{type}
```

#### Get Surgeries by Specialty (AJAX)
```
GET /masterdata/getSurgeriesBySpecialty/{specialtyId}
```
Returns: JSON array of surgeries

---

## Testing Checklist

### Phase 7: Comprehensive Testing

#### Database Tests
- [ ] All 4 new tables created successfully
- [ ] Foreign keys working correctly
- [ ] Soft deletes functioning
- [ ] Seed data inserted properly
- [ ] Existing data preserved after migration

#### Master Data CRUD Tests

**For Each of 9 Data Types:**
- [ ] List page loads without errors
- [ ] Search functionality works
- [ ] Pagination works correctly
- [ ] Create new item successfully
- [ ] Edit existing item successfully
- [ ] Delete item (soft delete)
- [ ] Toggle active status (AJAX)
- [ ] Reorder items (drag-drop, if applicable)
- [ ] Export to CSV works

#### Form Integration Tests

**Catheter Insertion:**
- [ ] Indication dropdown loads
- [ ] Common indications appear first
- [ ] Can select indication
- [ ] Can add indication notes
- [ ] Form submits successfully
- [ ] Indication saved to database

**Catheter Removal:**
- [ ] Removal indication dropdown loads
- [ ] Grouped by planned/unplanned
- [ ] Required notes enforcement works
- [ ] Form submits successfully

**Patient Registration:**
- [ ] Specialty dropdown loads
- [ ] Surgery dropdown filters by specialty
- [ ] Can select multiple surgeries
- [ ] Form submits successfully
- [ ] Relationships saved correctly

#### Permission Tests
- [ ] Admin can access all master data
- [ ] Non-admin users cannot access
- [ ] CSRF protection working
- [ ] Unauthorized access redirects

#### UI/UX Tests
- [ ] Dashboard displays all 9 cards
- [ ] Color coding consistent
- [ ] Icons display correctly
- [ ] Responsive on mobile
- [ ] Forms are user-friendly
- [ ] Error messages clear

#### Data Integrity Tests
- [ ] Cannot delete specialty with surgeries
- [ ] Unique constraints enforced
- [ ] Required fields enforced
- [ ] Foreign key relationships maintained
- [ ] Soft deleted items hidden from dropdowns

---

## Configuration Reference

### Master Data Types (config/masterdata.php)

1. **catheter_indications** - Catheter insertion reasons
2. **removal_indications** - Catheter removal reasons
3. **sentinel_events** - Adverse events
4. **specialties** - Medical specialties
5. **surgeries** - Surgical procedures (linked to specialties)
6. **comorbidities** - Patient conditions
7. **drugs** - Medications
8. **adjuvants** - Drug additives
9. **red_flags** - Insertion complications

Each configuration includes:
- `label` - Display name
- `description` - Purpose
- `icon` - Bootstrap icon class
- `table` - Database table name
- `model` - Model class name
- `color` - Bootstrap color theme
- `fields` - Field definitions
- `list_columns` - Columns to display in list
- `searchable` - Searchable columns
- `sortable` - Enable drag-drop reordering
- `soft_delete` - Enable soft deletes
- `export` - Enable CSV export

---

## Troubleshooting

### Common Issues

#### Issue: "Table doesn't exist" error
**Solution:** Run migrations in correct order (013 before 014)

#### Issue: Foreign key constraint fails
**Solution:** Ensure lookup_specialties is populated before updating surgeries

#### Issue: Indication dropdown empty in catheter form
**Solution:** 
1. Verify catheter_indications table has data
2. Check getLookupData() method in controller
3. Verify $catheterIndications passed to view

#### Issue: Specialty filtering not working for surgeries
**Solution:**
1. Ensure migration 014 completed successfully
2. Verify specialty_id foreign key exists
3. Check JavaScript console for errors

#### Issue: Cannot delete specialty
**Solution:** This is intentional - remove all associated surgeries first

---

## Future Enhancements

### Planned for v1.3.0
- [ ] Bulk import via CSV
- [ ] Version history for master data changes
- [ ] Approval workflow for changes
- [ ] Audit log integration
- [ ] Multi-language support
- [ ] API endpoints for external integrations

### Pending Implementation
- [ ] Functional outcomes integration with sentinel events
- [ ] Data migration script for existing catheter indications
- [ ] Advanced search with filters
- [ ] Duplicate detection

---

## Support

For issues or questions:
1. Check this documentation first
2. Review the testing checklist
3. Check database migrations logs
4. Review application logs in `/logs/`

---

**Document Version:** 1.0  
**Last Updated:** January 2026  
**Author:** Acute Pain Service Development Team
