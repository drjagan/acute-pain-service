<?php
namespace Helpers;

/**
 * CSRF Protection Helper
 */
class CSRF {
    
    /**
     * Generate CSRF token
     */
    public static function generate() {
        // Ensure session is started properly
        Session::start();
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Get CSRF token
     */
    public static function token() {
        return self::generate();
    }
    
    /**
     * Validate CSRF token
     */
    public static function validate($token) {
        // Ensure session is started properly
        Session::start();
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Check CSRF token from request
     */
    public static function check() {
        $token = self::requestToken();
        
        if (!self::validate($token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
        
        return true;
    }

    /**
     * Read CSRF token from approved request locations.
     */
    public static function requestToken() {
        if (!empty($_POST['csrf_token'])) {
            return $_POST['csrf_token'];
        }

        if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        if (!empty($_SERVER['HTTP_X_XSRF_TOKEN'])) {
            return $_SERVER['HTTP_X_XSRF_TOKEN'];
        }

        if (!empty($_GET['csrf_token'])) {
            return $_GET['csrf_token'];
        }

        return '';
    }
    
    /**
     * Generate CSRF input field
     */
    public static function field() {
        $token = self::token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Generate CSRF meta tag
     */
    public static function meta() {
        $token = self::token();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }
}
