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
        if (!isset($_SESSION)) {
            session_start();
        }
        
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
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Check CSRF token from request
     */
    public static function check() {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        
        if (!self::validate($token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
        
        return true;
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
