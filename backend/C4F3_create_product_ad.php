<?php
session_start();
require_once "../config/database.php";

// 1. Check login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$vendor_id = $_SESSION['user_id'];

// 2. Only allow POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Invalid request";
    exit;
}

// 3. Collect inputs
$product_name = trim($_POST['title'] ?? '');
$category     = trim($_POST['category'] ?? '');
$price        = $_POST['price'] ?? 0;
$description  = trim($_POST['description'] ?? '');

// 4. Basic validation
if ($product_name === '' || $price <= 0) {
    http_response_code(400);
    echo "Missing required fields";
    exit;
}

// 5. Handle image upload
$imagePath = null;

if (!empty($_FILES['image']['name'])) {
    $uploadDir = "../uploads/product_ads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["image"]["name"]);
    $targetFile = $uploadDir . $fileName;

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($_FILES['image']['type'], $allowedTypes)) {
        http_response_code(400);
        echo "Invalid image type";
        exit;
    }

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        http_response_code(500);
        echo "Image upload failed";
        exit;
    }

    $imagePath = "uploads/product_ads/" . $fileName;
}

// 6. Insert into database
try {
    $db = (new Database())->getConnection();

    $stmt = $db->prepare("
        INSERT INTO product_ads 
        (product_name, category, description, price, image_url, status, is_active, vendor_id)
        VALUES (?, ?, ?, ?, ?, 'pending', 1, ?)
    ");

    $stmt->execute([
        $product_name,
        $category,
        $description,
        $price,
        $imagePath,
        $vendor_id
    ]);

    echo "success";

} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
}
