# Database Setup Guide

## Quick Start with PhpMyAdmin

### Option 1: Import Complete SQL File (Recommended)

1. **Open PhpMyAdmin** in your browser (usually `http://localhost/phpmyadmin`)

2. **Create a new database:**
   - Click "New" in the left sidebar
   - Database name: `aps_database`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

3. **Import the SQL file:**
   - Select the `aps_database` database from the left sidebar
   - Click "Import" tab at the top
   - Click "Choose File" and select: `aps_database_complete.sql`
   - Keep default options
   - Click "Go" at the bottom

4. **Verify installation:**
   - Click "aps_database" in left sidebar
   - You should see 16 tables created
   - Click on "users" table and "Browse" to see 4 test users

5. **Update configuration:**
   - Edit `config/config.php` (or `.env` if using environment variables)
   - Set your database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'aps_database');
     define('DB_USER', 'your_mysql_username');
     define('DB_PASS', 'your_mysql_password');
     ```

6. **Test the application:**
   - Navigate to your application URL
   - Login with test credentials:
     - Username: `admin`
     - Password: `admin123`

---

## What's Included

### Complete SQL File: `aps_database_complete.sql`

This file contains:
- **All table structures** (16 tables)
- **Sample data** (4 test users, lookup data)
- **Indexes and foreign keys**
- **Optimized for production use**

**Size:** ~28 KB  
**Tables:** 16 tables  
**Lines:** 783 lines of SQL

### Tables Created

1. **users** - User authentication and roles
2. **patients** - Patient demographic and clinical data
3. **catheters** - Catheter insertion records
4. **drug_regimes** - Medication administration
5. **functional_outcomes** - Patient outcome assessments
6. **catheter_removals** - Catheter removal records
7. **alerts** - System alerts and notifications
8. **audit_logs** - Audit trail
9. **patient_physicians** - Patient-physician assignments
10. **notifications** - User notifications
11. **smtp_settings** - Email configuration
12. **lookup_catheter_types** - Catheter types reference
13. **lookup_comorbidities** - Comorbidity options
14. **lookup_surgeries** - Surgery types
15. **lookup_drug_names** - Drug names reference
16. **lookup_complications** - Complication types

### Test Users (Password: admin123)

| Username | Role | Email |
|----------|------|-------|
| admin | System Administrator | admin@hospital.com |
| dr.sharma | Attending Physician | sharma@hospital.com |
| dr.patel | Resident | patel@hospital.com |
| nurse.kumar | Nurse | kumar@hospital.com |

---

## Alternative Methods

### Option 2: Command Line Import

If you have command-line access:

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS aps_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import SQL file
mysql -u root -p aps_database < database/aps_database_complete.sql

# Verify
mysql -u root -p aps_database -e "SHOW TABLES;"
```

### Option 3: Use Installation Wizard

The application includes a web-based installation wizard:

1. Navigate to: `http://your-domain/install/`
2. Follow the step-by-step wizard:
   - Step 1: System requirements check
   - Step 2: Database configuration
   - Step 3: Create tables (automated)
   - Step 4: Create admin user
   - Step 5: Complete

**Note:** If the wizard stalls, check `logs/install.log` for detailed error messages.

### Option 4: Run PHP Setup Script

```bash
php install/database-setup.php
```

This script will:
- Connect to MySQL
- Create the database
- Run all migrations
- Load seed data
- Display summary

---

## Troubleshooting

### Import Fails with "Access Denied"

**Solution:** Make sure your MySQL user has sufficient privileges:

```sql
GRANT ALL PRIVILEGES ON aps_database.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
```

### "Table already exists" Error

**Solution:** Drop the existing database first (⚠️ WARNING: This deletes all data):

```sql
DROP DATABASE IF EXISTS aps_database;
CREATE DATABASE aps_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then import again.

### Foreign Key Constraint Errors

**Solution:** The SQL file is designed to import in the correct order. If you get foreign key errors:
1. Make sure to import the complete file, not individual tables
2. Check that you're using MySQL 5.7+ or MariaDB 10.2+
3. Verify InnoDB engine is available: `SHOW ENGINES;`

### PHP max_execution_time Error

**Solution:** For large imports through PhpMyAdmin:
1. Edit `php.ini`:
   ```ini
   max_execution_time = 300
   post_max_size = 50M
   upload_max_filesize = 50M
   ```
2. Restart Apache/PHP-FPM
3. Try import again

### Installation Wizard Stalls

**Solution:** Enable debugging:
1. Check `logs/install.log` for errors
2. Verify file permissions on `logs/` directory (must be writable)
3. Check PHP error log: `/var/log/apache2/error.log` or `/var/log/php-fpm/error.log`
4. Enable display_errors in php.ini for debugging (disable in production!)

Common issues:
- **Migrations directory not found:** Verify `src/Database/migrations/` exists
- **Database connection timeout:** Check MySQL is running and credentials are correct
- **Empty SQL files:** Re-download or re-extract the archive

---

## Database Configuration

### Using config.php (Recommended for production)

Edit `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'aps_database');
define('DB_USER', 'aps_user');
define('DB_PASS', 'your_secure_password');
define('DB_CHARSET', 'utf8mb4');
```

### Using Environment Variables (Recommended for dev/staging)

Create `.env` file:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=aps_database
DB_USER=aps_user
DB_PASS=your_secure_password
```

The `config/database.php` will automatically load from `.env` if `config.php` doesn't exist.

---

## Security Notes

### Change Default Password

**IMPORTANT:** The default password for all test users is `admin123`. Change this immediately after installation!

```sql
UPDATE users SET password_hash = '$2y$12$YOUR_NEW_HASH_HERE' WHERE username = 'admin';
```

Or use the application's "Change Password" feature.

### Create Production Database User

Don't use `root` in production! Create a dedicated user:

```sql
CREATE USER 'aps_user'@'localhost' IDENTIFIED BY 'strong_random_password';
GRANT ALL PRIVILEGES ON aps_database.* TO 'aps_user'@'localhost';
FLUSH PRIVILEGES;
```

### Enable SSL Connection (Production)

For production databases, enable SSL:

```php
define('DB_SSL', true);
define('DB_SSL_CA', '/path/to/ca-cert.pem');
```

---

## Backup and Restore

### Create Backup

```bash
# Full backup
mysqldump -u root -p aps_database > backup_$(date +%Y%m%d).sql

# Structure only
mysqldump -u root -p --no-data aps_database > structure.sql

# Data only
mysqldump -u root -p --no-create-info aps_database > data.sql
```

### Restore from Backup

```bash
mysql -u root -p aps_database < backup_20260112.sql
```

---

## Version Information

- **Database Version:** 1.1.2
- **Schema Version:** 1.1.2
- **Last Updated:** 2026-01-12
- **MySQL/MariaDB Required:** 5.7+ / 10.2+
- **Character Set:** utf8mb4
- **Collation:** utf8mb4_unicode_ci

---

## Support

For issues:
1. Check `logs/install.log` for detailed errors
2. Review this README for common solutions
3. See `DEPLOY.md` for deployment troubleshooting
4. Create an issue: https://github.com/drjagan/acute-pain-service/issues
