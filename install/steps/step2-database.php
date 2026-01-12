<?php
/**
 * Step 2: Database Configuration
 */

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = sanitizeInput($_POST['db_host'] ?? '');
    $port = sanitizeInput($_POST['db_port'] ?? '3306');
    $database = sanitizeInput($_POST['db_name'] ?? '');
    $username = sanitizeInput($_POST['db_user'] ?? '');
    $password = $_POST['db_pass'] ?? '';
    $createDb = isset($_POST['create_db']);
    
    // Test connection
    $connectionTest = testDatabaseConnection($host, $username, $password);
    
    if (!$connectionTest['success']) {
        $error = 'Database connection failed: ' . $connectionTest['message'];
    } else {
        // Create database if requested
        if ($createDb) {
            $dbResult = createDatabase($host, $username, $password, $database);
            if (!$dbResult['success']) {
                $error = 'Failed to create database: ' . $dbResult['message'];
            }
        }
        
        // Test connection to the database
        if (!$error) {
            $dbTest = testDatabaseConnection($host, $username, $password, $database);
            if (!$dbTest['success']) {
                $error = 'Cannot connect to database "' . $database . '": ' . $dbTest['message'];
            } else {
                // Write .env file (database credentials - SENSITIVE)
                $envWritten = writeEnvFile($host, $database, $username, $password, $port);
                
                // Write config.php file (application settings - NON-SENSITIVE)
                $configWritten = writeConfigFile($host, $database, $username, $password, $port);
                
                if ($envWritten && $configWritten) {
                    $_SESSION['db_configured'] = true;
                    $_SESSION['db_config'] = [
                        'host' => $host,
                        'port' => $port,
                        'database' => $database,
                        'username' => $username,
                        'password' => $password
                    ];
                    
                    error_log("[APS Install] Configuration files created: .env and config/config.php");
                    safeRedirect('?step=3');
                } else {
                    if (!$envWritten) {
                        $error = 'Failed to write .env file. Check permissions on root directory.';
                    } else {
                        $error = 'Failed to write configuration file. Check permissions on config/ directory.';
                    }
                }
            }
        }
    }
}

// Default values
$defaultHost = 'localhost';
$defaultPort = '3306';
$defaultDatabase = 'aps_database';
$defaultUsername = 'root';
?>

<h3 class="mb-4">
    <i class="bi bi-database"></i> Database Configuration
</h3>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    <strong>Database Setup</strong>
    <p class="mb-0 mt-2">
        Enter your MySQL/MariaDB database credentials. You can create a new database or use an existing empty one.
    </p>
</div>

<?php if ($error): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle"></i>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="POST" action="?step=2">
    <!-- Database Host -->
    <div class="mb-3">
        <label for="db_host" class="form-label">
            Database Host <span class="text-danger">*</span>
        </label>
        <input type="text" 
               class="form-control" 
               id="db_host" 
               name="db_host" 
               value="<?= htmlspecialchars($_POST['db_host'] ?? $defaultHost) ?>"
               placeholder="localhost or 127.0.0.1"
               required>
        <small class="text-muted">Usually "localhost" or "127.0.0.1"</small>
    </div>
    
    <!-- Database Port -->
    <div class="mb-3">
        <label for="db_port" class="form-label">
            Database Port <span class="text-danger">*</span>
        </label>
        <input type="number" 
               class="form-control" 
               id="db_port" 
               name="db_port" 
               value="<?= htmlspecialchars($_POST['db_port'] ?? $defaultPort) ?>"
               placeholder="3306"
               required>
        <small class="text-muted">Default MySQL/MariaDB port is 3306</small>
    </div>
    
    <!-- Database Name -->
    <div class="mb-3">
        <label for="db_name" class="form-label">
            Database Name <span class="text-danger">*</span>
        </label>
        <input type="text" 
               class="form-control" 
               id="db_name" 
               name="db_name" 
               value="<?= htmlspecialchars($_POST['db_name'] ?? $defaultDatabase) ?>"
               placeholder="aps_database"
               pattern="[a-zA-Z0-9_]+"
               required>
        <small class="text-muted">Only letters, numbers, and underscores allowed</small>
    </div>
    
    <!-- Create Database Option -->
    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" 
                   type="checkbox" 
                   id="create_db" 
                   name="create_db"
                   <?= isset($_POST['create_db']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="create_db">
                Create database if it doesn't exist
            </label>
        </div>
        <small class="text-muted">Your database user must have CREATE DATABASE privileges</small>
    </div>
    
    <hr class="my-4">
    
    <!-- Database Username -->
    <div class="mb-3">
        <label for="db_user" class="form-label">
            Database Username <span class="text-danger">*</span>
        </label>
        <input type="text" 
               class="form-control" 
               id="db_user" 
               name="db_user" 
               value="<?= htmlspecialchars($_POST['db_user'] ?? $defaultUsername) ?>"
               autocomplete="off"
               required>
    </div>
    
    <!-- Database Password -->
    <div class="mb-4">
        <label for="db_pass" class="form-label">
            Database Password
        </label>
        <input type="password" 
               class="form-control" 
               id="db_pass" 
               name="db_pass" 
               autocomplete="new-password">
        <small class="text-muted">Leave blank if no password is set</small>
    </div>
    
    <!-- Navigation -->
    <div class="d-flex justify-content-between gap-2">
        <a href="?step=1" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Previous
        </a>
        <button type="submit" class="btn btn-install btn-primary">
            Test Connection & Continue <i class="bi bi-arrow-right"></i>
        </button>
    </div>
</form>

<div class="mt-4 p-3 bg-light rounded">
    <h6 class="mb-2">
        <i class="bi bi-lightbulb"></i> Database Setup Tips
    </h6>
    <ul class="small mb-0">
        <li>You can create the database beforehand using phpMyAdmin or MySQL Workbench</li>
        <li>Make sure the database user has permissions: SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP</li>
        <li>For production, create a dedicated database user instead of using root</li>
        <li>The database must use UTF8MB4 character set for full Unicode support</li>
    </ul>
</div>
