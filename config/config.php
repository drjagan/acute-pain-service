<?php
/**
 * Acute Pain Service Application
 * Main Configuration File
 */

// Application Settings
define('APP_NAME', getenv('APP_NAME') ?: 'Acute Pain Service Portal');
define('APP_VERSION', getenv('APP_VERSION') ?: '1.2.0');
define('APP_ENV', getenv('APP_ENV') ?: 'development'); // development, production

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEWS_PATH', SRC_PATH . '/Views');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('EXPORT_PATH', PUBLIC_PATH . '/exports');
define('LOGS_PATH', ROOT_PATH . '/logs');

// URLs (PHP Built-in Server)
define('BASE_URL', 'http://localhost:8000');
define('ASSETS_URL', BASE_URL . '/assets');

// Security
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('REMEMBER_ME_LIFETIME', 2592000); // 30 days in seconds
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_COST', 12); // bcrypt cost factor

// Pagination
define('PER_PAGE', 25);

// Logging
define('LOG_ENABLED', true);
define('LOG_FILE', LOGS_PATH . '/app.log');
define('ERROR_LOG_FILE', LOGS_PATH . '/error.log');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG_FILE);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG_FILE);
}

// Load Database Configuration
require_once __DIR__ . '/database.php';

// Load Constants
require_once __DIR__ . '/constants.php';

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    // Convert namespace separators to directory separators
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});

// Load global helper functions
require_once SRC_PATH . '/Helpers/functions.php';
