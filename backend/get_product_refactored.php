<?php
/**
 * Refactored Get Product Details Script
 * Uses: ProductService, ResponseHelper
 * Demonstrates: OOP usage in API endpoint
 */

header('Content-Type: application/json');
require_once "../core/bootstrap.php";

try {
    $productId = RequestValidator::get('product_id', '');

    if (empty($productId)) {
        ResponseHelper::error("Validation failed", ["Product ID is required"], 400);
    }

    // Use ProductService to get product by ID
    $productService = new ProductService();
    $product = $productService->getById($productId);

    if (!$product) {
        ResponseHelper::error("Not found", ["Product not found"], 404);
    }

    ResponseHelper::json($product);

} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
