<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/database.php";

// Public endpoint: returns product when ad_id provided and status = 'approved'
if (!isset($_GET['ad_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing ad_id"]);
    exit;
}

$ad_id = $_GET['ad_id'];

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
        u.username AS vendor_name,
        pa.status
      FROM product_ads pa
      LEFT JOIN users u ON pa.vendor_id = u.user_id
      WHERE pa.ad_id = ? AND pa.status = 'approved'");

    $stmt->execute([$ad_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode([]);
        exit;
    }

    echo json_encode($product);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "database_error", "message" => $e->getMessage()]);
}

?>
