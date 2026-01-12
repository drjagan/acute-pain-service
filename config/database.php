<?php
/**
 * Database Configuration and Connection Handler
 * 
 * This file loads database credentials from .env file (recommended)
 * and provides a singleton Database connection class.
 */

// Load environment variables from .env file
require_once __DIR__ . '/env-loader.php';
loadEnv(dirname(__DIR__));

// Define database constants from environment variables
// These provide defaults for development if .env is not present
if (!defined('DB_HOST')) define('DB_HOST', env('DB_HOST', 'localhost'));
if (!defined('DB_PORT')) define('DB_PORT', env('DB_PORT', '3306'));
if (!defined('DB_NAME')) define('DB_NAME', env('DB_NAME', 'aps_database'));
if (!defined('DB_USER')) define('DB_USER', env('DB_USER', 'root'));
if (!defined('DB_PASS')) define('DB_PASS', env('DB_PASS', ''));
if (!defined('DB_CHARSET')) define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

/**
 * Database Singleton Class
 * Provides a single PDO connection instance throughout the application
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Private constructor - Singleton pattern
     * Establishes database connection on first instantiation
     */
    private function __construct() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log error
            error_log("Database connection failed: " . $e->getMessage());
            
            // User-friendly error message
            if (defined('APP_ENV') && APP_ENV === 'development') {
                die("<h1>Database Connection Failed</h1>" .
                    "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>" .
                    "<p><strong>Host:</strong> " . htmlspecialchars(DB_HOST) . ":" . htmlspecialchars(DB_PORT) . "</p>" .
                    "<p><strong>Database:</strong> " . htmlspecialchars(DB_NAME) . "</p>" .
                    "<p><strong>User:</strong> " . htmlspecialchars(DB_USER) . "</p>" .
                    "<hr>" .
                    "<p><strong>Troubleshooting:</strong></p>" .
                    "<ul>" .
                    "<li>Ensure MySQL is running</li>" .
                    "<li>Check database credentials in <code>.env</code> file</li>" .
                    "<li>Verify database <code>" . htmlspecialchars(DB_NAME) . "</code> exists</li>" .
                    "<li>Confirm user <code>" . htmlspecialchars(DB_USER) . "</code> has access</li>" .
                    "</ul>");
            } else {
                die("<h1>Service Unavailable</h1>" .
                    "<p>Database connection failed. Please contact your system administrator.</p>");
            }
        }
    }
    
    /**
     * Get singleton instance
     * 
     * @return PDO Database connection instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }
    
    /**
     * Prevent cloning of singleton
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of singleton
     * 
     * @throws Exception
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
