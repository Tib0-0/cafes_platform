<?php
/**
 * Refactored Registration Script
 * Uses: UserService, ResponseHelper
 * Demonstrates: OOP usage in existing script
 */

require_once "../core/bootstrap.php";

// Only allow POST requests
if (!RequestValidator::isPost()) {
    ResponseHelper::error("Invalid request method", ["Only POST requests allowed"], 405);
}

try {
    // Get and validate input
    $businessName = trim(RequestValidator::post('business_name', ''));
    $email = trim(RequestValidator::post('email', ''));
    $password = RequestValidator::post('password', '');
    $confirmPassword = RequestValidator::post('confirm_password', '');
    $role = RequestValidator::post('role', '');

    // Basic validation
    if (empty($businessName) || empty($email) || empty($password) || empty($role)) {
        ResponseHelper::error("Validation failed", ["All fields are required"], 400);
    }

    if ($password !== $confirmPassword) {
        ResponseHelper::error("Validation failed", ["Passwords do not match"], 400);
    }

    // Use UserService for registration
    $userService = new UserService();
    $userId = $userService->register([
        'business_name' => $businessName,
        'username' => $businessName,
        'email' => $email,
        'password' => $password,
        'role' => $role
    ]);

    if (!$userId) {
        $errors = $userService->getErrors();
        ResponseHelper::error("Registration failed", $errors, 400);
    }

    // Registration success - redirect to login
    ResponseHelper::redirect('../pages/1.C4F3_login.html');

} catch (PDOException $e) {
    ResponseHelper::error("Database error", [$e->getMessage()], 500);
} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
