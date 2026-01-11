# Release Notes - Version 1.1.1

**Release Date:** January 11, 2026  
**Type:** Minor Enhancement Release  
**Focus:** Admin Role Enhancement

---

## 🎯 What's New in v1.1.1

### Admin Role Enhancement

This release enhances the admin role to include **full attending physician capabilities** in addition to administrative privileges. All admins are now treated as attending physicians with extra system access.

---

## 🔑 Key Changes

### For Administrators

**You can now:**
- ✅ Be assigned to patients as an attending physician
- ✅ Access the "My Patients" page to see your assigned patients
- ✅ View the "My Patients" widget on your dashboard
- ✅ Receive notifications when assigned to patients
- ✅ Get alerts for catheter insertions and removals
- ✅ Appear in physician selection dropdowns when creating/editing patients

**You still have:**
- ✅ Full system administration access
- ✅ User management capabilities
- ✅ SMTP configuration settings
- ✅ System reports and analytics
- ✅ Master data management

### For Other Users

**No changes to your workflow:**
- Attending physicians continue to work as before
- Residents continue to work as before
- Nurses continue to work as before
- All existing features remain unchanged

---

## 📋 What Does This Mean?

### Role Hierarchy

```
ADMIN = ATTENDING PHYSICIAN + EXTRA PRIVILEGES
├── Can do everything an attending physician can
└── PLUS system administration tasks

ATTENDING PHYSICIAN
├── Only some attending physicians are admins
└── Clinical capabilities only (no admin access)
```

### Practical Examples

**Before v1.1.1:**
- Admin creates a patient → Cannot assign self as attending physician
- Admin wants to see their patients → No "My Patients" link
- Patient assigned to admin → Admin receives no notifications

**After v1.1.1:**
- Admin creates a patient → Can select self from "Attending Physicians" dropdown
- Admin clicks "My Patients" → Sees all patients assigned to them
- Patient assigned to admin → Admin gets real-time notifications

---

## 🔧 Technical Details

### Changes Summary

- **Files Modified:** 8
- **Lines Changed:** +21 additions, -13 deletions
- **Database Migrations:** None required
- **Breaking Changes:** None

### Modified Files

1. `src/Views/components/navigation.php` - Navigation visibility
2. `src/Controllers/DashboardController.php` - Dashboard widget logic
3. `src/Controllers/PatientController.php` - Physician assignment logic
4. `src/Controllers/CatheterController.php` - Notification comments
5. `src/Models/PatientPhysician.php` - Model documentation
6. `src/Views/dashboard/index.php` - Widget visibility
7. `src/Views/patients/create.php` - Form help text
8. `src/Views/patients/edit.php` - Form help text

### Why No Database Changes?

The existing database structure already supports this enhancement because:
- `patient_physicians` table uses `user_id` (not role)
- `notifications` table notifies by `user_id` (not role)
- All physician queries use user IDs, not role names

This is purely an **application logic enhancement**.

---

## 📦 Installation & Upgrade

### For New Installations

```bash
git clone https://github.com/drjagan/acute-pain-service.git
cd acute-pain-service
git checkout v1.1.1
# Follow INSTALL.md instructions
```

### Upgrading from v1.1.0

```bash
cd acute-pain-service
git fetch origin
git checkout v1.1.1
# No database migrations required
# No configuration changes needed
```

### Upgrading from v1.0.0

```bash
cd acute-pain-service
git fetch origin
git checkout v1.1.1

# Run v1.1 migrations
php run_migrations_v1.1.php

# Follow TESTING_GUIDE_v1.1.md for verification
```

---

## ✅ Testing Checklist

After upgrading, verify the following:

### As Admin User

1. **Navigation**
   - [ ] See "My Patients" link after Dashboard
   - [ ] See "Settings" link in Administration section

2. **Patient Creation**
   - [ ] Go to Patients → New Patient
   - [ ] Scroll to "Assign Physicians" section
   - [ ] Verify admin appears in "Attending Physicians" dropdown
   - [ ] Select self as attending physician
   - [ ] Save patient
   - [ ] Verify notification received

3. **My Patients Page**
   - [ ] Click "My Patients" in sidebar
   - [ ] See assigned patient(s) in table
   - [ ] Quick stats display correctly
   - [ ] Action buttons work (View, Edit)

4. **Dashboard**
   - [ ] See "My Patients" widget at top (green card)
   - [ ] Widget shows last 5 assigned patients
   - [ ] Active catheter indicators display
   - [ ] Quick links work

5. **Notifications**
   - [ ] Bell icon shows in header
   - [ ] Badge count updates when assigned to patient
   - [ ] Dropdown shows notification details
   - [ ] "Mark all as read" button works

### As Other Roles

1. **Attending Physician**
   - [ ] All features work as before
   - [ ] Can see admins in physician selection dropdowns
   - [ ] No new restrictions

2. **Resident**
   - [ ] All features work as before
   - [ ] No changes to workflow

3. **Nurse**
   - [ ] All features work as before
   - [ ] No changes to workflow

---

## 🐛 Bug Fixes

None - This is a pure enhancement release.

---

## 🔒 Security

No security changes in this release.

---

## 📊 Performance

No performance impact - all changes are at the application logic layer.

---

## 🚀 What's Next?

### Planned for v1.2.0

- Advanced reporting and analytics
- Batch operations for patient management
- Custom notification preferences
- Integration with hospital information systems
- Advanced search and filtering

---

## 📞 Support

**Issues & Questions:**
- GitHub Issues: https://github.com/drjagan/acute-pain-service/issues
- Email: [your-email@example.com]

**Documentation:**
- Installation Guide: `INSTALL.md`
- Testing Guide: `TESTING_GUIDE_v1.1.md`
- Changelog: `CHANGELOG.md`

---

## 👥 Contributors

- Development Team
- Quality Assurance
- Clinical Advisory Board

---

## 📜 License

[Your License Here]

---

## 🎉 Thank You!

Thank you for using the Acute Pain Service Management System. We hope this enhancement improves your workflow and patient care capabilities.

**Version:** 1.1.1  
**Release Date:** January 11, 2026  
**Commit:** 65862b6  
**Tag:** v1.1.1
