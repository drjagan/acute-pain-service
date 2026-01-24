# Agent Development Guide - Acute Pain Service

## Project Overview

**Acute Pain Service (APS) Management System** - A comprehensive PHP/MySQL web application for managing epidural and peripheral nerve catheters in acute postoperative pain control.

**Version:** 1.1.2  
**Location:** `Acute Pain Management 01/acute-pain-service/`  
**Tech Stack:** PHP 8.3+, MySQL 8.0+, Bootstrap 5, Select2, Chart.js

---

## Quick Start

### Development Server

```bash
# Navigate to project directory
cd "Acute Pain Management 01/acute-pain-service"

# Start PHP built-in server
php -S localhost:8000 -t public/

# OR use the provided script
./start-server.sh
```

Access at: http://localhost:8000

**Default Login Credentials:**
- Admin: `admin` / `admin123`
- Attending: `dr.sharma` / `admin123`
- Resident: `dr.patel` / `admin123`
- Nurse: `nurse.kumar` / `admin123`

---

## System Requirements

**Server:**
- PHP 8.1+ (8.3 recommended)
- MySQL 8.0+ or MariaDB 10.5+
- Apache 2.4+ or Nginx 1.18+
- Minimum 256MB RAM

**Required PHP Extensions:**
- `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `json`, `curl`

**Verify Extensions:**
```bash
php -m | grep -E "pdo|mysql|mbstring|openssl|json|curl"
```

---

## Project Structure

```
acute-pain-service/
├── config/
│   ├── config.php           # Main app configuration
│   ├── database.php         # Database connection
│   ├── constants.php        # System constants
│   └── masterdata.php       # Master data configuration
├── src/
│   ├── Controllers/         # 14 controllers (MVC pattern)
│   ├── Models/              # 23 models (database layer)
│   ├── Views/               # 18 views (PHP templates)
│   ├── Helpers/             # 8 helpers (CSRF, Session, Flash, Sanitizer)
│   ├── Middleware/          # Auth middleware
│   ├── Services/            # Business logic (Auth, Email)
│   └── Database/
│       ├── migrations/      # Database migrations
│       └── seeds/           # Seed data
├── public/                  # Web root (document root)
│   ├── index.php           # Front controller (entry point)
│   ├── assets/             # CSS, JS, images
│   ├── uploads/            # User uploaded files
│   └── exports/            # Generated reports
├── install/                 # Installation wizard (delete after install)
├── logs/                    # Application and error logs
├── documentation/           # Comprehensive documentation
├── .env                     # Environment variables (DO NOT COMMIT)
└── .env.example            # Environment template
```

---

## Code Style Guidelines

### PHP Standards

**Coding Standard:** PSR-12 (PHP Framework Interop Group)

**Key Conventions:**
- **Indentation:** 4 spaces (no tabs)
- **Line Length:** 120 characters max (soft limit), 80 preferred
- **Braces:** Opening brace on same line for control structures, next line for classes/functions
- **Naming:**
  - Classes: `PascalCase` (e.g., `AuthController`, `PatientModel`)
  - Methods: `camelCase` (e.g., `findByUsername`, `updateLastLogin`)
  - Properties: `camelCase` (e.g., `$authService`, `$userName`)
  - Constants: `UPPER_SNAKE_CASE` (e.g., `APP_NAME`, `SESSION_LIFETIME`)
  - Database columns: `snake_case` (e.g., `user_id`, `created_at`)
  - Private/protected: Prefix with underscore `_property` (optional)

### File Organization

**Namespaces:**
```php
<?php
namespace Controllers;  // Top-level namespace matches directory

use Services\AuthService;
use Helpers\Flash;
use Helpers\CSRF;
use Helpers\Sanitizer;
```

**Import Order:**
1. Built-in PHP classes
2. Third-party libraries
3. Application namespaces (Services, Models, Helpers)
4. Blank line between groups

### Controller Pattern

```php
<?php
namespace Controllers;

use Services\AuthService;
use Helpers\Flash;
use Helpers\CSRF;
use Helpers\Sanitizer;

/**
 * Authentication Controller
 * Handles login, logout, password reset
 */
class AuthController extends BaseController {
    
    private $authService;
    
    public function __construct() {
        parent::__construct();
        $this->authService = new AuthService();
    }
    
    /**
     * Show login form
     */
    public function login() {
        // Check if already authenticated
        if (isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth.login', [], 'auth');
    }
    
    /**
     * Process login submission
     */
    public function authenticate() {
        // 1. Validate CSRF token (ALWAYS required for POST)
        CSRF::check();
        
        // 2. Sanitize inputs
        $username = Sanitizer::string($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';  // Never sanitize passwords
        
        // 3. Validate required fields
        if (empty($username) || empty($password)) {
            Flash::error('Please provide username and password');
            $this->redirect('/auth/login');
        }
        
        // 4. Attempt authentication
        if ($this->authService->attempt($username, $password)) {
            Flash::success('Welcome back!');
            $this->redirect('/dashboard');
        } else {
            Flash::error('Invalid credentials');
            $this->redirect('/auth/login');
        }
    }
}
```

### Model Pattern

```php
<?php
namespace Models;

/**
 * User Model
 * Handles user database operations
 */
class User extends BaseModel {
    
    protected $table = 'users';
    
    /**
     * Find user by username
     * @param string $username
     * @return array|false User record or false
     */
    public function findByUsername($username) {
        return $this->findBy('username', $username);
    }
    
    /**
     * Update last login timestamp
     * ALWAYS use prepared statements for SQL injection prevention
     */
    public function updateLastLogin($userId, $ip) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET last_login_at = NOW(), last_login_ip = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$ip, $userId]);
    }
    
    /**
     * Get active users with role filtering
     */
    public function getActiveUsers($roleId = null) {
        $sql = "
            SELECT u.*, r.name as role_name 
            FROM {$this->table} u
            JOIN roles r ON u.role_id = r.id
            WHERE u.deleted_at IS NULL
        ";
        
        $params = [];
        if ($roleId !== null) {
            $sql .= " AND u.role_id = ?";
            $params[] = $roleId;
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
```

### View Pattern

```php
<?php
// Views use dot notation: 'auth.login' → src/Views/auth/login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? APP_NAME) ?></title>
    <link href="<?= ASSETS_URL ?>/css/main.css" rel="stylesheet">
</head>
<body>
    <!-- ALWAYS escape output to prevent XSS -->
    <h1><?= htmlspecialchars($heading) ?></h1>
    
    <!-- Use Flash messages for user feedback -->
    <?php if (Flash::has('success')): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars(Flash::get('success')) ?>
        </div>
    <?php endif; ?>
    
    <!-- CSRF token for all forms -->
    <form method="POST" action="/auth/authenticate">
        <?= CSRF::field() ?>
        <input type="text" name="username" required>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
```

---

## Security Guidelines

### Mandatory Security Practices

**1. SQL Injection Prevention - ALWAYS use prepared statements:**
```php
// ✅ GOOD - Parameterized query
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);

// ❌ BAD - String concatenation
$result = $db->query("SELECT * FROM users WHERE id = " . $userId);
```

**2. XSS Prevention - ALWAYS escape HTML output:**
```php
// ✅ GOOD
<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>

// ❌ BAD
<?= $user['name'] ?>
```

**3. CSRF Protection - ALWAYS validate POST requests:**
```php
// In controller
CSRF::check();  // Validates token, exits if invalid

// In form
<?= CSRF::field() ?>  // Generates hidden input
```

**4. Input Sanitization - Use Sanitizer helper:**
```php
use Helpers\Sanitizer;

$username = Sanitizer::string($_POST['username'] ?? '');
$email = Sanitizer::email($_POST['email'] ?? '');
$age = Sanitizer::int($_POST['age'] ?? 0);
// NEVER sanitize passwords - validate only
```

**5. Password Security:**
```php
// Hashing (cost factor 12)
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);

// Verification
if (password_verify($inputPassword, $storedHash)) {
    // Login successful
}
```

**6. Authentication Check:**
```php
// In controllers requiring auth
if (!isAuthenticated()) {
    $this->redirect('/auth/login');
}

// Role-based access
if (!hasRole(['admin', 'attending'])) {
    $this->redirect('/errors/403');
}
```

### Never Commit Sensitive Data

**DO NOT commit to git:**
- `.env` files (contains database credentials)
- `logs/` directory (contains sensitive logs)
- `public/uploads/` (user data)
- `public/exports/` (generated reports)
- Database dumps with real patient data

**Check .gitignore includes:**
```gitignore
.env
logs/*
!logs/.gitkeep
public/uploads/*
!public/uploads/.gitkeep
public/exports/*
!public/exports/.gitkeep
```

---

## Common Tasks

### Adding a New Feature

1. **Create Route** in `public/index.php`:
```php
$router->get('/patients/new', 'PatientController@create');
$router->post('/patients/store', 'PatientController@store');
```

2. **Create Controller Method** in `src/Controllers/PatientController.php`:
```php
public function store() {
    CSRF::check();
    
    $data = [
        'name' => Sanitizer::string($_POST['name'] ?? ''),
        'hospital_no' => Sanitizer::string($_POST['hospital_no'] ?? ''),
        // ... more fields
    ];
    
    // Validate
    if (empty($data['name'])) {
        Flash::error('Name is required');
        $this->redirect('/patients/new');
    }
    
    // Save via model
    $patientId = $this->patientModel->create($data);
    
    Flash::success('Patient created successfully');
    $this->redirect('/patients/' . $patientId);
}
```

3. **Create Model Method** in `src/Models/Patient.php`:
```php
public function create($data) {
    $stmt = $this->db->prepare("
        INSERT INTO patients (name, hospital_no, created_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$data['name'], $data['hospital_no']]);
    return $this->db->lastInsertId();
}
```

4. **Create View** in `src/Views/patients/create.php`:
```php
<form method="POST" action="/patients/store">
    <?= CSRF::field() ?>
    <input type="text" name="name" required>
    <button type="submit">Save</button>
</form>
```

### Adding a Database Table

1. **Create Migration** in `src/Database/migrations/`:
```php
// 2026_01_15_create_alerts_table.php
$sql = "
CREATE TABLE IF NOT EXISTS alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    message TEXT NOT NULL,
    severity ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
```

2. **Run Migration** manually via SQL or create migration runner

3. **Create Model** in `src/Models/Alert.php`

### Debugging

**Enable Debug Mode:**
```php
// In config/config.php
define('APP_ENV', 'development');
```

**View Logs:**
```bash
# Application logs
tail -f logs/app.log

# PHP errors
tail -f logs/error.log
tail -f logs/php-errors.log
```

**Database Query Debugging:**
```php
// In model
error_log("SQL: " . $stmt->queryString);
error_log("Params: " . json_encode($params));
```

---

## Testing & Validation Checklist

Before committing code:

1. ✅ **Security:**
   - [ ] All SQL uses prepared statements
   - [ ] All output is escaped with `htmlspecialchars()`
   - [ ] POST forms include CSRF tokens
   - [ ] User input is sanitized
   - [ ] No sensitive data in code

2. ✅ **Functionality:**
   - [ ] Feature works as expected
   - [ ] Error cases handled gracefully
   - [ ] Flash messages provide clear feedback
   - [ ] Redirects go to correct pages

3. ✅ **Code Quality:**
   - [ ] Follows PSR-12 naming conventions
   - [ ] Methods have docblock comments
   - [ ] No hardcoded values (use constants)
   - [ ] Code is DRY (Don't Repeat Yourself)

4. ✅ **Database:**
   - [ ] Foreign keys defined
   - [ ] Indexes on lookup columns
   - [ ] `deleted_at` for soft deletes
   - [ ] `created_at` and `updated_at` timestamps

---

## Environment Variables

**Required `.env` Configuration:**

```ini
# Application
APP_NAME="Acute Pain Service"
APP_ENV=development    # development | production
APP_DEBUG=true

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=acute_pain_service
DB_USER=root
DB_PASS=your_password_here

# Security
SESSION_LIFETIME=3600          # 1 hour in seconds
REMEMBER_ME_LIFETIME=2592000   # 30 days
PASSWORD_MIN_LENGTH=8
PASSWORD_COST=12               # bcrypt cost factor

# URLs
BASE_URL=http://localhost:8000
```

**Load with:**
```php
require_once __DIR__ . '/config/env-loader.php';
```

---

## Troubleshooting

### Database Connection Failed
```bash
# Check MySQL is running
mysql -u root -p

# Verify credentials in .env
cat .env | grep DB_

# Test connection
php test-env-config.php
```

### CSRF Token Mismatch
- Ensure session is started before CSRF usage
- Check `session.cookie_lifetime` in `php.ini`
- Clear browser cookies

### File Upload Issues
```bash
# Check directory permissions
chmod 755 public/uploads
chmod 755 public/exports

# Check PHP upload settings
php -i | grep upload
```

### Blank Page / No Output
```bash
# Check error logs
tail -f logs/error.log

# Enable display_errors temporarily
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

---

## Key Resources

- **Documentation:** `documentation/README.md`
- **Installation Guide:** `documentation/installation/INSTALL.md`
- **Database Schema:** `documentation/database/`
- **Release Notes:** `documentation/releases/CHANGELOG.md`
