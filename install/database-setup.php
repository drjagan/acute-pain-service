<?php
/**
 * Database Setup and Migration Script
 * Run this file once to create database and tables
 * 
 * Usage: php install/database-setup.php
 */

// Configuration - Read from config.php or environment variables
$configFile = dirname(__DIR__) . '/config/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
} else {
    // Fallback to environment variables or command line arguments
    define('DB_HOST', getenv('DB_HOST') ?: ($argv[1] ?? 'localhost'));
    define('DB_PORT', getenv('DB_PORT') ?: ($argv[2] ?? '3306'));
    define('DB_NAME', getenv('DB_NAME') ?: ($argv[3] ?? 'aps_database'));
    define('DB_USER', getenv('DB_USER') ?: ($argv[4] ?? 'root'));
    define('DB_PASS', getenv('DB_PASS') ?: ($argv[5] ?? ''));
    define('DB_CHARSET', 'utf8mb4');
}

// Show usage if password is empty
if (empty(DB_PASS) && php_sapi_name() === 'cli') {
    echo "Usage: php database-setup.php [host] [port] [database] [username] [password]\n";
    echo "Or set environment variables: DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS\n";
    echo "Or create config/config.php with database credentials\n\n";
}

echo "=====================================\n";
echo "  APS DATABASE INSTALLATION\n";
echo "=====================================\n\n";

// Step 1: Connect to MySQL (without database)
echo "[1/5] Connecting to MySQL...\n";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Connected to MySQL\n\n";
} catch (PDOException $e) {
    die("✗ Connection failed: " . $e->getMessage() . "\n");
}

// Step 2: Create database
echo "[2/5] Creating database...\n";
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    echo "✓ Database '" . DB_NAME . "' created\n\n";
} catch (PDOException $e) {
    die("✗ Database creation failed: " . $e->getMessage() . "\n");
}

// Step 3: Run migrations
echo "[3/5] Running migrations...\n";
$migrationPath = __DIR__ . '/../src/Database/migrations';
$migrations = glob($migrationPath . '/*.sql');
sort($migrations);

foreach ($migrations as $migration) {
    $filename = basename($migration);
    echo "  → Running: $filename\n";
    
    try {
        $sql = file_get_contents($migration);
        $pdo->exec($sql);
        echo "    ✓ Success\n";
    } catch (PDOException $e) {
        echo "    ✗ Failed: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// Step 4: Seed data
echo "[4/5] Seeding data...\n";
$seedPath = __DIR__ . '/../src/Database/seeds';
$seeds = glob($seedPath . '/*.sql');

foreach ($seeds as $seed) {
    $filename = basename($seed);
    echo "  → Seeding: $filename\n";
    
    try {
        $sql = file_get_contents($seed);
        $pdo->exec($sql);
        echo "    ✓ Success\n";
    } catch (PDOException $e) {
        echo "    ✗ Failed: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// Step 5: Verify installation
echo "[5/5] Verifying installation...\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "  ✓ Total tables created: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "    - $table\n";
    }
    
    // Count users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "\n  ✓ Sample users created: $userCount\n";
    
} catch (PDOException $e) {
    echo "  ✗ Verification failed: " . $e->getMessage() . "\n";
}

echo "\n=====================================\n";
echo "  INSTALLATION COMPLETE!\n";
echo "=====================================\n\n";

echo "Next steps:\n";
echo "1. Start PHP server: php -S localhost:8000 -t public/\n";
echo "2. Open browser: http://localhost:8000\n";
echo "3. Login with:\n";
echo "   - Username: admin\n";
echo "   - Password: admin123\n\n";

echo "Test accounts:\n";
echo "  Admin:     admin / admin123\n";
echo "  Attending: dr.sharma / admin123\n";
echo "  Resident:  dr.patel / admin123\n";
echo "  Nurse:     nurse.kumar / admin123\n\n";
