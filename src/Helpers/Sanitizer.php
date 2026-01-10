<?php
namespace Helpers;

/**
 * Input Sanitization Helper
 */
class Sanitizer {
    
    /**
     * Sanitize string
     */
    public static function string($input) {
        return trim(strip_tags($input ?? ''));
    }
    
    /**
     * Sanitize email
     */
    public static function email($input) {
        return filter_var($input, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Sanitize integer
     */
    public static function integer($input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Sanitize float
     */
    public static function float($input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    
    /**
     * Sanitize URL
     */
    public static function url($input) {
        return filter_var($input, FILTER_SANITIZE_URL);
    }
    
    /**
     * Sanitize JSON
     */
    public static function json($input) {
        $decoded = json_decode($input, true);
        return $decoded ?: [];
    }
    
    /**
     * Sanitize array
     */
    public static function array($input) {
        if (!is_array($input)) {
            return [];
        }
        
        return array_map([self::class, 'string'], $input);
    }
    
    /**
     * Sanitize boolean
     */
    public static function boolean($input) {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }
    
    /**
     * Clean HTML (allow safe tags)
     */
    public static function html($input, $allowedTags = '<p><br><strong><em><ul><ol><li>') {
        return strip_tags($input ?? '', $allowedTags);
    }
}
