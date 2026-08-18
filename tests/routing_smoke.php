<?php
/**
 * Minimal route-table smoke checks without requiring a database connection.
 */

require_once __DIR__ . '/../config/config.php';

$router = new \Routing\Router(require ROOT_PATH . '/config/routes.php');

$failures = [];

/**
 * Assert that a request resolves to a controller action.
 */
$expectRoute = static function (
    string $method,
    string $path,
    string $controller,
    string $action,
    array $params = [],
    bool $csrf = false
) use ($router, &$failures): void {
    $match = $router->match($method, $path);

    if ($match['status'] !== 'matched') {
        $failures[] = "$method $path expected matched, got {$match['status']}";
        return;
    }

    if ($match['controller'] !== $controller || $match['action'] !== $action) {
        $actual = $match['controller'] . '@' . $match['action'];
        $failures[] = "$method $path expected $controller@$action, got $actual";
    }

    if ($match['params'] !== $params) {
        $failures[] = "$method $path parameter mismatch";
    }

    $hasCsrf = !empty($match['policy']['csrf']);
    if ($hasCsrf !== $csrf) {
        $failures[] = "$method $path CSRF policy mismatch";
    }
};

/**
 * Assert that a request resolves to a router status.
 */
$expectStatus = static function (
    string $method,
    string $path,
    string $status
) use ($router, &$failures): void {
    $match = $router->match($method, $path);

    if ($match['status'] !== $status) {
        $failures[] = "$method $path expected $status, got {$match['status']}";
    }
};

$expectRoute('GET', '/dashboard', 'DashboardController', 'index');
$expectRoute('GET', '/patients/viewPatient/42', 'PatientController', 'viewPatient', ['42']);
$expectRoute('POST', '/patients/update/42', 'PatientController', 'update', ['42'], true);
$expectRoute('GET', '/masterdata/list/drugs/', 'MasterDataController', 'list', ['drugs']);
$expectRoute(
    'POST',
    '/masterdata/toggleActive/drugs/7/',
    'MasterDataController',
    'toggleActive',
    ['drugs', '7'],
    true
);
$expectRoute(
    'POST',
    '/masterdata/reorderChildren/surgeries/',
    'MasterDataController',
    'reorderChildren',
    ['surgeries'],
    true
);
$expectRoute('POST', '/notifications/markAllAsRead', 'NotificationController', 'markAllAsRead', [], true);
$expectRoute('POST', '/settings/testSMTP', 'SettingsController', 'testSMTP', [], true);
$expectStatus('GET', '/patients/store', 'method_not_allowed');
$expectStatus('GET', '/unknown/path', 'not_found');

if (!empty($failures)) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo "Routing smoke checks passed." . PHP_EOL;
