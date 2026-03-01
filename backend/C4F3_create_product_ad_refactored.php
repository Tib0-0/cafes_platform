<?php
/**
 * Refactored Create Product Advertisement Script
 * Uses: ProductService, AuthHelper, ResponseHelper
 * Demonstrates: OOP usage with authentication
 */

require_once "../core/bootstrap.php";

// Check if user is authenticated and is a vendor
AuthHelper::requireLogin();
AuthHelper::requireVendor();

// Only allow POST requests
if (!RequestValidator::isPost()) {
    ResponseHelper::error("Invalid request method", ["Only POST requests allowed"], 405);
}

try {
    // Get vendor ID from session
    $vendorId = AuthHelper::userId();

    // Get form data
    $productName = trim(RequestValidator::post('product_name', ''));
    $description = trim(RequestValidator::post('description', ''));
    $price = RequestValidator::post('price', '');
    $category = trim(RequestValidator::post('category', ''));

    // Handle image upload
    $imageUrl = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = PRODUCT_IMAGE_DIR;
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $imageUrl = 'uploads/products/' . $fileName;
        }
    }

    // Use ProductService to create product
    $productService = new ProductService();
    $productId = $productService->createProduct([
        'vendor_id' => $vendorId,
        'product_name' => $productName,
        'description' => $description,
        'price' => $price,
        'category' => $category,
        'image_url' => $imageUrl
    ]);

    if (!$productId) {
        $errors = $productService->getErrors();
        ResponseHelper::error("Product creation failed", $errors, 400);
    }

    // Success response
    ResponseHelper::success([
        'product_id' => $productId,
        'message' => 'Product created successfully and is pending approval'
    ], "Product created");

} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
