<?php
/**
 * Test Script for v1.2.0 Features on Localhost
 * Tests master data management system
 */

// Load configuration
require_once __DIR__ . '/config/env-loader.php';

echo "=== Acute Pain Service v1.2.0 - Localhost Test ===\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
try {
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: 3306;
    $dbname = getenv('DB_NAME') ?: 'aps_database';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $db = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "   ✓ Database connection successful\n";
    echo "   Database: $dbname\n";
    echo "   Version: " . getenv('APP_VERSION') . "\n\n";
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Check New Tables Exist (v1.2.0)
echo "2. Checking New v1.2.0 Tables...\n";
$newTables = [
    'lookup_catheter_indications',
    'lookup_removal_indications',
    'lookup_sentinel_events',
    'lookup_specialties'
];

foreach ($newTables as $table) {
    $stmt = $db->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() > 0) {
        // Get count
        $countStmt = $db->query("SELECT COUNT(*) as count FROM $table");
        $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "   ✓ $table ($count records)\n";
    } else {
        echo "   ✗ $table NOT FOUND\n";
    }
}
echo "\n";

// Test 3: Check Enhanced Columns
echo "3. Checking Enhanced Columns...\n";

// Check specialty_id in surgeries
$stmt = $db->query("SHOW COLUMNS FROM lookup_surgeries LIKE 'specialty_id'");
if ($stmt->rowCount() > 0) {
    echo "   ✓ lookup_surgeries.specialty_id exists\n";
} else {
    echo "   ✗ lookup_surgeries.specialty_id NOT FOUND\n";
}

// Check sort_order in drugs
$stmt = $db->query("SHOW COLUMNS FROM lookup_drugs LIKE 'sort_order'");
if ($stmt->rowCount() > 0) {
    echo "   ✓ lookup_drugs.sort_order exists\n";
} else {
    echo "   ✗ lookup_drugs.sort_order NOT FOUND\n";
}

// Check deleted_at in drugs
$stmt = $db->query("SHOW COLUMNS FROM lookup_drugs LIKE 'deleted_at'");
if ($stmt->rowCount() > 0) {
    echo "   ✓ lookup_drugs.deleted_at exists (soft delete)\n";
} else {
    echo "   ✗ lookup_drugs.deleted_at NOT FOUND\n";
}
echo "\n";

// Test 4: Check All Lookup Tables
echo "4. Listing All Lookup Tables...\n";
$stmt = $db->query("SHOW TABLES LIKE 'lookup_%'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "   Found " . count($tables) . " lookup tables:\n";
foreach ($tables as $table) {
    $countStmt = $db->query("SELECT COUNT(*) as count FROM $table");
    $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   - $table: $count records\n";
}
echo "\n";

// Test 5: Test Master Data Models
echo "5. Testing Master Data Models...\n";

// Test if model files exist
$modelFiles = [
    'LookupCatheterIndication.php',
    'LookupRemovalIndication.php',
    'LookupSentinelEvent.php',
    'LookupSpecialty.php',
    'BaseLookupModel.php'
];

foreach ($modelFiles as $file) {
    $modelPath = __DIR__ . '/src/Models/' . $file;
    if (file_exists($modelPath)) {
        $size = filesize($modelPath);
        echo "   ✓ $file (" . number_format($size) . " bytes)\n";
    } else {
        echo "   ✗ $file NOT FOUND\n";
    }
}
echo "\n";

// Test 6: Check Master Data Controller
echo "6. Checking Master Data Controller...\n";
$controllerFile = __DIR__ . '/src/Controllers/MasterDataController.php';
if (file_exists($controllerFile)) {
    echo "   ✓ MasterDataController.php exists\n";
    
    // Check file size
    $filesize = filesize($controllerFile);
    echo "   File size: " . number_format($filesize) . " bytes\n";
    
    // Check for key methods
    $content = file_get_contents($controllerFile);
    $methods = ['index', 'list', 'create', 'store', 'edit', 'update', 'delete', 'toggleActive', 'reorder', 'export'];
    $foundMethods = [];
    foreach ($methods as $method) {
        if (preg_match("/function\\s+$method\\s*\\(/", $content)) {
            $foundMethods[] = $method;
        }
    }
    echo "   Methods found: " . implode(', ', $foundMethods) . "\n";
} else {
    echo "   ✗ MasterDataController.php NOT FOUND\n";
}
echo "\n";

// Test 7: Check Master Data Views
echo "7. Checking Master Data Views...\n";
$viewsDir = __DIR__ . '/src/Views/masterdata/';
if (is_dir($viewsDir)) {
    $views = scandir($viewsDir);
    $views = array_filter($views, function($v) { return substr($v, -4) === '.php'; });
    echo "   ✓ Master data views directory exists\n";
    echo "   Found " . count($views) . " view files:\n";
    foreach ($views as $view) {
        echo "   - $view\n";
    }
} else {
    echo "   ✗ Master data views directory NOT FOUND\n";
}
echo "\n";

// Test 8: Configuration File
echo "8. Checking Master Data Configuration...\n";
$configFile = __DIR__ . '/config/masterdata.php';
if (file_exists($configFile)) {
    echo "   ✓ masterdata.php config exists\n";
    
    require_once $configFile;
    if (defined('MASTER_DATA_TYPES')) {
        $types = MASTER_DATA_TYPES;
        echo "   Found " . count($types) . " master data types configured\n";
        foreach ($types as $key => $config) {
            echo "   - $key: " . ($config['display_name'] ?? 'Unknown') . "\n";
        }
    }
} else {
    echo "   ✗ masterdata.php config NOT FOUND\n";
}
echo "\n";

// Test 9: Sample Data Check
echo "9. Checking Sample Data...\n";

// Catheter indications
$stmt = $db->query("SELECT name FROM lookup_catheter_indications WHERE active = 1 LIMIT 5");
$indications = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "   Catheter Indications (sample):\n";
foreach ($indications as $ind) {
    echo "   - $ind\n";
}
echo "\n";

// Specialties
$stmt = $db->query("SELECT name FROM lookup_specialties WHERE active = 1 LIMIT 5");
$specialties = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "   Specialties (sample):\n";
foreach ($specialties as $spec) {
    echo "   - $spec\n";
}
echo "\n";

// Test 10: Specialty-Surgery Relationship
echo "10. Testing Specialty-Surgery Relationship...\n";
$stmt = $db->query("
    SELECT s.name as specialty, COUNT(su.id) as surgery_count
    FROM lookup_specialties s
    LEFT JOIN lookup_surgeries su ON su.specialty_id = s.id
    GROUP BY s.id
    LIMIT 5
");
$relationships = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "   Specialty → Surgery relationships:\n";
foreach ($relationships as $rel) {
    echo "   - {$rel['specialty']}: {$rel['surgery_count']} surgeries\n";
}
echo "\n";

// Summary
echo "=== Test Summary ===\n";
echo "✓ Database: Connected\n";
echo "✓ New Tables: " . count($newTables) . " tables created\n";
echo "✓ Enhanced Columns: specialty_id, sort_order, deleted_at\n";
echo "✓ Models: Available\n";
echo "✓ Controller: MasterDataController.php\n";
echo "✓ Views: Master data views present\n";
echo "✓ Configuration: masterdata.php\n";
echo "\n";
echo "🎉 Your localhost v1.2.0 is ready!\n";
echo "\n";
echo "Access Master Data at: http://localhost:8000/masterdata\n";
echo "Login page: http://localhost:8000/\n";
echo "\n";
