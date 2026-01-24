# Cloudron Directory Structure Explained

**Important Clarification for aps.sbvu.ac.in Deployment**

---

## 🗂️ Directory Structure Confusion

You're seeing references to both `/app/code` and `/app/data` in the deployment commands. Let me explain:

### The Issue

**The deployment commands incorrectly reference `/app/code`**, but your actual Cloudron setup uses **`/app/data`** as the application root.

This is why you're getting:
```
fatal: not a git repository (or any of the parent directories): .git
```

---

## ✅ CORRECT Cloudron Structure for SBVU

### Your Actual Directory Layout

```
/app/data/                    ← YOUR APPLICATION ROOT (Web accessible)
├── public/                   ← Web root (Apache DocumentRoot)
│   ├── index.php
│   ├── .htaccess
│   ├── assets/
│   ├── uploads/
│   └── exports/
├── src/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   └── Helpers/
├── config/
│   ├── config.php
│   └── masterdata.php
├── .env                      ← Production credentials
├── VERSION
├── logs/
└── storage/
```

### Why `/app/code` Doesn't Exist

In a **LAMP Cloudron app**, the application is deployed directly to `/app/data/`, **NOT** `/app/code/`.

The `/app/code` reference was incorrect in the deployment documentation.

---

## 🚫 Why Git Doesn't Work (Currently)

When you deployed via Cloudron, the files were uploaded as a **static package**, NOT cloned from git.

**Current state:**
- ✅ Files are in `/app/data/`
- ❌ No `.git` directory exists
- ❌ Git history not preserved
- ❌ Can't use `git pull` to update

---

## 🎯 Two Deployment Strategies

You have two options for managing updates:

---

## Option 1: Initialize Git in Production (Recommended)

This allows you to use `git pull` for updates.

### Step 1: Initialize Git Repository

SSH into your Cloudron server and run:

```bash
cd /app/data

# Initialize git repository
git init

# Add GitHub remote
git remote add origin https://github.com/drjagan/acute-pain-service.git

# Fetch all branches
git fetch origin

# Check out the production branch
git checkout -b aps.sbvu.ac.in origin/aps.sbvu.ac.in

# Verify
git branch --show-current
# Should output: aps.sbvu.ac.in
```

### Step 2: Handle Local Changes

Since you already have files in `/app/data`, git will complain about conflicts. Here's how to handle it:

```bash
cd /app/data

# Stash your local .env file (has production credentials)
cp .env .env.production.backup

# Force reset to match GitHub (CAREFUL!)
git reset --hard origin/aps.sbvu.ac.in

# Restore your .env file
cp .env.production.backup .env

# Verify your .env has correct credentials
cat .env | grep DB_HOST
```

### Step 3: Future Updates

Now you can update using git:

```bash
cd /app/data

# Backup .env first (always!)
cp .env .env.backup

# Fetch latest changes
git fetch origin

# Pull updates
git pull origin aps.sbvu.ac.in

# Restore .env if it was overwritten
cp .env.backup .env

# Run any new migrations
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/NEW_MIGRATION.sql
```

### Important Notes for Git Method

**⚠️ WARNING:**
- `.env` file might get overwritten during `git pull`
- Always backup `.env` before pulling
- The `aps.sbvu.ac.in` branch has `.env.production.sbvu` tracked (exception)
- After pull, verify your database credentials are intact

**What to .gitignore on server:**
- `storage/sessions/*` (local session files)
- `logs/*.log` (local logs)
- `public/uploads/*` (uploaded files)
- `public/exports/*` (generated exports)

---

## Option 2: Manual File Upload (Simpler, No Git)

This is simpler and safer for production.

### How It Works

1. **Download the release package** from GitHub
2. **Extract locally**
3. **Upload to server** via SFTP/SCP
4. **Preserve .env file** (don't overwrite!)
5. **Run migrations** if needed

### Step-by-Step Process

#### On Your Local Machine:

```bash
# Download the release
wget https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.2.0.tar.gz

# Extract
tar -xzf v1.2.0.tar.gz
cd acute-pain-service-1.2.0

# Remove .env.example (don't upload this)
rm .env
rm .env.example
```

#### Upload to Server:

**Using SCP:**
```bash
# Upload all files EXCEPT .env
scp -r ./* cloudron@aps.sbvu.ac.in:/app/data/
```

**Using SFTP:**
```bash
sftp cloudron@aps.sbvu.ac.in

sftp> cd /app/data
sftp> put -r src/
sftp> put -r public/
sftp> put -r config/
sftp> put -r documentation/
sftp> put VERSION
sftp> quit
```

**Using Cloudron File Manager:**
1. Go to Cloudron dashboard
2. Open your app → "File Manager"
3. Navigate to `/app/data`
4. Upload folders one by one

#### On the Server:

```bash
cd /app/data

# Verify .env wasn't overwritten
cat .env | grep DB_HOST
# Should show: DB_HOST=mysql

# Run new migrations (if any)
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/013_create_new_lookup_tables.sql

mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/014_update_surgeries_with_specialties.sql

# Optional: Seed data
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/seeders/MasterDataSeeder.sql

# Verify version
cat VERSION
# Should show: 1.2.0
```

---

## 📊 Comparison: Git vs Manual

| Feature | Git Method | Manual Method |
|---------|-----------|---------------|
| **Update Speed** | Fast (`git pull`) | Slower (upload files) |
| **Risk of Error** | Medium (might overwrite .env) | Low (controlled upload) |
| **Setup Complexity** | Complex (one-time) | Simple (always) |
| **Version Control** | ✅ Full history | ❌ No history |
| **Rollback** | Easy (`git reset`) | Manual (restore backup) |
| **Best For** | Frequent updates | Occasional updates |

---

## 🎯 My Recommendation

### For Your Current Deployment: Use Option 2 (Manual)

**Why:**
1. **Safer**: No risk of overwriting production .env
2. **Simpler**: No git setup needed
3. **Clear**: You control exactly what gets uploaded
4. **Current state**: You don't have git initialized yet

### For Future (After v1.2.0 is stable): Consider Option 1 (Git)

**When:**
- After v1.2.0 runs successfully for a week
- When you're comfortable with git operations
- When you want faster deployment cycles

---

## 🚀 Immediate Action Plan for v1.2.0 Deployment

### Don't use the git commands! Instead:

```bash
# 1. SSH into server
ssh cloudron@aps.sbvu.ac.in
# Or: ssh root@45659acf-49d3-4ade-9a80-984c72816b55

# 2. Navigate to app directory
cd /app/data

# 3. Backup current state
tar -czf /tmp/aps_backup_$(date +%Y%m%d).tar.gz .
cp .env .env.backup

# 4. Download v1.2.0 directly from GitHub
wget https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.2.0.tar.gz -O /tmp/v1.2.0.tar.gz

# 5. Extract to temporary location
cd /tmp
tar -xzf v1.2.0.tar.gz

# 6. Copy new files (preserving .env)
cd /tmp/acute-pain-service-1.2.0
cp -r src /app/data/
cp -r config /app/data/
cp -r documentation /app/data/
cp VERSION /app/data/

# 7. Verify .env is intact
cat /app/data/.env | grep DB_HOST
# Should show: DB_HOST=mysql

# 8. Run migrations
cd /app/data
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/013_create_new_lookup_tables.sql

mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/migrations/014_update_surgeries_with_specialties.sql

# 9. Seed data (optional)
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < src/Database/seeders/MasterDataSeeder.sql

# 10. Clean up
rm -rf /tmp/acute-pain-service-1.2.0
rm /tmp/v1.2.0.tar.gz

# 11. Verify version
cat /app/data/VERSION
# Should show: 1.2.0

# 12. Test the application
curl -I https://aps.sbvu.ac.in
```

---

## 📝 Summary of the Confusion

**The Error:**
```
root@45659acf-49d3-4ade-9a80-984c72816b55:/app/code# git fetch origin
fatal: not a git repository (or any of the parent directories): .git
```

**Why it happened:**
1. Deployment commands said `/app/code` (wrong path)
2. Your actual path is `/app/data` (correct path)
3. No git repository was initialized during deployment
4. Files were uploaded as static package, not cloned via git

**The Solution:**
- **Immediate**: Use manual deployment (download + upload method)
- **Future**: Optionally set up git in `/app/data` for easier updates

---

## 🔗 Updated Deployment Commands

See the new file: `DEPLOYMENT_MANUAL_v1.2.0.md` (coming next)

This will have the correct commands using:
- ✅ Correct path: `/app/data` (not `/app/code`)
- ✅ No git required
- ✅ Safe .env handling
- ✅ Step-by-step verification

---

**Key Takeaway:**

🎯 **Your application is in `/app/data/`, NOT `/app/code/`**

All deployment commands should use `/app/data` as the working directory.

**Next Steps:**
1. I'll create corrected deployment commands without git
2. You can deploy v1.2.0 using the manual method
3. After successful deployment, optionally set up git for future updates

---

**Questions about this structure? Ask before proceeding with deployment!**
