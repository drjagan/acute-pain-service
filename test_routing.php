<?php
// Test routing for masterdata/store/comorbidities
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/masterdata/store/comorbidities';

require_once __DIR__ . '/config/config.php';

$uri = '/masterdata/store/comorbidities';
$method = 'POST';
$parts = explode('/', trim($uri, '/'));

echo "=== ROUTING TEST ===\n";
echo "URI: $uri\n";
echo "Method: $method\n";
echo "Parts: " . print_r($parts, true) . "\n";

$controllerName = 'MasterDataController';
$action = $parts[1];
$params = array_slice($parts, 2);

echo "Controller: $controllerName\n";
echo "Action: $action\n";
echo "Params: " . print_r($params, true) . "\n";

$controllerClass = "Controllers\\{$controllerName}";
$controllerFile = SRC_PATH . "/Controllers/{$controllerName}.php";

echo "Controller class: $controllerClass\n";
echo "Controller file: $controllerFile\n";
echo "File exists: " . (file_exists($controllerFile) ? 'YES' : 'NO') . "\n";
echo "Class exists: " . (class_exists($controllerClass) ? 'YES' : 'NO') . "\n";

if (class_exists($controllerClass)) {
    $controller = new $controllerClass();
    echo "Method exists: " . (method_exists($controller, $action) ? 'YES' : 'NO') . "\n";
    
    if (method_exists($controller, $action)) {
        echo "\n✓ ROUTING WOULD WORK!\n";
    } else {
        echo "\n✗ METHOD NOT FOUND\n";
        echo "Available methods containing 'store' or 'create':\n";
        foreach (get_class_methods($controller) as $m) {
            if (stripos($m, 'store') !== false || stripos($m, 'create') !== false) {
                echo "  - $m\n";
            }
        }
    }
}
