<?php
if (!defined('APP_PATH')) {
    require_once __DIR__ . '/../../../../config/config.php';
}
if (session_status() === PHP_SESSION_NONE) {
    Helpers\Session::start();
}

// Get the lookup type from the URL path, not the directory name
// This handles symlinks correctly
$pathParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$actionIndex = array_search('list', $pathParts);
if ($actionIndex !== false && isset($pathParts[$actionIndex + 1])) {
    $lookupType = $pathParts[$actionIndex + 1];
} else {
    $lookupType = basename(__DIR__);
}

require_once SRC_PATH . '/Controllers/MasterDataController.php';
$controller = new Controllers\MasterDataController();
$controller->list($lookupType);
