# Deployment Guide - Acute Pain Service

Complete guide for deploying the Acute Pain Service application on a production LAMP server.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Quick Installation](#quick-installation)
- [Manual Installation](#manual-installation)
- [Post-Installation](#post-installation)
- [Configuration](#configuration)
- [Security Hardening](#security-hardening)
- [Backup and Maintenance](#backup-and-maintenance)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Server Requirements

- **Operating System**: Ubuntu 20.04/22.04 LTS (or compatible)
- **Web Server**: Apache 2.4+
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **PHP**: 8.3+ (minimum PHP 8.1)
- **RAM**: Minimum 2GB (4GB recommended)
- **Storage**: Minimum 10GB free space
- **Domain**: Optional but recommended

### PHP Extensions Required

- php-mysql
- php-mbstring
- php-xml
- php-bcmath
- php-curl
- php-zip
- php-gd
- php-intl

---

## Quick Installation

### Option 1: Automated Installation Script

The fastest way to deploy on a fresh Ubuntu server:

```bash
# 1. Clone the repository to your server
cd /var/www
sudo git clone https://github.com/drjagan/acute-pain-service.git
cd acute-pain-service

# 2. Checkout the latest stable release
sudo git checkout v1.1.2

# 3. Make installation script executable
sudo chmod +x deployment/scripts/install.sh

# 4. Run installation as root
sudo ./deployment/scripts/install.sh
```

**The script will:**
- Install Apache, MySQL, PHP 8.3
- Create database and user
- Configure Apache virtual host
- Set proper permissions
- Run database migrations
- Create admin user
- Display credentials

**Time**: ~15-20 minutes

---

## Manual Installation

If you prefer manual installation or need custom configuration:

### Step 1: Install LAMP Stack

See [LAMP_INSTALL.md](LAMP_INSTALL.md) for detailed instructions.

**Quick commands:**

```bash
# Update system
sudo apt-get update
sudo apt-get upgrade -y

# Install Apache
sudo apt-get install -y apache2

# Install MySQL
sudo apt-get install -y mysql-server

# Install PHP 8.3
sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update
sudo apt-get install -y php8.3 php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd
```

### Step 2: Create Database

```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE aps_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'aps_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON aps_database.* TO 'aps_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 3: Upload Application Files

```bash
# Create application directory
sudo mkdir -p /var/www/acute-pain-service
cd /var/www/acute-pain-service

# Upload files (use SCP, SFTP, or Git)
# Option A: Via Git
sudo git clone https://github.com/drjagan/acute-pain-service.git .
sudo git checkout v1.1.2

# Option B: Via SCP/SFTP
# Upload the entire application folder to this directory
```

### Step 4: Configure Environment

```bash
# Copy environment template
sudo cp .env.example .env

# Edit configuration
sudo nano .env
```

**Update these values in .env:**

```ini
DB_HOST=localhost
DB_NAME=aps_database
DB_USER=aps_user
DB_PASS=your_secure_password

APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Generate secure key
APP_KEY=your_generated_key_here
```

**Generate APP_KEY:**

```bash
php -r "echo bin2hex(random_bytes(32));"
```

### Step 5: Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/acute-pain-service
sudo chmod -R 755 /var/www/acute-pain-service
sudo chmod 600 /var/www/acute-pain-service/.env
```

### Step 6: Configure Apache

```bash
# Copy virtual host configuration
sudo cp deployment/config/apache-vhost.conf /etc/apache2/sites-available/aps.conf

# Edit domain name
sudo nano /etc/apache2/sites-available/aps.conf

# Enable site and modules
sudo a2ensite aps.conf
sudo a2enmod rewrite
sudo a2enmod headers

# Disable default site
sudo a2dissite 000-default.conf

# Test configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

### Step 7: Run Database Migrations

```bash
cd /var/www/acute-pain-service

# Run v1.1 migrations
php run_migrations_v1.1.php
```

### Step 8: Create Admin User

```bash
# Login to MySQL
mysql -u aps_user -p aps_database

# Create admin user
INSERT INTO users (username, email, password, first_name, last_name, role, status, created_at)
VALUES ('admin', 'admin@localhost', MD5('admin123'), 'System', 'Administrator', 'admin', 'active', NOW());
EXIT;
```

---

## Post-Installation

### 1. Test Installation

Open your browser and navigate to your domain:

```
http://your-domain.com
```

**You should see the login page.**

**Default Credentials:**
- Username: `admin`
- Password: `admin123` (or the password you set)

### 2. Change Admin Password

1. Login as admin
2. Navigate to Users → View All
3. Edit admin user
4. Change password
5. Save

### 3. Create Additional Users

1. Navigate to Users → Create New
2. Fill in user details
3. Assign appropriate role:
   - `admin` - Full system access
   - `attending` - Attending physician
   - `resident` - Resident physician
   - `nurse` - Nursing staff

### 4. Configure SMTP (Optional)

For email notifications:

1. Login as admin
2. Navigate to Settings → SMTP Settings
3. Enter your SMTP server details
4. Test configuration
5. Enable email notifications

---

## Configuration

### Apache Configuration

**Virtual Host Location:**
```
/etc/apache2/sites-available/aps.conf
```

**Important Directives:**

```apache
DocumentRoot /var/www/acute-pain-service/public

<Directory /var/www/acute-pain-service/public>
    AllowOverride All
    Require all granted
</Directory>
```

### PHP Configuration

**PHP.ini Location:**
```
/etc/php/8.3/apache2/php.ini
```

**Recommended Settings:**

```ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
display_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
date.timezone = UTC
```

### MySQL Configuration

**My.cnf Location:**
```
/etc/mysql/mysql.conf.d/mysqld.cnf
```

**Recommended Settings:**

```ini
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
```

---

## Security Hardening

### 1. Enable Firewall

```bash
# Install UFW
sudo apt-get install ufw

# Allow SSH, HTTP, HTTPS
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https

# Enable firewall
sudo ufw enable
```

### 2. Install SSL Certificate

**Using Let's Encrypt (Free):**

```bash
# Install Certbot
sudo apt-get install -y certbot python3-certbot-apache

# Obtain certificate
sudo certbot --apache -d your-domain.com -d www.your-domain.com

# Auto-renewal is set up automatically
```

### 3. Secure MySQL

```bash
sudo mysql_secure_installation
```

Answer yes to:
- Remove anonymous users
- Disallow root login remotely
- Remove test database
- Reload privilege tables

### 4. Disable Directory Listing

Add to Apache config:

```apache
<Directory /var/www/acute-pain-service/public>
    Options -Indexes
</Directory>
```

### 5. Hide PHP Version

Edit `/etc/php/8.3/apache2/php.ini`:

```ini
expose_php = Off
```

### 6. Set Strong File Permissions

```bash
# Application files: read-only for web server
sudo find /var/www/acute-pain-service -type f -exec chmod 644 {} \;
sudo find /var/www/acute-pain-service -type d -exec chmod 755 {} \;

# Writable directories
sudo chmod -R 775 /var/www/acute-pain-service/storage
sudo chmod -R 775 /var/www/acute-pain-service/logs

# Protect sensitive files
sudo chmod 600 /var/www/acute-pain-service/.env
sudo chmod 600 /var/www/acute-pain-service/config/config.php
```

---

## Backup and Maintenance

### Database Backup

**Manual Backup:**

```bash
# Create backup directory
sudo mkdir -p /var/backups/aps

# Backup database
sudo mysqldump -u aps_user -p aps_database > /var/backups/aps/aps_$(date +%Y%m%d_%H%M%S).sql
```

**Automated Daily Backup:**

Create `/usr/local/bin/aps-backup.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/aps"
DB_NAME="aps_database"
DB_USER="aps_user"
DB_PASS="your_password"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/aps_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "aps_*.sql.gz" -mtime +30 -delete
```

Make executable and add to cron:

```bash
sudo chmod +x /usr/local/bin/aps-backup.sh
sudo crontab -e

# Add this line for daily backup at 2 AM
0 2 * * * /usr/local/bin/aps-backup.sh
```

### Application Files Backup

```bash
# Backup application
sudo tar -czf /var/backups/aps/app_$(date +%Y%m%d).tar.gz \
    /var/www/acute-pain-service \
    --exclude=/var/www/acute-pain-service/logs \
    --exclude=/var/www/acute-pain-service/storage/cache
```

### Restore from Backup

```bash
# Restore database
gunzip < /var/backups/aps/aps_20260111.sql.gz | mysql -u aps_user -p aps_database

# Restore application files
sudo tar -xzf /var/backups/aps/app_20260111.tar.gz -C /
```

---

## Troubleshooting

### Issue: 500 Internal Server Error

**Check Apache error log:**
```bash
sudo tail -f /var/log/apache2/aps-error.log
```

**Common causes:**
- Missing .htaccess file
- Incorrect file permissions
- PHP errors

**Solution:**
```bash
# Ensure mod_rewrite is enabled
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check permissions
sudo chown -R www-data:www-data /var/www/acute-pain-service
```

### Issue: Database Connection Failed

**Check credentials in .env:**
```bash
sudo cat /var/www/acute-pain-service/.env | grep DB_
```

**Test database connection:**
```bash
mysql -u aps_user -p -h localhost aps_database
```

**Check MySQL is running:**
```bash
sudo systemctl status mysql
```

### Issue: Blank White Page

**Enable error display temporarily:**

Edit `/var/www/acute-pain-service/config/config.php`:

```php
define('APP_DEBUG', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**Check PHP error log:**
```bash
sudo tail -f /var/log/php8.3-fpm.log
```

### Issue: Permission Denied Errors

```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/acute-pain-service

# Fix permissions
sudo chmod -R 755 /var/www/acute-pain-service
sudo chmod -R 775 /var/www/acute-pain-service/storage
sudo chmod 600 /var/www/acute-pain-service/.env
```

### Issue: Session Errors

**Check session directory:**
```bash
ls -la /var/lib/php/sessions
```

**Fix permissions:**
```bash
sudo chmod 1733 /var/lib/php/sessions
```

---

## Updating the Application

### Update to Latest Version

```bash
# Backup first!
sudo /usr/local/bin/aps-backup.sh

# Navigate to application directory
cd /var/www/acute-pain-service

# Pull latest code
sudo -u www-data git fetch origin
sudo -u www-data git checkout v1.1.2  # or latest version

# Run any new migrations
php run_migrations_v1.1.php

# Clear cache
php -r "opcache_reset();"

# Restart Apache
sudo systemctl restart apache2
```

---

## Support

**Documentation:**
- GitHub Repository: https://github.com/drjagan/acute-pain-service
- Installation Guide: INSTALL.md
- LAMP Setup: LAMP_INSTALL.md

**Issues:**
Report bugs or request features at:
https://github.com/drjagan/acute-pain-service/issues

---

## License

[Your License Here]

---

**Last Updated:** January 12, 2026  
**Version:** 1.1.1
