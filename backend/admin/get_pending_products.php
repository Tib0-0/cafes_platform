<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$db = (new Database())->getConnection();

$stmt = $db->prepare("
    SELECT 
        pa.ad_id,
        pa.product_name,
        pa.description,
        pa.price,
        pa.category,
        pa.image_url,
        pa.created_at,
        u.username AS vendor_name,
        u.user_id AS vendor_id
    FROM product_ads pa
    JOIN users u ON pa.vendor_id = u.user_id
    WHERE pa.status = 'pending'
    ORDER BY pa.created_at DESC
");

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($products);
?>
