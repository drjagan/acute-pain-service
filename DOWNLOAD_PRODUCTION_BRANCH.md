# How to Download Production Branch for Deployment

**Problem:** The branch name `aps.sbvu.ac.in` contains dots, which causes issues with GitHub's archive download system.

**Solution:** Use one of these 3 working methods below.

---

## ✅ Method 1: Git Clone (Recommended - Fastest)

This is the easiest and most reliable method.

```bash
# On your production server
cd /tmp

# Clone the repository
git clone --branch aps.sbvu.ac.in --depth 1 https://github.com/drjagan/acute-pain-service.git

# Navigate into the cloned directory
cd acute-pain-service

# You now have all files from aps.sbvu.ac.in branch
ls -la
# Should show: .env.production.sbvu, src/, config/, etc.
```

**Advantages:**
- ✅ Works reliably
- ✅ Gets exact branch content
- ✅ No archive extraction needed
- ✅ Can pull updates easily later

**Next Steps:**
```bash
# From /tmp/acute-pain-service
cp -r src /app/data/
cp -r config /app/data/
cp -r documentation /app/data/
cp VERSION /app/data/
cp .env.production.sbvu /app/data/.env
```

---

## ✅ Method 2: Download Latest Commit Tarball

GitHub provides a direct download for the latest commit:

```bash
cd /tmp

# Download the latest tarball from the branch
wget https://github.com/drjagan/acute-pain-service/tarball/aps.sbvu.ac.in -O aps-production.tar.gz

# Extract (GitHub creates a folder with commit hash)
tar -xzf aps-production.tar.gz

# Find the extracted directory (it will have a commit hash name)
ls -d drjagan-acute-pain-service-*

# Rename for easier handling
mv drjagan-acute-pain-service-* acute-pain-service-production

cd acute-pain-service-production
```

**Advantages:**
- ✅ No git required
- ✅ Direct download
- ✅ Works with branch names containing dots

**Next Steps:**
```bash
# From /tmp/acute-pain-service-production
cp -r src /app/data/
cp -r config /app/data/
cp -r documentation /app/data/
cp VERSION /app/data/
cp .env.production.sbvu /app/data/.env
```

---

## ✅ Method 3: Use GitHub API to Get Archive

Use GitHub's API to generate a download:

```bash
cd /tmp

# Download using GitHub API
curl -L -o aps-production.tar.gz \
  https://api.github.com/repos/drjagan/acute-pain-service/tarball/aps.sbvu.ac.in

# Extract
tar -xzf aps-production.tar.gz

# Find and rename the extracted directory
mv drjagan-acute-pain-service-* acute-pain-service-production

cd acute-pain-service-production
```

**Advantages:**
- ✅ Official API method
- ✅ Reliable
- ✅ Works with any branch name

---

## ❌ Method 4: Manual Download via Browser (Last Resort)

If none of the above work, you can download manually:

1. **On your local machine:**
   - Go to https://github.com/drjagan/acute-pain-service
   - Click the branch dropdown (shows "main")
   - Select "aps.sbvu.ac.in"
   - Click the green "Code" button
   - Select "Download ZIP"

2. **Upload to server:**
   ```bash
   # On your local machine
   scp acute-pain-service-aps.sbvu.ac.in.zip cloudron@aps.sbvu.ac.in:/tmp/
   
   # On server
   cd /tmp
   unzip acute-pain-service-aps.sbvu.ac.in.zip
   cd acute-pain-service-aps.sbvu.ac.in
   ```

---

## 🎯 Recommended Approach

### **Use Method 1 (Git Clone)**

**Full deployment commands with Method 1:**

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

# 3. Clone production branch
cd /tmp
git clone --branch aps.sbvu.ac.in --depth 1 https://github.com/drjagan/acute-pain-service.git
cd acute-pain-service

# 4. Verify you have production files
ls -la .env.production.sbvu
# Should show the file exists

# 5. Copy files to production
cp -r src /app/data/
cp -r config /app/data/
cp -r documentation /app/data/
cp VERSION /app/data/
cp -r storage /app/data/ 2>/dev/null || mkdir -p /app/data/storage/sessions

# 6. Set up .env file
cp .env.production.sbvu /app/data/.env

# 7. Verify .env has correct credentials
cat /app/data/.env | head -20

# 8. Set permissions
chmod 600 /app/data/.env
chmod 777 /app/data/storage/sessions

# 9. Run migrations
cd /app/data
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/013_create_new_lookup_tables.sql

mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/014_update_surgeries_with_specialties.sql

# 10. Optional: Seed data
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/seeders/MasterDataSeeder.sql

# 11. Clean up
rm -rf /tmp/acute-pain-service

# 12. Verify version
cat /app/data/VERSION
# Should show: 1.2.0

# 13. Test
curl -I https://aps.sbvu.ac.in
```

---

## 🔍 Why the Original URL Failed

**The URL:**
```
https://github.com/drjagan/acute-pain-service/archive/refs/heads/aps.sbvu.ac.in.zip
```

**Failed because:**
- Branch name contains dots: `aps.sbvu.ac.in`
- GitHub's archive system has issues with dots in branch names
- The URL is technically correct, but GitHub returns 404 for branches with dots

**Workarounds:**
- Use `git clone` (Method 1) ✅ Best
- Use `/tarball/` endpoint (Method 2) ✅ Good
- Use GitHub API (Method 3) ✅ Good
- Manual download (Method 4) ⚠️ Last resort

---

## 📋 Quick Comparison

| Method | Speed | Reliability | Requires Git | Best For |
|--------|-------|-------------|--------------|----------|
| Git Clone | ⚡ Fast | ⭐⭐⭐⭐⭐ | Yes | Most users |
| Tarball | ⚡ Fast | ⭐⭐⭐⭐ | No | No git on server |
| API | ⚡ Fast | ⭐⭐⭐⭐ | No | API users |
| Manual | 🐌 Slow | ⭐⭐⭐ | No | Last resort |

---

## ✅ Verification After Download

**Make sure you have these files:**

```bash
# Check for production-specific files
ls -la .env.production.sbvu
# Should exist

ls -la CLOUDRON_DEPLOYMENT_GUIDE.md
# Should exist

ls -la database-setup-sbvu.sql
# Should exist

# Check version
cat VERSION
# Should show: 1.2.0
```

If all these files exist, you have the correct production branch! ✅

---

## 🎯 Summary

**Problem:** GitHub archive URLs don't work with branch names containing dots.

**Solution:** Use `git clone` method:
```bash
git clone --branch aps.sbvu.ac.in --depth 1 https://github.com/drjagan/acute-pain-service.git
```

**This gets you:**
- ✅ All production files
- ✅ `.env.production.sbvu` with credentials
- ✅ All deployment guides
- ✅ Ready to copy to `/app/data`

---

**Date:** January 24, 2026  
**For:** aps.sbvu.ac.in Production Deployment  
**Issue:** GitHub archive URL format with dots in branch names  
**Solution:** Use git clone method
