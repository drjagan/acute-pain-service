<?php
namespace Helpers;

/**
 * Session Management Helper
 */
class Session {
    
    /**
     * Start session if not already started
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set custom session save path if default is not writable (common on XAMPP/localhost)
            // Must be set before session_start()
            $defaultPath = session_save_path();
            if (empty($defaultPath) || !is_writable($defaultPath)) {
                $customPath = dirname(__DIR__, 2) . '/storage/sessions';
                if (!is_dir($customPath)) {
                    @mkdir($customPath, 0777, true);
                }
                if (is_writable($customPath)) {
                    @session_save_path($customPath);
                }
            }
            
            // Suppress warnings for session start (handles permission issues gracefully)
            @session_start();
            
            // Check session timeout
            if (isset($_SESSION['last_activity'])) {
                $timeout = $_SESSION['timeout'] ?? SESSION_LIFETIME;
                if (time() - $_SESSION['last_activity'] > $timeout) {
                    self::destroy();
                    \Helpers\Flash::error('Your session has expired. Please login again.');
                    redirect('/auth/login?timeout=1');
                }
            }
            
            $_SESSION['last_activity'] = time();
        }
    }
    
    /**
     * Set session value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session key
     */
    public static function remove($key) {
        unset($_SESSION[$key]);
    }
    
    /**
     * Destroy session
     */
    public static function destroy() {
        session_destroy();
        $_SESSION = [];
    }
    
    /**
     * Regenerate session ID (prevent session fixation)
     */
    public static function regenerate() {
        session_regenerate_id(true);
    }
    
    /**
     * Get all session data
     */
    public static function all() {
        return $_SESSION;
    }
    
    /**
     * Flash data (available for next request only)
     */
    public static function flash($key, $value) {
        $_SESSION['_flash'][$key] = $value;
    }
    
    /**
     * Get flash data
     */
    public static function getFlash($key, $default = null) {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
    
    /**
     * Check if flash data exists
     */
    public static function hasFlash($key) {
        return isset($_SESSION['_flash'][$key]);
    }
}
