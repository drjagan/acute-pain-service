<?php
/**
 * Diagnostic Script: Database Permissions and Configuration
 * 
 * This script diagnoses why tables can't be created
 */

echo "==========================================\n";
echo "Database Diagnostic Script\n";
echo "==========================================\n\n";

// Load configuration
require_once __DIR__ . '/config/env-loader.php';
loadEnv(__DIR__);

require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance();
    echo "✓ Database connection established\n";
    echo "  Database: " . DB_NAME . "\n";
    echo "  Host: " . DB_HOST . "\n";
    echo "  User: " . DB_USER . "\n\n";
    
    // Test 1: Check user privileges
    echo "TEST 1: Checking user privileges...\n";
    try {
        $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
        $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "User grants:\n";
        foreach ($grants as $grant) {
            echo "  - $grant\n";
            
            // Check for CREATE privilege
            if (stripos($grant, 'CREATE') !== false || stripos($grant, 'ALL PRIVILEGES') !== false) {
                echo "    ✓ CREATE privilege found\n";
            }
        }
        echo "\n";
    } catch (PDOException $e) {
        echo "  ✗ Cannot check grants: " . $e->getMessage() . "\n\n";
    }
    
    // Test 2: Try creating a simple test table
    echo "TEST 2: Creating test table...\n";
    try {
        $pdo->exec("DROP TABLE IF EXISTS test_table_permissions");
        $pdo->exec("CREATE TABLE test_table_permissions (id INT)");
        echo "  ✓ Test table created successfully\n";
        
        // Clean up
        $pdo->exec("DROP TABLE test_table_permissions");
        echo "  ✓ Test table dropped successfully\n\n";
    } catch (PDOException $e) {
        echo "  ✗ Cannot create table: " . $e->getMessage() . "\n";
        echo "  Error code: " . $e->getCode() . "\n\n";
        echo "  ** This is the problem! User lacks CREATE TABLE permission **\n\n";
    }
    
    // Test 3: Check if migration file exists
    echo "TEST 3: Checking migration file...\n";
    $migrationFile = __DIR__ . '/src/Database/migrations/009_create_lookup_tables.sql';
    
    if (file_exists($migrationFile)) {
        echo "  ✓ Migration file found\n";
        echo "  Path: $migrationFile\n";
        echo "  Size: " . filesize($migrationFile) . " bytes\n";
        
        // Read and show first CREATE TABLE statement
        $sql = file_get_contents($migrationFile);
        $lines = explode("\n", $sql);
        
        echo "\n  First few lines of migration:\n";
        for ($i = 0; $i < min(10, count($lines)); $i++) {
            echo "  " . ($i+1) . ": " . $lines[$i] . "\n";
        }
        echo "\n";
    } else {
        echo "  ✗ Migration file NOT found: $migrationFile\n\n";
    }
    
    // Test 4: Try executing first CREATE TABLE with detailed error
    echo "TEST 4: Attempting to create lookup_comorbidities table...\n";
    try {
        $createSQL = "CREATE TABLE IF NOT EXISTS lookup_comorbidities (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT NULL,
            active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        echo "  SQL:\n";
        echo "  " . substr($createSQL, 0, 100) . "...\n\n";
        
        $pdo->exec($createSQL);
        echo "  ✓ Table created successfully!\n";
        
        // Verify it exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'lookup_comorbidities'");
        if ($stmt->rowCount() > 0) {
            echo "  ✓ Table verified in database\n\n";
        }
        
    } catch (PDOException $e) {
        echo "  ✗ Failed to create table\n";
        echo "  Error: " . $e->getMessage() . "\n";
        echo "  Code: " . $e->getCode() . "\n";
        echo "  SQL State: " . $e->errorInfo[0] . "\n\n";
        
        // Provide specific guidance based on error
        $errorCode = $e->getCode();
        echo "  ** DIAGNOSIS **\n";
        
        if ($errorCode == '42000') {
            echo "  Problem: SQL syntax error or access denied\n";
            echo "  Solution: Check user has CREATE privilege\n";
        } elseif ($errorCode == '42S01') {
            echo "  Problem: Table already exists\n";
            echo "  Solution: Table might be in different database\n";
        } elseif ($errorCode == '1044' || $errorCode == '1142') {
            echo "  Problem: Access denied - missing CREATE privilege\n";
            echo "  Solution: Grant CREATE privilege to user:\n";
            echo "           GRANT CREATE ON " . DB_NAME . ".* TO '" . DB_USER . "'@'%';\n";
            echo "           FLUSH PRIVILEGES;\n";
        } else {
            echo "  Problem: Unknown error\n";
        }
        echo "\n";
    }
    
    // Test 5: Check existing tables
    echo "TEST 5: Listing all tables in database...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "  Total tables: " . count($tables) . "\n";
    if (count($tables) > 0) {
        echo "  Tables:\n";
        foreach ($tables as $table) {
            echo "    - $table\n";
        }
    } else {
        echo "  (No tables found)\n";
    }
    echo "\n";
    
    // Test 6: Check database charset
    echo "TEST 6: Checking database configuration...\n";
    $stmt = $pdo->query("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME 
                         FROM information_schema.SCHEMATA 
                         WHERE SCHEMA_NAME = DATABASE()");
    $dbInfo = $stmt->fetch();
    
    echo "  Charset: " . $dbInfo['DEFAULT_CHARACTER_SET_NAME'] . "\n";
    echo "  Collation: " . $dbInfo['DEFAULT_COLLATION_NAME'] . "\n";
    
    if ($dbInfo['DEFAULT_CHARACTER_SET_NAME'] !== 'utf8mb4') {
        echo "  ⚠ Warning: Database should use utf8mb4\n";
    }
    echo "\n";
    
    // Summary
    echo "==========================================\n";
    echo "Diagnostic Summary\n";
    echo "==========================================\n";
    echo "Database: " . DB_NAME . "\n";
    echo "User: " . DB_USER . "\n";
    echo "Tables: " . count($tables) . "\n";
    echo "\nNext steps:\n";
    echo "1. Check the error messages above\n";
    echo "2. Ensure user has CREATE privilege\n";
    echo "3. If permission issue, contact database admin\n";
    echo "==========================================\n";
    
} catch (Exception $e) {
    echo "\n✗ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
