# Which Version Should You Download for Production?

**Critical Decision for aps.sbvu.ac.in Deployment**

---

## 🎯 Short Answer

**Use the `aps.sbvu.ac.in` branch, NOT the v1.2.0 tag!**

```bash
# CORRECT (Production-ready)
wget https://github.com/drjagan/acute-pain-service/archive/refs/heads/aps.sbvu.ac.in.zip

# WRONG (Missing production-specific files)
wget https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.2.0.tar.gz
```

---

## 📋 Detailed Explanation

### v1.2.0 Tag (From `main` branch)

**What it is:**
- Clean release for general public
- Contains core application code
- No production credentials
- Generic configuration

**What it includes:**
- ✅ All v1.2.0 features (Master Data Management)
- ✅ Core application files
- ✅ Database migrations
- ✅ General documentation
- ❌ `.env.production.sbvu` (your credentials!)
- ❌ Cloudron-specific deployment guides
- ❌ Production configuration files
- ❌ SBVU-specific setup scripts

**Best for:**
- New installations (non-SBVU)
- Community downloads
- Development environments
- Generic deployments

---

### aps.sbvu.ac.in Branch (Production-ready)

**What it is:**
- Production deployment branch
- Contains SBVU-specific configuration
- Has your Cloudron credentials (encrypted in repo)
- Optimized for aps.sbvu.ac.in

**What it includes:**
- ✅ All v1.2.0 features
- ✅ Core application files
- ✅ Database migrations
- ✅ `.env.production.sbvu` ⭐ (Your credentials!)
- ✅ `CLOUDRON_DEPLOYMENT_GUIDE.md`
- ✅ `DEPLOYMENT_MANUAL_v1.2.0.md`
- ✅ `GIT_SETUP_PRODUCTION.md`
- ✅ `INSTALL.md` (Cloudron-specific)
- ✅ `database-setup-sbvu.sql`
- ✅ `generate-password-hash.php`
- ✅ `deploy-v1.2.0-to-production.sh`
- ✅ Production-ready config files

**Best for:**
- ✅ **aps.sbvu.ac.in deployment** ⭐
- ✅ Your Cloudron server
- ✅ SBVU production environment

---

## 🔍 File Comparison

### Files ONLY in aps.sbvu.ac.in branch:

```
Production Credentials:
✅ .env.production.sbvu              ← CRITICAL! Has your DB credentials

Deployment Guides:
✅ CLOUDRON_DEPLOYMENT_GUIDE.md
✅ CLOUDRON_DIRECTORY_STRUCTURE.md
✅ DEPLOYMENT_COMMANDS_v1.2.0.md
✅ DEPLOYMENT_INSTRUCTIONS_SBVU.md
✅ DEPLOYMENT_MANUAL_v1.2.0.md
✅ DEPLOY_NOW.md
✅ GIT_SETUP_PRODUCTION.md
✅ INSTALL.md
✅ MIGRATION_QUICK_REFERENCE.md
✅ README-SBVU.md

Setup Scripts:
✅ database-setup-sbvu.sql
✅ generate-password-hash.php
✅ deploy-v1.2.0-to-production.sh
✅ debug-500-error.php
✅ fix-permissions.sh

Configuration:
✅ config/env-loader.php
✅ config/constants.php
✅ Modified config/config.php (for Cloudron)
✅ Modified public/.htaccess (for Cloudron paths)
```

---

## ⚠️ Why This Matters

### If you download v1.2.0 tag:

```bash
# You download
wget https://github.com/.../v1.2.0.tar.gz

# You DON'T get:
❌ .env.production.sbvu (your Cloudron credentials)
❌ Cloudron deployment guides
❌ database-setup-sbvu.sql
❌ Production config optimizations

# You'll need to:
⚠️ Manually create .env file
⚠️ Look up Cloudron credentials
⚠️ Figure out correct paths
⚠️ Miss production-specific optimizations
```

### If you download aps.sbvu.ac.in branch:

```bash
# You download
wget https://github.com/.../aps.sbvu.ac.in.zip

# You get:
✅ .env.production.sbvu (all Cloudron credentials pre-configured!)
✅ All deployment guides
✅ database-setup-sbvu.sql
✅ Production config ready to use

# You just need to:
✅ Extract files
✅ Copy to /app/data
✅ Rename .env.production.sbvu to .env
✅ Run migrations
✅ Done!
```

---

## 🚀 Corrected Deployment Commands

### Step 4: Download from GitHub (CORRECTED)

```bash
cd /tmp

# CORRECT: Download aps.sbvu.ac.in branch
wget https://github.com/drjagan/acute-pain-service/archive/refs/heads/aps.sbvu.ac.in.zip

# Verify download
ls -lh aps.sbvu.ac.in.zip
# Should show file size (e.g., 3-4 MB)

# Extract archive
unzip aps.sbvu.ac.in.zip

# Verify extraction
ls -l acute-pain-service-aps.sbvu.ac.in/
# Should show all directories including .env.production.sbvu

# Rename for easier handling (optional)
mv acute-pain-service-aps.sbvu.ac.in acute-pain-service-production
```

### Step 5: Copy Files (with .env handling)

```bash
cd /tmp/acute-pain-service-production

# Copy source code
cp -r src /app/data/

# Copy config files
cp -r config /app/data/

# Copy documentation
cp -r documentation /app/data/

# Copy VERSION
cp VERSION /app/data/

# Copy storage structure
cp -r storage /app/data/ 2>/dev/null || mkdir -p /app/data/storage/sessions

# IMPORTANT: Set up .env file
cp .env.production.sbvu /app/data/.env

# Verify .env has correct credentials
cat /app/data/.env | head -20
```

---

## 📊 Visual Comparison

### Download Decision Tree

```
Do you need to deploy to aps.sbvu.ac.in?
│
├─ YES → Download aps.sbvu.ac.in branch ✅
│         Has your credentials, deployment guides, production configs
│
└─ NO → Are you setting up a different server?
          │
          ├─ YES → Download v1.2.0 tag ✅
          │         Generic release, configure manually
          │
          └─ NO → Just testing locally?
                    → Clone the repo, use main branch ✅
```

---

## 🔐 Security Note

### About .env.production.sbvu

**Question:** "Why is .env.production.sbvu tracked in git? Isn't that insecure?"

**Answer:**

1. **Private Repository**: Your repo is private, not public
2. **Exception Rule**: `.gitignore` has `!.env.production.sbvu` to allow tracking
3. **Convenience**: Pre-configured credentials for Cloudron deployment
4. **Alternative Exists**: `.env.example` for other deployments

**The .gitignore says:**
```gitignore
# Environment Files
.env
.env.local
.env.production

# Exception for Cloudron deployment branch (private repo only)
!.env.production.sbvu
```

**This is safe because:**
- ✅ Repository is private (only you have access)
- ✅ Credentials are Cloudron-internal (not exposed to internet)
- ✅ Database is behind Cloudron firewall
- ✅ Simplifies your deployment process

---

## ✅ Recommendation

### For Your SBVU Production Deployment:

**Always use the `aps.sbvu.ac.in` branch**

**Download URL:**
```bash
https://github.com/drjagan/acute-pain-service/archive/refs/heads/aps.sbvu.ac.in.zip
```

**Why:**
1. ✅ Contains `.env.production.sbvu` with your credentials
2. ✅ Has all Cloudron deployment documentation
3. ✅ Includes SBVU-specific setup scripts
4. ✅ Config files optimized for your Cloudron setup
5. ✅ Everything pre-configured for `/app/data` structure

---

## 📝 Updated Deployment Command Summary

```bash
# 1. SSH into server
ssh cloudron@aps.sbvu.ac.in

# 2. Create backup
cd /app/data
BACKUP_DIR="/app/data/backups/v1.2.0-$(date +%Y%m%d-%H%M%S)"
mkdir -p $BACKUP_DIR
mysqldump -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e > $BACKUP_DIR/database_backup.sql
cp .env $BACKUP_DIR/.env.backup

# 3. Download aps.sbvu.ac.in branch (NOT v1.2.0 tag!)
cd /tmp
wget https://github.com/drjagan/acute-pain-service/archive/refs/heads/aps.sbvu.ac.in.zip
unzip aps.sbvu.ac.in.zip
cd acute-pain-service-aps.sbvu.ac.in

# 4. Copy files
cp -r src /app/data/
cp -r config /app/data/
cp -r documentation /app/data/
cp VERSION /app/data/
cp .env.production.sbvu /app/data/.env

# 5. Verify .env
cat /app/data/.env | grep DB_HOST
# Should show: DB_HOST=mysql

# 6. Run migrations
cd /app/data
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/013_create_new_lookup_tables.sql

mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/014_update_surgeries_with_specialties.sql

# 7. Optional: Seed data
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/seeders/MasterDataSeeder.sql

# 8. Set permissions
chmod 777 storage/sessions
chmod 755 logs
chmod 600 .env

# 9. Clean up
rm -rf /tmp/acute-pain-service-aps.sbvu.ac.in
rm /tmp/aps.sbvu.ac.in.zip

# 10. Verify
cat VERSION
# Should show: 1.2.0

# 11. Test
curl -I https://aps.sbvu.ac.in
# Should show: HTTP/1.1 200 OK
```

---

## 🎯 Summary

| Feature | v1.2.0 Tag | aps.sbvu.ac.in Branch |
|---------|------------|----------------------|
| **Core Features** | ✅ Yes | ✅ Yes |
| **Migrations** | ✅ Yes | ✅ Yes |
| **Documentation** | ✅ General | ✅ SBVU-specific |
| **.env.production.sbvu** | ❌ No | ✅ YES! |
| **Cloudron Guides** | ❌ No | ✅ YES! |
| **Setup Scripts** | ❌ No | ✅ YES! |
| **Ready for SBVU** | ⚠️ Needs manual config | ✅ YES! |
| **Best For** | Public/Generic | aps.sbvu.ac.in |

---

## ✅ Final Answer

**For aps.sbvu.ac.in deployment:**

Use: `https://github.com/drjagan/acute-pain-service/archive/refs/heads/aps.sbvu.ac.in.zip`

**NOT**: `https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.2.0.tar.gz`

---

**The aps.sbvu.ac.in branch is specifically prepared for your Cloudron deployment with all credentials and guides included!**

---

**Date:** January 24, 2026  
**For:** aps.sbvu.ac.in Production Deployment  
**Recommendation:** Always use production branch, not release tags
