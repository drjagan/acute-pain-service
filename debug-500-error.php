<?php
/**
 * Debug 500 Error Script
 * 
 * Run this to diagnose why /users/store returns 500 error
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "==========================================\n";
echo "Debugging 500 Error on /users/store\n";
echo "==========================================\n\n";

// Test 1: Check PHP error log
echo "TEST 1: Checking PHP error logs\n";
echo str_repeat('-', 40) . "\n";

$phpErrorLog = ini_get('error_log');
echo "PHP error_log setting: $phpErrorLog\n";

if ($phpErrorLog && file_exists($phpErrorLog)) {
    echo "PHP error log exists\n";
    echo "Last 20 lines:\n";
    $lines = file($phpErrorLog);
    $lastLines = array_slice($lines, -20);
    foreach ($lastLines as $line) {
        echo "  " . $line;
    }
} else {
    echo "PHP error log not found or not configured\n";
}
echo "\n";

// Test 2: Check application logs directory
echo "TEST 2: Checking application logs\n";
echo str_repeat('-', 40) . "\n";

$logsPath = __DIR__ . '/logs';
echo "Logs path: $logsPath\n";

if (is_dir($logsPath)) {
    echo "Logs directory exists\n";
    
    // Check permissions
    $perms = substr(sprintf('%o', fileperms($logsPath)), -4);
    echo "Permissions: $perms\n";
    
    if (is_writable($logsPath)) {
        echo "✓ Logs directory is writable\n";
    } else {
        echo "✗ Logs directory is NOT writable\n";
        echo "  Fix: chmod 777 logs (or chown cloudron:cloudron logs)\n";
    }
    
    // List log files
    $logFiles = glob($logsPath . '/*.log');
    echo "Log files found: " . count($logFiles) . "\n";
    
    foreach ($logFiles as $logFile) {
        $basename = basename($logFile);
        $size = filesize($logFile);
        $perms = substr(sprintf('%o', fileperms($logFile)), -4);
        $writable = is_writable($logFile) ? 'writable' : 'NOT writable';
        
        echo "  - $basename ($size bytes, perms: $perms, $writable)\n";
        
        // Show last few lines if file exists and has content
        if ($size > 0 && $size < 1000000) {
            $lines = file($logFile);
            $lastLines = array_slice($lines, -5);
            foreach ($lastLines as $line) {
                echo "    " . trim($line) . "\n";
            }
        }
    }
} else {
    echo "✗ Logs directory does NOT exist\n";
    echo "  Creating logs directory...\n";
    mkdir($logsPath, 0777, true);
    echo "  ✓ Created\n";
}
echo "\n";

// Test 3: Check file ownership
echo "TEST 3: Checking file ownership\n";
echo str_repeat('-', 40) . "\n";

$currentUser = posix_getpwuid(posix_geteuid());
echo "Current PHP user: " . $currentUser['name'] . " (UID: " . $currentUser['uid'] . ")\n";

if (is_dir($logsPath)) {
    $owner = posix_getpwuid(fileowner($logsPath));
    echo "Logs directory owner: " . $owner['name'] . " (UID: " . $owner['uid'] . ")\n";
    
    if ($owner['uid'] !== $currentUser['uid']) {
        echo "⚠ Owner mismatch! PHP runs as '{$currentUser['name']}' but logs owned by '{$owner['name']}'\n";
        echo "  Fix: chown -R {$currentUser['name']}:{$currentUser['name']} logs\n";
    } else {
        echo "✓ Ownership is correct\n";
    }
}
echo "\n";

// Test 4: Test writing to logs
echo "TEST 4: Testing log writing\n";
echo str_repeat('-', 40) . "\n";

$testLogFile = $logsPath . '/test-write.log';
$testMessage = "Test write at " . date('Y-m-d H:i:s') . "\n";

$result = @file_put_contents($testLogFile, $testMessage, FILE_APPEND);

if ($result !== false) {
    echo "✓ Successfully wrote to test log\n";
    echo "  File: $testLogFile\n";
    echo "  Content: $testMessage";
    
    // Clean up
    @unlink($testLogFile);
} else {
    echo "✗ FAILED to write to logs\n";
    $error = error_get_last();
    echo "  Error: " . ($error['message'] ?? 'Unknown') . "\n";
}
echo "\n";

// Test 5: Check database connection
echo "TEST 5: Checking database connection\n";
echo str_repeat('-', 40) . "\n";

try {
    require_once __DIR__ . '/config/env-loader.php';
    loadEnv(__DIR__);
    require_once __DIR__ . '/config/database.php';
    
    $pdo = Database::getInstance();
    echo "✓ Database connection successful\n";
    echo "  Database: " . DB_NAME . "\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Users table exists\n";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "  Columns: " . implode(', ', array_slice($columns, 0, 5)) . "...\n";
    } else {
        echo "✗ Users table does NOT exist\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database connection failed\n";
    echo "  Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Simulate user creation
echo "TEST 6: Simulating user creation\n";
echo str_repeat('-', 40) . "\n";

try {
    // Simulate the data that would be sent
    $testData = [
        'username' => 'test_user_' . time(),
        'email' => 'test' . time() . '@example.com',
        'password' => 'TestPassword123',
        'first_name' => 'Test',
        'last_name' => 'User',
        'phone' => '1234567890',
        'role' => 'nurse',
        'status' => 'active'
    ];
    
    echo "Test data:\n";
    echo "  Username: {$testData['username']}\n";
    echo "  Email: {$testData['email']}\n";
    echo "  Role: {$testData['role']}\n";
    
    // Hash password
    $passwordHash = password_hash($testData['password'], PASSWORD_BCRYPT, ['cost' => 12]);
    echo "  Password hash: " . substr($passwordHash, 0, 30) . "...\n";
    
    // Try to insert
    $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, phone, role, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $testData['username'],
        $testData['email'],
        $passwordHash,
        $testData['first_name'],
        $testData['last_name'],
        $testData['phone'],
        $testData['role'],
        $testData['status']
    ]);
    
    if ($result) {
        $userId = $pdo->lastInsertId();
        echo "✓ Test user created successfully\n";
        echo "  User ID: $userId\n";
        
        // Clean up - delete test user
        $pdo->exec("DELETE FROM users WHERE id = $userId");
        echo "  ✓ Test user deleted\n";
    } else {
        echo "✗ Failed to create test user\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database error during test insert\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "  Code: " . $e->getCode() . "\n";
}
echo "\n";

// Test 7: Check Apache/Nginx error logs
echo "TEST 7: Checking web server error logs\n";
echo str_repeat('-', 40) . "\n";

$possibleLogs = [
    '/var/log/apache2/error.log',
    '/var/log/httpd/error_log',
    '/var/log/nginx/error.log',
    '/var/log/cloudron/error.log',
];

foreach ($possibleLogs as $logFile) {
    if (file_exists($logFile)) {
        echo "Found: $logFile\n";
        if (is_readable($logFile)) {
            echo "  Reading last 10 lines...\n";
            $lines = file($logFile);
            $lastLines = array_slice($lines, -10);
            foreach ($lastLines as $line) {
                if (stripos($line, 'error') !== false || stripos($line, 'fatal') !== false) {
                    echo "  ! " . trim($line) . "\n";
                }
            }
        } else {
            echo "  (not readable)\n";
        }
    }
}
echo "\n";

// Summary
echo "==========================================\n";
echo "Summary\n";
echo "==========================================\n";

echo "\nCommon causes of 500 errors:\n";
echo "1. Logs directory not writable (check TEST 2)\n";
echo "2. File ownership mismatch (check TEST 3)\n";
echo "3. Database connection issues (check TEST 5)\n";
echo "4. Missing database tables/columns (check TEST 5)\n";
echo "5. PHP syntax errors (check web server logs)\n";
echo "6. Missing PHP extensions (check TEST 5)\n";

echo "\nRecommended fixes:\n";
echo "1. Set logs permissions: chmod -R 777 logs\n";
echo "2. Set logs ownership: chown -R cloudron:cloudron logs\n";
echo "3. Enable PHP error logging in .htaccess or php.ini\n";
echo "4. Check Apache/Nginx error logs for details\n";

echo "\n==========================================\n";
