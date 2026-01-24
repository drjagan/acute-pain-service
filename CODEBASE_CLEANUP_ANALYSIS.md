# 🧹 Codebase Cleanup Analysis - v1.2.0

**Date:** January 24, 2026  
**Current Version:** 1.2.0  
**Analysis Type:** Complete codebase audit for orphaned, unnecessary, and duplicate files

---

## 📊 Executive Summary

**Total Files Scanned:** ~150+ files  
**Orphaned/Unnecessary Files Found:** 15 files  
**Backup Files Found:** 1 file  
**System Files (.DS_Store):** 5 files  
**Total Size to Archive:** ~175 KB  
**Recommended Action:** Move to `_archive/` folder

---

## 🗑️ Files Recommended for Archival

### Category 1: Testing & Development Scripts (9 files, ~50 KB)

These are temporary test scripts used during development and debugging. They are no longer needed in production.

#### **1.1 Test Scripts (3 files)**

| File | Size | Purpose | Safe to Archive? |
|------|------|---------|------------------|
| `test_routing.php` | 1.6 KB | Tests routing for masterdata controller | ✅ YES - Development testing only |
| `test_v1.1_features.php` | 11 KB | Tests v1.1.0 patient-physician associations | ✅ YES - Feature already tested and deployed |
| `test-env-config.php` | 3.1 KB | Tests .env file loading | ✅ YES - Configuration already verified |

**Recommendation:** Archive all three. These were one-time testing scripts.

#### **1.2 Database Fix/Diagnostic Scripts (6 files)**

| File | Size | Purpose | Safe to Archive? |
|------|------|---------|------------------|
| `fix-lookup-tables.php` | 4.2 KB | Manual lookup table creation | ⚠️ MAYBE - Keep if installation issues persist |
| `fix_unique_constraints.php` | 3.2 KB | Fixes UNIQUE constraints for soft delete | ✅ YES - Migration 014 handles this now |
| `diagnose-database.php` | 6.7 KB | Diagnoses database permissions | ⚠️ MAYBE - Useful for troubleshooting |
| `create-lookup-tables-verbose.php` | 6.7 KB | Creates lookup tables with verbose errors | ✅ YES - Migration 013 handles this now |
| `run_master_data_migrations.php` | 5.4 KB | Old migration runner (v1) | ✅ YES - Replaced by v2 |
| `run_migrations_v1.1.php` | 3.3 KB | v1.1 migration runner | ✅ YES - v1.1 already deployed |

**Recommendation:** 
- **Archive:** `fix_unique_constraints.php`, `create-lookup-tables-verbose.php`, `run_master_data_migrations.php`, `run_migrations_v1.1.php` (4 files)
- **Keep:** `fix-lookup-tables.php`, `diagnose-database.php` (useful for production troubleshooting)

---

### Category 2: Deployment Scripts (2 files, ~4.5 KB)

| File | Size | Purpose | Safe to Archive? |
|------|------|---------|------------------|
| `create-release.sh` | 3.3 KB | Creates GitHub releases | ❌ NO - Keep for future releases |
| `start-server.sh` | 1.2 KB | Starts PHP development server | ❌ NO - Keep for local development |

**Recommendation:** Keep both. Actively used.

---

### Category 3: Documentation Files (5 files, ~40 KB)

#### **3.1 Root-Level .md Files**

| File | Size | Important Info | Recommendation |
|------|------|----------------|----------------|
| `AGENTS.md` | 14 KB | **Agent development guide** - PSR-12 standards, security guidelines, common patterns | 🔄 **MIGRATE** to `documentation/development/CODING_STANDARDS.md` |
| `DOCUMENTATION_REORGANIZATION.md` | 12 KB | **Historical record** - Explains documentation restructure in v1.1.3 | ✅ **ARCHIVE** - Historical, one-time event |
| `FIXES_APPLIED.md` | 8.7 KB | **Master data bug fixes** - Details all fixes for v1.2.0 drag & drop, duplicates, etc. | 🔄 **MIGRATE** to `documentation/troubleshooting/MASTER_DATA_FIXES.md` |
| `IMPLEMENTATION_MASTER_DATA.md` | 16 KB | **Master data implementation guide** - Complete feature documentation | 🔄 **MIGRATE** to `documentation/development/MASTER_DATA_SYSTEM.md` |
| `README.md` | 12 KB | **Main project README** | ❌ **KEEP** - Essential |

**Recommendation:**
- **Keep in root:** `README.md` only
- **Migrate to documentation/:** `AGENTS.md`, `FIXES_APPLIED.md`, `IMPLEMENTATION_MASTER_DATA.md`
- **Archive:** `DOCUMENTATION_REORGANIZATION.md` (historical record)

---

### Category 4: Utility/Config Files (4 files, ~110 KB)

| File | Size | Purpose | Safe to Archive? |
|------|------|---------|------------------|
| `GITHUB_PUSH_COMMANDS.txt` | <1 KB | Old GitHub push instructions | ✅ YES - One-time setup, outdated |
| `VERSION` | 6 B | Contains "1.1.3" (outdated) | ⚠️ UPDATE to 1.2.0 or REMOVE |
| `database.sql` | Symlink | Points to `documentation/database/aps_database_complete.sql` | ❌ KEEP - Useful shortcut |
| `Snazzy.terminal` | 109 KB | Terminal theme/config file | ✅ YES - Personal preference, not project-related |

**Recommendation:**
- **Archive:** `GITHUB_PUSH_COMMANDS.txt`, `Snazzy.terminal`
- **Update or Remove:** `VERSION` file (currently shows 1.1.3)
- **Keep:** `database.sql` symlink

---

### Category 5: Backup Files (1 file)

| File | Location | Safe to Delete? |
|------|----------|-----------------|
| `view.php.backup` | `src/Views/patients/` | ✅ YES - Old backup, current version works |

---

### Category 6: System Files (5 files)

| File | Location | Safe to Delete? |
|------|----------|-----------------|
| `.DS_Store` | Root directory | ✅ YES - macOS metadata |
| `.DS_Store` | `public/` | ✅ YES - macOS metadata |
| `.DS_Store` | `public/assets/` | ✅ YES - macOS metadata |
| `.DS_Store` | `src/` | ✅ YES - macOS metadata |
| `.DS_Store` | `src/Views/` | ✅ YES - macOS metadata |

**Recommendation:** Delete all `.DS_Store` files and add to `.gitignore`

---

## 📚 Important Information to Extract from .md Files

### From `AGENTS.md` (14 KB)

**Key Content:**
- ✅ **PSR-12 Coding Standards** - PHP naming conventions, indentation rules
- ✅ **Security Guidelines** - SQL injection prevention, XSS, CSRF, password hashing
- ✅ **Controller Patterns** - Standard controller structure with examples
- ✅ **Model Patterns** - Database query patterns with prepared statements
- ✅ **View Patterns** - Output escaping, CSRF tokens, flash messages
- ✅ **Common Tasks** - Adding features, database tables, debugging
- ✅ **Testing Checklist** - Security, functionality, code quality checks

**Recommendation:** Merge into `documentation/development/CODING_STANDARDS.md`

---

### From `FIXES_APPLIED.md` (8.7 KB)

**Key Content:**
- ✅ **Universal Bug Fixes** - Applied to all 9 master data types:
  1. hasColumn() SQL error fix (MariaDB compatibility)
  2. Duplicate entry error handling
  3. Drag & drop functionality fixes
  4. Console error suppression
  5. Reorder AJAX error handling
  6. Error logging in updateSortOrder
  7. Unique constraint violations

- ✅ **Type-Specific Fixes:**
  - Specialties-Surgery relationship errors
  - Surgery form filtering issues
  - Patient form specialty dropdown issues

**Recommendation:** Merge into `documentation/troubleshooting/MASTER_DATA_FIXES.md`

---

### From `IMPLEMENTATION_MASTER_DATA.md` (16 KB)

**Key Content:**
- ✅ **Implementation Summary** - 6 phases (100% complete)
- ✅ **Database Schema** - All 4 new tables + 5 enhanced tables
- ✅ **File Structure** - Complete file tree
- ✅ **Installation Instructions** - Step-by-step migration guide
- ✅ **Features List** - All CRUD operations, drag & drop, export
- ✅ **Usage Guide** - How to use master data interface
- ✅ **API Reference** - All model methods documented
- ✅ **Testing Checklist** - Comprehensive testing procedures

**Recommendation:** Merge into `documentation/development/MASTER_DATA_SYSTEM.md`

---

### From `DOCUMENTATION_REORGANIZATION.md` (12 KB)

**Key Content:**
- ✅ **Documentation Structure** - Before/after comparison
- ✅ **File Categories** - 6 categories explained
- ✅ **Benefits** - Why reorganization was needed
- ✅ **Migration Log** - What files moved where

**Recommendation:** Archive as historical record. Content is outdated (refers to v1.1.3 structure).

---

## 🎯 Recommended Actions

### Immediate Actions (Archive These)

**Create `_archive/` folder and move:**

```bash
mkdir -p _archive/test-scripts
mkdir -p _archive/fix-scripts
mkdir -p _archive/documentation
mkdir -p _archive/misc

# Move test scripts
mv test_routing.php _archive/test-scripts/
mv test_v1.1_features.php _archive/test-scripts/
mv test-env-config.php _archive/test-scripts/

# Move obsolete fix scripts
mv fix_unique_constraints.php _archive/fix-scripts/
mv create-lookup-tables-verbose.php _archive/fix-scripts/
mv run_master_data_migrations.php _archive/fix-scripts/
mv run_migrations_v1.1.php _archive/fix-scripts/

# Move documentation to migrate
mv DOCUMENTATION_REORGANIZATION.md _archive/documentation/

# Move misc files
mv GITHUB_PUSH_COMMANDS.txt _archive/misc/
mv Snazzy.terminal _archive/misc/

# Delete backup file
rm src/Views/patients/view.php.backup

# Delete .DS_Store files
find . -name ".DS_Store" -delete
```

---

### Documentation Migration (Reorganize These)

**Step 1: Create new documentation files:**

```bash
# Create coding standards doc
cat AGENTS.md > documentation/development/CODING_STANDARDS.md

# Create master data system doc
cat IMPLEMENTATION_MASTER_DATA.md > documentation/development/MASTER_DATA_SYSTEM.md

# Create master data fixes doc
cat FIXES_APPLIED.md > documentation/troubleshooting/MASTER_DATA_FIXES.md
```

**Step 2: Remove from root:**

```bash
rm AGENTS.md
rm IMPLEMENTATION_MASTER_DATA.md
rm FIXES_APPLIED.md
```

---

### Update VERSION File

```bash
# Option 1: Update to 1.2.0
echo "1.2.0" > VERSION

# Option 2: Remove (version tracked in .env and tags)
rm VERSION
```

---

### Update .gitignore

```bash
# Add these lines to .gitignore
echo "" >> .gitignore
echo "# macOS system files" >> .gitignore
echo ".DS_Store" >> .gitignore
echo "" >> .gitignore
echo "# Archive folder" >> .gitignore
echo "_archive/" >> .gitignore
```

---

## 📊 Summary Statistics

### Files to Archive
| Category | Count | Total Size |
|----------|-------|------------|
| Test Scripts | 3 | 15.7 KB |
| Fix Scripts | 4 | 18.3 KB |
| Documentation | 1 | 12.4 KB |
| Misc Files | 2 | 109 KB |
| **Total to Archive** | **10** | **~155 KB** |

### Files to Migrate to documentation/
| File | Size | New Location |
|------|------|--------------|
| `AGENTS.md` | 14 KB | `documentation/development/CODING_STANDARDS.md` |
| `IMPLEMENTATION_MASTER_DATA.md` | 16 KB | `documentation/development/MASTER_DATA_SYSTEM.md` |
| `FIXES_APPLIED.md` | 8.7 KB | `documentation/troubleshooting/MASTER_DATA_FIXES.md` |
| **Total to Migrate** | **38.7 KB** | **3 new files** |

### Files to Delete
| File | Count | Total Size |
|------|-------|------------|
| `.DS_Store` files | 5 | <100 KB |
| `view.php.backup` | 1 | ~5 KB |
| **Total to Delete** | **6** | **~105 KB** |

---

## ✅ Final Codebase Structure (After Cleanup)

### Root Directory (Clean)
```
acute-pain-service/
├── README.md                          ← Only main README
├── .env                               ← Dev environment (gitignored)
├── .env.example                       ← Template
├── .gitignore                         ← Updated with .DS_Store
├── .gitattributes                     ← Git config
├── VERSION                            ← Updated to 1.2.0 OR removed
├── database.sql                       ← Symlink (keep)
├── create-release.sh                  ← Keep (active use)
├── start-server.sh                    ← Keep (active use)
├── run_master_data_migrations_v2.php  ← Keep (current seeder)
├── fix-lookup-tables.php              ← Keep (troubleshooting)
├── diagnose-database.php              ← Keep (troubleshooting)
├── config/                            ← Config files
├── src/                               ← Application code
├── public/                            ← Web root
├── logs/                              ← Application logs
├── install/                           ← Installation wizard
├── documentation/                     ← All documentation
│   ├── development/
│   │   ├── CODING_STANDARDS.md        ← NEW (from AGENTS.md)
│   │   ├── MASTER_DATA_SYSTEM.md      ← NEW (from IMPLEMENTATION_MASTER_DATA.md)
│   │   └── (other dev docs)
│   ├── troubleshooting/
│   │   ├── MASTER_DATA_FIXES.md       ← NEW (from FIXES_APPLIED.md)
│   │   └── (other troubleshooting)
│   └── (other categories)
└── _archive/                          ← NEW - Archived files
    ├── test-scripts/
    ├── fix-scripts/
    ├── documentation/
    └── misc/
```

---

## 🔍 Files Currently Active (Keep These)

### Essential Scripts (5 files)
- ✅ `create-release.sh` - Used for releases
- ✅ `start-server.sh` - Local development
- ✅ `run_master_data_migrations_v2.php` - Current seeder
- ✅ `fix-lookup-tables.php` - Troubleshooting
- ✅ `diagnose-database.php` - Troubleshooting

### Essential Documentation (1 file in root)
- ✅ `README.md` - Main project README

### Essential Links (1 symlink)
- ✅ `database.sql` → `documentation/database/aps_database_complete.sql`

---

## 📝 Important Notes

### About Archived Files
- **Not deleted** - Just moved to `_archive/`
- **Still in git history** - Can always retrieve
- **Can be restored** - If ever needed again
- **Ignored in future** - Add `_archive/` to `.gitignore`

### About Migrated Documentation
- **Better organization** - Follows existing structure
- **Easier to find** - In appropriate categories
- **Consistent naming** - Matches other docs
- **More discoverable** - Via documentation index

### About .DS_Store Files
- **System files** - Created by macOS Finder
- **Not needed** - No functional purpose
- **Regenerated** - macOS recreates them
- **Should be gitignored** - Prevent future commits

---

## 🎯 Benefits After Cleanup

### Cleaner Codebase
- ✅ **10 fewer files** in root directory
- ✅ **No system files** (.DS_Store)
- ✅ **No backup files** cluttering source
- ✅ **Clear separation** between active and archived

### Better Organization
- ✅ **Documentation consolidated** in proper folders
- ✅ **Test scripts archived** not mixed with production
- ✅ **Fix scripts categorized** easy to find if needed
- ✅ **Professional structure** clean repository

### Improved Maintainability
- ✅ **Easier to navigate** - Less clutter
- ✅ **Clear purpose** - Each file has a place
- ✅ **Better git diffs** - Fewer irrelevant files
- ✅ **Faster searches** - Less noise in results

---

## 🚀 Next Steps

1. **Review this analysis** - Confirm which files to archive
2. **Create backup** - `git commit` before changes
3. **Execute cleanup** - Run the archive commands
4. **Migrate documentation** - Move and reorganize .md files
5. **Update VERSION** - To 1.2.0 or remove
6. **Update .gitignore** - Add .DS_Store and _archive/
7. **Commit changes** - Clean codebase
8. **Push to git** - Update both branches

---

## ⚠️ Safety First

**Before executing cleanup:**

```bash
# Create safety checkpoint
git add -A
git commit -m "Checkpoint before cleanup"

# Create a tag for safety
git tag pre-cleanup-$(date +%Y%m%d)

# Now you can safely cleanup
# If anything goes wrong: git reset --hard pre-cleanup-YYYYMMDD
```

---

**Analysis Complete:** January 24, 2026  
**Recommended for v1.2.1 maintenance release**
