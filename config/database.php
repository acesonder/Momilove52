<?php
/**
 * Database Configuration for Momilove52
 * 
 * This file contains the database connection settings and error handling
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'outsrglr_mom');
define('DB_USER', 'outsrglr_mom');
define('DB_PASS', 'born#1852Niptuck');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'Momilove52 Care Tracker');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost');

// Security configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('BCRYPT_COST', 12);
define('CSRF_TOKEN_LIFETIME', 1800); // 30 minutes

// File upload configuration
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);

// Logging configuration
define('LOG_FILE', __DIR__ . '/../logs/app.log');
define('ERROR_LOG_FILE', __DIR__ . '/../logs/error.log');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR, CRITICAL

// Timezone setting
date_default_timezone_set('America/Regina'); // Saskatchewan time

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users
ini_set('log_errors', 1);
ini_set('error_log', ERROR_LOG_FILE);

/**
 * Database connection class with error handling
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Log successful connection
            $this->logInfo("Database connection established successfully");
            
        } catch (PDOException $e) {
            $this->logError("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a prepared statement with error handling
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                throw new Exception("Query execution failed");
            }
            
            return $stmt;
            
        } catch (PDOException $e) {
            $this->logError("Database query failed: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Database operation failed. Please try again.");
        }
    }
    
    /**
     * Get a single record
     */
    public function fetchOne($sql, $params = []) {
        try {
            $stmt = $this->execute($sql, $params);
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->logError("Failed to fetch single record: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get multiple records
     */
    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->execute($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logError("Failed to fetch records: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Insert a record and return the ID
     */
    public function insert($sql, $params = []) {
        try {
            $this->execute($sql, $params);
            return $this->connection->lastInsertId();
        } catch (Exception $e) {
            $this->logError("Failed to insert record: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Log error to database and file
     */
    private function logError($message, $level = 'ERROR') {
        // Log to file
        error_log(date('Y-m-d H:i:s') . " [$level] $message" . PHP_EOL, 3, ERROR_LOG_FILE);
        
        // Try to log to database (if connection is available)
        try {
            if ($this->connection) {
                $sql = "INSERT INTO error_logs (error_level, error_message, file_path, line_number, user_agent, ip_address) VALUES (?, ?, ?, ?, ?, ?)";
                $backtrace = debug_backtrace();
                $file = $backtrace[1]['file'] ?? 'unknown';
                $line = $backtrace[1]['line'] ?? 0;
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([$level, $message, $file, $line, $userAgent, $ipAddress]);
            }
        } catch (Exception $e) {
            // If database logging fails, just log to file
            error_log(date('Y-m-d H:i:s') . " [ERROR] Failed to log to database: " . $e->getMessage() . PHP_EOL, 3, ERROR_LOG_FILE);
        }
    }
    
    /**
     * Log info message
     */
    private function logInfo($message) {
        if (LOG_LEVEL === 'DEBUG' || LOG_LEVEL === 'INFO') {
            error_log(date('Y-m-d H:i:s') . " [INFO] $message" . PHP_EOL, 3, LOG_FILE);
        }
    }
}

/**
 * Global error handler
 */
function globalErrorHandler($errno, $errstr, $errfile, $errline) {
    $message = "Error [$errno]: $errstr in $errfile on line $errline";
    error_log(date('Y-m-d H:i:s') . " [ERROR] $message" . PHP_EOL, 3, ERROR_LOG_FILE);
    
    // Don't execute PHP internal error handler
    return true;
}

/**
 * Global exception handler
 */
function globalExceptionHandler($exception) {
    $message = "Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    error_log(date('Y-m-d H:i:s') . " [CRITICAL] $message" . PHP_EOL, 3, ERROR_LOG_FILE);
    
    // Show user-friendly error page
    if (!headers_sent()) {
        http_response_code(500);
        include __DIR__ . '/../includes/error_page.php';
    }
}

// Set error and exception handlers
set_error_handler('globalErrorHandler');
set_exception_handler('globalExceptionHandler');

/**
 * Utility function to sanitize input
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           isset($_SESSION['csrf_token_time']) && 
           (time() - $_SESSION['csrf_token_time']) <= CSRF_TOKEN_LIFETIME &&
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Check user role
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        throw new Exception("Access denied. Required role: $role");
    }
}
?>