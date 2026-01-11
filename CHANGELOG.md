# Changelog

All notable changes to the Acute Pain Service Management System will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] - 2026-01-11

### 🔑 Role Enhancement - Admins as Attending Physicians

#### Changed
- **Admin Role Privileges**: All admins now have full attending physician capabilities in addition to their administrative privileges
  - Admins can be assigned to patients as attending physicians
  - Admins appear in the "Attending Physicians" dropdown when creating/editing patients
  - Admins can access the "My Patients" page and dashboard widget
  - Admins receive notifications for their assigned patients (new patient, catheter events)

#### What This Means
- **For Admins**: You now have dual roles - full system administration PLUS attending physician capabilities
- **For Workflow**: Admins can actively participate in patient care while managing the system
- **Role Hierarchy**: All admins are attending physicians, but only some attending physicians are admins

#### Files Modified (8)
1. `src/Views/components/navigation.php` - Added admin to "My Patients" link visibility
2. `src/Controllers/DashboardController.php` - Enabled "My Patients" widget for admins
3. `src/Controllers/PatientController.php` - Added admin access to myPatients() route and physician selection
4. `src/Controllers/CatheterController.php` - Updated notification comments
5. `src/Models/PatientPhysician.php` - Updated model documentation
6. `src/Views/dashboard/index.php` - Added admin to widget visibility
7. `src/Views/patients/create.php` - Updated help text for physician selection
8. `src/Views/patients/edit.php` - Updated help text for physician selection

#### Technical Details
- **No Database Changes**: Existing structure already supports this (user_id based, not role based)
- **Backward Compatible**: Does not affect existing attending physicians or residents
- **Zero Breaking Changes**: All existing functionality remains intact

#### Testing Checklist
- [ ] Login as admin → See "My Patients" link in sidebar
- [ ] Create patient → See admin in "Attending Physicians" dropdown
- [ ] Assign admin to patient → Admin appears in patient's physician list
- [ ] Check notifications → Admin receives notifications for assigned patients
- [ ] Dashboard widget → Shows "My Patients" for admin users

---

## [1.1.0] - 2026-01-11

### 🎉 Major Features Added

#### Patient-Physician Associations
- **Many-to-Many Relationships**: Patients can now be assigned to multiple attending physicians and residents
- **Physician Management**: Create, edit, and sync physician assignments for each patient
- **"My Patients" Dashboard Widget**: Attending physicians and residents see their last 5 assigned patients on the dashboard
- **Primary Physician Designation**: Mark primary attending and primary resident for each patient

#### Real-Time Notifications System
- **In-App Notifications**: Beautiful notification bell icon in header with real-time badge count
- **Notification Dropdown**: Color-coded notification panel with priority indicators
- **Auto-Refresh**: Notifications refresh automatically every 30 seconds
- **Auto-Mark-as-Read**: Notifications auto-mark as read after 10 seconds (configurable)
- **Notification Triggers**:
  - Patient registration (notifies assigned physicians)
  - Catheter insertion (notifies assigned physicians)
  - Catheter removal (notifies assigned physicians, highlights complications)

#### SMTP Email Configuration
- **Admin Settings Panel**: Configure SMTP settings for email notifications
- **Test Email Functionality**: Send test emails to verify configuration
- **Email Queue System**: Notifications can trigger both in-app and email alerts
- **Encryption Support**: TLS, SSL, or no encryption options
- **Email Templates**: Professional HTML email templates with color coding

### 📊 Database Changes

#### New Tables (3)
1. **patient_physicians**: Many-to-many relationship between patients and physicians
   - Fields: patient_id, user_id, physician_type, is_primary, status, assigned_at, unassigned_at
   - Supports primary physician designation
   - Tracks assignment history

2. **notifications**: Comprehensive notification system
   - Fields: user_id, type, title, message, priority, color, icon, is_read, send_email, auto_dismiss
   - Polymorphic relationships (related_type, related_id)
   - Email delivery tracking
   - Expiration support

3. **smtp_settings**: Email server configuration
   - Fields: smtp_host, smtp_port, smtp_encryption, smtp_username, smtp_password
   - Sender configuration (from_email, from_name, reply_to)
   - Testing and debugging options
   - Rate limiting support

### 🎨 UI/UX Improvements

#### Header Updates
- **Notification Bell Icon**: Pulsing badge animation for unread notifications
- **Responsive Dropdown**: Mobile-optimized notification panel (max 380px width)
- **Mark All as Read**: Quick action button in notification header

#### Dashboard Enhancements
- **"My Patients" Widget**: Shows last 5 patients for attending/resident roles
- **Active Catheter Indicators**: Display count of active catheters per patient
- **Quick Access Links**: Direct links to patient records from widget

#### Patient Management
- **Physician Multi-Select**: Select2-powered dropdowns for attending physicians and residents
- **Searchable Physician List**: Type-ahead search for physician assignment
- **Visual Physician Cards**: Display assigned physicians on patient view page

### 🔧 Backend Improvements

#### New Models (3)
- `PatientPhysician.php`: Manages patient-physician relationships
  - `getPhysiciansByPatient()`: Get all physicians for a patient
  - `getPatientsByPhysician()`: Get all patients for a physician
  - `syncPhysicians()`: Bulk assign/unassign physicians
  - `assignPhysician()` / `unassignPhysician()`: Individual operations

- `Notification.php`: Full notification CRUD
  - `getUnreadByUser()`: Get unread notifications
  - `markAsRead()` / `markAllAsRead()`: Mark notifications
  - `notify()` / `notifyMultiple()`: Send notifications
  - `getPendingEmails()`: Email queue management

- `SmtpSettings.php`: SMTP configuration management
  - `getActiveSettings()`: Get current SMTP config
  - `testConnection()`: Test SMTP settings
  - Password encryption/decryption

#### New Controllers (2)
- `NotificationController.php`: AJAX endpoints for notifications
  - `/notifications/getUnread`: Get unread notifications (AJAX)
  - `/notifications/markAsRead/{id}`: Mark notification as read (AJAX)
  - `/notifications/markAllAsRead`: Mark all as read (AJAX)
  - `/notifications/getUnreadCount`: Get count only (AJAX)

- `SettingsController.php`: System settings management
  - `/settings/smtp`: SMTP configuration page (admin only)
  - `/settings/saveSMTP`: Save SMTP settings (POST)
  - `/settings/testSMTP`: Test SMTP connection (AJAX)

#### New Helpers (1)
- `EmailService.php`: Email sending abstraction
  - PHPMailer support (with fallback to PHP mail())
  - `send()`: Send any email
  - `sendTestEmail()`: Test SMTP configuration
  - `sendNotificationEmail()`: Send formatted notification emails

### 📝 Code Quality

#### Updated Controllers (3)
- `PatientController.php`:
  - Added `getPhysiciansForForm()`: Load all active attending/resident users
  - Added `notifyPhysiciansAboutNewPatient()`: Send notifications on patient creation
  - Updated `create()` and `edit()`: Include physician lists in view data
  - Updated `store()` and `update()`: Sync physician assignments

- `CatheterController.php`:
  - Added `notifyPhysiciansAboutCatheterInsertion()`: Notify on catheter insertion
  - Added `notifyPhysiciansAboutCatheterRemoval()`: Notify on catheter removal (with complication detection)
  - Updated `store()`: Trigger insertion notifications
  - Updated `storeRemoval()`: Trigger removal notifications

- `DashboardController.php`:
  - Added `getMyPatients()`: Get patients assigned to current physician
  - Updated `index()`: Include "My Patients" data for attending/resident roles

#### Updated Models (1)
- `Patient.php`:
  - Added `getPatientWithPhysicians()`: Get patient with all assigned physicians
  - Added `getPatientsByPhysician()`: Get patients for a specific physician
  - Added `syncPhysicians()`: Sync physician assignments
  - Added `getPrimaryAttending()` / `getPrimaryResident()`: Get primary physicians

### 🎨 Frontend Updates

#### JavaScript (app.js)
- **Notification System Module**: Complete notification management
  - `APS.Notifications.init()`: Initialize notification system
  - `APS.Notifications.loadNotifications()`: Load notifications via AJAX
  - `APS.Notifications.markAsRead()`: Mark individual notification
  - `APS.Notifications.markAllAsRead()`: Mark all notifications
  - Auto-refresh timer (30 second interval)
  - Auto-mark-as-read timers (10 second delay)
  - Time ago formatting utility
  - HTML escape utility for security

#### CSS (main.css)
- **Notification Styles**: 145+ lines of notification-specific styles
  - Notification badge pulse animation
  - Color-coded notification icons (6 variants)
  - Unread notification highlighting
  - Hover effects and transitions
  - Mobile-responsive breakpoints
  - Empty state styling

### 🔒 Security

- **CSRF Protection**: All POST endpoints protected
- **Role-Based Access**: Admin-only access to SMTP settings
- **Password Encryption**: SMTP passwords encrypted (base64 - upgrade to openssl recommended)
- **HTML Escaping**: All notification content escaped to prevent XSS
- **SQL Injection Prevention**: All queries use prepared statements

### 🚀 Performance

- **AJAX Endpoints**: Lightweight JSON responses for notifications
- **Auto-Refresh Optimization**: 30-second intervals (not real-time to reduce load)
- **Database Indexing**: All foreign keys and frequently queried columns indexed
- **Efficient Queries**: JOINs used to minimize database round-trips

### 📱 Mobile Responsiveness

- **Notification Dropdown**: Adapts to 95vw on mobile devices
- **Touch-Optimized**: Larger touch targets for notification items
- **Smaller Icons**: Reduced icon sizes on small screens
- **Responsive Badge**: Scales appropriately across devices

### 🐛 Bug Fixes

- None (new features only)

### 📚 Documentation

- Updated README.md with v1.1.0 features
- Created CHANGELOG.md for version tracking
- Code comments added to all new methods
- PHPDoc blocks for all public methods

### ⚠️ Breaking Changes

**None** - v1.1.0 is fully backward compatible with v1.0.0

### 🔄 Database Migration Required

**Action Required**: Run the following migrations to upgrade from v1.0.0 to v1.1.0:

```sql
-- Located in src/Database/migrations/
010_create_patient_physicians_table.sql
011_create_notifications_table.sql
012_create_smtp_settings_table.sql
```

**Safe to Run**: All migrations use `CREATE TABLE IF NOT EXISTS`, so they're idempotent.

### 📦 Dependencies

#### Required
- PHP 8.1+ (no change)
- MySQL 8.0+ (no change)
- Bootstrap 5 (no change)
- jQuery 3.6+ (no change)
- Select2 4.1+ (existing)

#### Optional (Recommended)
- **PHPMailer 6.x**: For robust email sending (graceful fallback to PHP mail() if not installed)
  - Install via: `composer require phpmailer/phpmailer`
  - Not required, but highly recommended for production use

### 🎯 Usage Examples

#### Assigning Physicians to a Patient
```php
// In PatientController
$attendingIds = [1, 2]; // User IDs of attending physicians
$residentIds = [3];     // User IDs of residents

$this->patientModel->syncPhysicians(
    $patientId, 
    $attendingIds, 
    $residentIds, 
    $currentUserId
);
```

#### Sending a Notification
```php
// In any controller
$notificationModel = new Notification();

$notificationModel->notify(
    $userId,
    'patient_created',
    'New Patient Assigned',
    'A new patient has been assigned to you.',
    [
        'priority' => 'medium',
        'color' => 'success',
        'icon' => 'bi-user-plus',
        'action_url' => '/patients/viewPatient/' . $patientId,
        'send_email' => true
    ]
);
```

#### Getting "My Patients"
```php
// In DashboardController
$patientModel = new Patient();
$myPatients = $patientModel->getPatientsByPhysician($userId, 5);
```

### 🔮 Roadmap (v1.2.0 and Beyond)

Planned features for future releases:

#### v1.2.0 (Next Release)
- Real-time notifications via WebSockets
- Push notifications for mobile browsers
- Advanced notification filtering
- Notification preferences per user
- Batch physician assignment
- Physician handoff workflow

#### v2.0.0 (Major Release)
- Multi-hospital support
- Advanced analytics dashboard
- API endpoints for external integrations
- Mobile app (iOS/Android)
- Audit trail for all actions
- Advanced reporting with charts

---

## [1.0.0] - 2026-01-11

### Initial Release

#### Features
- **Screen 1**: Patient Registration & Demographics (22 fields)
- **Screen 2**: Catheter Insertion Details (21 catheter types)
- **Screen 3**: Drug Regime Management (6 sections)
- **Screen 4**: Functional Outcomes Assessment (13 fields)
- **Screen 5**: Catheter Removal Documentation (16 fields)
- **Dashboard**: Real-time statistics and alerts
- **Reports**: Individual patient reports and consolidated reports
- **User Management**: Full CRUD for users (admin only)
- **Authentication**: Secure login with role-based access control
- **Responsive Design**: Mobile-optimized interface
- **Select2 Integration**: Searchable patient dropdowns

#### Database
- 11 tables with full relationships
- UTF8MB4 character set
- InnoDB engine with transactions
- Comprehensive indexing

#### Security
- CSRF protection on all forms
- BCrypt password hashing (cost 12)
- Role-based access control (4 roles: admin, attending, resident, nurse)
- Prepared statements throughout
- Session security with timeouts

---

## Version History

- **v1.1.0** (2026-01-11): Notifications & Physician Associations
- **v1.0.0** (2026-01-11): Initial Release

---

**For detailed release notes and upgrade instructions, see [RELEASE_NOTES.md](RELEASE_NOTES.md)**
