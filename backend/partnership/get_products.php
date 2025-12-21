<?php
session_start();
require_once __DIR__ . "/../../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cafe_owner') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$db = (new Database())->getConnection();

$stmt = $db->prepare("
    SELECT 
        pa.ad_id,
        pa.product_name,
        pa.vendor_id,
        u.username AS vendor_name
    FROM product_ads pa
    JOIN users u ON u.user_id = pa.vendor_id
    WHERE pa.status = 'approved'
    ORDER BY pa.product_name
");
$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
