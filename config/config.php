<?php
/**
 * Acute Pain Service Application
 * Main Configuration File
 * 
 * Production Deployment: Cloudron at aps.sbvu.ac.in
 */

// Load environment variables from .env
require_once __DIR__ . '/env-loader.php';
loadEnv(dirname(__DIR__));

// Application Settings
define('APP_NAME', env('APP_NAME', 'Acute Pain Service'));
define('APP_VERSION', env('APP_VERSION', '1.1.3'));
define('APP_ENV', env('APP_ENV', 'production'));

// Paths - Cloudron specific
// In Cloudron: /app/data/public is the web root
// So ROOT_PATH should be /app/data
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEWS_PATH', SRC_PATH . '/Views');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('EXPORT_PATH', PUBLIC_PATH . '/exports');
define('LOGS_PATH', ROOT_PATH . '/logs');

// URLs - Dynamic detection for Cloudron
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'aps.sbvu.ac.in';

// For Cloudron, the app is at root domain (not in /public subdirectory)
define('BASE_URL', $protocol . '://' . $host);
define('ASSETS_URL', BASE_URL . '/assets');

// Date Formats
define('DATE_FORMAT_DISPLAY', 'd M Y');
define('DATETIME_FORMAT_DISPLAY', 'd M Y H:i');
define('DATE_FORMAT_INPUT', 'Y-m-d');
define('DATETIME_FORMAT_INPUT', 'Y-m-d H:i:s');

// Pagination
define('PER_PAGE', (int)env('PER_PAGE', 20));

// Logging
define('LOG_ENABLED', env('LOG_ENABLED', true));
define('LOG_FILE', LOGS_PATH . '/app.log');
define('LOG_LEVEL', env('LOG_LEVEL', 'INFO'));

// Security
define('SESSION_LIFETIME', (int)env('SESSION_LIFETIME', 7200));
define('PASSWORD_MIN_LENGTH', (int)env('PASSWORD_MIN_LENGTH', 8));
define('MAX_LOGIN_ATTEMPTS', (int)env('MAX_LOGIN_ATTEMPTS', 5));
define('LOGIN_TIMEOUT', (int)env('LOGIN_TIMEOUT', 900));

// File Upload
define('MAX_UPLOAD_SIZE', (int)env('MAX_UPLOAD_SIZE', 5242880));
$allowedExts = env('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,pdf,doc,docx');
define('ALLOWED_EXTENSIONS', explode(',', $allowedExts));

// Timezone
date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Kolkata'));

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/php-errors.log');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/php-errors.log');
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
