<?php
namespace Helpers;

/**
 * Flash Message Helper
 */
class Flash {
    
    /**
     * Set flash message
     */
    private static function set($type, $message) {
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION['_flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Success message
     */
    public static function success($message) {
        self::set(FLASH_SUCCESS, $message);
    }
    
    /**
     * Error message
     */
    public static function error($message) {
        self::set(FLASH_ERROR, $message);
    }
    
    /**
     * Warning message
     */
    public static function warning($message) {
        self::set(FLASH_WARNING, $message);
    }
    
    /**
     * Info message
     */
    public static function info($message) {
        self::set(FLASH_INFO, $message);
    }
    
    /**
     * Get all flash messages
     */
    public static function get() {
        if (!isset($_SESSION)) {
            return [];
        }
        
        $messages = $_SESSION['_flash_messages'] ?? [];
        unset($_SESSION['_flash_messages']);
        return $messages;
    }
    
    /**
     * Check if there are any flash messages
     */
    public static function has() {
        return !empty($_SESSION['_flash_messages']);
    }
    
    /**
     * Display flash messages as HTML
     */
    public static function display() {
        $messages = self::get();
        if (empty($messages)) {
            return '';
        }
        
        $html = '';
        foreach ($messages as $flash) {
            $type = htmlspecialchars($flash['type']);
            $message = htmlspecialchars($flash['message']);
            $html .= "<div class=\"alert alert-{$type} alert-dismissible fade show\" role=\"alert\">";
            $html .= $message;
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            $html .= '</div>';
        }
        
        return $html;
    }
}
