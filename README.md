# Acute Pain Service (APS) Portal

Complete lifecycle management system for epidural and peripheral nerve catheters in acute postoperative pain control.

## 🏥 Overview

The APS Portal is a comprehensive PHP/MySQL application designed to streamline clinical workflows across multiple user roles for managing acute pain service operations in hospitals.

## ✨ Phase 1 Features (Current)

- ✅ **User Authentication**
  - Login/Logout with session management
  - Remember Me functionality (30 days)
  - Password reset workflow (email stub)
  - Session timeout protection
  
- ✅ **Security**
  - CSRF protection on all forms
  - XSS prevention (output escaping)
  - SQL injection prevention (prepared statements)
  - Bcrypt password hashing (cost: 12)
  - Secure session management
  
- ✅ **Role-Based Access Control**
  - 4 roles: Admin, Attending Physician, Senior Resident, Nurse
  - Permission-based navigation
  - Role-specific dashboard views
  
- ✅ **Database Schema**
  - 13 tables created (users, patients, catheters, etc.)
  - Complete normalization
  - Soft delete support
  - Audit trail ready

## 📋 Requirements

- **PHP:** 8.3+
- **MySQL:** 8.0+
- **Web Server:** Apache with mod_rewrite OR PHP built-in server
- **Extensions:** PDO, PDO_MySQL, JSON, MBString, OpenSSL

## 🚀 Quick Start

### 1. Install Dependencies

Ensure XAMPP (or similar) is installed with PHP 8.3+ and MySQL 8.0+.

### 2. Database Setup

```bash
cd "Acute Pain Management 01/acute-pain-service"
/Applications/XAMPP/bin/php install/database-setup.php
```

### 3. Start Development Server

```bash
/Applications/XAMPP/bin/php -S localhost:8000 -t public/
```

### 4. Access Application

Open browser: http://localhost:8000

### 5. Login

**Test Accounts:**
- Admin: `admin` / `admin123`
- Attending: `dr.sharma` / `admin123`
- Resident: `dr.patel` / `admin123`
- Nurse: `nurse.kumar` / `admin123`

## 📁 Project Structure

```
acute-pain-service/
├── config/          # Configuration files
├── public/          # Web root (index.php, assets)
├── src/
│   ├── Controllers/ # MVC Controllers
│   ├── Models/      # Database Models
│   ├── Views/       # HTML Templates
│   ├── Helpers/     # Utility classes
│   ├── Services/    # Business logic
│   ├── Middleware/  # Request filters
│   └── Database/    # Migrations & seeds
├── logs/            # Application logs
└── install/         # Installation scripts
```

## 🔐 Security Features

- **Authentication:** Session-based with secure cookies
- **CSRF Protection:** Token validation on all state-changing requests
- **XSS Prevention:** Output escaping via `e()` function
- **SQL Injection:** Prepared statements exclusively
- **Password Security:** Bcrypt with cost factor 12
- **Session Security:** Timeout, regeneration, hijacking prevention

## 🛣️ Roadmap

### Phase 2 (Weeks 3-5)
- Screen 1: Patient Registration
- Screen 2: Catheter Insertion
- Enhanced RBAC

### Phase 3 (Weeks 6-7)
- Screen 3: Daily Drug Regime
- Screen 4: Functional Outcomes
- Pain scale visualization

### Phase 4 (Weeks 8-9)
- Screen 5: Catheter Removal
- Alert system
- Notification dashboard

### Phase 5 (Weeks 10-11)
- CSV/Excel/PDF export
- Statistical dashboard
- Charts & analytics

## 📝 Configuration

Edit `config/database.php` for database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'aps_database');
define('DB_USER', 'root');
define('DB_PASS', 'your-password');
```

## 🐛 Troubleshooting

**Database Connection Failed:**
- Ensure MySQL is running (XAMPP Control Panel)
- Verify credentials in `config/database.php`

**404 Errors:**
- Ensure running from `public/` directory
- Check `.htaccess` files are present

**Session Issues:**
- Clear browser cookies
- Check `logs/error.log` for details

## 📚 Documentation

- **Database Schema:** `docs/DATABASE.md` (Phase 2)
- **API Reference:** `docs/API.md` (Phase 2)
- **User Guide:** `docs/USER-GUIDE.md` (Phase 6)

## 👥 User Roles & Permissions

| Role | Permissions |
|------|-------------|
| **Admin** | Full system access, user management, reports |
| **Attending Physician** | All clinical screens, approvals, removal |
| **Senior Resident** | Patient registration, procedures, daily monitoring |
| **Nurse** | Daily drug regime, functional outcomes monitoring |

## 📄 License

Proprietary - Hospital Internal Use Only

## 🆘 Support

For issues or questions:
- Check `logs/app.log` and `logs/error.log`
- Review this README
- Contact system administrator

---

**Version:** 1.0.0 (Phase 1)  
**Last Updated:** January 10, 2026  
**Status:** Foundation Complete ✅
