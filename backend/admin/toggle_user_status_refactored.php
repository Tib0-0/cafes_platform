<?php
/**
 * Refactored Admin Toggle User Status Script
 * Uses: UserService, AuthHelper, ResponseHelper
 * Demonstrates: OOP usage with admin authorization
 */

header('Content-Type: application/json');
require_once "../../core/bootstrap.php";

// Check if user is authenticated and is an admin
AuthHelper::requireLogin();
AuthHelper::requireAdmin();

// Only allow POST requests
if (!RequestValidator::isPost()) {
    ResponseHelper::error("Invalid request method", ["Only POST requests allowed"], 405);
}

try {
    $userId = RequestValidator::post('user_id', '');
    $status = RequestValidator::post('status', '');

    if (empty($userId) || $status === '') {
        ResponseHelper::error("Validation failed", ["User ID and status are required"], 400);
    }

    // Use UserService to toggle user status
    $userService = new UserService();
    $success = $userService->toggleUserStatus($userId, (int)$status);

    if (!$success) {
        $errors = $userService->getErrors();
        ResponseHelper::error("Status toggle failed", $errors, 400);
    }

    ResponseHelper::success(null, "User status updated successfully");

} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
