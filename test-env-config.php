<?php
/**
 * Test Script: Environment Configuration Loading
 * 
 * This script tests the .env file loading and database configuration.
 * Run this from command line: php test-env-config.php
 */

echo "===========================================\n";
echo "APS Environment Configuration Test\n";
echo "===========================================\n\n";

// Step 1: Check if .env file exists
$envFile = __DIR__ . '/.env';
echo "1. Checking .env file...\n";
if (file_exists($envFile)) {
    echo "   ✓ .env file found: $envFile\n";
    echo "   File size: " . filesize($envFile) . " bytes\n";
    echo "   Permissions: " . substr(sprintf('%o', fileperms($envFile)), -4) . "\n";
} else {
    echo "   ✗ .env file NOT found: $envFile\n";
    echo "   (This is expected if you haven't run the installation wizard yet)\n";
}

echo "\n";

// Step 2: Load environment configuration
echo "2. Loading configuration...\n";
require_once __DIR__ . '/config/env-loader.php';

$loaded = loadEnv(__DIR__);
if ($loaded !== false) {
    echo "   ✓ Configuration loaded successfully\n";
} else {
    echo "   ⚠ Using default configuration (no .env file)\n";
}

echo "\n";

// Step 3: Check database configuration
echo "3. Database Configuration:\n";
require_once __DIR__ . '/config/database.php';

echo "   DB_HOST:     " . DB_HOST . "\n";
echo "   DB_PORT:     " . DB_PORT . "\n";
echo "   DB_NAME:     " . DB_NAME . "\n";
echo "   DB_USER:     " . DB_USER . "\n";
echo "   DB_PASS:     " . (DB_PASS ? str_repeat('*', min(strlen(DB_PASS), 8)) : '(empty)') . "\n";
echo "   DB_CHARSET:  " . DB_CHARSET . "\n";

echo "\n";

// Step 4: Test database connection
echo "4. Testing database connection...\n";
try {
    $pdo = Database::getInstance();
    echo "   ✓ Database connection successful!\n";
    
    // Get database info
    $stmt = $pdo->query("SELECT DATABASE() as db, VERSION() as version");
    $info = $stmt->fetch();
    
    echo "   Connected to: " . $info['db'] . "\n";
    echo "   MySQL version: " . $info['version'] . "\n";
    
    // Count tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   Tables found: " . count($tables) . "\n";
    
    if (count($tables) > 0) {
        echo "   First few tables: " . implode(', ', array_slice($tables, 0, 5)) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Database connection failed!\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 5: Check environment variables
echo "5. Environment Variables Loaded:\n";
$envVars = ['DB_HOST', 'DB_NAME', 'APP_ENV', 'APP_NAME', 'APP_VERSION', 'SESSION_LIFETIME'];
foreach ($envVars as $var) {
    $value = getenv($var);
    if ($value !== false) {
        // Mask sensitive values
        if (strpos($var, 'PASS') !== false || strpos($var, 'KEY') !== false) {
            $value = str_repeat('*', 8);
        }
        echo "   $var = $value\n";
    } else {
        echo "   $var = (not set)\n";
    }
}

echo "\n";
echo "===========================================\n";
echo "Test Complete\n";
echo "===========================================\n";
