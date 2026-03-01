<?php
/**
 * Refactored Admin Approve Product Script
 * Uses: ProductService, AuthHelper, ResponseHelper
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
    $productId = RequestValidator::post('product_id', '');

    if (empty($productId)) {
        ResponseHelper::error("Validation failed", ["Product ID is required"], 400);
    }

    // Use ProductService to approve product
    $productService = new ProductService();
    $success = $productService->approveProduct($productId);

    if (!$success) {
        ResponseHelper::error("Approval failed", ["Could not approve product"], 400);
    }

    ResponseHelper::success(null, "Product approved successfully");

} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
