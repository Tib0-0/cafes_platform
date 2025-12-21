<?php
/**
 * General Configuration
 * Cafes Platform
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Manila');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Site URL
define('SITE_URL', 'http://localhost/cafes_platform');

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('PRODUCT_IMAGE_DIR', UPLOAD_DIR . 'products/');

// Create upload directories if they don't exist
if (!file_exists(PRODUCT_IMAGE_DIR)) {
    mkdir(PRODUCT_IMAGE_DIR, 0777, true);
}

// Helper functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_role() {
    return $_SESSION['role'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . SITE_URL . '/1.C4F3_login.html');
        exit();
    }
}

function require_role($role) {
    require_login();
    if (get_user_role() !== $role) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }
}

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function get_user_agent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}
?>
