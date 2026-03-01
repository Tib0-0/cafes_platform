<?php
/**
 * Comprehensive Example: Admin Product Review
 * Uses: ProductService, UserService, AuthHelper, ResponseHelper
 * Demonstrates: Multiple service usage, polymorphism, encapsulation
 */

header('Content-Type: application/json');
require_once "../../core/bootstrap.php";

// Check if user is authenticated and is an admin
AuthHelper::requireLogin();
AuthHelper::requireAdmin();

try {
    $action = RequestValidator::get('action', 'list');
    $status = RequestValidator::get('status', 'pending');

    // Use ProductService to get products by status
    $productService = new ProductService();
    
    switch ($action) {
        case 'list':
            $products = $productService->getProductsByStatus($status);
            break;
        
        case 'approve':
            $productId = RequestValidator::get('product_id', '');
            if (empty($productId)) {
                ResponseHelper::error("Validation failed", ["Product ID required"], 400);
            }
            $success = $productService->approveProduct($productId);
            $products = ['success' => $success];
            break;
        
        case 'reject':
            $productId = RequestValidator::get('product_id', '');
            if (empty($productId)) {
                ResponseHelper::error("Validation failed", ["Product ID required"], 400);
            }
            $success = $productService->rejectProduct($productId);
            $products = ['success' => $success];
            break;
        
        default:
            ResponseHelper::error("Invalid action", ["Action not recognized"], 400);
    }

    ResponseHelper::json($products);

} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
