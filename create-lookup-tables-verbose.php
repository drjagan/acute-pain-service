<?php
/**
 * Verbose Lookup Tables Creation Script
 * Creates lookup tables one by one with detailed error reporting
 */

echo "==========================================\n";
echo "Verbose Lookup Tables Creation\n";
echo "==========================================\n\n";

// Load configuration
require_once __DIR__ . '/config/env-loader.php';
loadEnv(__DIR__);
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance();
    echo "✓ Connected to database: " . DB_NAME . "\n\n";
    
    // Define each table individually for better error tracking
    $tables = [
        'lookup_comorbidities' => "CREATE TABLE IF NOT EXISTS lookup_comorbidities (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT NULL,
            active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'lookup_surgeries' => "CREATE TABLE IF NOT EXISTS lookup_surgeries (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            speciality VARCHAR(50) NULL,
            active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'lookup_drugs' => "CREATE TABLE IF NOT EXISTS lookup_drugs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            generic_name VARCHAR(100) NULL,
            typical_concentration DECIMAL(5,2) NULL,
            max_dose DECIMAL(8,2) NULL,
            unit VARCHAR(20) NULL,
            active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'lookup_adjuvants' => "CREATE TABLE IF NOT EXISTS lookup_adjuvants (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            typical_dose DECIMAL(8,2) NULL,
            unit VARCHAR(20) NULL,
            active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'lookup_red_flags' => "CREATE TABLE IF NOT EXISTS lookup_red_flags (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            severity ENUM('mild', 'moderate', 'severe') DEFAULT 'moderate',
            requires_immediate_action BOOLEAN DEFAULT FALSE,
            active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    $created = 0;
    $failed = 0;
    $errors = [];
    
    foreach ($tables as $tableName => $sql) {
        echo "Creating table: $tableName\n";
        echo str_repeat('-', 40) . "\n";
        
        try {
            // Try to create the table
            $result = $pdo->exec($sql);
            
            // Verify it was created
            $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
            if ($stmt->rowCount() > 0) {
                echo "  ✓ SUCCESS: Table created and verified\n";
                $created++;
                
                // Get column count
                $stmt = $pdo->query("DESCRIBE $tableName");
                $columns = $stmt->rowCount();
                echo "  Columns: $columns\n";
            } else {
                echo "  ✗ FAILED: Table not found after creation\n";
                $failed++;
                $errors[] = "$tableName: Table not found after exec()";
            }
            
        } catch (PDOException $e) {
            echo "  ✗ ERROR: " . $e->getMessage() . "\n";
            echo "  Error Code: " . $e->getCode() . "\n";
            
            // Check if it's a permissions issue
            if (in_array($e->getCode(), ['1044', '1142', '42000'])) {
                echo "  >> This looks like a PERMISSIONS issue\n";
                echo "  >> User '" . DB_USER . "' may not have CREATE privilege\n";
            }
            
            $failed++;
            $errors[] = "$tableName: " . $e->getMessage();
        }
        
        echo "\n";
    }
    
    // Final summary
    echo "==========================================\n";
    echo "Summary\n";
    echo "==========================================\n";
    echo "Tables to create: " . count($tables) . "\n";
    echo "Successfully created: $created\n";
    echo "Failed: $failed\n\n";
    
    if ($failed > 0) {
        echo "Errors:\n";
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
        echo "\n";
        
        echo "TROUBLESHOOTING:\n";
        echo "================\n\n";
        
        echo "If you see permission errors, run these SQL commands as root:\n\n";
        echo "mysql -u root -p\n\n";
        echo "-- Grant CREATE privilege\n";
        echo "GRANT CREATE ON " . DB_NAME . ".* TO '" . DB_USER . "'@'%';\n";
        echo "GRANT CREATE ON " . DB_NAME . ".* TO '" . DB_USER . "'@'localhost';\n";
        echo "FLUSH PRIVILEGES;\n\n";
        
        echo "-- Or grant all privileges (for development)\n";
        echo "GRANT ALL PRIVILEGES ON " . DB_NAME . ".* TO '" . DB_USER . "'@'%';\n";
        echo "GRANT ALL PRIVILEGES ON " . DB_NAME . ".* TO '" . DB_USER . "'@'localhost';\n";
        echo "FLUSH PRIVILEGES;\n\n";
        
        echo "Then run this script again.\n";
        echo "==========================================\n";
        
        exit(1);
    } else {
        echo "✓ All lookup tables created successfully!\n";
        echo "You can now continue with the installation.\n";
        echo "==========================================\n";
        exit(0);
    }
    
} catch (Exception $e) {
    echo "\n✗ FATAL ERROR\n";
    echo "==========================================\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
