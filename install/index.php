<?php
/**
 * ACUTE PAIN SERVICE - INSTALLATION WIZARD
 * Version 1.0.0
 * 
 * This wizard will guide you through the installation process:
 * 1. Check system requirements
 * 2. Configure database connection
 * 3. Create database and tables
 * 4. Create admin user
 * 5. Finalize installation
 */

// Start output buffering to prevent "headers already sent" errors
ob_start();

session_start();

// Enable error reporting for installation
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/install.log');

// Log installation start
error_log("[APS Install] Installation wizard started - Step: " . ($_GET['step'] ?? 1));

// Configuration
define('APP_ROOT', dirname(__DIR__));
define('INSTALL_COMPLETE_FILE', APP_ROOT . '/config/.installed');

// Check if already installed
if (file_exists(INSTALL_COMPLETE_FILE) && !isset($_GET['reinstall'])) {
    // Clear output buffer before redirect
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Location: ../public/index.php');
    exit('Installation already completed. Delete config/.installed to reinstall.');
}

// Get current step
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Include installation functions
require_once 'functions.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Installation Wizard - Step <?= $step ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .install-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .install-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .install-body {
            padding: 40px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 0 20px;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
            z-index: -1;
        }
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #dee2e6;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .step-label {
            font-size: 12px;
            color: #6c757d;
        }
        .requirement-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .requirement-item:last-child {
            border-bottom: none;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .status-pass {
            background: #d4edda;
            color: #155724;
        }
        .status-fail {
            background: #f8d7da;
            color: #721c24;
        }
        .status-warning {
            background: #fff3cd;
            color: #856404;
        }
        .alert-info {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
        }
        .btn-install {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <!-- Header -->
            <div class="install-header">
                <h1 class="mb-0">
                    <i class="bi bi-heart-pulse"></i> Acute Pain Service
                </h1>
                <p class="mb-0 mt-2">Installation Wizard v1.0.0</p>
            </div>
            
            <!-- Step Indicator -->
            <div class="step-indicator mt-4">
                <div class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">
                    <div class="step-number"><?= $step > 1 ? '<i class="bi bi-check"></i>' : '1' ?></div>
                    <div class="step-label">Requirements</div>
                </div>
                <div class="step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">
                    <div class="step-number"><?= $step > 2 ? '<i class="bi bi-check"></i>' : '2' ?></div>
                    <div class="step-label">Database</div>
                </div>
                <div class="step <?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'completed' : '' ?>">
                    <div class="step-number"><?= $step > 3 ? '<i class="bi bi-check"></i>' : '3' ?></div>
                    <div class="step-label">Tables</div>
                </div>
                <div class="step <?= $step >= 4 ? 'active' : '' ?> <?= $step > 4 ? 'completed' : '' ?>">
                    <div class="step-number"><?= $step > 4 ? '<i class="bi bi-check"></i>' : '4' ?></div>
                    <div class="step-label">Admin User</div>
                </div>
                <div class="step <?= $step >= 5 ? 'active' : '' ?> <?= $step > 5 ? 'completed' : '' ?>">
                    <div class="step-number"><?= $step > 5 ? '<i class="bi bi-check"></i>' : '5' ?></div>
                    <div class="step-label">Complete</div>
                </div>
            </div>
            
            <!-- Body -->
            <div class="install-body">
                <?php
                switch ($step) {
                    case 1:
                        include 'steps/step1-requirements.php';
                        break;
                    case 2:
                        include 'steps/step2-database.php';
                        break;
                    case 3:
                        include 'steps/step3-tables.php';
                        break;
                    case 4:
                        include 'steps/step4-admin.php';
                        break;
                    case 5:
                        include 'steps/step5-complete.php';
                        break;
                    default:
                        // Invalid step, redirect to step 1
                        while (ob_get_level()) {
                            ob_end_clean();
                        }
                        header('Location: ?step=1');
                        exit;
                }
                ?>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-4 text-white">
            <p class="mb-0">&copy; 2026 Acute Pain Service Management System</p>
            <p class="small mb-0">Version 1.0.0</p>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
