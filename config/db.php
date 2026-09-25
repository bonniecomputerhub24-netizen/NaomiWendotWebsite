<?php
/**
 * Database Connection (PDO Singleton Pattern)
 * Naomi Wendot Writer & Ministry Website
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gudwjxjc_naomiwendot');
define('DB_USER', 'gudwjxjc_bonface');
define('DB_PASS', 'Bonface@2001#');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $connection;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error to file instead of exposing to browser
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Show generic error to user
            http_response_code(503);
            die("Service temporarily unavailable. Please try again in a moment.");
        }
    }
    
    /**
     * Close the database connection
     */
    public function closeConnection() {
        $this->connection = null;
    }
    
    /**
     * Destructor to ensure connection is closed
     */
    public function __destruct() {
        $this->closeConnection();
    }

    /**
     * Get singleton instance of Database
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper function to get database connection
 * 
 * @return PDO
 */
function getDb() {
    return Database::getInstance()->getConnection();
}
