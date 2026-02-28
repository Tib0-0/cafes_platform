<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/database.php";

// Public endpoint: returns all products with status = 'approved'
$db = (new Database())->getConnection();

try {
    $stmt = $db->prepare("SELECT
        pa.ad_id,
        pa.product_name,
        pa.description,
        pa.price,
        pa.category,
        pa.image_url,
        u.user_id AS vendor_id,
        u.username AS vendor_name
      FROM product_ads pa
      LEFT JOIN users u ON pa.vendor_id = u.user_id
      WHERE pa.status = 'approved'
      ORDER BY pa.product_name ASC");

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "database_error", "message" => $e->getMessage()]);
}

?>
