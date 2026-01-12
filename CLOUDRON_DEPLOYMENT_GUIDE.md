# Cloudron Deployment Guide - SBVU

**Application:** Acute Pain Service  
**Domain:** https://aps.sbvu.ac.in  
**Platform:** Cloudron  
**Branch:** aps.sbvu.ac.in  
**Date:** January 12, 2026

---

## 📋 Pre-Deployment Checklist

- [x] Cloudron MySQL credentials configured
- [x] Cloudron Mail credentials configured
- [x] Domain configured: aps.sbvu.ac.in
- [x] .env file created with production credentials
- [x] Admin user specified: jagan
- [x] Branch created: aps.sbvu.ac.in

---

## 🚀 Deployment Steps

### Step 1: Upload Application Files

**Extract the deployment package to:**
```
/app/data/
```

**Your directory structure should be:**
```
/app/data/
├── public/              ← Web root (this is where aps.sbvu.ac.in points)
│   ├── index.php
│   ├── .htaccess       ← NEW (for URL rewriting)
│   ├── assets/
│   ├── uploads/
│   └── exports/
├── src/
├── config/
├── logs/
├── .env                 ← Contains your Cloudron credentials
├── database-setup-sbvu.sql
└── documentation/
```

**Important:** The `.env` file is already configured with your Cloudron credentials!

---

### Step 2: Set File Permissions

```bash
cd /app/data

# Set ownership (Cloudron typically uses 'cloudron' user)
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
chmod 600 .env
```

---

### Step 3: Import Database

#### Option 1: Using MySQL Command Line (Recommended)

```bash
cd /app/data

# Import complete database structure and seed data
mysql -h mysql -u a916f81cc97ef00e -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e < documentation/database/aps_database_complete.sql

# Create admin user
mysql -h mysql -u a916f81cc97ef00e -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e < database-setup-sbvu.sql
```

#### Option 2: Using PhpMyAdmin

1. Access PhpMyAdmin (if available on Cloudron)
2. Select database: `a916f81cc97ef00e`
3. Click "Import"
4. Upload: `documentation/database/aps_database_complete.sql`
5. Click "Go"
6. After success, upload: `database-setup-sbvu.sql`
7. Click "Go"

---

### Step 4: Generate Admin Password Hash

The admin password hash needs to be generated securely:

```bash
cd /app/data
php generate-password-hash.php
```

**Output:**
```
Password: Panruti-Cuddalore-Pondicherry
Hash: $2y$12$...
```

**Copy the hash and update `database-setup-sbvu.sql` with it.**

Then run:
```bash
mysql -h mysql -u a916f81cc97ef00e -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e < database-setup-sbvu.sql
```

---

### Step 5: Verify Database Tables

```bash
mysql -h mysql -u a916f81cc97ef00e -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  -e "USE a916f81cc97ef00e; SHOW TABLES;"
```

**Expected output:** 16 tables
```
alerts
audit_logs
catheter_removals
catheters
drug_regimes
functional_outcomes
lookup_adjuvants
lookup_comorbidities
lookup_drugs
lookup_red_flags
lookup_surgeries
notifications
patient_physicians
patients
smtp_settings
users
```

---

### Step 6: Verify Admin User

```bash
mysql -h mysql -u a916f81cc97ef00e -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  -e "USE a916f81cc97ef00e; SELECT username, email, role, status FROM users WHERE username='jagan';"
```

**Expected output:**
```
+----------+-------------------+-------+--------+
| username | email             | role  | status |
+----------+-------------------+-------+--------+
| jagan    | jagan@sbvu.ac.in  | admin | active |
+----------+-------------------+-------+--------+
```

---

### Step 7: Test Application

#### Access the Application

Visit: **https://aps.sbvu.ac.in**

**You should see:**
- ✓ Login page loads
- ✓ No errors displayed
- ✓ CSS and assets load properly

#### Login

**Username:** `jagan`  
**Password:** `Panruti-Cuddalore-Pondicherry`

**You should be able to:**
- ✓ Login successfully
- ✓ See admin dashboard
- ✓ Access all menu items
- ✓ View patients, catheters, etc.

---

## 🔧 Troubleshooting

### Issue: "Database connection failed"

**Check .env file:**
```bash
cat /app/data/.env | grep DB_
```

Should show:
```
DB_HOST=mysql
DB_PORT=3306
DB_NAME=a916f81cc97ef00e
DB_USER=a916f81cc97ef00e
DB_PASS=33050ba714a937bf69970570779e802c33b9faa11e4864d4
```

**Test database connection:**
```bash
mysql -h mysql -u a916f81cc97ef00e -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  -e "SELECT 1+1;"
```

---

### Issue: "Table 'lookup_comorbidities' doesn't exist"

**Run the fix script:**
```bash
cd /app/data
php fix-lookup-tables.php
```

Or manually import:
```bash
mysql -h mysql -u a916f81cc97ef00e -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e < documentation/database/aps_database_complete.sql
```

---

### Issue: "Invalid username or password"

**Reset admin password:**
```bash
cd /app/data
php generate-password-hash.php
```

Copy the hash, then run in MySQL:
```sql
UPDATE users 
SET password_hash = '$2y$12$YOUR_HASH_HERE' 
WHERE username = 'jagan';
```

---

### Issue: "Permission denied" errors

**Fix permissions:**
```bash
cd /app/data
chmod 755 logs public/uploads public/exports
chmod 666 logs/*.log
chown -R cloudron:cloudron /app/data
```

---

### Issue: 404 or "File not found"

**Check .htaccess:**
```bash
cat /app/data/public/.htaccess
```

Should start with:
```apache
# Acute Pain Service - Apache Configuration
<IfModule mod_rewrite.c>
    RewriteEngine On
    ...
```

**Verify Apache mod_rewrite is enabled:**
```bash
apachectl -M | grep rewrite
```

---

## 📊 Configuration Details

### Database Connection
- **Host:** mysql (Cloudron internal hostname)
- **Port:** 3306
- **Database:** a916f81cc97ef00e
- **User:** a916f81cc97ef00e
- **Connection:** Via .env file (secure)

### Email Configuration
- **SMTP Host:** mail (Cloudron internal)
- **SMTP Port:** 2525 (TLS: 2465)
- **From Address:** aps.app@sbvu.ac.in
- **Domain:** sbvu.ac.in

### Application Settings
- **URL:** https://aps.sbvu.ac.in
- **Environment:** production
- **Debug:** disabled
- **Timezone:** Asia/Kolkata
- **Log Level:** ERROR (production)

---

## 🔐 Security Notes

### .env File Protection

The .env file contains sensitive credentials and is:
- ✓ Set to permissions 600 (owner read/write only)
- ✓ Excluded from web access via .htaccess
- ✓ Gitignored (not in version control)

### Password Security

**Change the admin password after first login!**

To change password:
1. Login as jagan
2. Go to Profile/Settings
3. Change password
4. Use a strong password (min 8 characters)

### Session Security

- Sessions expire after 2 hours (7200 seconds)
- Max 5 login attempts before lockout
- Lockout duration: 15 minutes

---

## 📝 Post-Deployment Tasks

### Immediate (Required)

1. **Test login** with jagan account
2. **Change admin password** to something more secure
3. **Verify database** has all 16 tables
4. **Test creating a patient record**
5. **Test creating a catheter record**
6. **Verify file uploads** work

### Within 24 Hours

1. **Configure SMTP** settings if emails not working
2. **Review users** - delete test users if not needed
3. **Configure backup** schedule
4. **Test email notifications**
5. **Review application logs**

### Within 1 Week

1. **Train users** on the system
2. **Configure additional settings**
3. **Customize lookup data** (surgeries, drugs, etc.)
4. **Set up monitoring** (optional)
5. **Document any customizations**

---

## 🔄 Updating the Application

To update in the future:

1. **Backup database:**
   ```bash
   mysqldump -h mysql -u a916f81cc97ef00e -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
     a916f81cc97ef00e > backup_$(date +%Y%m%d).sql
   ```

2. **Backup .env file:**
   ```bash
   cp .env .env.backup
   ```

3. **Upload new files** (preserving .env)

4. **Run any new migrations** (if provided)

5. **Clear cache** (if applicable)

6. **Test thoroughly**

---

## 📞 Support

### Logs Location
- Application logs: `/app/data/logs/app.log`
- PHP errors: `/app/data/logs/php-errors.log`
- Installation log: `/app/data/logs/install.log`

### Check Logs
```bash
# Application errors
tail -50 /app/data/logs/app.log

# PHP errors
tail -50 /app/data/logs/php-errors.log

# Real-time monitoring
tail -f /app/data/logs/app.log
```

### Diagnostic Scripts
```bash
cd /app/data

# Diagnose database issues
php diagnose-database.php

# Test configuration
php test-env-config.php

# Fix lookup tables
php fix-lookup-tables.php
```

---

## ✅ Deployment Checklist

- [ ] Files uploaded to /app/data/
- [ ] Permissions set correctly
- [ ] Database imported (aps_database_complete.sql)
- [ ] Admin user created (database-setup-sbvu.sql)
- [ ] Admin password hash generated
- [ ] Application accessible at https://aps.sbvu.ac.in
- [ ] Login works with jagan account
- [ ] Admin password changed
- [ ] Email configuration tested
- [ ] Sample patient record created
- [ ] File uploads tested
- [ ] All 16 database tables verified
- [ ] Logs are writable
- [ ] Backup procedure documented

---

## 📚 Additional Documentation

- **Complete Installation Guide:** `documentation/installation/INSTALL.md`
- **Environment Configuration:** `documentation/installation/ENV_CONFIGURATION.md`
- **Database Setup:** `documentation/database/README.md`
- **Troubleshooting:** `documentation/troubleshooting/`
- **Testing Guide:** `documentation/development/TESTING_GUIDE_v1.1.md`

---

**Deployment Prepared By:** OpenCode AI  
**Date:** January 12, 2026  
**Branch:** aps.sbvu.ac.in  
**Status:** Ready for Production  

**Questions?** Review the troubleshooting section or check the logs.

---

## 🎉 Congratulations!

Your Acute Pain Service application is now deployed and ready for use at:

**https://aps.sbvu.ac.in**

Login with:
- **Username:** jagan
- **Password:** Panruti-Cuddalore-Pondicherry

**Remember to change your password after first login!**
