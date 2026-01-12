# Acute Pain Service - SBVU Production Deployment

**Domain:** https://aps.sbvu.ac.in  
**Platform:** Cloudron  
**Branch:** aps.sbvu.ac.in  
**Version:** 1.1.3  
**Date:** January 12, 2026

---

## 🚀 Quick Start

### 1. Extract Files

Extract this package to `/app/data/` on your Cloudron server.

### 2. Import Database

```bash
cd /app/data
mysql -h mysql -u a916f81cc97ef00e -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e < documentation/database/aps_database_complete.sql
```

### 3. Create Admin User

First, generate the password hash:
```bash
php generate-password-hash.php
```

Then update `database-setup-sbvu.sql` with the generated hash and run:
```bash
mysql -h mysql -u a916f81cc97ef00e -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e < database-setup-sbvu.sql
```

### 4. Set Permissions

```bash
chmod 755 logs public/uploads public/exports
chmod 600 .env
chown -R cloudron:cloudron /app/data
```

### 5. Access Application

Visit: **https://aps.sbvu.ac.in**

**Login:**
- Username: `jagan`
- Password: `Panruti-Cuddalore-Pondicherry`

**Change password immediately after first login!**

---

## 📋 What's Configured

### ✅ Database Connection
- Host: mysql (Cloudron)
- Database: a916f81cc97ef00e
- User: a916f81cc97ef00e
- Configured in: `.env` file

### ✅ Email/SMTP
- Host: mail (Cloudron)
- Port: 2525
- From: aps.app@sbvu.ac.in
- Configured in: `.env` file

### ✅ Application Settings
- URL: https://aps.sbvu.ac.in
- Environment: production
- Timezone: Asia/Kolkata
- Session: 2 hours

### ✅ Security
- Debug mode: OFF
- Error display: OFF
- .env file: Protected (600 permissions)
- Password: Bcrypt with cost 12

---

## 📖 Full Documentation

**Complete deployment guide:**  
→ `CLOUDRON_DEPLOYMENT_GUIDE.md`

This guide includes:
- Step-by-step deployment instructions
- Troubleshooting common issues
- Security configuration
- Post-deployment tasks
- Update procedures

---

## 🔧 Quick Troubleshooting

### Can't connect to database?
```bash
php diagnose-database.php
```

### Tables missing?
```bash
php fix-lookup-tables.php
```

### Test configuration?
```bash
php test-env-config.php
```

### Check logs?
```bash
tail -50 logs/app.log
```

---

## 📁 Directory Structure

```
/app/data/
├── public/              ← Web root (https://aps.sbvu.ac.in)
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── src/                 ← Application code
├── config/              ← Configuration files
├── logs/                ← Application logs
├── .env                 ← Cloudron credentials (PROTECTED)
├── database-setup-sbvu.sql
├── CLOUDRON_DEPLOYMENT_GUIDE.md
└── documentation/
```

---

## ⚠️ Important Notes

1. **The .env file contains your Cloudron credentials** - it's already configured!
2. **Change the admin password** after first login
3. **Set proper file permissions** before accessing the app
4. **Import the database** before accessing the app
5. **Review the full deployment guide** for detailed instructions

---

## 🆘 Need Help?

1. **Read:** `CLOUDRON_DEPLOYMENT_GUIDE.md`
2. **Check logs:** `logs/app.log`
3. **Run diagnostics:** `php diagnose-database.php`
4. **Review troubleshooting:** `documentation/troubleshooting/`

---

## 🎯 Deployment Checklist

```
[ ] Extracted files to /app/data/
[ ] Set file permissions (chmod/chown)
[ ] Imported database SQL
[ ] Created admin user
[ ] Generated password hash
[ ] Verified .env file exists
[ ] Tested https://aps.sbvu.ac.in loads
[ ] Logged in successfully
[ ] Changed admin password
[ ] Tested creating patient record
```

---

## 📊 System Information

- **PHP Version Required:** 8.1+
- **MySQL Version:** 8.0+ (Cloudron provides this)
- **Database Tables:** 16
- **Initial Users:** 5 (4 test + 1 admin)
- **Lookup Tables:** 5
- **Timezone:** Asia/Kolkata
- **Character Set:** UTF-8 (utf8mb4)

---

**Ready to deploy!** 🚀

For complete instructions, see: `CLOUDRON_DEPLOYMENT_GUIDE.md`
