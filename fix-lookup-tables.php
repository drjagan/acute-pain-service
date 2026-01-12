<?php
/**
 * Fix Script: Manually Create Lookup Tables
 * 
 * This script manually creates lookup tables if they're missing.
 * Run this if installation fails at the seed stage.
 * 
 * Usage: php fix-lookup-tables.php
 */

echo "==========================================\n";
echo "Lookup Tables Fix Script\n";
echo "==========================================\n\n";

// Load configuration
require_once __DIR__ . '/config/env-loader.php';
loadEnv(__DIR__);

require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance();
    echo "✓ Database connection established\n";
    echo "  Database: " . DB_NAME . "\n\n";
    
    // Check which lookup tables exist
    echo "Checking existing lookup tables...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'lookup_%'");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($existingTables) . " lookup tables:\n";
    foreach ($existingTables as $table) {
        echo "  - $table\n";
    }
    echo "\n";
    
    // Define all lookup tables
    $lookupTables = [
        'lookup_comorbidities',
        'lookup_surgeries',
        'lookup_drugs',
        'lookup_adjuvants',
        'lookup_red_flags'
    ];
    
    $missingTables = array_diff($lookupTables, $existingTables);
    
    if (empty($missingTables)) {
        echo "✓ All lookup tables exist!\n";
        echo "  No action needed.\n\n";
        exit(0);
    }
    
    echo "⚠ Missing tables found: " . count($missingTables) . "\n";
    foreach ($missingTables as $table) {
        echo "  - $table\n";
    }
    echo "\n";
    
    echo "Creating missing tables...\n\n";
    
    // Read migration file
    $migrationFile = __DIR__ . '/src/Database/migrations/009_create_lookup_tables.sql';
    
    if (!file_exists($migrationFile)) {
        die("ERROR: Migration file not found: $migrationFile\n");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Split into individual CREATE TABLE statements
    $statements = explode(';', $sql);
    $created = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        // Skip empty statements and comments
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        // Only process CREATE TABLE statements
        if (stripos($statement, 'CREATE TABLE') !== false) {
            try {
                $pdo->exec($statement);
                
                // Extract table name
                if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                    $tableName = $matches[1];
                    echo "  ✓ Created: $tableName\n";
                    $created++;
                }
            } catch (PDOException $e) {
                // Check if table already exists
                if ($e->getCode() == '42S01') {
                    if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                        $tableName = $matches[1];
                        echo "  - Skipped: $tableName (already exists)\n";
                    }
                } else {
                    echo "  ✗ Error: " . $e->getMessage() . "\n";
                    throw $e;
                }
            }
        }
    }
    
    echo "\n";
    echo "==========================================\n";
    echo "Summary\n";
    echo "==========================================\n";
    echo "Tables created: $created\n";
    
    // Verify all tables now exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'lookup_%'");
    $finalTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Total lookup tables: " . count($finalTables) . "/5\n";
    
    if (count($finalTables) == 5) {
        echo "\n✓ All lookup tables are now present!\n";
        echo "  You can now re-run the seed script or continue installation.\n";
    } else {
        echo "\n⚠ Some tables are still missing!\n";
        $stillMissing = array_diff($lookupTables, $finalTables);
        foreach ($stillMissing as $table) {
            echo "  - $table\n";
        }
    }
    
    echo "==========================================\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
