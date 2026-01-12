<?php
/**
 * Step 3: Create Database Tables
 */

if (!isset($_SESSION['db_configured'])) {
    safeRedirect('?step=2');
}

$error = null;
$migrationResults = [];
$seedResults = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("[APS Install] Step 3: Starting table creation");
    
    $config = $_SESSION['db_config'];
    
    try {
        error_log("[APS Install] Connecting to database: {$config['database']}@{$config['host']}");
        
        // Connect to database
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4",
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 10
            ]
        );
        
        error_log("[APS Install] Database connection established");
        
        // Run migrations
        $migrationsPath = dirname(dirname(__DIR__)) . '/src/Database/migrations';
        error_log("[APS Install] Migrations path: $migrationsPath");
        
        if (!is_dir($migrationsPath)) {
            $error = "Migrations directory not found: $migrationsPath";
            error_log("[APS Install] ERROR: $error");
        } else {
            $migrationResult = runMigrations($pdo, $migrationsPath);
            
            if (!$migrationResult['success']) {
                $error = 'Migration failed: ' . $migrationResult['message'];
                error_log("[APS Install] Migration error: $error");
            } else {
                $migrationResults = $migrationResult['results'];
                error_log("[APS Install] Migrations completed: " . count($migrationResults) . " files");
                
                // Run seed data
                $seedsPath = dirname(dirname(__DIR__)) . '/src/Database/seeds';
                error_log("[APS Install] Seeds path: $seedsPath");
                
                if (!is_dir($seedsPath)) {
                    error_log("[APS Install] WARNING: Seeds directory not found, skipping");
                    $_SESSION['tables_created'] = true;
                    $success = true;
                } else {
                    $seedResult = runSeeds($pdo, $seedsPath);
                    
                    if (!$seedResult['success']) {
                        $error = 'Seed data failed: ' . $seedResult['message'];
                        error_log("[APS Install] Seed error: $error");
                    } else {
                        $seedResults = $seedResult['results'];
                        error_log("[APS Install] Seeds completed: " . count($seedResults) . " files");
                        $_SESSION['tables_created'] = true;
                        $success = true;
                    }
                }
            }
        }
        
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')';
        error_log("[APS Install] PDO Exception: $error");
        error_log("[APS Install] Stack trace: " . $e->getTraceAsString());
    } catch (Exception $e) {
        $error = 'General error: ' . $e->getMessage();
        error_log("[APS Install] Exception: $error");
        error_log("[APS Install] Stack trace: " . $e->getTraceAsString());
    }
}
?>

<h3 class="mb-4">
    <i class="bi bi-table"></i> Create Database Tables
</h3>

<?php if (!$_SERVER['REQUEST_METHOD'] === 'POST' || $error): ?>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    <strong>Database Structure Setup</strong>
    <p class="mb-0 mt-2">
        This step will create all necessary tables and populate them with initial data including:
    </p>
    <ul class="mt-2 mb-0">
        <li>User authentication tables</li>
        <li>Patient and clinical data tables</li>
        <li>Catheter, drug regime, and outcome tables</li>
        <li>Lookup tables for dropdowns</li>
        <li>Test users for each role</li>
    </ul>
</div>

<?php if ($error): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle"></i>
    <strong>Installation Error</strong>
    <p class="mb-0 mt-2"><?= htmlspecialchars($error) ?></p>
</div>

<div class="card border-warning mb-3">
    <div class="card-header bg-warning bg-opacity-10">
        <strong><i class="bi bi-bug"></i> Debug Information</strong>
    </div>
    <div class="card-body">
        <p><strong>Migrations Path:</strong> <code><?= htmlspecialchars(dirname(dirname(__DIR__)) . '/src/Database/migrations') ?></code></p>
        <p><strong>Seeds Path:</strong> <code><?= htmlspecialchars(dirname(dirname(__DIR__)) . '/src/Database/seeds') ?></code></p>
        <p><strong>Log File:</strong> <code><?= htmlspecialchars(dirname(dirname(__DIR__)) . '/logs/install.log') ?></code></p>
        <p class="mb-0 small text-muted">Check the log file for detailed error messages.</p>
    </div>
</div>
<?php endif; ?>

<form method="POST" action="?step=3">
    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>Ready to Install</strong>
        </div>
        <div class="card-body">
            <p class="mb-3">
                <i class="bi bi-check-circle text-success"></i> Database connection verified
            </p>
            <p class="mb-0">
                <strong>Database:</strong> <?= htmlspecialchars($_SESSION['db_config']['database']) ?>
            </p>
            <p class="mb-0">
                <strong>Host:</strong> <?= htmlspecialchars($_SESSION['db_config']['host']) ?>
            </p>
        </div>
    </div>
    
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Warning:</strong> This will create new tables in the database. 
        If tables already exist, this may fail or overwrite existing data.
    </div>
    
    <div class="d-flex justify-content-between gap-2">
        <a href="?step=2" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Previous
        </a>
        <button type="submit" class="btn btn-install btn-primary">
            <i class="bi bi-play-circle"></i> Create Tables & Load Data
        </button>
    </div>
</form>

<?php else: ?>

<!-- Success Display -->
<div class="alert alert-success">
    <i class="bi bi-check-circle"></i>
    <strong>Tables Created Successfully!</strong>
</div>

<!-- Migration Results -->
<div class="card mb-3">
    <div class="card-header bg-light">
        <strong>Database Migrations</strong>
    </div>
    <div class="list-group list-group-flush">
        <?php foreach ($migrationResults as $result): ?>
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-file-earmark-code text-primary"></i>
                <?= htmlspecialchars($result['file']) ?>
            </span>
            <span class="badge bg-success">✓ Success</span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Seed Results -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <strong>Seed Data</strong>
    </div>
    <div class="list-group list-group-flush">
        <?php foreach ($seedResults as $result): ?>
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-database-fill-add text-success"></i>
                <?= htmlspecialchars($result['file']) ?>
            </span>
            <span class="badge bg-success">✓ Success</span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    <strong>Test Users Created</strong>
    <p class="mb-2 mt-2">The following test users have been created (all with password: <code>admin123</code>):</p>
    <ul class="mb-0">
        <li><strong>admin</strong> - System Administrator</li>
        <li><strong>dr.sharma</strong> - Attending Physician</li>
        <li><strong>dr.patel</strong> - Resident</li>
        <li><strong>nurse.kumar</strong> - Nurse</li>
    </ul>
    <p class="mt-2 mb-0 small text-muted">
        You will create your own admin account in the next step.
    </p>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="?step=4" class="btn btn-install btn-primary">
        Next Step <i class="bi bi-arrow-right"></i>
    </a>
</div>

<?php endif; ?>
