# 🚀 Production Deployment Package - v1.1.2

Complete production-ready deployment package for the Acute Pain Service Management System with automated installation scripts and comprehensive documentation.

## ⚡ Quick Installation

```bash
wget https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.1.2.tar.gz
tar -xzf v1.1.2.tar.gz
cd acute-pain-service-1.1.2
sudo chmod +x deployment/scripts/install.sh
sudo ./deployment/scripts/install.sh
```

**Installation Time:** 15-20 minutes  
**Requirements:** Fresh Ubuntu 20.04/22.04 LTS server

---

## 📦 What's New in v1.1.2

### Added
- **Automated Installation Script** (`deployment/scripts/install.sh`)
  - One-command LAMP stack installation
  - Automatic database creation and configuration
  - Apache virtual host setup
  - Admin user creation with generated credentials
  
- **Comprehensive Documentation** (1,900+ lines)
  - `DEPLOY.md` - Complete production deployment guide
  - `LAMP_INSTALL.md` - LAMP stack installation guide
  - `DEPLOYMENT_SUMMARY.md` - Package overview and quick start
  - `DOWNLOAD_INSTRUCTIONS.md` - Download and installation methods
  - `deployment/DEPLOYMENT_CHECKLIST.md` - Step-by-step checklist

- **Configuration Templates**
  - `.env.example` - Environment configuration template
  - `deployment/config/apache-vhost.conf` - Apache virtual host configuration
  - `.gitattributes` - Git export and line ending settings

### Fixed
- PHP 8.3 deprecation warnings in reports when null values passed to `number_format()`
- Notification dropdown positioning - now properly overlays content
- Notifications page 404 error - added route mapping and view
- "My Patients" menu highlighting issue

### Changed
- Updated README.md with deployment information and badges
- Version bumped from 1.1.1 to 1.1.2

---

## 📚 Documentation

After downloading, check these files for detailed instructions:
- **Start Here**: `DEPLOYMENT_SUMMARY.md` - Quick overview
- **Deployment**: `DEPLOY.md` - Complete deployment guide
- **LAMP Setup**: `LAMP_INSTALL.md` - Server preparation
- **Checklist**: `deployment/DEPLOYMENT_CHECKLIST.md` - Verification steps

---

## 🔧 System Requirements

- **OS**: Ubuntu 20.04/22.04 LTS (other Linux distributions supported with manual setup)
- **RAM**: Minimum 2GB (4GB recommended)
- **Disk**: 10GB available space
- **PHP**: 8.3+
- **MySQL**: 8.0+
- **Apache**: 2.4+

---

## ✨ Features

- Complete patient lifecycle management
- 21 types of epidural and nerve catheters support
- Drug regime tracking with VNRS scores
- Functional outcomes assessment
- Individual and consolidated reports
- Role-based access control (Admin, Attending, Resident, Nurse)
- Real-time notifications
- Mobile responsive design
- Zero framework dependencies (pure PHP MVC)

---

## 🔒 Security Features

- Environment-based configuration
- SQL injection protection (PDO with prepared statements)
- XSS protection with output escaping
- CSRF protection
- Password hashing (bcrypt)
- Session security
- Input validation and sanitization

---

## 🚦 Installation Methods

### Method 1: Automated (Recommended)
One-command installation that sets up everything automatically (15-20 minutes).

### Method 2: Manual Installation
Step-by-step manual setup for custom configurations (see `DEPLOY.md`).

### Method 3: Git Clone
Clone the repository for development purposes (see `README.md`).

---

## 📖 Quick Start

1. Download and extract the archive
2. Run the installation script: `sudo ./deployment/scripts/install.sh`
3. Follow the prompts (domain name, email, etc.)
4. Access your installation at the configured domain
5. Login with the generated admin credentials
6. Configure SMTP settings (optional but recommended)
7. Create additional user accounts
8. Start managing patients!

---

## 🔗 Links

- **GitHub Repository**: https://github.com/drjagan/acute-pain-service
- **Documentation**: See included markdown files
- **Issues**: https://github.com/drjagan/acute-pain-service/issues

---

## 📝 License

This project is licensed under the MIT License.

---

## 👨‍⚕️ About

The Acute Pain Service Management System is a comprehensive solution for managing epidural and peripheral nerve catheters in acute postoperative pain control. It provides complete lifecycle tracking from insertion to removal, with integrated drug regime management and functional outcome assessment.

---

## 🆘 Support

For issues, questions, or feature requests, please use the GitHub Issues page.

---

**Full Changelog**: https://github.com/drjagan/acute-pain-service/compare/v1.1.1...v1.1.2
