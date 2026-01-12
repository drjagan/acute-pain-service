<?php
/**
 * Simple .env File Loader
 * Loads environment variables from .env file
 * 
 * This is a lightweight implementation without external dependencies.
 * For production apps with complex needs, consider using vlucas/phpdotenv package.
 */

/**
 * Load environment variables from .env file
 * 
 * @param string $path Directory containing .env file
 * @return bool True if loaded successfully, false otherwise
 */
function loadEnv($path = null) {
    // Default to root directory
    if ($path === null) {
        $path = dirname(__DIR__);
    }
    
    $envFile = rtrim($path, '/') . '/.env';
    
    // Check if .env file exists
    if (!file_exists($envFile)) {
        error_log("[APS] .env file not found at: $envFile");
        return false;
    }
    
    // Read and parse .env file
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if ($lines === false) {
        error_log("[APS] Failed to read .env file");
        return false;
    }
    
    foreach ($lines as $line) {
        // Skip comments and empty lines
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE format
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set in environment
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

/**
 * Get environment variable with fallback
 */
function env($key, $default = null) {
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    // Handle boolean values
    if (in_array(strtolower($value = strtolower($value)), ['true', 'false', '(true)', '(false)'])) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
    
    return $value;
}
