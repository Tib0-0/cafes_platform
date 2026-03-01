<?php
/**
 * Refactored Admin Reject Product Script
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

    // Use ProductService to reject product
    $productService = new ProductService();
    $success = $productService->rejectProduct($productId);

    if (!$success) {
        ResponseHelper::error("Rejection failed", ["Could not reject product"], 400);
    }

    ResponseHelper::success(null, "Product rejected successfully");

} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
