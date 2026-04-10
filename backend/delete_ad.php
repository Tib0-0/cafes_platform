<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$ad_id = $data['ad_id'] ?? null;

if (!$ad_id) {
    echo json_encode(["error" => "Missing ad_id"]);
    exit;
}

$db = (new Database())->getConnection();
$vendor_id = $_SESSION['user_id'];

// Verify ownership
$checkStmt = $db->prepare("SELECT vendor_id FROM product_ads WHERE ad_id = ?");
$checkStmt->execute([$ad_id]);
$ad = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$ad || $ad['vendor_id'] != $vendor_id) {
    http_response_code(403);
    echo json_encode(["error" => "Not authorized to delete this ad"]);
    exit;
}

// Delete ad
$deleteStmt = $db->prepare("DELETE FROM product_ads WHERE ad_id = ?");
if ($deleteStmt->execute([$ad_id])) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to delete ad"]);
}
?>
