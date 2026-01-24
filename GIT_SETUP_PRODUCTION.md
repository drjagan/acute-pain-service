# Setting Up Git in Production (Optional)

**For aps.sbvu.ac.in Cloudron Deployment**

This guide shows you how to initialize git in `/app/data` so you can use `git pull` for future updates.

---

## ⚠️ When to Use This

**Use git setup if:**
- ✅ You want faster deployments in the future
- ✅ You're comfortable with git commands
- ✅ You deploy updates frequently (monthly/weekly)
- ✅ v1.2.0 is already running successfully

**DON'T use git if:**
- ❌ You're deploying v1.2.0 for the first time → Use `DEPLOYMENT_MANUAL_v1.2.0.md` instead
- ❌ You're not familiar with git
- ❌ You deploy updates rarely (once per year)

---

## 🎯 What This Achieves

**Before (Manual):**
```bash
# Download → Extract → Upload → Copy files
# Takes: 10-15 minutes
```

**After (With Git):**
```bash
git pull origin aps.sbvu.ac.in
# Takes: 30 seconds
```

---

## 📋 Prerequisites

- ✅ v1.2.0 already deployed and working
- ✅ SSH access to aps.sbvu.ac.in
- ✅ `.env` file backed up
- ✅ Database backed up

---

## 🚀 Setup Steps

### Step 1: SSH into Server

```bash
ssh cloudron@aps.sbvu.ac.in
# OR
ssh root@45659acf-49d3-4ade-9a80-984c72816b55
```

---

### Step 2: Navigate to Application Directory

```bash
cd /app/data
pwd
# Output: /app/data
```

---

### Step 3: Backup Current State

```bash
# Backup .env file (CRITICAL!)
cp .env .env.production.backup

# Backup database
mysqldump -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e > /tmp/db_backup_before_git_$(date +%Y%m%d).sql

# Backup application files
tar -czf /tmp/app_backup_before_git_$(date +%Y%m%d).tar.gz /app/data

echo "Backups created in /tmp/"
ls -lh /tmp/*before_git*
```

---

### Step 4: Initialize Git Repository

```bash
cd /app/data

# Initialize git
git init

# Output: Initialized empty Git repository in /app/data/.git/
```

---

### Step 5: Add GitHub Remote

```bash
cd /app/data

# Add remote
git remote add origin https://github.com/drjagan/acute-pain-service.git

# Verify remote
git remote -v
# Output:
# origin  https://github.com/drjagan/acute-pain-service.git (fetch)
# origin  https://github.com/drjagan/acute-pain-service.git (push)
```

---

### Step 6: Configure Git (Important!)

```bash
cd /app/data

# Set git to not track certain files
cat > .gitignore << 'EOF'
# Local files to never overwrite
.env
.env.production.backup

# Session files
storage/sessions/*
!storage/sessions/.gitkeep

# Logs
logs/*.log

# Uploaded files (user data)
public/uploads/*
!public/uploads/.gitkeep

# Generated exports
public/exports/*
!public/exports/.gitkeep

# Backups
backups/

# Temporary files
/tmp/
*.tmp
EOF

echo ".gitignore created"
cat .gitignore
```

---

### Step 7: Fetch from GitHub

```bash
cd /app/data

# Fetch all branches
git fetch origin

# Output:
# remote: Enumerating objects: ...
# remote: Counting objects: ...
# Receiving objects: 100% ...
```

---

### Step 8: Checkout Production Branch

```bash
cd /app/data

# Create and checkout the production branch
git checkout -b aps.sbvu.ac.in origin/aps.sbvu.ac.in

# Output:
# Branch 'aps.sbvu.ac.in' set up to track remote branch 'aps.sbvu.ac.in' from 'origin'.
# Switched to a new branch 'aps.sbvu.ac.in'
```

---

### Step 9: Handle Local Changes

**⚠️ CRITICAL STEP:** Git will detect that your local files don't match GitHub.

```bash
cd /app/data

# Check git status
git status

# You'll see:
# Your branch is up to date with 'origin/aps.sbvu.ac.in'.
# Changes not staged for commit:
#   modified: ...many files...
# Untracked files:
#   .env.production.backup
#   backups/
#   logs/
```

**Two options:**

#### Option A: Reset to match GitHub (Recommended for fresh git setup)

```bash
cd /app/data

# IMPORTANT: Save .env first!
cp .env .env.saved

# Reset to match GitHub
git reset --hard origin/aps.sbvu.ac.in

# Restore .env
cp .env.saved .env

# Verify .env
cat .env | grep DB_HOST
# Should show: DB_HOST=mysql
```

#### Option B: Keep local changes (Advanced)

```bash
cd /app/data

# Stage all local changes
git add .

# Create a commit with local state
git commit -m "Local production state"

# Rebase on top of GitHub
git pull --rebase origin aps.sbvu.ac.in

# Resolve conflicts manually if any
```

---

### Step 10: Verify Git Setup

```bash
cd /app/data

# Check current branch
git branch --show-current
# Output: aps.sbvu.ac.in

# Check git status
git status
# Output: On branch aps.sbvu.ac.in
# Your branch is up to date with 'origin/aps.sbvu.ac.in'.

# Verify .env is intact
cat .env | grep DB_HOST
# Should show: DB_HOST=mysql

# Verify application works
curl -I https://aps.sbvu.ac.in
# Should show: HTTP/1.1 200 OK
```

---

## 🔄 Using Git for Future Updates

### Update Workflow (Every Time)

```bash
# 1. SSH into server
ssh cloudron@aps.sbvu.ac.in

# 2. Navigate to app
cd /app/data

# 3. Backup .env (ALWAYS!)
cp .env .env.backup

# 4. Check current version
cat VERSION

# 5. Fetch latest changes
git fetch origin

# 6. Check what will change
git log HEAD..origin/aps.sbvu.ac.in --oneline

# 7. Pull updates
git pull origin aps.sbvu.ac.in

# 8. Verify .env wasn't overwritten
cat .env | grep DB_HOST
# If wrong, restore: cp .env.backup .env

# 9. Run any new migrations (check release notes)
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/NEW_MIGRATION.sql

# 10. Verify new version
cat VERSION

# 11. Test application
curl -I https://aps.sbvu.ac.in
```

---

## ⚠️ Important Warnings

### 1. .env File Protection

**CRITICAL:** The `.env` file can be overwritten by `git pull`!

**Solution:** Always backup before pulling:
```bash
cp .env .env.backup
git pull origin aps.sbvu.ac.in
# Check if .env was changed:
diff .env .env.backup
# If different, restore:
cp .env.backup .env
```

### 2. The aps.sbvu.ac.in Branch Exception

The `aps.sbvu.ac.in` branch has a special `.gitignore` that ALLOWS `.env.production.sbvu` to be tracked.

**This means:**
- ✅ Production credentials are in the branch
- ⚠️ `git pull` might overwrite your .env
- ✅ But it will have the correct credentials
- ⚠️ UNLESS you made local changes to .env

**Best Practice:**
```bash
# Before pull: Note any custom .env settings
diff .env .env.production.sbvu

# After pull: Re-apply custom settings if needed
```

### 3. User-Generated Content

**Never affected by git:**
- `public/uploads/*` (patient files)
- `public/exports/*` (generated CSVs)
- `logs/*.log` (log files)
- `storage/sessions/*` (session data)

These are in `.gitignore`, so git ignores them.

---

## 🔄 Rollback with Git

### If update breaks something:

```bash
cd /app/data

# Check commit history
git log --oneline -10

# Rollback to previous commit
git reset --hard HEAD~1

# Or rollback to specific commit
git reset --hard <COMMIT_HASH>

# Restore .env
cp .env.backup .env

# Restart application
# Via Cloudron dashboard
```

---

## 🎯 Git vs Manual Comparison

### Future Update: v1.3.0

**With Git (30 seconds):**
```bash
cd /app/data
cp .env .env.backup
git pull origin aps.sbvu.ac.in
# Run migrations
# Test
```

**Without Git (10-15 minutes):**
```bash
# Download release
wget https://github.com/.../v1.3.0.tar.gz
# Extract
tar -xzf v1.3.0.tar.gz
# Copy src/
# Copy config/
# Copy documentation/
# Copy VERSION
# Run migrations
# Test
```

---

## ❓ FAQs

### Q: Will git overwrite my .env file?

**A:** Possibly. The `aps.sbvu.ac.in` branch tracks `.env.production.sbvu`.

**Solution:** Always backup .env before pulling, and restore if needed.

---

### Q: What if I made local changes to code?

**A:** Git will conflict on pull.

**Solution:** 
```bash
# Commit local changes first
git add .
git commit -m "Local production changes"
git pull origin aps.sbvu.ac.in
# Resolve conflicts if any
```

---

### Q: Can I switch branches?

**A:** Not recommended in production.

**Why:** The `aps.sbvu.ac.in` branch has production-specific files.

**Stick to:** `git pull origin aps.sbvu.ac.in` only.

---

### Q: What if git pull fails?

**A:** 
```bash
# Check status
git status

# If conflicts:
git merge --abort
git reset --hard origin/aps.sbvu.ac.in
cp .env.backup .env

# Or use manual deployment instead
```

---

## ✅ Verification Checklist

After git setup:

- [ ] Git initialized in /app/data
- [ ] Remote "origin" added
- [ ] Branch aps.sbvu.ac.in checked out
- [ ] .gitignore created
- [ ] .env file intact and working
- [ ] Application still accessible
- [ ] Can run `git pull` successfully
- [ ] .env backup procedure tested
- [ ] Know how to rollback if needed

---

## 📝 Summary

**You now have git set up in production!**

**For future updates:**
1. Backup .env
2. `git pull origin aps.sbvu.ac.in`
3. Run migrations
4. Test

**If you prefer manual deployment:**
- That's perfectly fine!
- Use `DEPLOYMENT_MANUAL_v1.2.0.md` for future updates
- Just change the version number

---

**Git setup complete!** 🎉

Now you can use `git pull` for faster deployments in the future.

**Remember:** Always backup .env before pulling!

---

**Setup Date:** January 24, 2026  
**Application Path:** /app/data  
**Branch:** aps.sbvu.ac.in  
**Remote:** https://github.com/drjagan/acute-pain-service.git
