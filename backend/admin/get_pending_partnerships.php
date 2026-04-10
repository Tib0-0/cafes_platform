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
        pr.request_id,
        pr.ad_id,
        pr.created_at,
        pr.status,
        cafe.username AS cafe_name,
        cafe.user_id AS cafe_owner_id,
        vendor.username AS vendor_name,
        vendor.user_id AS vendor_id,
        pa.product_name,
        pa.price,
        pa.category,
        pa.image_url
    FROM partnership_requests pr
    JOIN users cafe ON pr.cafe_owner_id = cafe.user_id
    JOIN users vendor ON pr.vendor_id = vendor.user_id
    LEFT JOIN product_ads pa ON pr.ad_id = pa.ad_id
    WHERE pr.status = 'pending'
    ORDER BY pr.created_at DESC
");

$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($requests);
?>
