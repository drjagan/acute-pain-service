# Acute Pain Service - Documentation Index

Complete documentation for the Acute Pain Service Management System v1.1.3

---

## 📚 Quick Start

**New users? Start here:**
1. [README.md](../README.md) - Project overview and features
2. [Installation Guide](installation/INSTALL.md) - Development setup
3. [Deployment Guide](deployment/DEPLOY.md) - Production deployment

---

## 📁 Documentation Structure

### 🚀 Installation
Installation guides for different environments and methods.

- **[INSTALL.md](installation/INSTALL.md)** - Development environment setup
- **[DOWNLOAD_INSTRUCTIONS.md](installation/DOWNLOAD_INSTRUCTIONS.md)** - How to download the application
- **[ENV_CONFIGURATION.md](installation/ENV_CONFIGURATION.md)** - Environment configuration (.env) guide (NEW in v1.1.3+)
- **[INSTALLATION_FIXES.md](installation/INSTALLATION_FIXES.md)** - Installation troubleshooting and fixes (v1.1.3)

**Quick Links:**
- [System Requirements](installation/INSTALL.md#system-requirements)
- [Local Development Setup](installation/INSTALL.md#installation-steps)
- [Download Methods](installation/DOWNLOAD_INSTRUCTIONS.md#download-options)
- [.env Configuration](installation/ENV_CONFIGURATION.md) - **NEW**

---

### 🌐 Deployment
Production deployment guides and server setup.

- **[DEPLOY.md](deployment/DEPLOY.md)** - Complete production deployment guide
- **[DEPLOYMENT_SUMMARY.md](deployment/DEPLOYMENT_SUMMARY.md)** - Quick deployment overview
- **[LAMP_INSTALL.md](deployment/LAMP_INSTALL.md)** - LAMP stack installation (Ubuntu)
- **[DEPLOYMENT_CHECKLIST.md](deployment/DEPLOYMENT_CHECKLIST.md)** - Pre-deployment checklist

**Quick Links:**
- [Automated Installation](deployment/DEPLOY.md#quick-installation) (15-20 minutes)
- [Manual Installation](deployment/DEPLOY.md#manual-installation)
- [LAMP Stack Setup](deployment/LAMP_INSTALL.md)
- [Security Hardening](deployment/DEPLOY.md#security-hardening)
- [Backup Procedures](deployment/DEPLOY.md#backup-and-maintenance)

---

### 🗄️ Database
Database setup, import, and troubleshooting.

- **[README.md](database/README.md)** - Complete database setup guide
- **[aps_database_complete.sql](database/aps_database_complete.sql)** - Ready-to-import SQL file (29 KB)

**Quick Links:**
- [PhpMyAdmin Import](database/README.md#quick-start-with-phpmyadmin) (2-3 minutes)
- [Command Line Import](database/README.md#option-2-command-line-import)
- [Database Schema](database/README.md#tables-created)
- [Test Users](database/README.md#test-users)
- [Troubleshooting](database/README.md#troubleshooting)

---

### 📦 Releases
Version history, release notes, and changelogs.

- **[CHANGELOG.md](releases/CHANGELOG.md)** - Complete version history
- **[RELEASE_NOTES_v1.1.3.md](releases/RELEASE_NOTES_v1.1.3.md)** - Latest release (v1.1.3)
- **[RELEASE_NOTES_v1.1.2.md](releases/RELEASE_NOTES_v1.1.2.md)** - v1.1.2 release notes
- **[RELEASE_NOTES_v1.1.1.md](releases/RELEASE_NOTES_v1.1.1.md)** - v1.1.1 release notes
- **[RELEASE_NOTES.md](releases/RELEASE_NOTES.md)** - v1.1.0 release notes
- **[GITHUB_RELEASE_INSTRUCTIONS.md](releases/GITHUB_RELEASE_INSTRUCTIONS.md)** - How to create releases

**Current Version:** v1.1.3  
**Release Date:** 2026-01-12  
**Download:** [GitHub Releases](https://github.com/drjagan/acute-pain-service/releases)

**What's New in v1.1.3:**
- SQL export file for PhpMyAdmin (2-minute setup)
- Installation wizard debugging and logging
- Fixed hardcoded database credentials
- Comprehensive troubleshooting documentation

---

### 🔧 Troubleshooting
Common issues, errors, and their solutions.

- **[HEADER_FIX.md](troubleshooting/HEADER_FIX.md)** - "Headers already sent" error fix
- **[SQL_CONSTRAINT_FIX.md](troubleshooting/SQL_CONSTRAINT_FIX.md)** - PhpMyAdmin import error fix
- **[STEP3_LOGIC_FIX.md](troubleshooting/STEP3_LOGIC_FIX.md)** - Step 3 false success message fix

**Common Issues:**
| Issue | Solution | Document |
|-------|----------|----------|
| Step 3 shows success but no tables | Logic operator fix applied | [STEP3_LOGIC_FIX.md](troubleshooting/STEP3_LOGIC_FIX.md) |
| "Headers already sent" error | Output buffering fix applied | [HEADER_FIX.md](troubleshooting/HEADER_FIX.md) |
| PhpMyAdmin import fails | Constraint name fix applied | [SQL_CONSTRAINT_FIX.md](troubleshooting/SQL_CONSTRAINT_FIX.md) |
| Installation wizard stalls | Check logs, enable debugging | [INSTALLATION_FIXES.md](installation/INSTALLATION_FIXES.md) |
| Database connection fails | Check credentials, MySQL running | [Database README](database/README.md#troubleshooting) |
| Permission errors | Set correct folder permissions | [DEPLOY.md](deployment/DEPLOY.md#manual-installation) |

---

### 👨‍💻 Development
Internal documentation, testing guides, and development notes.

- **[TESTING_GUIDE_v1.1.md](development/TESTING_GUIDE_v1.1.md)** - Testing procedures for v1.1
- **[SELECT2_PATIENT_COMPONENT.md](development/SELECT2_PATIENT_COMPONENT.md)** - Patient selector component
- **[COMPLETION_SUMMARY.md](development/COMPLETION_SUMMARY.md)** - v1.1.2 completion summary
- **[NEXT_STEPS.md](development/NEXT_STEPS.md)** - Next development steps
- **[PUBLIC_REPO_UPDATE.md](development/PUBLIC_REPO_UPDATE.md)** - Public repository changes

**For Developers:**
- [Development Setup](installation/INSTALL.md)
- [Testing Guide](development/TESTING_GUIDE_v1.1.md)
- [Contributing Guidelines](../README.md#contributing) (if available)

---

## 🎯 Quick Reference

### Installation Methods

| Method | Time | Best For | Guide |
|--------|------|----------|-------|
| **PhpMyAdmin Import** | 2-3 min | Quick setup, shared hosting | [Database README](database/README.md) |
| **Installation Wizard** | 5-10 min | Custom config, learning | [INSTALL.md](installation/INSTALL.md) |
| **Automated LAMP** | 15-20 min | Fresh server, production | [DEPLOY.md](deployment/DEPLOY.md) |
| **Manual Installation** | 30-45 min | Custom setup, advanced | [DEPLOY.md](deployment/DEPLOY.md) |

### System Requirements

- **PHP:** 8.1+ (8.3 recommended)
- **MySQL:** 8.0+ or MariaDB 10.5+
- **Apache:** 2.4+
- **OS:** Ubuntu 20.04/22.04 LTS (or compatible)
- **RAM:** 2GB minimum (4GB recommended)
- **Disk:** 10GB available

### Default Credentials

**Test Users** (password: `admin123`):
- `admin` - System Administrator
- `dr.sharma` - Attending Physician
- `dr.patel` - Resident
- `nurse.kumar` - Nurse

**⚠️ Change these passwords after installation!**

---

## 📖 Document Categories

### By User Type

**System Administrators:**
- [Deployment Guide](deployment/DEPLOY.md)
- [LAMP Installation](deployment/LAMP_INSTALL.md)
- [Database Setup](database/README.md)
- [Security Hardening](deployment/DEPLOY.md#security-hardening)

**Developers:**
- [Development Setup](installation/INSTALL.md)
- [Testing Guide](development/TESTING_GUIDE_v1.1.md)
- [Component Docs](development/SELECT2_PATIENT_COMPONENT.md)

**End Users:**
- [README](../README.md) - Features and overview
- [Quick Start](installation/DOWNLOAD_INSTRUCTIONS.md)

**DevOps/IT:**
- [Deployment Checklist](deployment/DEPLOYMENT_CHECKLIST.md)
- [Backup Procedures](deployment/DEPLOY.md#backup-and-maintenance)
- [Troubleshooting](troubleshooting/)

### By Task

**Installing the Application:**
1. Download: [DOWNLOAD_INSTRUCTIONS.md](installation/DOWNLOAD_INSTRUCTIONS.md)
2. Install: [INSTALL.md](installation/INSTALL.md) or [DEPLOY.md](deployment/DEPLOY.md)
3. Database: [Database README](database/README.md)

**Deploying to Production:**
1. Server Setup: [LAMP_INSTALL.md](deployment/LAMP_INSTALL.md)
2. Deployment: [DEPLOY.md](deployment/DEPLOY.md)
3. Checklist: [DEPLOYMENT_CHECKLIST.md](deployment/DEPLOYMENT_CHECKLIST.md)
4. Security: [DEPLOY.md](deployment/DEPLOY.md#security-hardening)

**Troubleshooting Issues:**
1. Check: [Troubleshooting folder](troubleshooting/)
2. Installation: [INSTALLATION_FIXES.md](installation/INSTALLATION_FIXES.md)
3. Database: [Database README](database/README.md#troubleshooting)

---

## 🔄 Version History

### v1.1.3 (2026-01-12) - Current
- SQL export file for PhpMyAdmin
- Installation wizard debugging
- Fixed hardcoded credentials
- Troubleshooting documentation

### v1.1.2 (2026-01-12)
- Production deployment package
- Automated installation script
- Comprehensive documentation

### v1.1.1 (2026-01-11)
- Admin role enhancements
- "My Patients" feature improvements

### v1.1.0 (2026-01-11)
- Initial production release

[View Complete Changelog](releases/CHANGELOG.md)

---

## 🆘 Getting Help

### Documentation Not Clear?
1. Check the [Troubleshooting](troubleshooting/) section
2. Review [Common Issues](#common-issues) above
3. Search the [Changelog](releases/CHANGELOG.md)

### Found a Bug?
1. Check [GitHub Issues](https://github.com/drjagan/acute-pain-service/issues)
2. Search existing issues first
3. Create new issue with details

### Need Support?
1. Read relevant documentation first
2. Check error logs: `logs/install.log` or `logs/app.log`
3. Review [Troubleshooting guides](troubleshooting/)
4. Create GitHub issue with:
   - Error messages
   - Steps to reproduce
   - System information
   - Screenshots (if applicable)

---

## 📝 Documentation Standards

### Naming Convention
- `README.md` - Main index/overview for folder
- `FEATURE_NAME.md` - Specific feature or topic (UPPERCASE)
- `feature-guide.md` - Guides and tutorials (lowercase)

### File Locations
- **Root:** Only `README.md` (project overview)
- **documentation/** - All other documentation
- **Subfolders:** Organized by category

### Link Format
Use relative links to other documentation:
```markdown
[Link to deployment](documentation/deployment/DEPLOY.md)
[Database setup](documentation/database/README.md)
```

---

## 🗂️ Folder Structure

```
documentation/
├── README.md                          # This file (documentation index)
│
├── installation/                      # Installation guides
│   ├── INSTALL.md                    # Development setup
│   ├── DOWNLOAD_INSTRUCTIONS.md      # Download methods
│   └── INSTALLATION_FIXES.md         # v1.1.3 installation fixes
│
├── deployment/                        # Production deployment
│   ├── DEPLOY.md                     # Main deployment guide
│   ├── DEPLOYMENT_SUMMARY.md         # Quick overview
│   ├── LAMP_INSTALL.md               # LAMP stack setup
│   └── DEPLOYMENT_CHECKLIST.md       # Pre-deployment checklist
│
├── database/                          # Database documentation
│   ├── README.md                     # Database setup guide
│   └── aps_database_complete.sql     # SQL export file
│
├── releases/                          # Version history
│   ├── CHANGELOG.md                  # Complete changelog
│   ├── RELEASE_NOTES_v1.1.3.md       # Latest release
│   ├── RELEASE_NOTES_v1.1.2.md       # v1.1.2 notes
│   ├── RELEASE_NOTES_v1.1.1.md       # v1.1.1 notes
│   ├── RELEASE_NOTES.md              # v1.1.0 notes
│   └── GITHUB_RELEASE_INSTRUCTIONS.md # Release process
│
├── troubleshooting/                   # Common issues & fixes
│   ├── HEADER_FIX.md                 # Headers already sent fix
│   └── SQL_CONSTRAINT_FIX.md         # PhpMyAdmin import fix
│
└── development/                       # Developer documentation
    ├── TESTING_GUIDE_v1.1.md         # Testing procedures
    ├── SELECT2_PATIENT_COMPONENT.md  # Component docs
    ├── COMPLETION_SUMMARY.md         # Project milestones
    ├── NEXT_STEPS.md                 # Future development
    └── PUBLIC_REPO_UPDATE.md         # Repository changes
```

---

## 🔗 External Links

- **GitHub Repository:** https://github.com/drjagan/acute-pain-service
- **Latest Release:** https://github.com/drjagan/acute-pain-service/releases/latest
- **Issue Tracker:** https://github.com/drjagan/acute-pain-service/issues
- **Releases Page:** https://github.com/drjagan/acute-pain-service/releases

---

## 📅 Last Updated

**Date:** 2026-01-12  
**Version:** 1.1.3  
**Documentation Version:** 1.0

---

**Need quick help?** 
- **Installation:** [Database README](database/README.md) (fastest - 2 min PhpMyAdmin import)
- **Deployment:** [DEPLOY.md](deployment/DEPLOY.md) (complete production guide)
- **Issues:** [Troubleshooting](troubleshooting/) (common problems & solutions)
