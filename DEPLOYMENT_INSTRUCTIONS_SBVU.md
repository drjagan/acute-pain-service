# 🚀 SBVU Cloudron Deployment - Quick Instructions

**Application:** Acute Pain Service  
**Domain:** https://aps.sbvu.ac.in  
**Branch:** aps.sbvu.ac.in  
**Status:** READY TO DEPLOY ✅

---

## 📥 Download the Deployment Package

### Option 1: Download from GitHub (Recommended)

1. Go to: https://github.com/drjagan/acute-pain-service
2. Click on branch dropdown (currently shows "main")
3. Select branch: `aps.sbvu.ac.in`
4. Click "Code" button → "Download ZIP"
5. Extract the ZIP file

### Option 2: Clone the Branch

```bash
git clone -b aps.sbvu.ac.in https://github.com/drjagan/acute-pain-service.git
```

### Option 3: Download Direct Link

https://github.com/drjagan/acute-pain-service/archive/refs/heads/aps.sbvu.ac.in.zip

---

## 📦 What's Included

This branch contains:

✅ **Production .env file** with your Cloudron credentials  
✅ **Database import SQL** file  
✅ **Admin user setup** SQL script  
✅ **Apache .htaccess** configuration  
✅ **Complete documentation** for deployment  
✅ **Diagnostic tools** for troubleshooting  
✅ **All application code** ready to run  

---

## 🎯 Deployment Steps (5 Minutes)

### Step 1: Upload Files (1 min)

Extract the downloaded package to your Cloudron server at:
```
/app/data/
```

Your structure should be:
```
/app/data/
├── public/         ← This is your web root
├── src/
├── config/
├── .env           ← Already configured!
└── ...
```

---

### Step 2: Set Permissions (30 seconds)

```bash
cd /app/data
chmod 755 logs public/uploads public/exports
chmod 600 .env
chown -R cloudron:cloudron /app/data
```

---

### Step 3: Import Database (2 min)

```bash
cd /app/data

# Import database structure and data
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e < documentation/database/aps_database_complete.sql
```

---

### Step 4: Create Admin User (1 min)

**First, generate the password hash:**
```bash
php generate-password-hash.php
```

This will output:
```
Password: Panruti-Cuddalore-Pondicherry
Hash: $2y$12$...
```

**Edit `database-setup-sbvu.sql` and replace the password_hash with the generated hash.**

Then run:
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e < database-setup-sbvu.sql
```

---

### Step 5: Test! (30 seconds)

Visit: **https://aps.sbvu.ac.in**

Login with:
- **Username:** `jagan`
- **Password:** `Panruti-Cuddalore-Pondicherry`

**✅ You should see the dashboard!**

---

## 🔧 If Something Goes Wrong

### Database Connection Issues?

Run diagnostics:
```bash
cd /app/data
php diagnose-database.php
```

### Tables Missing?

Fix automatically:
```bash
php fix-lookup-tables.php
```

Or manually reimport:
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e < documentation/database/aps_database_complete.sql
```

### Can't Login?

Verify admin user exists:
```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  -e "USE a916f81cc97ef00e; SELECT * FROM users WHERE username='jagan';"
```

### Check Logs

```bash
tail -50 /app/data/logs/app.log
tail -50 /app/data/logs/php-errors.log
```

---

## 📖 Detailed Documentation

For complete step-by-step instructions with troubleshooting:

**→ Read: `CLOUDRON_DEPLOYMENT_GUIDE.md`**

This comprehensive guide includes:
- Detailed deployment steps
- Security configuration
- Troubleshooting common issues
- Post-deployment tasks
- Update procedures

---

## ⚠️ Important Security Notes

1. **Change password after first login!**
   - The default password is in the documentation
   - Use a strong, unique password
   
2. **The .env file is protected**
   - Permissions set to 600 (owner only)
   - Contains sensitive Cloudron credentials
   - Never commit to version control

3. **Production mode is enabled**
   - Debug mode: OFF
   - Error display: OFF
   - Logging: ERROR level only

---

## ✅ Deployment Checklist

Use this checklist to track your deployment:

```
[ ] Downloaded aps.sbvu.ac.in branch
[ ] Extracted to /app/data/
[ ] Set file permissions
[ ] Imported aps_database_complete.sql
[ ] Generated password hash
[ ] Created admin user (database-setup-sbvu.sql)
[ ] Verified .env file exists
[ ] Tested https://aps.sbvu.ac.in loads
[ ] Logged in successfully
[ ] Changed admin password
[ ] Tested creating a record
```

---

## 🎉 You're Done!

Your Acute Pain Service is now live at:

**https://aps.sbvu.ac.in**

**Login:**
- Username: `jagan`
- Password: `Panruti-Cuddalore-Pondicherry` (change this!)

---

## 📞 Need More Help?

1. **Quick Start:** This file
2. **Complete Guide:** `CLOUDRON_DEPLOYMENT_GUIDE.md`
3. **Troubleshooting:** `documentation/troubleshooting/`
4. **Run Diagnostics:** `php diagnose-database.php`
5. **Check Logs:** `tail -f logs/app.log`

---

## 📊 What's Configured

All of these are already set up in the `.env` file:

✅ Database: mysql (Cloudron internal)  
✅ Email: mail (Cloudron SMTP)  
✅ Domain: https://aps.sbvu.ac.in  
✅ Environment: production  
✅ Timezone: Asia/Kolkata  
✅ Character set: UTF-8  
✅ Session: 2 hours  
✅ Security: Enabled  

You don't need to configure anything - it's ready to go!

---

**Questions?** Check the logs or read the deployment guide.

**Ready to deploy?** Follow the 5 steps above!

🚀 **Happy deploying!**
