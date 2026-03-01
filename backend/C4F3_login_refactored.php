<?php
/**
 * Refactored Login Script
 * Uses: UserService, AuthHelper, ResponseHelper
 * Demonstrates: OOP usage in existing script
 */

require_once "../core/bootstrap.php";

// Only allow POST requests
if (!RequestValidator::isPost()) {
    ResponseHelper::error("Invalid request method", ["Only POST requests allowed"], 405);
}

try {
    $email = trim(RequestValidator::post('email', ''));
    $password = RequestValidator::post('password', '');

    if (empty($email) || empty($password)) {
        ResponseHelper::error("Validation failed", ["Email and password are required"], 400);
        exit;
    }

    // Use UserService for login
    $userService = new UserService();
    $user = $userService->login($email, $password);

    if (!$user) {
        // Get error message from service
        $errorMsg = $userService->getLastError() ?? "Login failed";
        ResponseHelper::error("Login failed", [$errorMsg], 401);
        exit;
    }

    // Login success - set session
    AuthHelper::setSession('user_id', $user['user_id']);
    AuthHelper::setSession('email', $user['email']);
    AuthHelper::setSession('role', $user['role']);

    // Redirect by role
    $redirectMap = [
        'vendor' => '../pages/6.C4F3_Vendor_Dashboard.html',
        'cafe_owner' => '../pages/10.C4F3_Owner_Dashboard.html',
        'admin' => '../pages/14.C4F3_Admin_Dashboard_Page.html'
    ];

    $redirectPath = $redirectMap[$user['role']] ?? '../pages/3.C4F3_Homepage.html';
    ResponseHelper::redirect($redirectPath);

} catch (PDOException $e) {
    ResponseHelper::error("Database error", [$e->getMessage()], 500);
} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
