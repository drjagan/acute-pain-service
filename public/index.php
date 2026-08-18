<?php
/**
 * Acute Pain Service Application
 * Front Controller - Entry Point
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Helpers\Session::start();

/**
 * Render the application 404 response.
 */
function apsRenderNotFound(): void
{
    http_response_code(404);

    if (file_exists(VIEWS_PATH . '/errors/404.php')) {
        include VIEWS_PATH . '/errors/404.php';
        return;
    }

    echo "<h1>404 - Page Not Found</h1>";
}

/**
 * Render the application 405 response.
 */
function apsRenderMethodNotAllowed(array $allowedMethods): void
{
    http_response_code(405);
    header('Allow: ' . implode(', ', $allowedMethods));
    echo "<h1>405 - Method Not Allowed</h1>";
}

/**
 * Construct and invoke the matched controller action.
 */
function apsDispatchController(array $match): void
{
    $controllerName = $match['controller'];
    $action = $match['action'];
    $controllerClass = "Controllers\\{$controllerName}";
    $controllerFile = SRC_PATH . "/Controllers/{$controllerName}.php";

    if (!file_exists($controllerFile) || !class_exists($controllerClass)) {
        http_response_code(404);
        echo "Controller not found";
        return;
    }

    $controller = new $controllerClass();

    if (!is_callable([$controller, $action])) {
        error_log("Action '$action' not callable in $controllerClass");
        apsRenderNotFound();
        return;
    }

    call_user_func_array([$controller, $action], $match['params']);
}

// Get request URI and method
$uri = \Routing\Router::normalizePath($_SERVER['REQUEST_URI'] ?? '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Default route
if (empty($uri) || $uri === '/') {
    if (isAuthenticated()) {
        $uri = '/dashboard';
    } else {
        $uri = '/auth/login';
    }
}

$router = new \Routing\Router(require ROOT_PATH . '/config/routes.php');
$match = $router->match($method, $uri);

if ($match['status'] === 'matched') {
    (new \Routing\ActionPolicy())->enforce($match['policy']);
    apsDispatchController($match);
    return;
}

if ($match['status'] === 'method_not_allowed') {
    apsRenderMethodNotAllowed($match['allowed_methods']);
    return;
}

apsRenderNotFound();
