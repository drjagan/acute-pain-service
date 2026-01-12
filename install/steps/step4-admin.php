<?php
/**
 * Step 4: Create Admin User
 */

if (!isset($_SESSION['tables_created'])) {
    safeRedirect('?step=3');
}

$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $firstName = sanitizeInput($_POST['first_name'] ?? '');
    $lastName = sanitizeInput($_POST['last_name'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
        $error = 'All fields are required';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Passwords do not match';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } else {
        $config = $_SESSION['db_config'];
        
        try {
            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $result = createAdminUser($pdo, $username, $email, $password, $firstName, $lastName);
            
            if ($result['success']) {
                $_SESSION['admin_created'] = true;
                $_SESSION['admin_username'] = $username;
                safeRedirect('?step=5');
            } else {
                $error = $result['message'];
            }
            
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<h3 class="mb-4">
    <i class="bi bi-person-badge"></i> Create Admin Account
</h3>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    <strong>Administrator Account</strong>
    <p class="mb-0 mt-2">
        Create your administrator account. This account will have full access to all features and settings.
    </p>
</div>

<?php if ($error): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle"></i>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="POST" action="?step=4" class="needs-validation" novalidate>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="first_name" class="form-label">
                First Name <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control" 
                   id="first_name" 
                   name="first_name" 
                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                   required>
        </div>
        
        <div class="col-md-6 mb-3">
            <label for="last_name" class="form-label">
                Last Name <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control" 
                   id="last_name" 
                   name="last_name" 
                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                   required>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="username" class="form-label">
            Username <span class="text-danger">*</span>
        </label>
        <input type="text" 
               class="form-control" 
               id="username" 
               name="username" 
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               pattern="[a-zA-Z0-9._-]{3,}"
               autocomplete="off"
               required>
        <small class="text-muted">At least 3 characters, letters, numbers, dots, dashes only</small>
    </div>
    
    <div class="mb-3">
        <label for="email" class="form-label">
            Email Address <span class="text-danger">*</span>
        </label>
        <input type="email" 
               class="form-control" 
               id="email" 
               name="email" 
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               autocomplete="off"
               required>
    </div>
    
    <div class="mb-3">
        <label for="password" class="form-label">
            Password <span class="text-danger">*</span>
        </label>
        <input type="password" 
               class="form-control" 
               id="password" 
               name="password" 
               minlength="8"
               autocomplete="new-password"
               required>
        <small class="text-muted">At least 8 characters</small>
    </div>
    
    <div class="mb-4">
        <label for="password_confirm" class="form-label">
            Confirm Password <span class="text-danger">*</span>
        </label>
        <input type="password" 
               class="form-control" 
               id="password_confirm" 
               name="password_confirm" 
               minlength="8"
               autocomplete="new-password"
               required>
    </div>
    
    <div class="alert alert-warning">
        <i class="bi bi-shield-exclamation"></i>
        <strong>Important:</strong> Remember these credentials! You'll need them to log in after installation.
    </div>
    
    <div class="d-flex justify-content-between gap-2">
        <a href="?step=3" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Previous
        </a>
        <button type="submit" class="btn btn-install btn-primary">
            Create Account <i class="bi bi-arrow-right"></i>
        </button>
    </div>
</form>

<script>
// Password match validation
document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    
    if (password !== confirm) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

// Form validation
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>
