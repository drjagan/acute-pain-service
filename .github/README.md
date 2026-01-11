# 🏥 Acute Pain Service Management System

<div align="center">

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/license-Proprietary-red.svg)
![Status](https://img.shields.io/badge/status-Production%20Ready-success.svg)

**Complete lifecycle management for epidural and peripheral nerve catheters in acute postoperative pain control**

[Features](#-features) • [Installation](#-installation) • [Documentation](#-documentation) • [Screenshots](#-screenshots)

</div>

---

## 📋 Overview

The **Acute Pain Service Management System** is a comprehensive web-based application designed for managing epidural and peripheral nerve catheters in acute postoperative pain control. Built with PHP 8.3, MySQL 8.0, and Bootstrap 5, this system provides a complete lifecycle management solution for pain service departments.

### 🎯 Key Highlights

- ✅ **Production Ready** - Complete v1.0.0 release with installation wizard
- ✅ **Zero Framework** - Pure PHP MVC architecture for maximum control
- ✅ **Mobile Responsive** - Bootstrap 5 with optimized mobile layouts
- ✅ **Enterprise Security** - CSRF, SQL injection prevention, BCrypt hashing
- ✅ **Easy Installation** - 5-step wizard takes just 5 minutes
- ✅ **Comprehensive Docs** - 2,000+ lines of documentation

---

## ✨ Features

### 📊 Clinical Workflow Management

| Feature | Description |
|---------|-------------|
| **Patient Registration** | 22-field comprehensive form with BMI auto-calculation |
| **Catheter Management** | 21 catheter types organized hierarchically |
| **Drug Regime Tracking** | Detailed medication with VNRS pain scores |
| **Functional Outcomes** | 13-field assessment with functional scoring |
| **Catheter Removal** | Complete documentation with satisfaction tracking |

### 📈 Analytics & Reporting

- **Real-time Dashboard** - 13 live statistics and activity feed
- **Individual Reports** - 8-section patient lifecycle reports
- **Consolidated Reports** - Monthly aggregate statistics and KPIs
- **Print-to-PDF** - Professional report generation

### 👥 User Management

- **4 Role Types** - Admin, Attending, Resident, Nurse
- **RBAC** - Role-based access control
- **Full CRUD** - Complete user lifecycle management
- **Security** - BCrypt hashing, session management

### 🔍 Advanced Features

- **Select2 Integration** - AJAX-powered searchable dropdowns
- **Latest 5 Patients** - Quick access to recent patients
- **Real-time Search** - Filter by name or hospital number
- **Mobile Optimized** - Touch-friendly responsive design

---

## 🚀 Installation

### Quick Start (5 Minutes)

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/acute-pain-service.git
cd acute-pain-service

# 2. Set permissions
chmod -R 755 public/
chmod -R 777 config/ logs/ public/uploads/ public/exports/

# 3. Navigate to installation wizard
# Open in browser: http://localhost:8000/install/

# 4. Follow the 5-step wizard
# - Requirements check
# - Database configuration
# - Create tables
# - Admin account setup
# - Complete installation

# 5. Delete install folder (security)
rm -rf install/

# 6. Login and start using!
# http://localhost:8000/public/
```

For detailed installation instructions, see [INSTALL.md](../INSTALL.md)

---

## 📚 Documentation

- **[README.md](../README.md)** - Complete system overview (525 lines)
- **[INSTALL.md](../INSTALL.md)** - Installation guide (399 lines)
- **[RELEASE_NOTES.md](../RELEASE_NOTES.md)** - Version details (447 lines)
- **[SELECT2 Component](../docs/SELECT2_PATIENT_COMPONENT.md)** - Component guide (400+ lines)

---

## 🖥️ Screenshots

### Dashboard
![Dashboard](../docs/screenshots/dashboard.png)
*Real-time statistics and activity feed*

### Patient Registration
![Patient Registration](../docs/screenshots/patient-registration.png)
*Comprehensive 22-field registration form*

### Reports
![Reports](../docs/screenshots/reports.png)
*Individual and consolidated reports with print-to-PDF*

---

## 🛠️ Tech Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | 8.1+ | Backend logic |
| MySQL/MariaDB | 8.0+/10.5+ | Database |
| Bootstrap | 5.3.0 | UI framework |
| jQuery | 3.7.1 | DOM manipulation |
| Select2 | 4.1.0 | Enhanced dropdowns |
| Chart.js | 4.4.0 | Visualizations (ready) |

---

## 📊 System Requirements

### Server
- PHP 8.1 or higher
- MySQL 8.0+ or MariaDB 10.5+
- Apache 2.4+ or Nginx 1.18+
- 256MB RAM minimum
- 100MB disk space

### PHP Extensions
- `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `json`, `curl`

### Browsers
- Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

---

## 🔒 Security Features

- ✅ **CSRF Protection** - All forms protected
- ✅ **SQL Injection Prevention** - PDO prepared statements
- ✅ **XSS Prevention** - HTML encoding throughout
- ✅ **Password Security** - BCrypt with cost 12
- ✅ **Session Management** - Configurable timeouts
- ✅ **RBAC** - 4-tier role system
- ✅ **Audit Trails** - created_by, updated_by tracking
- ✅ **Soft Deletes** - Data retention and recovery

---

## 🗄️ Database Schema

### Core Tables (11 total)
- `users` - Authentication and RBAC
- `patients` - Demographics and clinical data
- `catheters` - Catheter insertion details
- `drug_regimes` - Medication records
- `functional_outcomes` - Patient assessments
- `catheter_removals` - Removal documentation
- 5 lookup tables (comorbidities, surgeries, drugs, etc.)

---

## 🎓 Default Test Accounts

All test accounts have password: `admin123`

| Username | Role | Access |
|----------|------|--------|
| `admin` | Administrator | Full system access |
| `dr.sharma` | Attending | Clinical + patient management |
| `dr.patel` | Resident | Clinical data entry |
| `nurse.kumar` | Nurse | Limited clinical access |

**⚠️ Security Warning:** Change or delete these accounts in production!

---

## 🔄 Version History

### v1.0.0 (January 11, 2026) - Production Ready
- ✅ Complete clinical workflow (5 screens)
- ✅ Dashboard and reporting
- ✅ User management
- ✅ Installation wizard
- ✅ Mobile-responsive design
- ✅ Comprehensive documentation

See [RELEASE_NOTES.md](../RELEASE_NOTES.md) for complete details.

---

## 🗺️ Roadmap

### Version 1.1 (Q2 2026)
- Patient-Physician associations
- In-app notifications system
- Email notifications with SMTP
- Settings page for configuration

### Version 1.2 (Q3 2026)
- Excel export functionality
- Chart.js visualizations
- Advanced analytics
- Audit logging

### Version 2.0 (Future)
- REST API
- Mobile apps
- Cloud-native architecture
- Multi-language support

---

## 📝 Contributing

This is a proprietary project for internal use. For feature requests or bug reports, please use the GitHub issue templates.

---

## 📜 License

**Proprietary Software**  
© 2026 Acute Pain Service  
All Rights Reserved

This software is proprietary and confidential. Unauthorized copying, distribution, or use of this software, via any medium, is strictly prohibited.

---

## 🙏 Acknowledgments

Built with:
- [PHP](https://www.php.net/) - Server-side logic
- [MySQL](https://www.mysql.com/) - Database management
- [Bootstrap](https://getbootstrap.com/) - UI framework
- [Select2](https://select2.org/) - Enhanced dropdowns
- [Chart.js](https://www.chartjs.org/) - Data visualizations

---

## 📞 Support

**Documentation:**
- Review the comprehensive docs in the `docs/` folder
- Check `INSTALL.md` for installation issues
- See `RELEASE_NOTES.md` for version information

**Issues:**
- Use GitHub Issues for bug reports
- Use feature request template for new features
- Check existing issues before creating new ones

---

<div align="center">

**Made with ❤️ for the Acute Pain Service Department**

[⬆ Back to Top](#-acute-pain-service-management-system)

</div>
