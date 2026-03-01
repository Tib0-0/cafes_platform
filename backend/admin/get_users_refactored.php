<?php
/**
 * Refactored Admin Get Users Script
 * Uses: UserService, AuthHelper, ResponseHelper
 * Demonstrates: OOP usage with admin authorization
 */

header('Content-Type: application/json');
require_once "../core/bootstrap.php";

// Check if user is authenticated and is an admin
AuthHelper::requireLogin();
AuthHelper::requireAdmin();

try {
    // Use UserService to get all users
    $userService = new UserService();
    $users = $userService->getAll();

    // Remove sensitive data
    foreach ($users as &$user) {
        unset($user['password_hash']);
    }

    // Return JSON response
    ResponseHelper::json($users);

} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
