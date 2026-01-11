<?php
/**
 * Acute Pain Service Application
 * Front Controller - Entry Point
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Start session
Helpers\Session::start();

// Get request URI and method
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string
$uri = strtok($uri, '?');

// Remove trailing slash
$uri = rtrim($uri, '/');

// Default route
if (empty($uri) || $uri === '/') {
    if (isAuthenticated()) {
        $uri = '/dashboard';
    } else {
        $uri = '/auth/login';
    }
}

// Simple router
$routes = [
    // Auth routes
    'GET /auth/login' => 'AuthController@login',
    'POST /auth/login' => 'AuthController@authenticate',
    'GET /auth/logout' => 'AuthController@logout',
    'GET /auth/forgot-password' => 'AuthController@forgotPassword',
    'POST /auth/forgot-password' => 'AuthController@sendResetLink',
    'GET /auth/reset-password' => 'AuthController@resetPassword',
    'POST /auth/reset-password' => 'AuthController@updatePassword',
    
    // Dashboard
    'GET /dashboard' => 'DashboardController@index',
    
    // Patients (Screen 1)
    'GET /patients' => 'PatientController@index',
    'GET /patients/create' => 'PatientController@create',
    'POST /patients/store' => 'PatientController@store',
    'POST /patients/check-hospital-number' => 'PatientController@checkHospitalNumber',
    // Note: /patients/viewPatient/:id, /patients/edit/:id, /patients/update/:id, /patients/delete/:id 
    // are handled by dynamic routing below
];

$route = $method . ' ' . $uri;

// Check if route exists
if (isset($routes[$route])) {
    list($controllerName, $action) = explode('@', $routes[$route]);
    
    $controllerClass = "Controllers\\{$controllerName}";
    $controllerFile = SRC_PATH . "/Controllers/{$controllerName}.php";
    
    if (file_exists($controllerFile)) {
        $controller = new $controllerClass();
        $controller->$action();
    } else {
        http_response_code(404);
        echo "Controller not found";
    }
} else {
    // Try dynamic routing for future routes
    $parts = explode('/', trim($uri, '/'));
    
    if (count($parts) >= 1) {
        // Map plural routes to singular controller names
        $controllerMap = [
            'patients' => 'PatientController',
            'catheters' => 'CatheterController',
            'regimes' => 'DrugRegimeController',
            'outcomes' => 'FunctionalOutcomeController',
            'reports' => 'ReportController',
            'users' => 'UserController',
            'notifications' => 'NotificationController',
            'settings' => 'SettingsController',
        ];
        
        $controllerName = $controllerMap[$parts[0]] ?? ucfirst($parts[0]) . 'Controller';
        $action = $parts[1] ?? 'index';
        $params = array_slice($parts, 2);
        
        $controllerClass = "Controllers\\{$controllerName}";
        $controllerFile = SRC_PATH . "/Controllers/{$controllerName}.php";
        
        if (file_exists($controllerFile) && class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if (method_exists($controller, $action)) {
                call_user_func_array([$controller, $action], $params);
                exit;
            }
        }
    }
    
    // 404 - Not found
    http_response_code(404);
    if (file_exists(VIEWS_PATH . '/errors/404.php')) {
        include VIEWS_PATH . '/errors/404.php';
    } else {
        echo "<h1>404 - Page Not Found</h1>";
    }
}
