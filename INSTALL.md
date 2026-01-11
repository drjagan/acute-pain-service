# Installation Guide - Quick Start

**Version:** 1.0.0  
**Last Updated:** January 11, 2026

---

## 🚀 Quick Installation (5 Minutes)

### Prerequisites

Before you begin, ensure you have:
- ✅ PHP 8.1 or higher
- ✅ MySQL 8.0+ or MariaDB 10.5+
- ✅ Apache or Nginx web server
- ✅ Required PHP extensions: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `json`, `curl`

---

## Method 1: Installation Wizard (Recommended)

### Step 1: Download & Extract

Extract the application to your web server directory:
```bash
cd /path/to/your/webroot
# Extract the application here
```

### Step 2: Set Permissions

```bash
chmod -R 755 public/
chmod -R 777 config/
chmod -R 777 logs/
chmod -R 777 public/uploads/
chmod -R 777 public/exports/
```

### Step 3: Open Installation Wizard

Navigate to the installation wizard in your browser:
```
http://yourdomain.com/install/
```

### Step 4: Follow the Wizard

The wizard will guide you through 5 steps:

**Step 1: Requirements Check**
- Automatically checks PHP version
- Validates required extensions
- Checks directory permissions

**Step 2: Database Configuration**
- Enter MySQL/MariaDB credentials
- Option to create database automatically
- Tests connection before proceeding

**Step 3: Create Tables**
- Creates all 11 database tables
- Populates lookup tables
- Creates test users

**Step 4: Admin Account**
- Create your administrator account
- Set username and password
- Configure email address

**Step 5: Complete**
- Shows installation summary
- Provides next steps
- Links to documentation

### Step 5: Delete Install Folder

**IMPORTANT:** For security, delete the install folder:
```bash
rm -rf install/
```

### Step 6: Login

Navigate to:
```
http://yourdomain.com/public/
```

Login with the admin credentials you created in Step 4.

---

## Method 2: Manual Installation

### 1. Create Database

```sql
CREATE DATABASE aps_database 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### 2. Configure Application

Copy and edit the configuration file:
```bash
cp config/config.example.php config/config.php
nano config/config.php
```

Update database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'aps_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 3. Import Database Schema

Run all migration files in order:
```bash
cd src/Database/migrations
for file in *.sql; do
    mysql -u username -p aps_database < "$file"
done
```

### 4. Import Seed Data

```bash
cd ../seeds
for file in *.sql; do
    mysql -u username -p aps_database < "$file"
done
```

### 5. Create Admin User

Use the pre-seeded admin account:
- Username: `admin`
- Password: `admin123`

**Change this password immediately after first login!**

### 6. Set Permissions

```bash
chmod -R 755 public/
chmod -R 777 config/
chmod -R 777 logs/
chmod -R 777 public/uploads/
chmod -R 777 public/exports/
```

### 7. Configure Web Server

#### Apache (.htaccess already included)

Point DocumentRoot to `public/` directory:
```apache
DocumentRoot "/path/to/acute-pain-service/public"
```

#### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/acute-pain-service/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 8. Start Application

Navigate to: `http://yourdomain.com/`

---

## Testing the Installation

### 1. Login with Test Accounts

All test accounts have password: `admin123`

| Username | Role | Access Level |
|----------|------|--------------|
| `admin` | Administrator | Full access |
| `dr.sharma` | Attending | Clinical + patients |
| `dr.patel` | Resident | Clinical data entry |
| `nurse.kumar` | Nurse | Limited clinical |

### 2. Test Core Features

- [ ] **Dashboard** - Verify statistics display
- [ ] **Patient Registration** - Create a test patient
- [ ] **Catheter Insertion** - Insert a catheter for the patient
- [ ] **Drug Regime** - Record a drug regime
- [ ] **Functional Outcomes** - Record an assessment
- [ ] **Catheter Removal** - Document removal
- [ ] **Reports** - Generate individual and consolidated reports
- [ ] **User Management** - Create/edit/delete users (admin only)

### 3. Test Search Functionality

- [ ] **Patient Search** - Use Select2 dropdown on Reports page
- [ ] Type patient name - should filter results
- [ ] Type hospital number - should filter results
- [ ] Verify latest 5 patients show on dropdown open

---

## Troubleshooting

### Common Issues

**Problem:** "Requirements Not Met" in wizard
```bash
# Install missing PHP extensions
sudo apt-get install php8.1-mysql php8.1-mbstring php8.1-curl
# Restart web server
sudo service apache2 restart
```

**Problem:** "Permission Denied" errors
```bash
# Set correct permissions
chmod -R 777 config/ logs/ public/uploads/ public/exports/
```

**Problem:** "Database Connection Failed"
```bash
# Check MySQL is running
sudo service mysql status
# Test connection manually
mysql -u username -p
```

**Problem:** "404 Not Found" for CSS/JS
```bash
# Ensure files are in correct location
ls -la public/assets/css/
ls -la public/assets/js/
```

**Problem:** "jQuery is not defined"
```bash
# Clear browser cache
# Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
```

---

## Production Deployment

### Security Checklist

- [ ] Change all test user passwords
- [ ] Set `APP_ENV` to `'production'` in config
- [ ] Enable error logging, disable display errors
- [ ] Use strong database passwords
- [ ] Enable HTTPS (SSL certificate)
- [ ] Configure firewall rules
- [ ] Regular database backups
- [ ] Keep PHP and MySQL updated
- [ ] Delete or protect install folder
- [ ] Review file permissions (avoid 777 in production)

### Recommended Production Settings

```php
// config/config.php
define('APP_ENV', 'production');
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . '/php-errors.log');
```

### Performance Optimization

```bash
# Enable PHP OpCache
sudo apt-get install php8.1-opcache

# Configure OpCache in php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

---

## Docker Deployment (Optional)

Create a `Dockerfile`:
```dockerfile
FROM php:8.1-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN a2enmod rewrite

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
```

Create `docker-compose.yml`:
```yaml
version: '3.8'
services:
  web:
    build: .
    ports:
      - "8000:80"
    volumes:
      - ./:/var/www/html
    depends_on:
      - db
  
  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: aps_database
      MYSQL_USER: aps_user
      MYSQL_PASSWORD: aps_password
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

Run with:
```bash
docker-compose up -d
```

---

## Getting Help

### Documentation
- `README.md` - Complete system overview
- `RELEASE_NOTES.md` - Version information
- `docs/SELECT2_PATIENT_COMPONENT.md` - Component guide

### Support
- Check error logs: `logs/app.log`
- Review PHP error log
- Check browser console for JavaScript errors

---

## Next Steps After Installation

1. **Login** with your admin account
2. **Change test user passwords** (Users menu)
3. **Create real users** for your team
4. **Configure system settings** as needed
5. **Import existing patient data** (if applicable)
6. **Train users** on the system
7. **Set up backup schedule**

---

## System Information

**Version:** 1.0.0  
**PHP Version Required:** 8.1+  
**Database:** MySQL 8.0+ / MariaDB 10.5+  
**Installation Time:** ~5 minutes  
**Disk Space:** ~100MB

---

**Installation complete!** 🎉

For detailed usage instructions, refer to `README.md`.

For version information, see `RELEASE_NOTES.md`.
