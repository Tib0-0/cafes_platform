<?php
/**
 * Refactored Get Categories Script
 * Uses: ProductService, ResponseHelper
 * Demonstrates: OOP usage in existing script (API endpoint)
 */

header('Content-Type: application/json');
require_once "../core/bootstrap.php";

try {
    // Use ProductService to get categories
    $productService = new ProductService();
    $categories = $productService->getCategories();

    // Return JSON response
    ResponseHelper::json($categories);

} catch (PDOException $e) {
    ResponseHelper::error("Database error", [$e->getMessage()], 500);
} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
