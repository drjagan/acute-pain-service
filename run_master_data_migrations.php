<?php
/**
 * Master Data Migrations Runner
 * Executes all master data migrations in the correct order
 * 
 * Usage: Run this file from the command line or browser
 */

// Load configuration
require_once __DIR__ . '/config/config.php';

// Connect to database
try {
    $db = Database::getInstance();
    echo "✓ Database connection successful\n";
    echo "=====================================\n\n";
} catch (Exception $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Migration files in order
$migrationFiles = [
    'src/Database/migrations/013_create_new_lookup_tables.sql',
    'src/Database/migrations/014_update_surgeries_with_specialties.sql',
    'src/Database/seeders/MasterDataSeeder.sql'
];

$success = true;

foreach ($migrationFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    
    echo "Running: " . basename($file) . "\n";
    echo "-------------------------------------\n";
    
    if (!file_exists($filePath)) {
        echo "✗ ERROR: File not found: $filePath\n\n";
        $success = false;
        continue;
    }
    
    // Read SQL file
    $sql = file_get_contents($filePath);
    
    if ($sql === false) {
        echo "✗ ERROR: Could not read file: $filePath\n\n";
        $success = false;
        continue;
    }
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            // Remove comments and empty statements
            $stmt = preg_replace('/--.*$/m', '', $stmt);
            $stmt = preg_replace('/\/\*.*?\*\//s', '', $stmt);
            return trim($stmt) !== '';
        }
    );
    
    $executed = 0;
    $failed = 0;
    
    foreach ($statements as $statement) {
        try {
            $db->exec($statement);
            $executed++;
            echo ".";
            flush();
        } catch (PDOException $e) {
            // Check if error is "table already exists" - that's OK
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate entry') !== false ||
                strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "s"; // s = skipped (already exists)
                flush();
            } else {
                $failed++;
                echo "\n✗ Error executing statement: " . $e->getMessage() . "\n";
                echo "Statement: " . substr($statement, 0, 100) . "...\n";
                $success = false;
            }
        }
    }
    
    echo "\n";
    echo "✓ Executed: $executed statements\n";
    if ($failed > 0) {
        echo "✗ Failed: $failed statements\n";
    }
    echo "\n";
}

echo "=====================================\n";

if ($success) {
    echo "✓ ALL MIGRATIONS COMPLETED SUCCESSFULLY!\n\n";
    
    // Verify tables were created
    echo "Verifying new tables...\n";
    echo "-------------------------------------\n";
    
    $newTables = [
        'lookup_catheter_indications',
        'lookup_removal_indications',
        'lookup_sentinel_events',
        'lookup_specialties'
    ];
    
    foreach ($newTables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✓ $table - " . $result['count'] . " records\n";
        } catch (PDOException $e) {
            echo "✗ $table - ERROR: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    echo "Verifying specialty_id in surgeries...\n";
    echo "-------------------------------------\n";
    try {
        $stmt = $db->query("SHOW COLUMNS FROM lookup_surgeries LIKE 'specialty_id'");
        if ($stmt->fetch()) {
            echo "✓ specialty_id column exists in lookup_surgeries\n";
            
            // Check if surgeries are linked to specialties
            $stmt = $db->query("SELECT COUNT(*) as count FROM lookup_surgeries WHERE specialty_id IS NOT NULL");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✓ " . $result['count'] . " surgeries linked to specialties\n";
        } else {
            echo "✗ specialty_id column NOT found in lookup_surgeries\n";
        }
    } catch (PDOException $e) {
        echo "✗ Error checking specialty_id: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    echo "=====================================\n";
    echo "NEXT STEPS:\n";
    echo "1. Update the catheters table (see instructions below)\n";
    echo "2. Test the master data interface at: " . BASE_URL . "/masterdata/index\n";
    echo "3. Delete this file (run_master_data_migrations.php) after success\n";
    echo "=====================================\n\n";
    
    echo "SQL TO RUN FOR CATHETERS TABLE:\n";
    echo "-------------------------------------\n";
    echo "ALTER TABLE catheters \n";
    echo "ADD COLUMN indication_id INT UNSIGNED NULL AFTER indication,\n";
    echo "ADD COLUMN indication_notes TEXT NULL AFTER indication_id;\n\n";
    
    echo "ALTER TABLE catheters\n";
    echo "ADD CONSTRAINT fk_catheter_indication\n";
    echo "FOREIGN KEY (indication_id) \n";
    echo "REFERENCES lookup_catheter_indications(id)\n";
    echo "ON DELETE SET NULL\n";
    echo "ON UPDATE CASCADE;\n";
    echo "=====================================\n";
    
} else {
    echo "✗ SOME MIGRATIONS FAILED - Please review errors above\n";
    echo "=====================================\n";
}
