# Patient Select2 Component - Documentation

## Overview

The Patient Select2 component provides a searchable, AJAX-powered dropdown for patient selection across the APS application. It shows the latest 5 patients by default and allows real-time searching by patient name or hospital number.

---

## Features

✅ **Latest 5 Patients** - Shows most recently added patients when dropdown opens  
✅ **Search as You Type** - Real-time filtering by name or hospital number  
✅ **AJAX Powered** - Efficient database queries, only loads what's needed  
✅ **Pagination Support** - Handles large patient lists with "load more" functionality  
✅ **Formatted Display** - Shows patient name, hospital number, age, and gender  
✅ **Bootstrap 5 Theme** - Matches application design system  
✅ **Error Handling** - Graceful fallbacks and error messages  
✅ **Debug Logging** - Console logs for troubleshooting

---

## Quick Start

### 1. HTML Markup

Add the `patient-select2` class to any patient selection dropdown:

```html
<div class="mb-3">
    <label for="patient_id" class="form-label">
        Select Patient <span class="text-danger">*</span>
    </label>
    <select class="form-select patient-select2" id="patient_id" name="patient_id" required>
        <option value="">-- Search or select a patient --</option>
    </select>
    <small class="form-text text-muted">
        <i class="bi bi-search"></i> Type to search by name or hospital number
    </small>
</div>
```

### 2. Auto-Initialization

The component automatically initializes on page load. No additional JavaScript needed!

```javascript
// Auto-initialized by app.js on $(document).ready
```

### 3. Manual Initialization (if needed)

```javascript
// Initialize a specific element
window.APS.initPatientSelect2('#patient_id');

// Initialize all elements with class
window.APS.initPatientSelect2('.patient-select2');

// With custom options
window.APS.initPatientSelect2('#patient_id', {
    placeholder: 'Custom placeholder text',
    minimumInputLength: 2  // Require 2 chars before search
});
```

---

## Component Structure

### Backend Files

**Controller:** `src/Controllers/PatientController.php`
- Method: `searchAjax()` (lines 365-453)
- Endpoint: `GET /patients/searchAjax`
- Parameters: `q` (search term), `page` (pagination)

### Frontend Files

**JavaScript:** `public/js/app.js`
- Function: `window.APS.initPatientSelect2(selector, options)`
- Auto-initialization on DOM ready

**CSS:** `public/css/main.css`
- Custom Select2 styling (lines 567-620)
- Bootstrap 5 theme integration

**Layout:** `src/Views/layouts/main.php`
- Loads jQuery, Select2 libraries
- Sets `window.BASE_URL` for AJAX calls

---

## AJAX Endpoint

### Request

```
GET /patients/searchAjax?q=john&page=1
```

**Parameters:**
- `q` (optional) - Search term for patient name or hospital number
- `page` (optional) - Page number for pagination (default: 1)

### Response

```json
{
  "results": [
    {
      "id": "123",
      "text": "John Doe (HN: HN001234) - 45y/male",
      "hospital_number": "HN001234",
      "age": "45",
      "gender": "male",
      "diagnosis": "Post-operative pain"
    }
  ],
  "pagination": {
    "more": false
  }
}
```

### Error Response

```json
{
  "error": "Failed to search patients",
  "message": "Database connection error"
}
```

---

## Configuration Options

All standard Select2 options are supported. Common overrides:

```javascript
window.APS.initPatientSelect2('#patient_id', {
    // Placeholder text
    placeholder: 'Search patients...',
    
    // Allow clearing selection
    allowClear: true,
    
    // Minimum characters before search
    minimumInputLength: 0,  // 0 = show latest 5 on open
    
    // Custom width
    width: '100%',
    
    // Disable search box
    minimumResultsForSearch: Infinity,
    
    // Custom AJAX delay
    ajax: {
        delay: 500  // milliseconds
    }
});
```

---

## Event Handlers

### On Patient Selection

```javascript
$('#patient_id').on('select2:select', function (e) {
    var data = e.params.data;
    console.log('Patient selected:', data);
    console.log('Patient ID:', data.id);
    console.log('Hospital Number:', data.hospital_number);
    console.log('Age:', data.age);
    console.log('Gender:', data.gender);
});
```

### On Clear Selection

```javascript
$('#patient_id').on('select2:clear', function (e) {
    console.log('Selection cleared');
});
```

### On Dropdown Open

```javascript
$('#patient_id').on('select2:open', function (e) {
    console.log('Dropdown opened');
});
```

---

## Debugging

### Enable Console Logging

Console logs are automatically enabled. Open browser DevTools to see:

```
APS Patient Select2: DOM ready, checking for elements...
APS Patient Select2: Found 1 element(s) to initialize
APS Patient Select2: Initializing 1 element(s)
APS Patient Select2: AJAX response {results: Array(5), pagination: {…}}
APS Patient Select2: Successfully initialized element patient_id
```

### Common Issues

**Issue:** Dropdown doesn't open  
**Fix:** Check console for errors. Verify jQuery and Select2 are loaded.

**Issue:** No results shown  
**Fix:** Check AJAX endpoint returns valid JSON. Verify database has patients.

**Issue:** Search not working  
**Fix:** Ensure `window.BASE_URL` is defined. Check network tab for failed requests.

**Issue:** Styling looks wrong  
**Fix:** Verify Bootstrap 5 theme CSS is loaded. Check for CSS conflicts.

---

## Database Query

### Default Query (Latest 5)

```sql
SELECT id, patient_name, hospital_number, age, gender, diagnosis
FROM patients
WHERE deleted_at IS NULL
ORDER BY created_at DESC
LIMIT 5
```

### Search Query

```sql
SELECT id, patient_name, hospital_number, age, gender, diagnosis
FROM patients
WHERE deleted_at IS NULL
AND (patient_name LIKE ? OR hospital_number LIKE ?)
ORDER BY patient_name ASC
LIMIT 10 OFFSET 0
```

---

## Styling Customization

### Override Default Styles

Add to your custom CSS:

```css
/* Change dropdown max height */
.select2-container--bootstrap-5 .select2-dropdown {
    max-height: 400px;
}

/* Customize result formatting */
.select2-patient-result .fw-bold {
    color: #0d6efd;
    font-size: 1rem;
}

/* Change highlighted result color */
.select2-container--bootstrap-5 .select2-results__option--highlighted {
    background-color: #198754;
}
```

---

## Performance

- **Initial Load:** ~100-200ms (loads 5 patients)
- **Search Query:** ~150-300ms (depends on database size)
- **AJAX Delay:** 250ms (debounce prevents excessive queries)
- **Cache:** Enabled (reduces duplicate requests)
- **Pagination:** 10 results per page

---

## Security

✅ **Authentication Required** - `$this->requireAuth()` in controller  
✅ **SQL Injection Prevention** - Prepared statements with bound parameters  
✅ **XSS Prevention** - HTML encoding in templates  
✅ **Soft Deletes** - Only shows active patients (`deleted_at IS NULL`)

---

## Browser Compatibility

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

---

## Migration Guide

### From Static Dropdown

**Before:**
```html
<select class="form-select" name="patient_id">
    <option value="">-- Select Patient --</option>
    <?php foreach ($patients as $patient): ?>
    <option value="<?= $patient['id'] ?>">
        <?= $patient['patient_name'] ?>
    </option>
    <?php endforeach; ?>
</select>
```

**After:**
```html
<select class="form-select patient-select2" name="patient_id">
    <option value="">-- Search or select a patient --</option>
</select>
```

**Benefits:**
- No need to load all patients in controller
- Faster page load
- Better UX for large datasets
- Searchable interface

---

## Testing

### Unit Test AJAX Endpoint

```bash
# Test default query (latest 5)
curl -H "Cookie: PHPSESSID=your_session_id" \
  http://localhost:8000/patients/searchAjax

# Test search query
curl -H "Cookie: PHPSESSID=your_session_id" \
  "http://localhost:8000/patients/searchAjax?q=john"

# Test pagination
curl -H "Cookie: PHPSESSID=your_session_id" \
  "http://localhost:8000/patients/searchAjax?q=john&page=2"
```

### Manual Testing Checklist

- [ ] Dropdown opens on click
- [ ] Shows latest 5 patients by default
- [ ] Search filters results as you type
- [ ] Selection populates hidden input
- [ ] Form submission works correctly
- [ ] Error messages display properly
- [ ] Works on mobile devices
- [ ] Keyboard navigation works (arrow keys, enter)

---

## Future Enhancements

- [ ] Add patient photos/avatars
- [ ] "Add New Patient" button in dropdown
- [ ] Recently selected patients cache
- [ ] Keyboard shortcuts (Ctrl+K to open)
- [ ] Multi-select support
- [ ] Export selected patients
- [ ] Advanced filters (age, gender, diagnosis)

---

## Support

**Documentation:** `/docs/SELECT2_PATIENT_COMPONENT.md`  
**Example Usage:** `src/Views/reports/index.php` (line 30)  
**Example Usage:** `src/Views/catheters/create.php` (line 18)

---

## Version History

**v1.0** (Current)
- Initial implementation
- AJAX search endpoint
- Auto-initialization
- Bootstrap 5 theme
- Debug logging
- Comprehensive error handling

---

## License

Part of the Acute Pain Service Management System  
© 2026 - Internal Use Only
