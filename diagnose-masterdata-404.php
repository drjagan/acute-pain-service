<?php
/**
 * Diagnostic Script for Master Data 404 Issue
 * Run this on the production server to diagnose why /masterdata URLs return 404
 */

echo "=== Master Data 404 Diagnostic Tool ===\n\n";

// 1. Check if MasterDataController exists
echo "1. Checking MasterDataController file...\n";
$controllerPath = __DIR__ . '/src/Controllers/MasterDataController.php';
if (file_exists($controllerPath)) {
    echo "   ✓ MasterDataController.php EXISTS\n";
    echo "   Location: $controllerPath\n";
    echo "   Size: " . filesize($controllerPath) . " bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', filemtime($controllerPath)) . "\n";
} else {
    echo "   ✗ MasterDataController.php NOT FOUND\n";
    echo "   Expected at: $controllerPath\n";
}

// 2. Check if config/masterdata.php exists
echo "\n2. Checking masterdata config file...\n";
$configPath = __DIR__ . '/config/masterdata.php';
if (file_exists($configPath)) {
    echo "   ✓ config/masterdata.php EXISTS\n";
    echo "   Size: " . filesize($configPath) . " bytes\n";
} else {
    echo "   ✗ config/masterdata.php NOT FOUND\n";
}

// 3. Check if master data views exist
echo "\n3. Checking master data views...\n";
$viewsPath = __DIR__ . '/src/Views/masterdata';
if (is_dir($viewsPath)) {
    echo "   ✓ masterdata views directory EXISTS\n";
    $files = scandir($viewsPath);
    echo "   Files:\n";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "     - $file\n";
        }
    }
} else {
    echo "   ✗ masterdata views directory NOT FOUND\n";
    echo "   Expected at: $viewsPath\n";
}

// 4. Check if BaseLookupModel exists
echo "\n4. Checking BaseLookupModel...\n";
$basePath = __DIR__ . '/src/Models/BaseLookupModel.php';
if (file_exists($basePath)) {
    echo "   ✓ BaseLookupModel.php EXISTS\n";
    echo "   Size: " . filesize($basePath) . " bytes\n";
} else {
    echo "   ✗ BaseLookupModel.php NOT FOUND\n";
}

// 5. Check individual lookup models
echo "\n5. Checking lookup models...\n";
$models = [
    'LookupCatheterIndication',
    'LookupRemovalIndication',
    'LookupSentinelEvent',
    'LookupSpecialty'
];
foreach ($models as $model) {
    $modelPath = __DIR__ . "/src/Models/$model.php";
    if (file_exists($modelPath)) {
        echo "   ✓ $model.php EXISTS\n";
    } else {
        echo "   ✗ $model.php NOT FOUND\n";
    }
}

// 6. Check .htaccess
echo "\n6. Checking .htaccess file...\n";
$htaccessPath = __DIR__ . '/public/.htaccess';
if (file_exists($htaccessPath)) {
    echo "   ✓ public/.htaccess EXISTS\n";
    echo "   Checking RewriteRule for masterdata...\n";
    $htaccess = file_get_contents($htaccessPath);
    if (strpos($htaccess, 'RewriteEngine On') !== false) {
        echo "   ✓ RewriteEngine is enabled\n";
    } else {
        echo "   ✗ RewriteEngine not found\n";
    }
} else {
    echo "   ✗ public/.htaccess NOT FOUND\n";
}

// 7. Check if Apache mod_rewrite is enabled
echo "\n7. Checking Apache modules...\n";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "   ✓ mod_rewrite is ENABLED\n";
    } else {
        echo "   ✗ mod_rewrite is NOT enabled\n";
    }
} else {
    echo "   ⚠ Cannot check (apache_get_modules not available)\n";
    echo "   Run: apachectl -M | grep rewrite\n";
}

// 8. Test URL routing simulation
echo "\n8. Testing URL routing simulation...\n";
$testUrls = [
    '/masterdata',
    '/masterdata/index',
    '/masterdata/specialties',
    '/masterdata/catheter_indications'
];

foreach ($testUrls as $testUrl) {
    echo "   Testing: $testUrl\n";
    $uri = $testUrl;
    $parts = explode('/', trim($uri, '/'));
    
    if ($parts[0] === 'masterdata') {
        $action = $parts[1] ?? 'index';
        echo "     → Would call: MasterDataController@$action\n";
        
        // Check if method exists
        if (file_exists($controllerPath)) {
            require_once $controllerPath;
            if (class_exists('Controllers\MasterDataController')) {
                $controller = new \ReflectionClass('Controllers\MasterDataController');
                if ($controller->hasMethod($action)) {
                    echo "     ✓ Method exists: $action()\n";
                } else {
                    echo "     ✗ Method NOT found: $action()\n";
                    echo "     Available methods:\n";
                    foreach ($controller->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                        if (!$method->isConstructor() && !$method->isDestructor()) {
                            echo "       - " . $method->getName() . "()\n";
                        }
                    }
                }
            }
        }
    }
}

// 9. Check database tables
echo "\n9. Checking database tables...\n";
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
    try {
        $db = new \PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS
        );
        
        $stmt = $db->query("SHOW TABLES LIKE 'lookup_%'");
        $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        echo "   Found " . count($tables) . " lookup tables:\n";
        foreach ($tables as $table) {
            echo "     - $table\n";
        }
        
        if (count($tables) === 9) {
            echo "   ✓ All 9 lookup tables exist\n";
        } else {
            echo "   ✗ Expected 9 tables, found " . count($tables) . "\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✗ config/config.php not found\n";
}

// 10. Check file permissions
echo "\n10. Checking file permissions...\n";
$pathsToCheck = [
    '/src/Controllers/MasterDataController.php',
    '/config/masterdata.php',
    '/src/Views/masterdata',
    '/src/Models/BaseLookupModel.php'
];

foreach ($pathsToCheck as $path) {
    $fullPath = __DIR__ . $path;
    if (file_exists($fullPath) || is_dir($fullPath)) {
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        echo "   $path: $perms\n";
    }
}

echo "\n=== Diagnosis Complete ===\n";
echo "\nTo fix 404 errors, check:\n";
echo "1. All MasterDataController files copied to /app/data/src/\n";
echo "2. Apache mod_rewrite is enabled\n";
echo "3. .htaccess file exists in /app/data/public/\n";
echo "4. File permissions are correct (644 for files, 755 for directories)\n";
echo "\nRun this on production: php diagnose-masterdata-404.php\n";
