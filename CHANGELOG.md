# Changelog

All notable changes to the Acute Pain Service application will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.1] - 2026-01-25

### Fixed
- **CSRF Token Validation**: Fixed session handling in CSRF.php to use `Session::start()` instead of direct `session_start()` for proper session initialization
- **Toggle Active Status**: Fixed `toggleActive()` method in `BaseLookupModel` to properly handle string/int conversion for database active field
  - Changed from `!$record['active']` to `((int)$record['active']) === 1 ? 0 : 1`
  - Resolves issue where toggle would fail due to PHP's falsy evaluation of string '0'
- **Master Data Form Labels**: Fixed singular/plural naming in form titles
  - Added `singular` key to all 9 master data type configurations
  - Prevents incorrect labels like "Add New Medical Specialtie" (now shows "Add New Medical Specialty")
- **Form Submission on Cloudron**: Added trailing slashes to form action URLs to prevent Apache 301 redirects that strip POST data

### Added (Cloudron Production Only - aps.sbvu.ac.in branch)
- **Master Data Wrapper Files**: Created 84 wrapper index.php files for all CRUD actions
  - Actions: list, create, store, edit, update, delete, toggleActive
  - Supports all 9 master data types + 3 legacy symlinked names
  - Enables proper routing without Nginx configuration changes
- **Apache .htaccess Routing**: Updated routing rules for Cloudron LAMP stack
  - Routes `/masterdata/{action}/{type}/{id}/` to appropriate wrapper files
  - Excludes `/masterdata/` from trailing slash removal to prevent redirects
  - Bypasses front controller for masterdata routes

### Technical Details
- Session management now properly initialized before CSRF token generation
- Database active field toggle now handles both string and integer values correctly
- Cloudron deployment uses physical directory structure due to read-only Nginx config
- All changes backward compatible with standard Apache/Nginx setups

## [1.2.0] - 2026-01-12

### Added
- Master Data Management System with CRUD operations for:
  - Catheter Indications
  - Removal Indications  
  - Sentinel Events
  - Medical Specialties
  - Surgical Procedures
  - Comorbidities
  - Drugs
  - Adjuvants
  - Red Flags
- Environment-based configuration (.env files)
- Comprehensive deployment documentation
- Database migration system
- Session handling improvements

### Changed
- Refactored configuration to use 12-Factor App methodology
- Updated database schema with proper foreign key relationships
- Improved error logging and debugging

### Fixed
- MySQL compatibility for migration files
- Lookup table creation during installation
- Session handling on login page

## [1.1.0] - 2025-12-15

### Added
- Patient management system
- Catheter insertion tracking
- Drug regime management
- Functional outcomes monitoring
- User authentication and authorization
- Report generation capabilities

### Security
- Implemented CSRF protection
- Added XSS prevention
- Secure session management
- Password hashing with bcrypt

## [1.0.0] - 2025-11-01

### Added
- Initial release
- Basic patient tracking
- Pain assessment forms
- User management
- Database structure
- Installation wizard

---

## Version Comparison

### Main Branch (v1.2.1)
- Latest stable code
- Works on standard Apache/Nginx configurations
- Suitable for local development and most production environments

### aps.sbvu.ac.in Branch (v1.2.1-cloudron)
- Includes all fixes from main branch
- Additional Cloudron-specific wrapper files and routing
- Designed for Cloudron LAMP stack with read-only Nginx
- Production deployment for Sri Balaji Vidyapeeth University

---

## Migration Guide

### From 1.2.0 to 1.2.1

No database migrations required. Simply update the codebase:

```bash
# Standard deployment
git pull origin main

# Cloudron deployment  
git pull origin aps.sbvu.ac.in
```

The fixes are code-only and backward compatible.

---

## Support

For issues, questions, or contributions:
- GitHub: https://github.com/drjagan/acute-pain-service
- Issues: https://github.com/drjagan/acute-pain-service/issues
