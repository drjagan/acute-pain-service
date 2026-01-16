<?php
/**
 * Master Data Migrations Runner v2
 * Improved version with better SQL parsing and PDO handling
 */

// Load configuration
require_once __DIR__ . '/config/config.php';

// Connect to database with buffered queries
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    echo "✓ Database connection successful\n";
    echo "=====================================\n\n";
} catch (Exception $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Helper function to execute SQL file
function executeSqlFile($pdo, $filePath, $fileName) {
    echo "Running: $fileName\n";
    echo "-------------------------------------\n";
    
    if (!file_exists($filePath)) {
        echo "✗ ERROR: File not found: $filePath\n\n";
        return false;
    }
    
    $sql = file_get_contents($filePath);
    
    // Remove comments
    $sql = preg_replace('/--[^\n]*\n/', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Split by semicolon but keep compound statements intact
    $statements = [];
    $buffer = '';
    $inString = false;
    $stringChar = '';
    
    for ($i = 0; $i < strlen($sql); $i++) {
        $char = $sql[$i];
        
        // Handle string literals
        if (($char === '"' || $char === "'") && ($i === 0 || $sql[$i-1] !== '\\')) {
            if (!$inString) {
                $inString = true;
                $stringChar = $char;
            } elseif ($char === $stringChar) {
                $inString = false;
            }
        }
        
        $buffer .= $char;
        
        // Split on semicolon if not in string
        if ($char === ';' && !$inString) {
            $stmt = trim($buffer);
            if (!empty($stmt) && $stmt !== ';') {
                $statements[] = $stmt;
            }
            $buffer = '';
        }
    }
    
    // Add any remaining buffer
    if (trim($buffer)) {
        $statements[] = trim($buffer);
    }
    
    $executed = 0;
    $skipped = 0;
    $failed = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            $pdo->exec($statement);
            $executed++;
            echo ".";
            flush();
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            
            // Skip if already exists
            if (strpos($errorMsg, 'already exists') !== false || 
                strpos($errorMsg, 'Duplicate entry') !== false ||
                strpos($errorMsg, 'Duplicate column') !== false ||
                strpos($errorMsg, 'Duplicate key') !== false) {
                $skipped++;
                echo "s";
                flush();
            } else {
                $failed++;
                echo "\n✗ Error: " . substr($errorMsg, 0, 100) . "...\n";
                if ($failed > 5) {
                    echo "Too many errors, stopping this file...\n";
                    break;
                }
            }
        }
    }
    
    echo "\n";
    echo "✓ Executed: $executed | Skipped: $skipped | Failed: $failed\n\n";
    
    return $failed === 0;
}

// Migration files
$migrations = [
    [
        'file' => 'src/Database/migrations/013_create_new_lookup_tables.sql',
        'name' => '013_create_new_lookup_tables.sql'
    ],
    [
        'file' => 'src/Database/migrations/014_update_surgeries_with_specialties.sql',
        'name' => '014_update_surgeries_with_specialties.sql'
    ],
    [
        'file' => 'src/Database/seeders/MasterDataSeeder.sql',
        'name' => 'MasterDataSeeder.sql'
    ]
];

$allSuccess = true;

foreach ($migrations as $migration) {
    $filePath = __DIR__ . '/' . $migration['file'];
    $success = executeSqlFile($pdo, $filePath, $migration['name']);
    if (!$success) {
        $allSuccess = false;
    }
}

echo "=====================================\n";

// Verification
echo "VERIFICATION\n";
echo "=====================================\n\n";

// Check tables
$tables = [
    'lookup_catheter_indications' => 'Catheter Indications',
    'lookup_removal_indications' => 'Removal Indications',
    'lookup_sentinel_events' => 'Sentinel Events',
    'lookup_specialties' => 'Specialties',
    'lookup_surgeries' => 'Surgeries',
    'lookup_comorbidities' => 'Comorbidities',
    'lookup_drugs' => 'Drugs',
    'lookup_adjuvants' => 'Adjuvants',
    'lookup_red_flags' => 'Red Flags'
];

foreach ($tables as $table => $label) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ $label: " . $result['count'] . " records\n";
    } catch (PDOException $e) {
        echo "✗ $label: ERROR\n";
    }
}

echo "\n";

// Check specialty_id column
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM lookup_surgeries LIKE 'specialty_id'");
    if ($stmt->fetch()) {
        echo "✓ specialty_id column exists in lookup_surgeries\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM lookup_surgeries WHERE specialty_id IS NOT NULL");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ " . $result['count'] . " surgeries linked to specialties\n";
    } else {
        echo "✗ specialty_id column NOT found\n";
    }
} catch (PDOException $e) {
    echo "✗ Error checking specialty_id\n";
}

echo "\n=====================================\n";
echo "MIGRATION COMPLETE!\n";
echo "=====================================\n\n";

echo "NEXT STEP: Update catheters table\n";
echo "Run this SQL in phpMyAdmin:\n";
echo "-------------------------------------\n";
echo "ALTER TABLE catheters \n";
echo "ADD COLUMN IF NOT EXISTS indication_id INT UNSIGNED NULL AFTER indication,\n";
echo "ADD COLUMN IF NOT EXISTS indication_notes TEXT NULL AFTER indication_id;\n\n";

echo "ALTER TABLE catheters\n";
echo "ADD CONSTRAINT fk_catheter_indication\n";
echo "FOREIGN KEY (indication_id) \n";
echo "REFERENCES lookup_catheter_indications(id)\n";
echo "ON DELETE SET NULL\n";
echo "ON UPDATE CASCADE;\n";
echo "=====================================\n\n";

echo "Then visit: " . BASE_URL . "/masterdata/index\n";
echo "=====================================\n";
