# Correct Master Data URLs

## ❌ The Issue

You're accessing URLs with **British spelling** but the system uses **American spelling**:

**What you tried (WRONG):**
```
https://aps.sbvu.ac.in/masterdata/specialities  ❌ (404 Error)
```

**What you should use (CORRECT):**
```
https://aps.sbvu.ac.in/masterdata/specialties   ✅ (Works!)
```

Notice: `specialit**i**es` (British) vs `specialt**i**es` (American)

---

## ✅ Correct Master Data URLs

### Main Dashboard
```
https://aps.sbvu.ac.in/masterdata
https://aps.sbvu.ac.in/masterdata/index
```

### Individual Master Data Types

| Type | Correct URL | British Spelling (DON'T USE) |
|------|------------|------------------------------|
| **Specialties** | `/masterdata/list/specialties` | ~~specialities~~ ❌ |
| Catheter Indications | `/masterdata/list/catheter_indications` | - |
| Removal Indications | `/masterdata/list/removal_indications` | - |
| Sentinel Events | `/masterdata/list/sentinel_events` | - |
| Surgeries | `/masterdata/list/surgeries` | - |
| Drugs | `/masterdata/list/drugs` | - |
| Adjuvants | `/masterdata/list/adjuvants` | - |
| Comorbidities | `/masterdata/list/comorbidities` | - |
| Red Flags | `/masterdata/list/red_flags` | - |

---

## 🎯 How to Access

### Method 1: Via Settings Page (Recommended)

1. Login to https://aps.sbvu.ac.in
2. Go to **Settings** (in navigation)
3. Find the **Master Data** section
4. Click **"Configure"** next to any data type
5. The correct URL will be used automatically ✅

### Method 2: Via Master Data Dashboard

1. Go to https://aps.sbvu.ac.in/masterdata
2. Click on any data type card
3. The correct URL will be used automatically ✅

### Method 3: Direct URL (Use American Spelling!)

Type the URL manually, but use **American spelling**:
```
https://aps.sbvu.ac.in/masterdata/list/specialties
                                          ^^^ American spelling
```

---

## 📋 Complete URL List

Copy-paste these URLs (all use American spelling):

```
# Main Dashboard
https://aps.sbvu.ac.in/masterdata

# All Master Data Types (list view)
https://aps.sbvu.ac.in/masterdata/list/catheter_indications
https://aps.sbvu.ac.in/masterdata/list/removal_indications
https://aps.sbvu.ac.in/masterdata/list/sentinel_events
https://aps.sbvu.ac.in/masterdata/list/specialties           ← American spelling!
https://aps.sbvu.ac.in/masterdata/list/surgeries
https://aps.sbvu.ac.in/masterdata/list/drugs
https://aps.sbvu.ac.in/masterdata/list/adjuvants
https://aps.sbvu.ac.in/masterdata/list/comorbidities
https://aps.sbvu.ac.in/masterdata/list/red_flags

# Create New (example)
https://aps.sbvu.ac.in/masterdata/create/specialties         ← American spelling!
https://aps.sbvu.ac.in/masterdata/create/catheter_indications

# Edit (replace {id} with actual ID)
https://aps.sbvu.ac.in/masterdata/edit/specialties/{id}
https://aps.sbvu.ac.in/masterdata/edit/catheter_indications/{id}
```

---

## 🔍 Why American Spelling?

The codebase uses **American English** throughout:

**In `config/masterdata.php`:**
```php
'specialties' => [          // ← American spelling
    'label' => 'Specialties',
    'table' => 'lookup_specialties',
    ...
]
```

**In database:**
```sql
CREATE TABLE lookup_specialties ...  -- American spelling
```

**In models:**
```php
class LookupSpecialty ...  // American spelling
```

Everything in the system uses `specialties` (American), not `specialities` (British).

---

## ✅ Test the Correct URLs

Run these commands on your server to verify:

```bash
# Test main dashboard
curl -I https://aps.sbvu.ac.in/masterdata
# Should return: HTTP 200 or HTTP 302 (redirect to login)

# Test specialties (American spelling)
curl -I https://aps.sbvu.ac.in/masterdata/list/specialties
# Should return: HTTP 200 or HTTP 302

# Test with British spelling (will fail!)
curl -I https://aps.sbvu.ac.in/masterdata/list/specialities
# Should return: HTTP 404 (not found)
```

---

## 📝 Browser Test

1. **Go to Settings page:**
   ```
   https://aps.sbvu.ac.in/settings
   ```

2. **Find "Master Data" section** (yellow/warning color)

3. **Click "Manage All"** - Should open Master Data dashboard ✅

4. **Click "Configure"** next to "Specialties & Surgeries" - Should open specialties list ✅

All links on the Settings page use the correct American spelling.

---

## 🎯 Summary

**Problem:** Using British spelling `specialities` in URL  
**Solution:** Use American spelling `specialties` in URL  
**Best way:** Use links from Settings page or Master Data dashboard (they use correct spelling automatically)

---

## 🔧 If You Really Want British Spelling URLs

If you prefer British spelling, you would need to:

1. Add an alias in `config/masterdata.php`:
   ```php
   'specialities' => [  // British spelling alias
       'alias_for' => 'specialties',  // Points to American spelling
       ...
   ]
   ```

2. Update the MasterDataController to handle aliases

**But it's easier to just use the American spelling since that's what the code uses!** ✅

---

**Use `/masterdata/list/specialties` (American) instead of `/masterdata/list/specialities` (British)** 🎯
