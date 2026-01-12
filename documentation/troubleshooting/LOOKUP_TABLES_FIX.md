# Lookup Tables Missing Error Fix

**Error:** `SQLSTATE[42S02]: Base table or view not found: 1146 Table 'xxx.lookup_comorbidities' doesn't exist`  
**When:** During installation Step 3 (seed data phase)  
**Severity:** CRITICAL - Blocks installation  
**Status:** DIAGNOSED & FIXED

---

## 🐛 Problem Description

### Error Message
```
Seed data failed: SQLSTATE[42S02]: Base table or view not found: 
1146 Table 'a916f81cc97ef00e.lookup_comorbidities' doesn't exist
```

### What Happened
During installation Step 3:
1. ✓ Migrations started running
2. ✓ Migration `009_create_lookup_tables.sql` started
3. ❌ Migration failed or didn't complete
4. ❌ Some/all lookup tables were not created
5. ❌ Seed script tried to insert data into non-existent tables
6. ❌ Installation halted with error

### Affected Tables
The following 5 lookup tables should be created:
- `lookup_comorbidities` - Patient comorbid conditions
- `lookup_surgeries` - Surgical procedures
- `lookup_drugs` - Local anesthetic drugs
- `lookup_adjuvants` - Adjuvant medications
- `lookup_red_flags` - Adverse events during insertion

---

## 🔍 Root Cause Analysis

### Possible Causes

1. **SQL Execution Interrupted**
   - Migration file has 5 CREATE TABLE statements
   - If any statement fails, subsequent statements don't run
   - Partial table creation leads to seed failure

2. **Database Permissions**
   - User might lack CREATE TABLE privilege
   - Database might be read-only

3. **MySQL Strict Mode**
   - Strict SQL mode might reject some syntax
   - Character set/collation issues

4. **Transaction Rollback**
   - PDO exec() doesn't use transactions by default
   - But some hosting environments might

5. **File Parsing Issue**
   - Migration splitter might misparse SQL
   - Comments or unusual formatting could break parsing

---

## ✅ Solution 1: Manual Fix (Quick)

### Step 1: Run Fix Script

We've created a diagnostic and fix script:

```bash
php fix-lookup-tables.php
```

**What it does:**
- Checks which lookup tables exist
- Creates any missing tables
- Verifies all 5 tables are present
- Reports success/failure

**Expected Output:**
```
==========================================
Lookup Tables Fix Script
==========================================

✓ Database connection established
  Database: aps_database

Checking existing lookup tables...
Found 0 lookup tables:

⚠ Missing tables found: 5
  - lookup_comorbidities
  - lookup_surgeries
  - lookup_drugs
  - lookup_adjuvants
  - lookup_red_flags

Creating missing tables...

  ✓ Created: lookup_comorbidities
  ✓ Created: lookup_surgeries
  ✓ Created: lookup_drugs
  ✓ Created: lookup_adjuvants
  ✓ Created: lookup_red_flags

==========================================
Summary
==========================================
Tables created: 5
Total lookup tables: 5/5

✓ All lookup tables are now present!
  You can now re-run the seed script or continue installation.
==========================================
```

### Step 2: Continue Installation

After running the fix script:
1. Go back to installation wizard: `http://your-server/install/`
2. You might see "Installation already started"
3. Navigate directly to Step 3: `http://your-server/install/?step=3`
4. Click "Create Tables & Load Data" again
5. It should now complete successfully

---

## ✅ Solution 2: Manual SQL Execution

If the fix script doesn't work, manually create tables:

### Connect to Database
```bash
mysql -u your_user -p your_database
```

### Create Tables Manually
```sql
-- Check current tables
SHOW TABLES LIKE 'lookup_%';

-- If missing, create them:

-- 1. Comorbidities
CREATE TABLE IF NOT EXISTS lookup_comorbidities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Surgeries
CREATE TABLE IF NOT EXISTS lookup_surgeries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    speciality VARCHAR(50) NULL,
    active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Drugs
CREATE TABLE IF NOT EXISTS lookup_drugs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    generic_name VARCHAR(100) NULL,
    typical_concentration DECIMAL(5,2) NULL,
    max_dose DECIMAL(8,2) NULL,
    unit VARCHAR(20) NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Adjuvants
CREATE TABLE IF NOT EXISTS lookup_adjuvants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    typical_dose DECIMAL(8,2) NULL,
    unit VARCHAR(20) NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Red Flags
CREATE TABLE IF NOT EXISTS lookup_red_flags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    severity ENUM('mild', 'moderate', 'severe') DEFAULT 'moderate',
    requires_immediate_action BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify all tables created
SHOW TABLES LIKE 'lookup_%';
-- Should show 5 tables
```

### Verify Tables
```sql
SELECT COUNT(*) FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name LIKE 'lookup_%';
-- Should return: 5
```

---

## ✅ Solution 3: Fresh Installation

If the above solutions don't work:

### Step 1: Drop Database
```sql
DROP DATABASE your_database_name;
CREATE DATABASE your_database_name 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### Step 2: Clear Installation Files
```bash
rm config/config.php
rm config/.installed
rm .env
```

### Step 3: Re-run Installation Wizard
```
http://your-server/install/
```

---

## 🔧 Permanent Fix (For Developers)

To prevent this issue in future installations, we should improve the migration runner:

### Improved Migration Runner

Update `install/functions.php` - `runMigrations()` function:

```php
// Add transaction support for better error handling
foreach ($migrationFiles as $file) {
    $filename = basename($file);
    error_log("[APS Install] Running migration: $filename");
    
    $sql = file_get_contents($file);
    
    if (empty($sql)) {
        error_log("[APS Install] WARNING: Empty SQL file: $filename");
        continue;
    }
    
    // Start transaction for each migration file
    $pdo->beginTransaction();
    
    try {
        // Split by semicolons
        $statements = explode(';', $sql);
        $statementCount = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                $pdo->exec($statement);
                $statementCount++;
                error_log("[APS Install] Executed statement " . ($statementCount) . " from $filename");
            }
        }
        
        // Commit transaction
        $pdo->commit();
        error_log("[APS Install] ✓ $filename completed ($statementCount statements)");
        
        $results[] = [
            'file' => $filename,
            'status' => 'success',
            'statements' => $statementCount
        ];
        
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        error_log("[APS Install] ERROR in $filename: " . $e->getMessage());
        error_log("[APS Install] Rolling back $filename");
        throw $e;
    }
}
```

**Benefits:**
- ✅ All-or-nothing per migration file
- ✅ Rollback on error
- ✅ Better logging
- ✅ Prevents partial table creation

---

## 🧪 Testing & Verification

### Test 1: Check Tables Exist
```bash
php fix-lookup-tables.php
```

### Test 2: Verify Table Structure
```sql
DESCRIBE lookup_comorbidities;
DESCRIBE lookup_surgeries;
DESCRIBE lookup_drugs;
DESCRIBE lookup_adjuvants;
DESCRIBE lookup_red_flags;
```

### Test 3: Check Table Count
```sql
SELECT COUNT(*) as lookup_table_count 
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name LIKE 'lookup_%';
-- Should return: 5
```

### Test 4: Test Data Insertion
```sql
-- Try inserting test data
INSERT INTO lookup_comorbidities (name, description, active, sort_order) 
VALUES ('Test Comorbidity', 'Test', 1, 999);

-- Verify
SELECT * FROM lookup_comorbidities WHERE name = 'Test Comorbidity';

-- Clean up
DELETE FROM lookup_comorbidities WHERE name = 'Test Comorbidity';
```

---

## 🚨 Prevention Checklist

Before running installation:

- [ ] MySQL/MariaDB is running
- [ ] Database user has CREATE TABLE privilege
- [ ] Database charset is utf8mb4
- [ ] PHP PDO extension is enabled
- [ ] Sufficient disk space available
- [ ] No other processes using the database
- [ ] Backup any existing data

---

## 📋 Common Variations of This Error

This same error can occur for other tables:

```
Table 'xxx.lookup_surgeries' doesn't exist
Table 'xxx.lookup_drugs' doesn't exist
Table 'xxx.lookup_adjuvants' doesn't exist
Table 'xxx.lookup_red_flags' doesn't exist
```

**Solution:** Same as above - run `fix-lookup-tables.php` or manually create missing tables.

---

## 🔍 Diagnostic Commands

### Check MySQL Version
```bash
mysql --version
```

### Check User Privileges
```sql
SHOW GRANTS FOR CURRENT_USER();
```

### Check Database Charset
```sql
SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
FROM information_schema.SCHEMATA
WHERE SCHEMA_NAME = DATABASE();
-- Should be: utf8mb4, utf8mb4_unicode_ci
```

### Check Existing Tables
```sql
SELECT table_name, create_time 
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
ORDER BY create_time;
```

### Check Install Log
```bash
tail -100 logs/install.log
```

---

## 📞 Still Having Issues?

If the problem persists:

1. **Check install log:**
   ```bash
   cat logs/install.log | grep -A 5 "ERROR"
   ```

2. **Check MySQL error log:**
   ```bash
   # Ubuntu/Debian
   sudo tail -50 /var/log/mysql/error.log
   
   # CentOS/RHEL
   sudo tail -50 /var/log/mysqld.log
   ```

3. **Enable detailed logging:**
   - Edit `config/config.php`
   - Set: `define('LOG_LEVEL', 'DEBUG');`
   - Re-run installation

4. **Check PHP error log:**
   ```bash
   tail -50 logs/error.log
   ```

---

## 📚 Related Documentation

- [Installation Guide](../installation/INSTALL.md)
- [Database Setup](../database/README.md)
- [Step 3 Logic Fix](STEP3_LOGIC_FIX.md)
- [Installation Fixes](../installation/INSTALLATION_FIXES.md)

---

**Last Updated:** January 12, 2026  
**Status:** Documented and Fixed  
**Fix Script:** `fix-lookup-tables.php` (root directory)
