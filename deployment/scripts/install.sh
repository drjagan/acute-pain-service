#!/bin/bash
#######################################################################
# Acute Pain Service - Installation Script
# For Ubuntu 20.04/22.04 LTS with Apache, MySQL 8.0, PHP 8.3
#######################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="Acute Pain Service"
APP_DIR="/var/www/acute-pain-service"
DB_NAME="aps_database"
DB_USER="aps_user"
DOMAIN="localhost"

echo -e "${BLUE}======================================${NC}"
echo -e "${BLUE}  $APP_NAME - Installation Script${NC}"
echo -e "${BLUE}======================================${NC}"
echo ""

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}This script must be run as root (use sudo)${NC}" 
   exit 1
fi

# Function to print status
print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Function to generate random password
generate_password() {
    openssl rand -base64 32 | tr -d "=+/" | cut -c1-25
}

# Prompt for domain
echo -e "${YELLOW}Enter your domain name (e.g., aps.example.com or IP address):${NC}"
read -p "Domain [localhost]: " user_domain
DOMAIN=${user_domain:-localhost}

# Generate database password
DB_PASS=$(generate_password)

echo ""
echo -e "${BLUE}Installation Configuration:${NC}"
echo -e "  Application Directory: ${GREEN}$APP_DIR${NC}"
echo -e "  Domain: ${GREEN}$DOMAIN${NC}"
echo -e "  Database Name: ${GREEN}$DB_NAME${NC}"
echo -e "  Database User: ${GREEN}$DB_USER${NC}"
echo -e "  Database Password: ${GREEN}$DB_PASS${NC}"
echo ""
read -p "Proceed with installation? (y/n): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    exit 1
fi

echo ""
echo -e "${BLUE}Step 1: Updating system packages...${NC}"
apt-get update
apt-get upgrade -y
print_status "System packages updated"

echo ""
echo -e "${BLUE}Step 2: Installing Apache web server...${NC}"
apt-get install -y apache2
systemctl enable apache2
systemctl start apache2
print_status "Apache installed"

echo ""
echo -e "${BLUE}Step 3: Installing MySQL 8.0...${NC}"
apt-get install -y mysql-server
systemctl enable mysql
systemctl start mysql
print_status "MySQL installed"

echo ""
echo -e "${BLUE}Step 4: Installing PHP 8.3 and extensions...${NC}"
# Add PHP repository
apt-get install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt-get update

# Install PHP and required extensions
apt-get install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd php8.3-intl
    
# Enable Apache PHP module
a2enmod php8.3
a2enmod rewrite
a2enmod headers
print_status "PHP 8.3 and extensions installed"

echo ""
echo -e "${BLUE}Step 5: Configuring MySQL database...${NC}"
# Secure MySQL installation (automated)
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_PASS';"
mysql -uroot -p$DB_PASS -e "DELETE FROM mysql.user WHERE User='';"
mysql -uroot -p$DB_PASS -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -uroot -p$DB_PASS -e "DROP DATABASE IF EXISTS test;"
mysql -uroot -p$DB_PASS -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
mysql -uroot -p$DB_PASS -e "FLUSH PRIVILEGES;"

# Create application database and user
mysql -uroot -p$DB_PASS <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

print_status "MySQL configured and database created"

echo ""
echo -e "${BLUE}Step 6: Creating application directory...${NC}"
mkdir -p $APP_DIR
cd $APP_DIR

# If current directory has files, copy them
if [ -f "../../public/index.php" ]; then
    print_status "Copying application files from current directory..."
    cp -r ../../* .
else
    print_warning "Application files not found. Please upload files to $APP_DIR"
fi

print_status "Application directory created"

echo ""
echo -e "${BLUE}Step 7: Setting up permissions...${NC}"
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage 2>/dev/null || true
chmod -R 775 $APP_DIR/logs 2>/dev/null || true
print_status "Permissions set"

echo ""
echo -e "${BLUE}Step 8: Creating .env configuration file...${NC}"
if [ -f "$APP_DIR/.env.example" ]; then
    cp $APP_DIR/.env.example $APP_DIR/.env
    
    # Update .env with actual values
    sed -i "s/DB_HOST=.*/DB_HOST=localhost/" $APP_DIR/.env
    sed -i "s/DB_NAME=.*/DB_NAME=$DB_NAME/" $APP_DIR/.env
    sed -i "s/DB_USER=.*/DB_USER=$DB_USER/" $APP_DIR/.env
    sed -i "s/DB_PASS=.*/DB_PASS=$DB_PASS/" $APP_DIR/.env
    sed -i "s|APP_URL=.*|APP_URL=http://$DOMAIN|" $APP_DIR/.env
    
    # Generate APP_KEY
    APP_KEY=$(php -r "echo bin2hex(random_bytes(32));")
    sed -i "s/APP_KEY=.*/APP_KEY=$APP_KEY/" $APP_DIR/.env
    
    chmod 600 $APP_DIR/.env
    print_status ".env file created and configured"
else
    print_warning ".env.example not found, please configure manually"
fi

echo ""
echo -e "${BLUE}Step 9: Configuring Apache virtual host...${NC}"
cat > /etc/apache2/sites-available/aps.conf <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    DocumentRoot $APP_DIR/public
    
    <Directory $APP_DIR/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    <Directory $APP_DIR/config>
        Require all denied
    </Directory>
    
    <Directory $APP_DIR/src>
        Require all denied
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/aps-error.log
    CustomLog \${APACHE_LOG_DIR}/aps-access.log combined
</VirtualHost>
EOF

a2ensite aps.conf
a2dissite 000-default.conf 2>/dev/null || true
systemctl reload apache2
print_status "Apache virtual host configured"

echo ""
echo -e "${BLUE}Step 10: Running database migrations...${NC}"
if [ -f "$APP_DIR/run_migrations_v1.1.php" ]; then
    php $APP_DIR/run_migrations_v1.1.php
    print_status "Database migrations completed"
else
    print_warning "Migration script not found. Please run migrations manually"
fi

echo ""
echo -e "${BLUE}Step 11: Creating admin user...${NC}"
ADMIN_PASS=$(generate_password)
mysql -u$DB_USER -p$DB_PASS $DB_NAME <<EOF
INSERT INTO users (username, email, password, first_name, last_name, role, status, created_at)
VALUES ('admin', 'admin@localhost', PASSWORD('$ADMIN_PASS'), 'System', 'Administrator', 'admin', 'active', NOW())
ON DUPLICATE KEY UPDATE username=username;
EOF
print_status "Admin user created"

echo ""
echo -e "${BLUE}Step 12: Setting up firewall...${NC}"
ufw allow 'Apache Full'
print_status "Firewall configured"

echo ""
echo -e "${GREEN}======================================${NC}"
echo -e "${GREEN}  Installation Complete!${NC}"
echo -e "${GREEN}======================================${NC}"
echo ""
echo -e "${YELLOW}Important Information:${NC}"
echo ""
echo -e "Application URL: ${GREEN}http://$DOMAIN${NC}"
echo -e "Admin Username: ${GREEN}admin${NC}"
echo -e "Admin Password: ${GREEN}$ADMIN_PASS${NC}"
echo ""
echo -e "Database Name: ${GREEN}$DB_NAME${NC}"
echo -e "Database User: ${GREEN}$DB_USER${NC}"
echo -e "Database Password: ${GREEN}$DB_PASS${NC}"
echo -e "MySQL Root Password: ${GREEN}$DB_PASS${NC}"
echo ""
echo -e "${YELLOW}IMPORTANT: Save these credentials securely!${NC}"
echo ""
echo -e "${BLUE}Next Steps:${NC}"
echo -e "1. Open ${GREEN}http://$DOMAIN${NC} in your browser"
echo -e "2. Login with the admin credentials above"
echo -e "3. Change the admin password immediately"
echo -e "4. Configure SMTP settings for email notifications (optional)"
echo -e "5. Create additional user accounts"
echo -e "6. Consider setting up SSL certificate (Let's Encrypt recommended)"
echo ""
echo -e "${YELLOW}For SSL setup, run:${NC}"
echo -e "  apt-get install -y certbot python3-certbot-apache"
echo -e "  certbot --apache -d $DOMAIN"
echo ""
echo -e "${GREEN}Thank you for installing Acute Pain Service!${NC}"
echo ""

# Save credentials to file
cat > /root/aps-credentials.txt <<EOF
Acute Pain Service - Installation Credentials
Generated: $(date)

Application URL: http://$DOMAIN
Admin Username: admin
Admin Password: $ADMIN_PASS

Database Name: $DB_NAME
Database User: $DB_USER
Database Password: $DB_PASS
MySQL Root Password: $DB_PASS

IMPORTANT: Delete this file after saving credentials securely!
EOF

chmod 600 /root/aps-credentials.txt
echo -e "${YELLOW}Credentials also saved to: ${GREEN}/root/aps-credentials.txt${NC}"
echo ""
