<?php
/**
 * Step 5: Installation Complete
 */

if (!isset($_SESSION['admin_created'])) {
    header('Location: ?step=4');
    exit;
}

// Mark installation as complete
markInstallationComplete();

// Get config
$config = $_SESSION['db_config'];
$adminUsername = $_SESSION['admin_username'] ?? 'admin';

// Clear session
session_destroy();
?>

<div class="text-center">
    <div class="mb-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 80px;"></i>
    </div>
    
    <h3 class="mb-3">
        Installation Completed Successfully!
    </h3>
    
    <p class="text-muted mb-4">
        Your Acute Pain Service Management System is now ready to use.
    </p>
</div>

<!-- Installation Summary -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <strong>Installation Summary</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6 class="text-muted mb-2">Database Configuration</h6>
                <p class="mb-1"><strong>Host:</strong> <?= htmlspecialchars($config['host']) ?></p>
                <p class="mb-1"><strong>Database:</strong> <?= htmlspecialchars($config['database']) ?></p>
                <p class="mb-0"><strong>Username:</strong> <?= htmlspecialchars($config['username']) ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <h6 class="text-muted mb-2">Administrator Account</h6>
                <p class="mb-1"><strong>Username:</strong> <?= htmlspecialchars($adminUsername) ?></p>
                <p class="mb-0"><strong>Role:</strong> Administrator</p>
            </div>
        </div>
    </div>
</div>

<!-- Next Steps -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <strong>
            <i class="bi bi-list-check"></i> Next Steps
        </strong>
    </div>
    <div class="card-body">
        <ol class="mb-0">
            <li class="mb-2">
                <strong>Delete the install folder</strong> for security
                <div class="small text-muted">
                    Run: <code>rm -rf install/</code> or delete it manually
                </div>
            </li>
            <li class="mb-2">
                <strong>Log in to the system</strong> with your admin credentials
            </li>
            <li class="mb-2">
                <strong>Change test user passwords</strong> or delete them
                <div class="small text-muted">
                    Go to Users menu after logging in
                </div>
            </li>
            <li class="mb-2">
                <strong>Configure system settings</strong>
                <div class="small text-muted">
                    Review configuration in config/config.php
                </div>
            </li>
            <li class="mb-0">
                <strong>Start using the system!</strong>
                <div class="small text-muted">
                    Begin by creating patients, catheters, and recording data
                </div>
            </li>
        </ol>
    </div>
</div>

<!-- Test Users Information -->
<div class="alert alert-info">
    <h6 class="mb-2">
        <i class="bi bi-people"></i> Test User Accounts
    </h6>
    <p class="mb-2">The following test accounts are available (password: <code>admin123</code>):</p>
    <ul class="mb-0">
        <li><strong>admin</strong> - System Administrator</li>
        <li><strong>dr.sharma</strong> - Attending Physician</li>
        <li><strong>dr.patel</strong> - Resident</li>
        <li><strong>nurse.kumar</strong> - Nurse</li>
    </ul>
    <p class="mt-2 mb-0 small">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Security Note:</strong> Delete or change passwords for these accounts in production!
    </p>
</div>

<!-- Action Buttons -->
<div class="d-flex justify-content-center gap-3 mt-4">
    <a href="../public/index.php" class="btn btn-install btn-primary btn-lg">
        <i class="bi bi-box-arrow-in-right"></i> Go to Login Page
    </a>
</div>

<!-- Installation Info -->
<div class="mt-4 p-3 bg-light rounded text-center">
    <p class="mb-1 small text-muted">
        <strong>Version:</strong> 1.0.0 | 
        <strong>Installation Date:</strong> <?= date('F j, Y g:i A') ?>
    </p>
    <p class="mb-0 small text-muted">
        <strong>Configuration File:</strong> config/config.php
    </p>
</div>

<hr class="my-4">

<!-- Documentation Links -->
<div class="text-center">
    <h6 class="mb-3">Need Help?</h6>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="../docs/USER_GUIDE.md" class="btn btn-sm btn-outline-primary" target="_blank">
            <i class="bi bi-book"></i> User Guide
        </a>
        <a href="../docs/SELECT2_PATIENT_COMPONENT.md" class="btn btn-sm btn-outline-primary" target="_blank">
            <i class="bi bi-question-circle"></i> Documentation
        </a>
        <a href="../README.md" class="btn btn-sm btn-outline-primary" target="_blank">
            <i class="bi bi-file-text"></i> README
        </a>
    </div>
</div>
