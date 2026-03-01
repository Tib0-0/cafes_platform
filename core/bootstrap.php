<?php
/**
 * Bootstrap File - Application Bootstrap
 * Demonstrates: Encapsulation, Initialization
 * Initializes the application, loads classes and configuration
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define paths
define('BASE_PATH', __DIR__ . '/../');
define('CORE_PATH', BASE_PATH . 'core/');
define('CONFIG_PATH', BASE_PATH . 'config/');
define('UPLOAD_DIR', BASE_PATH . 'uploads/');
define('PRODUCT_IMAGE_DIR', UPLOAD_DIR . 'products/');

// Define URLs
define('SITE_URL', 'http://localhost/cafes_platform');

// Create upload directories if they don't exist
if (!file_exists(PRODUCT_IMAGE_DIR)) {
    mkdir(PRODUCT_IMAGE_DIR, 0777, true);
}

// Load Database configuration
require_once CONFIG_PATH . 'database.php';

// Load Autoloader
require_once CORE_PATH . 'Autoloader.php';

/**
 * Response Helper
 * Demonstrates: Encapsulation
 * Provides standardized response format for API endpoints
 */
class ResponseHelper {
    
    /**
     * Send JSON response
     * @param mixed $data
     * @param int $statusCode
     */
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Send success response
     * @param mixed $data
     * @param string $message
     */
    public static function success($data, $message = "Success") {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], 200);
    }

    /**
     * Send error response
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     */
    public static function error($message, $errors = [], $statusCode = 400) {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * Send redirect response
     * @param string $path
     */
    public static function redirect($path) {
        header("Location: {$path}");
        exit;
    }
}

/**
 * RequestValidator Helper
 * Demonstrates: Encapsulation
 * Validates HTTP requests
 */
class RequestValidator {
    
    /**
     * Check if request method is POST
     * @return bool
     */
    public static function isPost() {
        return $_SERVER["REQUEST_METHOD"] === "POST";
    }

    /**
     * Check if request method is GET
     * @return bool
     */
    public static function isGet() {
        return $_SERVER["REQUEST_METHOD"] === "GET";
    }

    /**
     * Get POST parameter
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function post($key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET parameter
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    /**
     * Check if POST parameter exists
     * @param string $key
     * @return bool
     */
    public static function hasPost($key) {
        return isset($_POST[$key]);
    }

    /**
     * Get session value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function session($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     * @param string $key
     * @param mixed $value
     */
    public static function setSession($key, $value) {
        $_SESSION[$key] = $value;
    }
}

/**
 * AuthHelper Class
 * Demonstrates: Encapsulation, Abstraction
 * Handles authentication logic
 */
class AuthHelper {
    
    /**
     * Get current logged-in user ID
     * @return int|null
     */
    public static function userId() {
        return self::isLoggedIn() ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Get current logged-in user email
     * @return string|null
     */
    public static function userEmail() {
        return self::isLoggedIn() ? $_SESSION['email'] : null;
    }

    /**
     * Get current logged-in user role
     * @return string|null
     */
    public static function userRole() {
        return self::isLoggedIn() ? $_SESSION['role'] : null;
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['role']);
    }

    /**
     * Check if user is admin
     * @return bool
     */
    public static function isAdmin() {
        return self::isLoggedIn() && $_SESSION['role'] === 'admin';
    }

    /**
     * Check if user is vendor
     * @return bool
     */
    public static function isVendor() {
        return self::isLoggedIn() && $_SESSION['role'] === 'vendor';
    }

    /**
     * Check if user is cafe owner
     * @return bool
     */
    public static function isCafeOwner() {
        return self::isLoggedIn() && $_SESSION['role'] === 'cafe_owner';
    }

    /**
     * Logout user
     */
    public static function logout() {
        session_destroy();
        self::redirect('../pages/1.C4F3_login.html');
    }

    /**
     * Redirect if not logged in
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            ResponseHelper::redirect('../pages/1.C4F3_login.html');
        }
    }

    /**
     * Redirect if not admin
     */
    public static function requireAdmin() {
        if (!self::isAdmin()) {
            ResponseHelper::error("Unauthorized", ["Access denied"], 403);
        }
    }

    /**
     * Redirect if not vendor
     */
    public static function requireVendor() {
        if (!self::isVendor()) {
            ResponseHelper::error("Unauthorized", ["Access denied"], 403);
        }
    }

    /**
     * Set session value
     * @param string $key
     * @param mixed $value
     */
    public static function setSession($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Redirect helper
     */
    private static function redirect($path) {
        header("Location: {$path}");
        exit;
    }
}
?>
