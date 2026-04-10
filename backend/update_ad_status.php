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
$status = $data['status'] ?? null;

if (!$ad_id || !$status) {
    echo json_encode(["error" => "Missing ad_id or status"]);
    exit;
}

// Map user statuses to database values
$statusMap = ['approved' => 'Approved', 'rejected' => 'Rejected', 'pending' => 'Pending'];
$dbStatus = $statusMap[$status] ?? null;

if (!$dbStatus) {
    echo json_encode(["error" => "Invalid status"]);
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
    echo json_encode(["error" => "Not authorized to update this ad"]);
    exit;
}

// Update status
$updateStmt = $db->prepare("UPDATE product_ads SET status = ? WHERE ad_id = ?");
if ($updateStmt->execute([$dbStatus, $ad_id])) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to update ad"]);
}
?>
