# Acute Pain Service (APS) Management System

**Version:** 1.2.1  
**Release Date:** January 25, 2026  
**License:** MIT

[![PHP Version](https://img.shields.io/badge/PHP-8.3+-blue.svg)](https://www.php.net/)
[![MySQL Version](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## 📋 Overview

The **Acute Pain Service Management System** is a comprehensive web-based application designed for managing epidural and peripheral nerve catheters in acute postoperative pain control. Built with PHP 8.3, MySQL 8.0, and Bootstrap 5, this system provides a complete lifecycle management solution for pain service departments.

---

## ✨ Key Features

### 📊 Clinical Workflow Management
- **Patient Registration** - Complete demographics and clinical information
- **Catheter Management** - 21 catheter types with hierarchical selection
- **Drug Regime Tracking** - Detailed medication administration with VNRS pain scores
- **Functional Outcomes** - Comprehensive functional assessment scoring
- **Catheter Removal** - Complete removal documentation with satisfaction tracking

### 📈 Analytics & Reporting
- **Real-time Dashboard** - 13 live statistics and recent activity feed
- **Individual Patient Reports** - Complete lifecycle reports with 8 sections
- **Consolidated Reports** - Monthly aggregate statistics and KPIs
- **Print-to-PDF** - Professional report generation via browser print

### 👥 User Management
- **Role-Based Access Control** - 4 roles (Admin, Attending, Resident, Nurse)
- **User CRUD Operations** - Complete user lifecycle management
- **Session Management** - Secure authentication with remember-me functionality
- **Password Security** - BCrypt hashing with configurable cost factor

### 🔍 Advanced Search
- **Searchable Dropdowns** - Select2 integration with AJAX-powered search
- **Latest 5 Patients** - Quick access to recently added patients
- **Real-time Filtering** - Search by patient name or hospital number

### 📱 Mobile-Responsive Design
- **Bootstrap 5** - Modern, mobile-first interface
- **Optimized Layouts** - Proper responsive breakpoints for all devices
- **Touch-Friendly** - Enhanced mobile interaction patterns

### 🎯 Master Data Management (v1.2.0)
- **Centralized Admin Interface** - Manage all lookup data from one dashboard
- **9 Data Types** - Catheter indications, removal indications, sentinel events, specialties, surgeries, drugs, adjuvants, comorbidities, red flags
- **CRUD Operations** - Create, read, update, delete with validation
- **Drag & Drop Reordering** - Intuitive sort order management
- **Search & Pagination** - Handle large datasets efficiently
- **CSV Export** - Download data for analysis
- **Active/Inactive Toggle** - Enable/disable entries without deletion
- **Soft Delete** - Recover deleted entries
- **Specialty-Based Filtering** - Surgery list auto-filters by specialty

---

## 🎯 System Requirements

### Server Requirements
- **PHP:** 8.1 or higher
- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **Database:** MySQL 8.0+ or MariaDB 10.5+
- **Memory:** Minimum 256MB RAM
- **Disk Space:** 100MB minimum

### PHP Extensions (Required)
- `pdo`
- `pdo_mysql`
- `mbstring`
- `openssl`
- `json`
- `curl`

### Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

---

## 🔔 What's New in v1.2.1 (January 25, 2026)

### Bug Fixes
- ✅ **CSRF Token Validation** - Fixed session initialization in CSRF protection
- ✅ **Toggle Active Status** - Fixed string/int handling in database toggle operations  
- ✅ **Form Labels** - Fixed singular/plural naming in Master Data forms
- ✅ **Cloudron Compatibility** - Added wrapper files and routing for Cloudron LAMP stack

### Deployment Notes
- **Main Branch**: Use for standard Apache/Nginx deployments
- **aps.sbvu.ac.in Branch**: Use for Cloudron production deployments (includes wrapper files)

See [CHANGELOG.md](CHANGELOG.md) for complete version history.

---

## 🚀 Installation

### Quick Start (Production Server)

For production deployment on a LAMP server:

```bash
# Download latest release
wget https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.2.1.tar.gz
tar -xzf v1.2.1.tar.gz
cd acute-pain-service-1.2.1

# Run automated installation
sudo chmod +x deployment/scripts/install.sh
sudo ./deployment/scripts/install.sh
```

The installation script will:
- Install Apache, MySQL 8.0, PHP 8.3
- Create database and configure application
- Set up virtual host and permissions
- Create admin user and display credentials

**Installation time:** ~15-20 minutes

### Documentation

**📚 [Complete Documentation](documentation/README.md)** - Full documentation index

Quick links:
- **[Deployment Guide](documentation/deployment/DEPLOY.md)** - Production deployment
- **[Database Setup](documentation/database/README.md)** - PhpMyAdmin import (2 minutes)
- **[Installation Guide](documentation/installation/INSTALL.md)** - Development setup
- **[Troubleshooting](documentation/troubleshooting/)** - Common issues & fixes

### Development Setup

For local development:

```bash
# Clone repository
git clone https://github.com/drjagan/acute-pain-service.git
cd acute-pain-service

# Configure environment
cp .env.example .env
nano .env  # Update database credentials

# Start PHP development server
php -S localhost:8000 -t public/
```

Visit `http://localhost:8000` in your browser.

---

## 🔐 Default Credentials

**Test Accounts** (Password: `admin123` for all)

| Username | Role | Description |
|----------|------|-------------|
| `admin` | Administrator | Full system access |
| `dr.sharma` | Attending Physician | Clinical and patient management |
| `dr.patel` | Resident | Clinical data entry |
| `nurse.kumar` | Nurse | Limited clinical access |

⚠️ **Security Warning:** Change or delete these accounts in production!

---

## 📁 Project Structure

```
acute-pain-service/
├── config/                 # Configuration files
│   ├── config.php         # Main configuration
│   └── .installed         # Installation flag (created by wizard)
├── docs/                   # Documentation
│   └── SELECT2_PATIENT_COMPONENT.md
├── install/                # Installation wizard (DELETE after install)
│   ├── index.php
│   ├── functions.php
│   └── steps/
├── logs/                   # Application logs
├── public/                 # Web root (point your web server here)
│   ├── index.php          # Front controller
│   ├── assets/            # CSS, JS, images
│   ├── uploads/           # User uploaded files
│   ├── exports/           # Generated export files
│   └── favicon.ico
├── src/                    # Application source code
│   ├── Controllers/       # MVC Controllers
│   ├── Models/            # MVC Models
│   ├── Views/             # MVC Views
│   ├── Helpers/           # Helper classes (CSRF, Session, Flash, etc.)
│   └── Database/
│       ├── migrations/    # Database schema migrations
│       └── seeds/         # Seed data
├── .gitignore
├── VERSION
└── README.md
```

---

## 🗄️ Database Schema

### Core Tables
- **users** - User authentication and RBAC
- **patients** - Patient demographics and clinical data
- **catheters** - Catheter insertion details
- **drug_regimes** - Medication administration records
- **functional_outcomes** - Patient functional assessments
- **catheter_removals** - Catheter removal documentation

### Lookup Tables
- **lookup_comorbidities** - Comorbidity reference data
- **lookup_surgeries** - Surgery types
- **lookup_drugs** - Drug reference data
- **lookup_adjuvants** - Adjuvant medications
- **lookup_red_flags** - Clinical red flags

---

## 🎨 Features in Detail

### Patient Management (Screen 1)
- 22-field comprehensive registration form
- BMI auto-calculation with categorization
- JSON storage for comorbidities and surgeries
- Soft delete functionality
- Audit trail tracking

### Catheter Insertion (Screen 2)
- 21 catheter types organized hierarchically
- 3 main categories: Epidural, Peripheral Nerve, Fascial Plane
- Detailed insertion data capture
- Patient pre-selection via URL parameter
- Real-time patient info display

### Drug Regime Management (Screen 3)
- 6-section comprehensive form
- VNRS pain scoring (static and dynamic)
- Baseline and 15-minute comparative scores
- Drug concentration and volume tracking
- Side effects and adverse events monitoring

### Functional Outcomes (Screen 4)
- 13-field functional assessment
- POD (Post-Operative Day) tracking
- Spirometry, ambulation, cough ability scoring
- SpO2 monitoring with room air status
- Sentinel event tracking

### Catheter Removal (Screen 5)
- 16-field removal documentation
- Indication for removal tracking
- Catheter tip integrity verification
- Patient satisfaction scoring (4-point scale)
- Complication documentation

### Dashboard (Phase 6A)
- 13 real-time statistics
- Active/discharged patient counts
- Catheter statistics by type
- Recent activity feed
- Critical alerts section

### Reports (Phase 6B & 6C)
- Individual patient lifecycle reports
- Consolidated monthly reports
- 9-section aggregate statistics
- Print-optimized layouts
- Export-ready formatting

### User Management (Phase 7)
- Full CRUD operations (admin only)
- Search and filter capabilities
- Password management
- Status toggle (active/inactive/suspended)
- Last login tracking
- Self-account protection

---

## 🔧 Configuration

### Database Settings
Edit `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'aps_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Application Settings
```php
define('APP_ENV', 'production');  // development | production
define('APP_NAME', 'Acute Pain Service');
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('PER_PAGE', 20);          // Pagination limit
```

### Security Settings
```php
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900);    // 15 minutes
```

---

## 🔒 Security Features

- ✅ **CSRF Protection** - Token validation on all forms
- ✅ **SQL Injection Prevention** - Prepared statements with PDO
- ✅ **XSS Prevention** - HTML encoding on all outputs
- ✅ **Password Hashing** - BCrypt with cost factor 12
- ✅ **Session Security** - Secure session management
- ✅ **Role-Based Access Control** - 4-tier permission system
- ✅ **Soft Deletes** - Data retention with deleted_at timestamps
- ✅ **Audit Trails** - created_by and updated_by tracking

---

## 📊 Performance Optimization

- **Select2 AJAX Search** - Loads only 5-10 results at a time
- **Database Indexing** - Optimized queries with proper indexes
- **Asset Caching** - Version-based cache busting
- **Lazy Loading** - On-demand data loading
- **Query Optimization** - Efficient JOINs and aggregations

---

## 🐛 Troubleshooting

### Installation Issues

**Problem:** "Requirements Not Met"
- **Solution:** Install missing PHP extensions, check PHP version

**Problem:** "Database Connection Failed"
- **Solution:** Verify credentials, check MySQL service is running

**Problem:** "Permission Denied" on directories
- **Solution:** Set correct permissions (777 for config/, logs/, uploads/)

### Runtime Issues

**Problem:** "jQuery is not defined"
- **Solution:** Clear browser cache (Ctrl+Shift+R)

**Problem:** Select2 dropdown not working
- **Solution:** Check browser console for errors, verify AJAX endpoint

**Problem:** "404 Not Found" for CSS/JS
- **Solution:** Files must be in `public/assets/` not `public/css/`

### Database Issues

**Problem:** "Table doesn't exist"
- **Solution:** Run migrations again via installation wizard

**Problem:** "Duplicate entry for key"
- **Solution:** Check unique constraints, may need to clear test data

---

## 📝 Development Roadmap (Future Versions)

### Version 1.1 (Planned)
- Patient-Physician associations (many-to-many)
- "My Patients" dashboard widget
- In-app notifications system
- Email notifications with SMTP
- Settings page for SMTP configuration

### Version 1.2 (Planned)
- Excel export functionality
- Chart.js visualizations
- Advanced filtering and search
- Audit logging for all CRUD operations

### Version 2.0 (Future)
- REST API for mobile apps
- Real-time WebSocket notifications
- Advanced analytics and predictive insights
- Multi-language support
- Cloud deployment templates

---

## 🤝 Support & Contribution

This is an internal project for the Acute Pain Service department.

**For Support:**
- Check the `docs/` directory for detailed documentation
- Review code comments for inline documentation
- Contact the development team

---

## 📜 License & Copyright

**Copyright © 2026 Acute Pain Service**  
**All Rights Reserved**

This software is proprietary and confidential. Unauthorized copying, distribution, or use of this software, via any medium, is strictly prohibited.

---

## 📋 Changelog

### Version 1.0.0 (January 2026)
- ✅ Initial production release
- ✅ Complete clinical workflow (5 screens)
- ✅ Dashboard with real-time statistics
- ✅ Individual and consolidated reports
- ✅ User management (CRUD operations)
- ✅ Searchable patient dropdowns (Select2)
- ✅ Mobile-responsive design
- ✅ Installation wizard
- ✅ Comprehensive documentation

---

## 🙏 Acknowledgments

Built with:
- **PHP 8.3** - Server-side logic
- **MySQL 8.0** - Database management
- **Bootstrap 5** - UI framework
- **Select2** - Enhanced dropdowns
- **Chart.js** - Data visualizations (loaded, ready for use)
- **Bootstrap Icons** - Icon library

---

**Version:** 1.0.0  
**Last Updated:** January 11, 2026  
**Installation Wizard:** Included  
**Documentation:** Complete
