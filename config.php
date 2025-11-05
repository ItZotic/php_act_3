<?php
// config.php - Store this OUTSIDE the web root in production
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'postgres');
define('DB_PASS', '1234567890'); // Change this to YOUR PostgreSQL password

// CSRF Token Generation
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Validation
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Database Connection Singleton
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $this->pdo = new PDO(
                "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log($e->getMessage()); // Log instead of displaying
            die("Database connection failed. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

// Rate Limiting (Simple Implementation)
function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 900) {
    session_start();
    $key = 'rate_limit_' . $identifier;
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + $timeWindow];
    }
    
    if (time() > $_SESSION[$key]['reset_time']) {
        $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + $timeWindow];
    }
    
    $_SESSION[$key]['count']++;
    
    if ($_SESSION[$key]['count'] > $maxAttempts) {
        http_response_code(429);
        die("Too many attempts. Please try again in " . 
            ceil(($_SESSION[$key]['reset_time'] - time()) / 60) . " minutes.");
    }
}

// Input Sanitization Helper
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>