# 🚀 Quick Migration Reference - v1.2.0

**For:** aps.sbvu.ac.in (Cloudron Production)  
**Database:** a916f81cc97ef00e  
**Update Time:** ~5 minutes

---

## ⚡ Quick Commands (Copy & Paste)

### 1️⃣ Backup Database (REQUIRED)

```bash
cd /app/data
mysqldump -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2️⃣ Run Migration 013 (New Tables)

```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  < src/Database/migrations/013_create_new_lookup_tables.sql
```

### 3️⃣ Run Migration 014 (Update Tables)

```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  < src/Database/migrations/014_update_surgeries_with_specialties.sql
```

### 4️⃣ Seed Master Data (Recommended)

```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  < src/Database/seeders/MasterDataSeeder.sql
```

### 5️⃣ Verify Success

```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  -e "SHOW TABLES LIKE 'lookup_%';"
```

**Expected:** 9 tables (including 4 new ones)

---

## 📋 PhpMyAdmin Steps

### Access PhpMyAdmin
1. Open PhpMyAdmin on Cloudron
2. Select database: `a916f81cc97ef00e`

### Import Files (In Order)
1. **Import** → Choose File → `src/Database/migrations/013_create_new_lookup_tables.sql` → **Go**
2. **Import** → Choose File → `src/Database/migrations/014_update_surgeries_with_specialties.sql` → **Go**
3. **Import** → Choose File → `src/Database/seeders/MasterDataSeeder.sql` → **Go**

---

## ✅ Test After Migration

Visit: **https://aps.sbvu.ac.in**

1. Login
2. Go to **Settings** → **Master Data**
3. Should see **9 cards** (Specialties, Catheter Indications, etc.)
4. Click "Specialties" → Should show 15 entries

---

## 🔄 Rollback (If Needed)

```bash
mysql -h mysql -u a916f81cc97ef00e \
  -p'33050ba714a937bf69970570779e802c33b9faa11e4864d4' \
  a916f81cc97ef00e \
  < backup_YYYYMMDD_HHMMSS.sql
```

---

## 📊 What Gets Created

### New Tables (4)
- `lookup_catheter_indications` (14 rows)
- `lookup_removal_indications` (10 rows)
- `lookup_sentinel_events` (8 rows)
- `lookup_specialties` (15 rows)

### Updated Tables (5)
- `lookup_surgeries` (+specialty_id, sort_order, deleted_at)
- `lookup_drugs` (+sort_order, deleted_at)
- `lookup_adjuvants` (+sort_order, deleted_at)
- `lookup_comorbidities` (+sort_order, deleted_at)
- `lookup_complications` → renamed to `lookup_red_flags`

---

**Full Guide:** See `INSTALL.md` for detailed instructions and troubleshooting.
