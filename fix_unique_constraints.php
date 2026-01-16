<?php
/**
 * Fix UNIQUE Constraints for Soft Delete Support
 * Removes UNIQUE constraints from lookup tables to allow soft-deleted items to be re-added
 */

require_once __DIR__ . '/config/config.php';

try {
    $db = Database::getInstance();
    echo "✓ Database connection successful\n";
    echo "=====================================\n\n";
} catch (Exception $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

$tables = [
    'lookup_comorbidities' => 'name',
    'lookup_drugs' => 'name',
    'lookup_adjuvants' => 'name',
    'lookup_removal_indications' => 'code',
    'lookup_specialties' => 'code'
];

echo "Removing UNIQUE constraints to support soft delete...\n";
echo "=====================================\n\n";

foreach ($tables as $table => $column) {
    echo "Processing: $table ($column)\n";
    
    try {
        // Check if constraint exists
        $stmt = $db->query("SHOW CREATE TABLE $table");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $createTable = $result['Create Table'];
        
        if (strpos($createTable, "UNIQUE KEY") !== false || strpos($createTable, "UNIQUE") !== false) {
            echo "  - Removing UNIQUE constraint...\n";
            
            // Drop the index/constraint
            // Try different methods as MySQL/MariaDB syntax varies
            try {
                $db->exec("ALTER TABLE $table DROP INDEX $column");
                echo "  ✓ Dropped index: $column\n";
            } catch (PDOException $e) {
                // Try alternate syntax
                try {
                    $db->exec("ALTER TABLE $table DROP KEY $column");
                    echo "  ✓ Dropped key: $column\n";
                } catch (PDOException $e2) {
                    echo "  ⚠ Could not drop constraint (may not exist): " . $e2->getMessage() . "\n";
                }
            }
            
            // Add regular index instead for performance
            try {
                $db->exec("ALTER TABLE $table ADD INDEX idx_{$column} ($column)");
                echo "  ✓ Added regular index: idx_$column\n";
            } catch (PDOException $e) {
                echo "  ⚠ Index may already exist\n";
            }
        } else {
            echo "  ✓ No UNIQUE constraint found\n";
        }
        
        echo "\n";
    } catch (PDOException $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
    }
}

echo "=====================================\n";
echo "VERIFICATION\n";
echo "=====================================\n\n";

// Verify constraints are removed
foreach ($tables as $table => $column) {
    $stmt = $db->query("SHOW CREATE TABLE $table");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $createTable = $result['Create Table'];
    
    if (strpos($createTable, "UNIQUE") !== false) {
        echo "⚠ $table still has UNIQUE constraint\n";
    } else {
        echo "✓ $table - UNIQUE constraint removed\n";
    }
}

echo "\n=====================================\n";
echo "COMPLETE!\n";
echo "=====================================\n\n";

echo "Now you can:\n";
echo "1. Delete items (soft delete)\n";
echo "2. Re-add items with the same name\n";
echo "3. Application-level validation prevents active duplicates\n";
echo "=====================================\n";
