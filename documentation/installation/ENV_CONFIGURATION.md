# Environment Configuration (.env) Guide

**Version:** 1.1.3+  
**Date:** January 12, 2026  
**Status:** NEW CONFIGURATION SYSTEM

---

## 📋 Overview

Starting from version 1.1.3, the Acute Pain Service application uses **environment-based configuration** following the [12-Factor App](https://12factor.net/config) methodology. This provides better security, flexibility, and deployment practices.

### What Changed?

| Before (≤ 1.1.2) | After (≥ 1.1.3) |
|------------------|-----------------|
| Database credentials in `config/config.php` (hardcoded) | Database credentials in `.env` file (environment variables) |
| Config file committed to git (security risk) | `.env` file gitignored (secure) |
| Same config for all environments | Different `.env` per environment |
| Manual editing required | Installation wizard generates `.env` |

---

## 🎯 Why .env Files?

### Benefits

1. **✅ Security**
   - Sensitive credentials never committed to version control
   - Each server has its own `.env` file
   - Easy to restrict file permissions (chmod 600)

2. **✅ Flexibility**
   - Different settings per environment (dev/staging/production)
   - Easy to change without modifying code
   - Support for multiple deployment scenarios

3. **✅ Best Practice**
   - Industry standard (Laravel, Symfony, Node.js, etc.)
   - Docker and container-friendly
   - CI/CD pipeline compatible

4. **✅ Maintainability**
   - Single source of truth for environment config
   - Clear separation of code and configuration
   - Easy to document and template

---

## 📁 File Structure

```
acute-pain-service/
├── .env                    # YOUR configuration (DO NOT commit)
├── .env.example            # Template file (committed to git)
├── .gitignore              # Ensures .env is not committed
├── config/
│   ├── env-loader.php      # NEW: Loads .env variables
│   ├── database.php        # UPDATED: Reads from .env
│   └── config.php          # UPDATED: Non-sensitive settings only
└── test-env-config.php     # NEW: Test configuration loading
```

---

## 🚀 Quick Start

### Method 1: Installation Wizard (Recommended)

The installation wizard automatically creates your `.env` file:

1. Navigate to: `http://your-server/install/`
2. Complete Step 1: Requirements Check
3. Complete Step 2: Database Configuration
   - Wizard generates `.env` file with your settings
   - Wizard generates `config/config.php` (non-sensitive)
4. Continue with remaining steps

**✅ No manual configuration needed!**

---

### Method 2: Manual Setup

If you prefer manual configuration:

1. **Copy the template:**
   ```bash
   cp .env.example .env
   ```

2. **Edit with your settings:**
   ```bash
   nano .env
   # or
   vi .env
   ```

3. **Update required fields:**
   ```env
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=aps_database
   DB_USER=your_db_user
   DB_PASS=your_secure_password
   ```

4. **Generate APP_KEY:**
   ```bash
   php -r "echo bin2hex(random_bytes(32));"
   ```
   Copy the output to `APP_KEY=` in `.env`

5. **Set secure permissions:**
   ```bash
   chmod 600 .env
   chown www-data:www-data .env  # or your web server user
   ```

---

## 🔧 Configuration Reference

### Required Settings

These MUST be configured for the application to work:

```env
# Database (REQUIRED)
DB_HOST=localhost          # Database server hostname
DB_PORT=3306               # MySQL port
DB_NAME=aps_database       # Database name
DB_USER=root               # Database username
DB_PASS=                   # Database password (use strong password!)
DB_CHARSET=utf8mb4         # Character encoding

# Application (REQUIRED)
APP_ENV=production         # Environment: production, development, staging
APP_NAME=Acute Pain Service
APP_VERSION=1.1.3
APP_KEY=                   # 64-character random hex string (generate with command above)
```

### Session Settings

```env
SESSION_LIFETIME=3600      # Session timeout in seconds (1 hour)
SESSION_NAME=APS_SESSION   # Session cookie name
```

### Security Settings

```env
PASSWORD_MIN_LENGTH=8      # Minimum password length
MAX_LOGIN_ATTEMPTS=5       # Login attempts before lockout
LOGIN_TIMEOUT=900          # Login lockout duration (15 minutes)
```

### Application Behavior

```env
PER_PAGE=20                # Pagination: items per page
LOG_ENABLED=true           # Enable application logging
LOG_LEVEL=INFO             # Log level: DEBUG, INFO, WARNING, ERROR
APP_TIMEZONE=Asia/Kolkata  # Application timezone
```

### File Upload

```env
MAX_UPLOAD_SIZE=5242880    # Max file size in bytes (5MB)
ALLOWED_EXTENSIONS=jpg,jpeg,png,pdf,doc,docx
```

### Email (Optional)

Configure if you need email notifications:

```env
SMTP_ENABLED=false         # Enable/disable email
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls        # tls or ssl
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_FROM_EMAIL=noreply@example.com
SMTP_FROM_NAME=Acute Pain Service
```

---

## 🔒 Security Best Practices

### 1. File Permissions

```bash
# Restrictive permissions (owner read/write only)
chmod 600 .env

# Ensure correct ownership
chown www-data:www-data .env  # Ubuntu/Debian
chown apache:apache .env      # CentOS/RHEL
```

### 2. Never Commit .env

The `.gitignore` file already excludes `.env`, but double-check:

```bash
# Verify .env is ignored
git status

# Should NOT show .env file
# If it does, run:
git rm --cached .env
```

### 3. Use Strong Passwords

```env
# ❌ BAD
DB_PASS=password123

# ✅ GOOD
DB_PASS=X9$mK#2pL8@vR5nQ
```

Generate strong passwords:
```bash
# Random 32-character password
openssl rand -base64 32
```

### 4. Different Keys Per Environment

Each environment (dev/staging/production) should have:
- Different `APP_KEY`
- Different database credentials
- Different SMTP credentials

### 5. Backup Securely

When backing up:
```bash
# Backup .env to secure location (NOT in public directory)
cp .env /secure/backups/aps/.env.backup.$(date +%Y%m%d)

# Set restrictive permissions on backup
chmod 600 /secure/backups/aps/.env.backup.*
```

---

## 🧪 Testing Configuration

### Test Script

Run the provided test script to verify your configuration:

```bash
php test-env-config.php
```

**Expected Output:**
```
===========================================
APS Environment Configuration Test
===========================================

1. Checking .env file...
   ✓ .env file found: /path/to/.env
   File size: 1234 bytes
   Permissions: 0600

2. Loading configuration...
   ✓ Configuration loaded successfully

3. Database Configuration:
   DB_HOST:     localhost
   DB_PORT:     3306
   DB_NAME:     aps_database
   DB_USER:     aps_user
   DB_PASS:     ********
   DB_CHARSET:  utf8mb4

4. Testing database connection...
   ✓ Database connection successful!
   Connected to: aps_database
   MySQL version: 8.0.35
   Tables found: 16

5. Environment Variables Loaded:
   DB_HOST = localhost
   DB_NAME = aps_database
   APP_ENV = production
   APP_NAME = Acute Pain Service
   APP_VERSION = 1.1.3
   SESSION_LIFETIME = 3600

===========================================
Test Complete
===========================================
```

---

## 🚨 Troubleshooting

### Problem: .env file not found

**Symptoms:**
- Application uses default configuration
- Database connection fails
- Test script shows ".env file NOT found"

**Solution:**
```bash
# Check if .env exists
ls -la .env

# If not, create from template
cp .env.example .env
nano .env  # Edit with your settings
```

---

### Problem: Permission denied

**Symptoms:**
- "Failed to write .env file" during installation
- PHP cannot read .env

**Solution:**
```bash
# Check permissions
ls -la .env

# Fix ownership (Ubuntu/Debian)
sudo chown www-data:www-data .env

# Fix permissions
chmod 600 .env

# Verify web server user
ps aux | grep apache
# or
ps aux | grep nginx
```

---

### Problem: Database connection fails

**Symptoms:**
- "Database connection failed" error
- PDO exception about access denied

**Solution:**
1. Verify credentials in `.env`:
   ```bash
   cat .env | grep DB_
   ```

2. Test MySQL connection manually:
   ```bash
   mysql -h localhost -u aps_user -p aps_database
   ```

3. Check user has permissions:
   ```sql
   SHOW GRANTS FOR 'aps_user'@'localhost';
   ```

4. Verify database exists:
   ```sql
   SHOW DATABASES LIKE 'aps_database';
   ```

---

### Problem: Configuration not loading

**Symptoms:**
- Application uses defaults despite .env existing
- Environment variables not set

**Solution:**

1. **Check .env syntax:**
   ```bash
   # Ensure KEY=VALUE format
   # No spaces around =
   # No quotes needed (unless value has spaces)
   
   # ✅ CORRECT
   DB_HOST=localhost
   DB_NAME=aps_database
   
   # ❌ WRONG
   DB_HOST = localhost     # Spaces around =
   DB_NAME='aps_database'  # Unnecessary quotes
   ```

2. **Verify env-loader.php:**
   ```bash
   ls -la config/env-loader.php
   ```

3. **Check PHP error logs:**
   ```bash
   tail -f logs/error.log
   tail -f /var/log/apache2/error.log
   ```

---

## 🔄 Migration from Old Config

If upgrading from version ≤ 1.1.2:

### Step 1: Backup existing config

```bash
cp config/config.php config/config.php.backup
```

### Step 2: Extract database credentials

Open `config/config.php.backup` and note:
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

### Step 3: Create .env file

```bash
cp .env.example .env
nano .env
# Enter the credentials from step 2
```

### Step 4: Test new configuration

```bash
php test-env-config.php
```

### Step 5: Verify application works

Visit your application in browser and test:
- Login works
- Database queries work
- No errors in logs

### Step 6: Remove old config (optional)

Once confirmed working:
```bash
# Keep backup just in case
mv config/config.php.backup config/config.php.old
```

---

## 📚 Additional Resources

### Environment-Specific Configuration

**Development (.env.development):**
```env
APP_ENV=development
APP_DEBUG=true
LOG_LEVEL=DEBUG
SMTP_ENABLED=false
```

**Production (.env.production):**
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=ERROR
SMTP_ENABLED=true
```

**Load specific environment:**
```php
// In code, you can specify environment file
loadEnv(__DIR__, '.env.production');
```

---

### Docker Integration

**docker-compose.yml:**
```yaml
version: '3.8'
services:
  aps-web:
    image: aps:latest
    env_file:
      - .env
    volumes:
      - ./.env:/var/www/html/.env:ro
```

---

### CI/CD Pipeline

**GitHub Actions example:**
```yaml
- name: Create .env file
  run: |
    echo "DB_HOST=${{ secrets.DB_HOST }}" >> .env
    echo "DB_NAME=${{ secrets.DB_NAME }}" >> .env
    echo "DB_USER=${{ secrets.DB_USER }}" >> .env
    echo "DB_PASS=${{ secrets.DB_PASS }}" >> .env
    chmod 600 .env
```

---

## ❓ FAQ

### Q: Can I use environment variables instead of .env file?

**A:** Yes! The system reads from actual environment variables first:

```bash
# Set in shell
export DB_HOST=localhost
export DB_NAME=aps_database

# Or in Apache
SetEnv DB_HOST localhost
SetEnv DB_NAME aps_database

# Or in Nginx
fastcgi_param DB_HOST localhost;
fastcgi_param DB_NAME aps_database;
```

The priority order is:
1. Actual environment variables (highest)
2. `.env` file values
3. Default values (lowest)

---

### Q: What if I want to keep using config.php?

**A:** The old method still works as a fallback. If no `.env` file exists, the system uses defaults or values from `config/config.php`. However, we strongly recommend migrating to `.env` for better security.

---

### Q: Can I add custom variables to .env?

**A:** Yes! Add any custom variables and access them with:

```php
$customValue = env('MY_CUSTOM_VAR', 'default value');
```

---

### Q: How do I rotate credentials?

**A:** Update `.env` file and restart web server:

```bash
nano .env  # Update credentials
sudo systemctl restart apache2  # or nginx
```

No code changes needed!

---

## 📞 Support

If you encounter issues:

1. Run test script: `php test-env-config.php`
2. Check logs: `tail -f logs/app.log`
3. Verify permissions: `ls -la .env`
4. Review this guide's troubleshooting section
5. Check [GitHub Issues](https://github.com/drjagan/acute-pain-service/issues)

---

**Last Updated:** January 12, 2026  
**Version:** 1.1.3+  
**Related Documents:**
- [Installation Guide](INSTALL.md)
- [Deployment Guide](../deployment/DEPLOY.md)
- [Database Setup](../database/README.md)
