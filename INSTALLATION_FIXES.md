# Installation Wizard Fixes & SQL Export - Summary

**Date:** 2026-01-12  
**Issues Addressed:**
1. Installation wizard stalls with no errors
2. Need SQL file for PhpMyAdmin import
3. Hardcoded database credentials in config/database.php

---

## ✅ Changes Made

### 1. Added Comprehensive Debugging to Installation Wizard

#### Error Logging
- **Enabled error_reporting** during installation (`install/index.php`)
- **Created install.log** - All installation steps now logged to `logs/install.log`
- **Added error_log() calls** throughout the installation process:
  - Database connection attempts
  - Migration file loading
  - SQL execution status
  - Seed data loading
  - Success/failure for each step

#### Visual Debug Information
- **Added debug card** in `install/steps/step3-tables.php` that displays:
  - Migrations path
  - Seeds path  
  - Log file location
  - Detailed error messages with error codes
  
#### Enhanced Error Handling
- **Added timeout handling** - Database connections now have 5-10 second timeouts
- **Better exception catching** - Catches both PDOException and general Exception
- **Stack trace logging** - Full stack traces written to log file
- **Path validation** - Checks if migration/seed directories exist before processing
- **Empty file detection** - Warns if SQL files are empty
- **Statement counting** - Logs how many SQL statements were executed per file

### 2. Created Complete SQL Export File for PhpMyAdmin

#### File: `database/aps_database_complete.sql`
- **Size:** 28 KB (783 lines)
- **Contains:**
  - All 16 table structures with proper indexes and foreign keys
  - Sample data (4 test users with password: admin123)
  - Lookup data for dropdowns
  - Proper SQL transaction handling (START TRANSACTION...COMMIT)
  - Character set and collation settings
  
#### Tables Included (16 total):
1. users
2. patients
3. catheters
4. drug_regimes
5. functional_outcomes
6. catheter_removals
7. alerts
8. audit_logs
9. patient_physicians
10. notifications
11. smtp_settings
12. lookup_catheter_types
13. lookup_comorbidities
14. lookup_surgeries
15. lookup_drug_names
16. lookup_complications

#### Test Users Created:
| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | System Administrator |
| dr.sharma | admin123 | Attending Physician |
| dr.patel | admin123 | Resident |
| nurse.kumar | admin123 | Nurse |

### 3. Fixed Hardcoded Credentials Issue

#### config/database.php - Now Dynamic
**Before:**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Cuddalore-Panruti-Pondicherry'); // Hardcoded!
```

**After:**
```php
// Load from config.php if available
if (file_exists('config/config.php')) {
    require_once 'config/config.php';
} else {
    // Fallback to environment variables or defaults
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
}
```

**Priority order:**
1. `config/config.php` (if exists, created by installation wizard)
2. Environment variables (`DB_HOST`, `DB_USER`, `DB_PASS`, etc.)
3. Default values (localhost, root, empty password)

#### install/database-setup.php - CLI Arguments Support
**Now accepts:**
```bash
# Option 1: Command line arguments
php install/database-setup.php localhost 3306 aps_database root password

# Option 2: Environment variables
export DB_HOST=localhost
export DB_USER=aps_user
export DB_PASS=secret
php install/database-setup.php

# Option 3: config.php (if exists)
php install/database-setup.php
```

### 4. Created Comprehensive Documentation

#### File: `database/README.md` (500+ lines)

**Sections:**
- **Quick Start with PhpMyAdmin** - Step-by-step import guide
- **What's Included** - File contents and table list
- **Alternative Methods** - CLI import, wizard, PHP script
- **Troubleshooting** - Common issues and solutions:
  - Access denied errors
  - Table already exists
  - Foreign key constraints
  - PHP timeouts
  - Installation wizard stalls (with solutions!)
- **Database Configuration** - Using config.php vs .env
- **Security Notes** - Change default passwords, create prod user
- **Backup and Restore** - mysqldump commands
- **Version Information** - Requirements and compatibility

---

## 🎯 How to Use the Fixes

### Option 1: Import SQL File in PhpMyAdmin (Easiest)

1. **Create database** in PhpMyAdmin:
   - Database name: `aps_database`
   - Collation: `utf8mb4_unicode_ci`

2. **Import SQL file:**
   - Select database
   - Click "Import" tab
   - Choose file: `database/aps_database_complete.sql`
   - Click "Go"

3. **Update config:**
   - Edit `config/config.php` with your credentials

4. **Test:**
   - Login with `admin` / `admin123`

**Time:** 2-3 minutes

### Option 2: Use Installation Wizard (With Debugging)

1. **Navigate to:** `http://your-domain/install/`

2. **Follow wizard steps:**
   - Step 1: Requirements check
   - Step 2: Database config
   - Step 3: Create tables (now with debugging!)
   - Step 4: Create admin user
   - Step 5: Complete

3. **If it stalls:**
   - Check `logs/install.log` for detailed errors
   - Debug information will be displayed on the page
   - Look for specific error messages and codes

4. **Common fixes:**
   - Verify `src/Database/migrations/` directory exists
   - Ensure `logs/` directory is writable (`chmod 777 logs/`)
   - Check MySQL is running: `service mysql status`
   - Verify credentials are correct

### Option 3: Command Line Setup

```bash
# If you have config.php
php install/database-setup.php

# Or with arguments
php install/database-setup.php localhost 3306 aps_database root yourpassword
```

---

## 🐛 Debugging Installation Issues

### Check the Log File

```bash
# View installation log
tail -f logs/install.log

# Or view entire log
cat logs/install.log
```

**What to look for:**
```
[APS Install] Installation wizard started - Step: 3
[APS Install] Step 3: Starting table creation
[APS Install] Connecting to database: aps_database@localhost
[APS Install] Database connection established
[APS Install] Migrations path: /path/to/src/Database/migrations
[APS Install] Found 12 migration files
[APS Install] Running migration: 001_create_users_table.sql
[APS Install] ✓ 001_create_users_table.sql completed (1 statements)
...
```

**Common error patterns:**
- `Connection failed` = MySQL not running or wrong credentials
- `No migration files found` = Wrong path or files missing
- `Access denied` = Database user lacks privileges
- `Table already exists` = Database not clean, drop and recreate

### Enable PHP Error Display

If you need more debugging, edit `php.ini` temporarily:

```ini
display_errors = On
error_reporting = E_ALL
log_errors = On
error_log = /path/to/logs/php_errors.log
```

Restart Apache: `sudo service apache2 restart`

**⚠️ Remember to disable `display_errors` in production!**

### Check Directory Permissions

```bash
# Logs directory must be writable
chmod 777 logs/
touch logs/install.log
chmod 666 logs/install.log

# Config directory must be writable (for .installed flag)
chmod 777 config/

# Verify
ls -la logs/
ls -la config/
```

### Verify Migration Files Exist

```bash
ls -la src/Database/migrations/
# Should show 12 .sql files

ls -la src/Database/seeds/
# Should show 2 .sql files
```

If missing, re-download or re-extract the archive.

---

## 📋 What Gets Logged

### Installation Log (`logs/install.log`)

**Format:**
```
[APS Install] <message>
```

**Events logged:**
1. Wizard start (with step number)
2. Database connection attempts (with host/db name)
3. Connection success/failure
4. Migration directory checks
5. Each migration file execution
6. Statement counts per file
7. Seed data loading
8. All errors with full details
9. Stack traces for exceptions

**Example successful log:**
```
[APS Install] Installation wizard started - Step: 3
[APS Install] Step 3: Starting table creation
[APS Install] Connecting to database: aps_database@localhost
[APS Install] Database connection established
[APS Install] Migrations path: /var/www/html/aps/src/Database/migrations
[APS Install] Found 12 migration files
[APS Install] Running migration: 001_create_users_table.sql
[APS Install] ✓ 001_create_users_table.sql completed (1 statements)
[APS Install] Running migration: 002_create_patients_table.sql
[APS Install] ✓ 002_create_patients_table.sql completed (1 statements)
... (more migrations)
[APS Install] All migrations completed successfully
[APS Install] Seeds path: /var/www/html/aps/src/Database/seeds
[APS Install] Running seed: users_seed.sql
[APS Install] Seeds completed: 2 files
```

**Example error log:**
```
[APS Install] Installation wizard started - Step: 3
[APS Install] Step 3: Starting table creation
[APS Install] Connecting to database: aps_database@localhost
[APS Install] Database connection failed: SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: YES)
[APS Install] PDO Exception: Database error: SQLSTATE[HY000] [1045] Access denied...
```

---

## 🔒 Security Improvements

### No More Hardcoded Passwords

**Before:** Password hardcoded in source files (bad for version control!)

**After:** Multiple secure options:
1. Use config.php (generated by wizard, not in git)
2. Use environment variables (12-factor app pattern)
3. Use CLI arguments (for automated deployments)

### Benefits:
- ✅ Passwords not committed to git
- ✅ Different credentials per environment (dev/staging/prod)
- ✅ Follows security best practices
- ✅ Compatible with Docker, CI/CD, cloud platforms

---

## 📦 Files Modified/Created

### Modified Files (5):
1. `config/database.php` - Dynamic credential loading
2. `install/database-setup.php` - CLI args + env vars support
3. `install/functions.php` - Extensive logging added
4. `install/index.php` - Enable error reporting
5. `install/steps/step3-tables.php` - Debug info display

### Created Files (3):
1. `database/aps_database_complete.sql` - PhpMyAdmin import file (28 KB)
2. `database/README.md` - Comprehensive import guide (500+ lines)
3. `INSTALLATION_FIXES.md` - This document

---

## ✅ Testing Checklist

To verify the fixes work:

### Test 1: PhpMyAdmin Import
- [ ] Create fresh database in PhpMyAdmin
- [ ] Import `database/aps_database_complete.sql`
- [ ] Verify 16 tables created
- [ ] Browse `users` table - should have 4 users
- [ ] Login with admin/admin123 - should work

### Test 2: Installation Wizard
- [ ] Delete `config/.installed` file (if exists)
- [ ] Navigate to `/install/`
- [ ] Complete all steps
- [ ] If it stalls, check `logs/install.log`
- [ ] Debug info should display on error
- [ ] Should see detailed error messages

### Test 3: CLI Setup
- [ ] Drop database if exists
- [ ] Run: `php install/database-setup.php localhost 3306 aps_database root password`
- [ ] Should show progress and create 16 tables
- [ ] Should create 4 test users

### Test 4: Logging
- [ ] Tail the log: `tail -f logs/install.log`
- [ ] Run installation
- [ ] Should see detailed step-by-step logging
- [ ] Errors should include stack traces

---

## 🎉 Summary

**Problems Solved:**
1. ✅ Installation wizard now has comprehensive error logging
2. ✅ Debug information displays when errors occur
3. ✅ SQL export file available for quick PhpMyAdmin import
4. ✅ No more hardcoded database credentials
5. ✅ Detailed troubleshooting documentation
6. ✅ Multiple installation methods with proper error handling

**What You Get:**
- **Faster installation** - PhpMyAdmin import takes 2-3 minutes
- **Better debugging** - Know exactly what failed and why
- **More secure** - No passwords in source code
- **Well documented** - Complete guide in database/README.md
- **Flexible setup** - Multiple installation methods to choose from

**Next Steps:**
1. Try PhpMyAdmin import method (fastest!)
2. If using wizard, check logs/install.log if issues occur
3. Read database/README.md for detailed instructions
4. Change default passwords after installation!

---

**Committed:** ef1e779  
**Pushed:** Yes  
**Status:** Ready for testing
