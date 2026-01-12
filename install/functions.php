<?php
/**
 * Installation Helper Functions
 */

/**
 * Check if PHP version meets requirements
 */
function checkPhpVersion() {
    return version_compare(PHP_VERSION, '8.1.0', '>=');
}

/**
 * Check if required PHP extensions are loaded
 */
function checkRequiredExtensions() {
    $required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'curl'];
    $loaded = [];
    $missing = [];
    
    foreach ($required as $ext) {
        if (extension_loaded($ext)) {
            $loaded[] = $ext;
        } else {
            $missing[] = $ext;
        }
    }
    
    return [
        'loaded' => $loaded,
        'missing' => $missing,
        'all_loaded' => empty($missing)
    ];
}

/**
 * Check if directories are writable
 */
function checkWritableDirectories() {
    $baseDir = dirname(__DIR__);
    $directories = [
        'config' => $baseDir . '/config',
        'logs' => $baseDir . '/logs',
        'uploads' => $baseDir . '/public/uploads',
        'exports' => $baseDir . '/public/exports'
    ];
    
    $results = [];
    
    foreach ($directories as $name => $path) {
        $writable = is_dir($path) && is_writable($path);
        $results[$name] = [
            'path' => $path,
            'writable' => $writable
        ];
    }
    
    return $results;
}

/**
 * Test database connection
 */
function testDatabaseConnection($host, $username, $password, $database = null) {
    try {
        error_log("[APS Install] Testing database connection to $host as $username");
        
        $dsn = "mysql:host=$host";
        if ($database) {
            $dsn .= ";dbname=$database";
        }
        
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        
        error_log("[APS Install] Database connection successful");
        
        return [
            'success' => true,
            'message' => 'Connection successful',
            'pdo' => $pdo
        ];
    } catch (PDOException $e) {
        error_log("[APS Install] Database connection failed: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ];
    }
}

/**
 * Create database if it doesn't exist
 */
function createDatabase($host, $username, $password, $database) {
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` 
                    CHARACTER SET utf8mb4 
                    COLLATE utf8mb4_unicode_ci");
        
        return [
            'success' => true,
            'message' => "Database '$database' created successfully"
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Run SQL migration files
 */
function runMigrations($pdo, $migrationsPath) {
    try {
        error_log("[APS Install] Starting migrations from: $migrationsPath");
        
        $migrationFiles = glob($migrationsPath . '/*.sql');
        
        if (empty($migrationFiles)) {
            error_log("[APS Install] ERROR: No migration files found in $migrationsPath");
            return [
                'success' => false,
                'message' => "No migration files found in $migrationsPath"
            ];
        }
        
        sort($migrationFiles); // Run in order
        error_log("[APS Install] Found " . count($migrationFiles) . " migration files");
        
        $results = [];
        
        foreach ($migrationFiles as $file) {
            $filename = basename($file);
            error_log("[APS Install] Running migration: $filename");
            
            $sql = file_get_contents($file);
            
            if (empty($sql)) {
                error_log("[APS Install] WARNING: Empty SQL file: $filename");
                continue;
            }
            
            // Split by semicolons but respect SQL delimiters
            $statements = explode(';', $sql);
            $statementCount = 0;
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    try {
                        $pdo->exec($statement);
                        $statementCount++;
                    } catch (PDOException $e) {
                        error_log("[APS Install] ERROR in $filename: " . $e->getMessage());
                        throw $e;
                    }
                }
            }
            
            error_log("[APS Install] ✓ $filename completed ($statementCount statements)");
            
            $results[] = [
                'file' => $filename,
                'status' => 'success',
                'statements' => $statementCount
            ];
        }
        
        error_log("[APS Install] All migrations completed successfully");
        
        return [
            'success' => true,
            'results' => $results
        ];
    } catch (PDOException $e) {
        $errorMsg = "Migration failed in " . ($filename ?? 'unknown') . ": " . $e->getMessage();
        error_log("[APS Install] FATAL ERROR: $errorMsg");
        
        return [
            'success' => false,
            'message' => $errorMsg,
            'file' => $filename ?? 'unknown',
            'error_code' => $e->getCode()
        ];
    }
}

/**
 * Run seed data files
 */
function runSeeds($pdo, $seedsPath) {
    try {
        $seedFiles = glob($seedsPath . '/*.sql');
        sort($seedFiles);
        
        $results = [];
        
        foreach ($seedFiles as $file) {
            $filename = basename($file);
            $sql = file_get_contents($file);
            
            $statements = explode(';', $sql);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
            $results[] = [
                'file' => $filename,
                'status' => 'success'
            ];
        }
        
        return [
            'success' => true,
            'results' => $results
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'file' => $filename ?? 'unknown'
        ];
    }
}

/**
 * Create admin user
 */
function createAdminUser($pdo, $username, $email, $password, $firstName, $lastName) {
    try {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (
                username, email, password_hash, 
                first_name, last_name, role, status
            ) VALUES (?, ?, ?, ?, ?, 'admin', 'active')
        ");
        
        $stmt->execute([
            $username,
            $email,
            $passwordHash,
            $firstName,
            $lastName
        ]);
        
        return [
            'success' => true,
            'message' => 'Admin user created successfully',
            'user_id' => $pdo->lastInsertId()
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Write .env file with database credentials
 * This is the NEW recommended way - stores sensitive data in .env
 */
function writeEnvFile($host, $database, $username, $password, $port = 3306) {
    $envPath = dirname(__DIR__) . '/.env';
    
    // Generate secure random key for APP_KEY
    $appKey = bin2hex(random_bytes(32));
    
    $envContent = "# Acute Pain Service - Environment Configuration
# Generated by Installation Wizard
# Date: " . date('Y-m-d H:i:s') . "
# 
# IMPORTANT: This file contains sensitive credentials
# Do NOT commit this file to version control
# Do NOT share this file publicly

# Database Configuration
DB_HOST=$host
DB_PORT=$port
DB_NAME=$database
DB_USER=$username
DB_PASS=$password
DB_CHARSET=utf8mb4

# Application Configuration
APP_ENV=production
APP_NAME=Acute Pain Service
APP_VERSION=1.1.3
APP_KEY=$appKey

# Session Configuration
SESSION_LIFETIME=3600
SESSION_NAME=APS_SESSION

# Security
PASSWORD_MIN_LENGTH=8
MAX_LOGIN_ATTEMPTS=5
LOGIN_TIMEOUT=900

# Pagination
PER_PAGE=20

# Logging
LOG_ENABLED=true
LOG_LEVEL=INFO

# File Upload
MAX_UPLOAD_SIZE=5242880
ALLOWED_EXTENSIONS=jpg,jpeg,png,pdf,doc,docx

# Timezone
APP_TIMEZONE=Asia/Kolkata

# Email Configuration (Optional - configure later if needed)
SMTP_ENABLED=false
SMTP_HOST=
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USERNAME=
SMTP_PASSWORD=
SMTP_FROM_EMAIL=noreply@example.com
SMTP_FROM_NAME=Acute Pain Service
";
    
    $result = file_put_contents($envPath, $envContent);
    
    if ($result !== false) {
        // Set restrictive permissions (owner read/write only)
        chmod($envPath, 0600);
    }
    
    return $result !== false;
}

/**
 * Write configuration file (NON-SENSITIVE settings only)
 * Database credentials are now in .env file
 */
function writeConfigFile($host, $database, $username, $password, $port = 3306) {
    $configPath = dirname(__DIR__) . '/config/config.php';
    
    $configContent = "<?php
/**
 * Application Configuration
 * Generated by Installation Wizard
 * Date: " . date('Y-m-d H:i:s') . "
 * 
 * NOTE: Database credentials are stored in .env file (root directory)
 * This file contains only non-sensitive application settings
 */

// Environment
define('APP_ENV', 'production');
define('APP_NAME', 'Acute Pain Service');
define('APP_VERSION', '1.1.3');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('SRC_PATH', BASE_PATH . '/src');
define('VIEWS_PATH', SRC_PATH . '/Views');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('EXPORTS_PATH', PUBLIC_PATH . '/exports');
define('LOG_PATH', BASE_PATH . '/logs');

// URLs
\$protocol = (!empty(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
\$host = \$_SERVER['HTTP_HOST'] ?? 'localhost';
\$baseUrl = \$protocol . '://' . \$host . str_replace('/index.php', '', \$_SERVER['SCRIPT_NAME']);
\$baseUrl = rtrim(dirname(\$baseUrl), '/');

define('BASE_URL', \$baseUrl);
define('ASSETS_URL', BASE_URL . '/assets');

// Date Formats
define('DATE_FORMAT_DISPLAY', 'd M Y');
define('DATETIME_FORMAT_DISPLAY', 'd M Y H:i');
define('DATE_FORMAT_INPUT', 'Y-m-d');
define('DATETIME_FORMAT_INPUT', 'Y-m-d H:i:s');

// Pagination
define('PER_PAGE', 20);

// Logging
define('LOG_ENABLED', true);
define('LOG_FILE', LOG_PATH . '/app.log');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Security
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// File Upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOG_PATH . '/php-errors.log');
}
";
    
    $result = file_put_contents($configPath, $configContent);
    
    return $result !== false;
}

/**
 * Create installation complete flag
 */
function markInstallationComplete() {
    $flagFile = dirname(__DIR__) . '/config/.installed';
    $content = "Installation completed on: " . date('Y-m-d H:i:s') . "\n";
    $content .= "Version: 1.0.0\n";
    
    return file_put_contents($flagFile, $content) !== false;
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Safe redirect function that works with output buffering
 */
function safeRedirect($url) {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Send redirect header
    header('Location: ' . $url);
    exit;
}
