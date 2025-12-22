<?php
session_start();
require_once __DIR__ . "/../../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

if (!isset($_GET['ad_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing ad_id"]);
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
    u.username AS vendor_name
  FROM product_ads pa
  JOIN users u ON pa.vendor_id = u.user_id
  WHERE pa.ad_id = ?
");

$stmt->execute([$_GET['ad_id']]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($product ?: []);
