<?php
/**
 * Migration Runner for v1.1.0
 * Run the three new migrations for v1.1.0
 */

// Include config
require_once __DIR__ . '/config/config.php';

echo "\n";
echo "========================================\n";
echo "  APS - v1.1.0 Migration Runner\n";
echo "========================================\n";
echo "\n";

// Connect to database
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✓ Connected to database: " . DB_NAME . "\n\n";
    
} catch (PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Migrations to run
$migrations = [
    '010_create_patient_physicians_table.sql',
    '011_create_notifications_table.sql',
    '012_create_smtp_settings_table.sql'
];

$migrationPath = __DIR__ . '/src/Database/migrations/';

echo "Running v1.1.0 migrations...\n";
echo "========================================\n\n";

$successCount = 0;
$errorCount = 0;

foreach ($migrations as $migration) {
    $filePath = $migrationPath . $migration;
    
    if (!file_exists($filePath)) {
        echo "✗ Migration file not found: {$migration}\n";
        $errorCount++;
        continue;
    }
    
    echo "Running: {$migration}\n";
    
    try {
        // Read migration file
        $sql = file_get_contents($filePath);
        
        // Execute migration
        $db->exec($sql);
        
        echo "  ✓ Success\n\n";
        $successCount++;
        
    } catch (PDOException $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
        $errorCount++;
    }
}

echo "========================================\n";
echo "Migration Summary:\n";
echo "  ✓ Successful: {$successCount}\n";
echo "  ✗ Failed: {$errorCount}\n";
echo "========================================\n\n";

// Verify tables were created
echo "Verifying tables...\n";
echo "========================================\n";

$tables = ['patient_physicians', 'notifications', 'smtp_settings'];

foreach ($tables as $table) {
    try {
        $stmt = $db->query("SHOW TABLES LIKE '{$table}'");
        $result = $stmt->fetch();
        
        if ($result) {
            // Get row count
            $countStmt = $db->query("SELECT COUNT(*) as count FROM {$table}");
            $count = $countStmt->fetch()['count'];
            
            echo "  ✓ Table '{$table}' exists ({$count} rows)\n";
        } else {
            echo "  ✗ Table '{$table}' not found\n";
        }
        
    } catch (PDOException $e) {
        echo "  ✗ Error checking '{$table}': " . $e->getMessage() . "\n";
    }
}

echo "\n";
echo "========================================\n";
echo "Migration complete!\n";
echo "========================================\n";
echo "\n";

if ($errorCount === 0) {
    echo "✓ All migrations ran successfully!\n";
    echo "\n";
    echo "Next steps:\n";
    echo "1. Test the notification system at: http://localhost:8000/\n";
    echo "2. Create a test patient and assign physicians\n";
    echo "3. Check notifications in the bell icon\n";
    echo "4. Configure SMTP settings (admin only)\n";
    echo "\n";
} else {
    echo "⚠ Some migrations failed. Please review errors above.\n";
    echo "\n";
}
