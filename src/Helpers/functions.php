<?php
/**
 * Global Helper Functions
 */

/**
 * Escape output for XSS protection
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    if (!str_starts_with($url, 'http')) {
        $url = BASE_URL . '/' . ltrim($url, '/');
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Get current URL
 */
function currentUrl() {
    return $_SERVER['REQUEST_URI'];
}

/**
 * Check if current route matches
 */
function isRoute($route) {
    $current = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $route = trim($route, '/');
    return $current === $route || str_starts_with($current, $route . '/');
}

/**
 * Format date for display
 */
function formatDate($date, $format = DATE_FORMAT_DISPLAY) {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = DATETIME_FORMAT_DISPLAY) {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * Calculate time ago
 */
function timeAgo($datetime) {
    if (empty($datetime)) return '';
    
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return $diff . ' seconds ago';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('d M Y', $time);
}

/**
 * Get user's IP address
 */
function getIpAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Log message to file
 */
function logMessage($message, $level = 'INFO') {
    if (!LOG_ENABLED) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
}

/**
 * Debug helper (only in development)
 */
function dd(...$vars) {
    if (APP_ENV !== 'development') return;
    
    echo '<pre>';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    die();
}

/**
 * Get asset URL
 */
function asset($path) {
    return ASSETS_URL . '/' . ltrim($path, '/');
}

/**
 * Generate random string
 */
function randomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return Helpers\Session::has('user_id');
}

/**
 * Get current user
 */
function currentUser() {
    return [
        'id' => Helpers\Session::get('user_id'),
        'username' => Helpers\Session::get('username'),
        'email' => Helpers\Session::get('email'),
        'role' => Helpers\Session::get('role'),
        'first_name' => Helpers\Session::get('first_name'),
        'last_name' => Helpers\Session::get('last_name'),
    ];
}

/**
 * Check if user has role
 */
function hasRole($role) {
    return Helpers\Session::get('role') === $role;
}

/**
 * Check if user has any of the roles
 */
function hasAnyRole($roles) {
    $userRole = Helpers\Session::get('role');
    return in_array($userRole, (array)$roles);
}
