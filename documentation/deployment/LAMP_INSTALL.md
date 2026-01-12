# LAMP Stack Installation Guide

Complete guide for installing Apache, MySQL, and PHP 8.3 on Ubuntu Server.

## Table of Contents

- [System Requirements](#system-requirements)
- [Preparation](#preparation)
- [Install Apache](#install-apache)
- [Install MySQL](#install-mysql)
- [Install PHP 8.3](#install-php-83)
- [Configure Firewall](#configure-firewall)
- [Verify Installation](#verify-installation)
- [Troubleshooting](#troubleshooting)

---

## System Requirements

- **OS**: Ubuntu 20.04 LTS or Ubuntu 22.04 LTS
- **RAM**: Minimum 1GB (2GB recommended)
- **Storage**: Minimum 10GB free space
- **Root/Sudo Access**: Required
- **Internet Connection**: Required for package installation

---

## Preparation

### Update System Packages

```bash
# Update package index
sudo apt-get update

# Upgrade installed packages
sudo apt-get upgrade -y

# Install essential tools
sudo apt-get install -y software-properties-common curl wget git unzip
```

### Set Timezone

```bash
# List available timezones
timedatectl list-timezones

# Set your timezone (example: Asia/Kolkata)
sudo timedatectl set-timezone Asia/Kolkata

# Verify
timedatectl
```

---

## Install Apache

### Step 1: Install Apache Web Server

```bash
# Install Apache
sudo apt-get install -y apache2

# Enable Apache to start on boot
sudo systemctl enable apache2

# Start Apache
sudo systemctl start apache2

# Check status
sudo systemctl status apache2
```

### Step 2: Verify Apache Installation

Open your browser and visit:
```
http://your-server-ip
```

You should see the Apache2 Ubuntu Default Page.

### Step 3: Configure Apache

```bash
# Enable important modules
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl

# Restart Apache
sudo systemctl restart apache2
```

### Step 4: Test Apache

```bash
# Check Apache version
apache2 -v

# Check loaded modules
apache2ctl -M
```

**Expected output:**
```
Server version: Apache/2.4.XX (Ubuntu)
```

---

## Install MySQL

### Step 1: Install MySQL Server

```bash
# Install MySQL 8.0
sudo apt-get install -y mysql-server

# Enable MySQL to start on boot
sudo systemctl enable mysql

# Start MySQL
sudo systemctl start mysql

# Check status
sudo systemctl status mysql
```

### Step 2: Secure MySQL Installation

```bash
sudo mysql_secure_installation
```

**Follow the prompts:**

1. **VALIDATE PASSWORD COMPONENT**
   - Answer: `y` (yes)
   - Select password strength: `2` (STRONG)

2. **Set root password**
   - Enter a strong password
   - Confirm password

3. **Remove anonymous users**
   - Answer: `y` (yes)

4. **Disallow root login remotely**
   - Answer: `y` (yes)

5. **Remove test database**
   - Answer: `y` (yes)

6. **Reload privilege tables**
   - Answer: `y` (yes)

### Step 3: Test MySQL

```bash
# Login to MySQL
sudo mysql -u root -p

# Check version
mysql> SELECT VERSION();

# Show databases
mysql> SHOW DATABASES;

# Exit
mysql> EXIT;
```

### Step 4: Configure MySQL (Optional)

Edit MySQL configuration:

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

**Recommended settings:**

```ini
[mysqld]
# Performance tuning
max_connections = 200
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M

# Character set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Slow query log (for debugging)
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2
```

Restart MySQL:
```bash
sudo systemctl restart mysql
```

---

## Install PHP 8.3

### Step 1: Add PHP Repository

Ubuntu's default repository might not have PHP 8.3, so we'll add Ondřej Surý's PPA:

```bash
# Add PHP repository
sudo add-apt-repository -y ppa:ondrej/php

# Update package index
sudo apt-get update
```

### Step 2: Install PHP 8.3 and Extensions

```bash
# Install PHP 8.3 with common extensions
sudo apt-get install -y \
    php8.3 \
    php8.3-cli \
    php8.3-common \
    php8.3-mysql \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-bcmath \
    php8.3-curl \
    php8.3-zip \
    php8.3-gd \
    php8.3-intl \
    php8.3-soap \
    php8.3-readline \
    libapache2-mod-php8.3
```

**Extension purposes:**
- `php8.3-mysql` - MySQL database support
- `php8.3-mbstring` - Multibyte string handling
- `php8.3-xml` - XML processing
- `php8.3-bcmath` - Arbitrary precision mathematics
- `php8.3-curl` - HTTP requests
- `php8.3-zip` - ZIP archive handling
- `php8.3-gd` - Image manipulation
- `php8.3-intl` - Internationalization
- `php8.3-soap` - SOAP protocol support

### Step 3: Configure PHP

```bash
# Edit PHP configuration for Apache
sudo nano /etc/php/8.3/apache2/php.ini
```

**Important settings to modify:**

```ini
# Memory and execution
memory_limit = 256M
max_execution_time = 300
max_input_time = 300

# File uploads
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20

# Error handling (production)
display_errors = Off
display_startup_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
log_errors = On
error_log = /var/log/php/error.log

# Date/Time
date.timezone = Asia/Kolkata

# Session
session.save_handler = files
session.save_path = "/var/lib/php/sessions"
session.gc_probability = 1
session.gc_divisor = 1000
session.gc_maxlifetime = 1440

# Security
expose_php = Off
allow_url_fopen = On
allow_url_include = Off
```

### Step 4: Create PHP Error Log Directory

```bash
# Create log directory
sudo mkdir -p /var/log/php

# Set permissions
sudo chown -R www-data:www-data /var/log/php
sudo chmod -R 755 /var/log/php
```

### Step 5: Enable PHP Module in Apache

```bash
# Disable older PHP versions (if any)
sudo a2dismod php7.4 2>/dev/null || true
sudo a2dismod php8.0 2>/dev/null || true
sudo a2dismod php8.1 2>/dev/null || true
sudo a2dismod php8.2 2>/dev/null || true

# Enable PHP 8.3
sudo a2enmod php8.3

# Restart Apache
sudo systemctl restart apache2
```

### Step 6: Test PHP

Create a test PHP file:

```bash
# Create info.php
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/info.php
```

Open in browser:
```
http://your-server-ip/info.php
```

You should see the PHP information page showing PHP 8.3.x

**Important:** Delete the test file after verification:

```bash
sudo rm /var/www/html/info.php
```

### Step 7: Verify PHP Installation

```bash
# Check PHP version
php -v

# Check loaded extensions
php -m

# Check PHP info (CLI)
php -i | grep "PHP Version"
```

**Expected output:**
```
PHP 8.3.x (cli) (built: ...)
Copyright (c) The PHP Group
```

---

## Configure Firewall

### Using UFW (Uncomplicated Firewall)

```bash
# Install UFW
sudo apt-get install -y ufw

# Allow SSH (IMPORTANT: Do this first!)
sudo ufw allow ssh
sudo ufw allow 22/tcp

# Allow HTTP
sudo ufw allow http
sudo ufw allow 80/tcp

# Allow HTTPS
sudo ufw allow https
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status verbose
```

**Expected output:**
```
Status: active

To                         Action      From
--                         ------      ----
22/tcp                     ALLOW       Anywhere
80/tcp                     ALLOW       Anywhere
443/tcp                    ALLOW       Anywhere
```

---

## Verify Installation

### Complete LAMP Stack Test

Create a comprehensive test script:

```bash
sudo nano /var/www/html/test.php
```

**Content:**

```php
<?php
// Test PHP
echo "<h1>LAMP Stack Test</h1>";
echo "<h2>PHP Version: " . PHP_VERSION . "</h2>";

// Test MySQL connection
$mysqli = new mysqli("localhost", "root", "your_password", "mysql");
if ($mysqli->connect_error) {
    echo "<p style='color:red'>MySQL Connection Failed: " . $mysqli->connect_error . "</p>";
} else {
    echo "<p style='color:green'>MySQL Connection Successful!</p>";
    echo "<p>MySQL Version: " . $mysqli->server_info . "</p>";
}
$mysqli->close();

// Test loaded extensions
echo "<h3>Loaded PHP Extensions:</h3>";
echo "<ul>";
$extensions = ['mysqli', 'mbstring', 'xml', 'bcmath', 'curl', 'zip', 'gd'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? "✓" : "✗";
    $color = $loaded ? "green" : "red";
    echo "<li style='color:$color'>$status $ext</li>";
}
echo "</ul>";

// System info
echo "<h3>System Information:</h3>";
echo "<ul>";
echo "<li>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
echo "<li>PHP SAPI: " . php_sapi_name() . "</li>";
echo "</ul>";
?>
```

Visit:
```
http://your-server-ip/test.php
```

**Expected results:**
- ✓ PHP version 8.3.x displayed
- ✓ MySQL connection successful
- ✓ All required extensions loaded
- ✓ Apache server info displayed

**Cleanup:**
```bash
sudo rm /var/www/html/test.php
```

---

## Troubleshooting

### Apache Won't Start

**Check for errors:**
```bash
sudo systemctl status apache2
sudo journalctl -xeu apache2
```

**Common issues:**
- Port 80 already in use
- Configuration syntax errors

**Solutions:**
```bash
# Check if port 80 is in use
sudo netstat -tulpn | grep :80

# Test configuration
sudo apache2ctl configtest

# Check error log
sudo tail -f /var/log/apache2/error.log
```

### MySQL Won't Start

**Check for errors:**
```bash
sudo systemctl status mysql
sudo journalctl -xeu mysql
```

**Check error log:**
```bash
sudo tail -f /var/log/mysql/error.log
```

**Reset MySQL root password:**
```bash
sudo mysql
mysql> ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'new_password';
mysql> FLUSH PRIVILEGES;
mysql> EXIT;
```

### PHP Not Working

**Check if PHP module is loaded:**
```bash
apache2ctl -M | grep php
```

**Expected:** `php_module (shared)`

**If not loaded:**
```bash
sudo a2enmod php8.3
sudo systemctl restart apache2
```

**Check PHP error log:**
```bash
sudo tail -f /var/log/php/error.log
```

### Permission Issues

**Fix Apache permissions:**
```bash
sudo chown -R www-data:www-data /var/www
sudo chmod -R 755 /var/www
```

**Check Apache user:**
```bash
ps aux | grep apache2
```

Should show `www-data` user.

---

## Next Steps

After LAMP stack is installed:

1. **Secure your server:**
   - Configure firewall properly
   - Set up fail2ban
   - Keep system updated

2. **Install SSL certificate:**
   ```bash
   sudo apt-get install certbot python3-certbot-apache
   sudo certbot --apache
   ```

3. **Deploy your application:**
   - Follow [DEPLOY.md](DEPLOY.md) for Acute Pain Service installation

4. **Set up monitoring:**
   - Install monitoring tools
   - Configure log rotation
   - Set up backups

---

## Maintenance Commands

### System Updates

```bash
# Update package lists
sudo apt-get update

# Upgrade packages
sudo apt-get upgrade -y

# Clean up
sudo apt-get autoremove -y
sudo apt-get autoclean
```

### Service Management

```bash
# Apache
sudo systemctl start apache2
sudo systemctl stop apache2
sudo systemctl restart apache2
sudo systemctl reload apache2
sudo systemctl status apache2

# MySQL
sudo systemctl start mysql
sudo systemctl stop mysql
sudo systemctl restart mysql
sudo systemctl status mysql
```

### Log Files

```bash
# Apache logs
sudo tail -f /var/log/apache2/access.log
sudo tail -f /var/log/apache2/error.log

# MySQL logs
sudo tail -f /var/log/mysql/error.log

# PHP logs
sudo tail -f /var/log/php/error.log

# System logs
sudo journalctl -f
```

---

## Resources

- [Apache Documentation](https://httpd.apache.org/docs/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [PHP Documentation](https://www.php.net/docs.php)
- [Ubuntu Server Guide](https://ubuntu.com/server/docs)

---

**Last Updated:** January 12, 2026  
**For:** Acute Pain Service v1.1.2
