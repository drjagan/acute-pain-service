# v1.1.0 Testing Guide - Complete Walkthrough

## Quick Access Links
- **Application:** http://localhost:8000/
- **Dashboard:** http://localhost:8000/dashboard
- **Create Patient:** http://localhost:8000/patients/create
- **SMTP Settings:** http://localhost:8000/settings/smtp (admin only)

---

## Test Credentials

```
Admin:
  Username: admin
  Password: admin123
  
Attending Physician:
  Username: dr.sharma
  Password: admin123
  
Resident:
  Username: dr.patel
  Password: admin123
  
Nurse:
  Username: nurse.kumar
  Password: admin123
```

---

## Test Scenario 1: Physician Assignment (15 minutes)

### Step 1: Create Patient with Physicians

1. **Login** as `admin` or `dr.sharma`
2. Go to **Patients** → **Create New Patient**
3. **Scroll down** to find the new **"Assign Physicians (Optional)"** section (Green card)
4. Fill required patient fields:
   - Patient Name: `Test Patient v1.1`
   - Hospital Number: `TEST-V11-001`
   - Age: `45`
   - Gender: Male
   - Height: `170` cm
   - Weight: `75` kg
   - Speciality: General Surgery
   - Diagnosis: `Test diagnosis for v1.1`
   - ASA Status: ASA II

5. **In the "Assign Physicians" section:**
   - Click the **"Attending Physicians"** dropdown
   - You should see Select2 with searchable list
   - Select **"Rajesh Sharma"** (or any attending)
   - Click the **"Residents"** dropdown
   - Select **"Priya Patel"** (or any resident)
   
6. **Save** the patient

### Expected Results:
✓ Patient created successfully  
✓ Redirected to patient view page  
✓ See **"Assigned Physicians"** section showing:
  - Attending Physicians (left column)
  - Residents (right column)
  - Primary badge on first selected physician  
✓ **Check notification bell** - assigned physicians should have received notifications

---

## Test Scenario 2: Edit Patient Physicians (5 minutes)

### Step 1: Edit Existing Patient

1. Go to **Patients** → Select any patient → **Edit**
2. **Scroll down** to **"Assign Physicians"** section
3. Notice **pre-selected physicians** (if already assigned)
4. **Change the selection:**
   - Add more physicians
   - Or remove some
   - Or change the order (first is primary)
5. **Save**

### Expected Results:
✓ Patient updated successfully  
✓ View page shows updated physician list  
✓ Primary designation updated based on order  

---

## Test Scenario 3: "My Patients" Widget (5 minutes)

### Step 1: View as Physician

1. **Logout** (if logged in as admin)
2. **Login as** `dr.sharma` (attending) or `dr.patel` (resident)
3. Go to **Dashboard**
4. **Look for** the green **"My Patients"** card at the top

### Expected Results:
✓ "My Patients" widget visible (only for attending/resident roles)  
✓ Shows assigned patients in a table  
✓ Displays:
  - Patient Name
  - Hospital Number
  - Age/Gender
  - Specialty
  - Active Catheters count
  - Assigned date
  - "View" button  
✓ Click "View" → goes to patient detail page

### If Widget Not Showing:
- Make sure you're logged in as `dr.sharma` or `dr.patel` (not admin or nurse)
- Make sure you've assigned patients to this physician (see Scenario 1)
- Refresh the page

---

## Test Scenario 4: Notifications System (10 minutes)

### Step 1: Check Existing Notification

1. **Login as** `dr.sharma`
2. **Look at the header** (top-right)
3. See the **bell icon** with a badge showing `1` (from test notification)
4. **Click the bell** → dropdown opens
5. See the test notification created during migration

### Expected Results:
✓ Bell icon shows badge with number  
✓ Dropdown shows notifications  
✓ Color-coded (info = blue, success = green, etc.)  
✓ Shows time ago ("Just now", "2h ago", etc.)  
✓ "Mark all as read" button visible  

### Step 2: Generate New Notification

1. **Create a new patient** (as admin)
2. **Assign physicians** to this patient
3. **Save the patient**
4. **Logout and login as** the assigned physician (`dr.sharma`)
5. **Check the bell icon** → should show `2` or more
6. **Click the bell** → see new notification about patient assignment

### Expected Results:
✓ New notification appears  
✓ Title: "New Patient Assigned"  
✓ Message mentions patient name and who assigned it  
✓ Green color (success)  
✓ "View Patient" button in notification  
✓ Click "View Patient" → goes to patient page  

### Step 3: Auto-Mark-as-Read

1. With notification dropdown open
2. **Wait 10 seconds** (don't close dropdown)
3. Notification should **fade or change style** (marked as read)
4. **Refresh page** → badge count decreases

### Step 4: Mark All as Read

1. Open notification dropdown
2. Click **"Mark all as read"**
3. All notifications change to "read" style
4. Badge disappears from bell icon

---

## Test Scenario 5: Catheter Notifications (10 minutes)

### Step 1: Insert Catheter

1. **Login as** `admin` or `dr.sharma`
2. Go to **Catheters** → **Insert Catheter**
3. Select a patient **with assigned physicians**
4. Fill catheter details:
   - Category: Epidural
   - Type: Lumbar Epidural
   - Date of insertion: Today
   - Fill other required fields
5. **Save**

### Expected Results:
✓ Catheter inserted successfully  
✓ **Assigned physicians receive notification**  
✓ Notification title: "Catheter Inserted"  
✓ Blue/info color  
✓ Shows catheter type and patient name  

### Step 2: Remove Catheter

1. Go to the inserted catheter
2. Click **"Document Removal"**
3. Fill removal details:
   - Indication: `Adequate pain control`
   - Date of removal: Today
   - Duration: (auto-calculated)
   - Complications: Leave blank OR add text
4. **Save**

### Expected Results:
✓ Removal documented  
✓ **Physicians receive notification**  
✓ If NO complications: Green "Catheter Removed" notification  
✓ If complications: Yellow "Catheter Removed - Complications" warning  

---

## Test Scenario 6: SMTP Settings (Admin Only) (5 minutes)

### Step 1: Access Settings

1. **Login as** `admin`
2. Go to: http://localhost:8000/settings/smtp
3. See the SMTP configuration page

### Expected Results:
✓ Form with all SMTP fields visible  
✓ Current configuration shown (from migration)  
✓ Sidebar shows:
  - Email Status: Disabled (default)
  - PHPMailer Status: Not Installed (unless you ran composer)  

### Step 2: Update Settings (Optional)

1. Fill in your email provider details:
   - **Gmail Example:**
     - Host: `smtp.gmail.com`
     - Port: `587`
     - Encryption: TLS
     - Username: `your-email@gmail.com`
     - Password: `your-app-password` (not regular password!)
     - From Email: `noreply@yourapp.com`
     - From Name: `APS System`
2. Check **"Enable Email Notifications"**
3. Click **"Save Settings"**

### Step 3: Test Email (Optional - Requires PHPMailer)

1. Enter test email address
2. Click **"Send Test Email"**
3. Check your inbox

**Note:** Email functionality requires PHPMailer:
```bash
composer require phpmailer/phpmailer
```

---

## Test Scenario 7: Patient View - Physicians Display (2 minutes)

### Step 1: View Patient with Physicians

1. Go to any patient that has assigned physicians
2. Scroll to **"Assigned Physicians"** section (Green card, after Demographics)
3. See two columns:
   - Left: Attending Physicians
   - Right: Residents

### Expected Results:
✓ Physicians shown with names  
✓ Primary physician has ⭐ "Primary" badge  
✓ Shows email addresses  
✓ Status badge (Active/Inactive)  
✓ "Edit Physicians" button at bottom (for attending/admin)  

---

## Troubleshooting

### Issue: Physician Dropdowns Not Showing
**Solution:** 
- Make sure you're logged in as admin, attending, or resident
- Check browser console for JavaScript errors
- Verify Select2 is loaded (check page source)

### Issue: "My Patients" Widget Not Showing
**Solution:**
- Only shows for **attending** and **resident** roles
- Only shows if physician has **assigned patients**
- Login as `dr.sharma` and assign a patient to yourself first

### Issue: Notifications Not Appearing
**Solution:**
- Check browser console for AJAX errors
- Verify notification bell icon is visible in header
- Create a test patient with assigned physicians to trigger notification
- Check database: `SELECT * FROM notifications WHERE user_id = 2;`

### Issue: Select2 Dropdowns Look Plain
**Solution:**
- Clear browser cache
- Check if Select2 CSS is loading
- Open browser console, check for 404 errors
- Verify layout includes Select2 CDN links

### Issue: Email Not Sending
**Solution:**
- PHPMailer is optional - emails won't send without it
- Install: `composer require phpmailer/phpmailer`
- Configure SMTP settings at /settings/smtp
- Use Gmail App Password (not regular password)
- Check "Enable Email Notifications" checkbox

---

## Browser Console Checks

Open browser Developer Tools (F12) and check:

1. **Console Tab:**
   - Should see: "APS Mobile-Responsive Features Initialized"
   - Should see: "APS Notifications: Initialized"
   - Should NOT see red errors

2. **Network Tab:**
   - When clicking bell icon, see AJAX request to `/notifications/getUnread`
   - Should return JSON with notifications array
   - Status: 200 OK

3. **Application Tab → Local Storage:**
   - Check if any notification-related data is stored

---

## Database Verification Queries

```sql
-- Check physician assignments
SELECT pp.*, 
       p.patient_name, 
       u.first_name, u.last_name, u.role
FROM patient_physicians pp
JOIN patients p ON pp.patient_id = p.id
JOIN users u ON pp.user_id = u.id
WHERE pp.status = 'active';

-- Check notifications
SELECT id, user_id, type, title, message, is_read, created_at
FROM notifications
ORDER BY created_at DESC
LIMIT 10;

-- Check unread count for user
SELECT user_id, COUNT(*) as unread_count
FROM notifications
WHERE is_read = 0
GROUP BY user_id;

-- Check SMTP settings
SELECT id, smtp_host, smtp_port, from_email, is_active
FROM smtp_settings;
```

---

## Success Criteria Checklist

Use this checklist to verify all features:

### Physician Assignment
- [ ] Attending physician dropdown shows in create form
- [ ] Resident dropdown shows in create form
- [ ] Dropdowns are searchable (Select2)
- [ ] Can select multiple physicians
- [ ] Physicians show in edit form with pre-selected values
- [ ] Can update physician assignments
- [ ] Physicians display on patient view page
- [ ] Primary physician marked with star badge

### Notifications
- [ ] Bell icon visible in header
- [ ] Badge shows unread count
- [ ] Clicking bell opens dropdown
- [ ] Notifications are color-coded
- [ ] Shows time ago
- [ ] "Mark all as read" works
- [ ] Individual notifications can be marked read
- [ ] Notifications auto-mark-as-read after 10s
- [ ] "View Details" button works

### My Patients Widget
- [ ] Widget shows for attending physicians
- [ ] Widget shows for residents
- [ ] Widget does NOT show for admin/nurse
- [ ] Shows last 5 assigned patients
- [ ] Table displays all patient info correctly
- [ ] Active catheter count shows
- [ ] "View" button works

### Notification Triggers
- [ ] Patient creation triggers notification
- [ ] Catheter insertion triggers notification
- [ ] Catheter removal triggers notification
- [ ] Complication removal shows warning color

### SMTP Settings (Admin)
- [ ] Settings page accessible by admin
- [ ] Shows current configuration
- [ ] Can update settings
- [ ] Shows PHPMailer status
- [ ] Test email button works (if PHPMailer installed)

---

## Performance Testing

1. **Page Load Times:**
   - Dashboard with "My Patients": Should load in < 2 seconds
   - Patient create form: Should load in < 1 second
   - Notification dropdown: Should open instantly

2. **AJAX Response Times:**
   - Get notifications: Should respond in < 500ms
   - Mark as read: Should respond in < 300ms

3. **Select2 Performance:**
   - Dropdown should open instantly
   - Search should filter quickly
   - Should handle 100+ users without lag

---

## Known Limitations & Future Enhancements

### Current Limitations:
1. Notifications refresh every 30 seconds (not real-time WebSocket)
2. Email requires PHPMailer (not included by default)
3. "My Patients" limited to last 5 (no pagination)
4. No notification preferences per user yet

### Planned for v1.2.0:
1. Real-time notifications via WebSockets
2. User notification preferences
3. Advanced filtering for "My Patients"
4. Push notifications for mobile browsers
5. Notification categories and muting
6. Batch physician operations

---

## Contact & Support

If you encounter issues during testing:

1. Check browser console for errors
2. Check database tables exist (patient_physicians, notifications, smtp_settings)
3. Verify migrations ran successfully
4. Clear browser cache and reload
5. Check server logs for PHP errors

---

**Testing Complete! All v1.1.0 features should now be fully functional.**

Last Updated: 2026-01-11
Version: 1.1.0
