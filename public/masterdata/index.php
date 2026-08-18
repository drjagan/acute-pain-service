<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/config.php';
}
if (session_status() === PHP_SESSION_NONE) {
    Helpers\Session::start();
}

require_once SRC_PATH . '/Controllers/MasterDataController.php';
$controller = new Controllers\MasterDataController();
$controller->index();
