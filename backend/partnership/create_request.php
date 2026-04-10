<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../../config/database.php";

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cafe_owner') {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

/* =========================
   READ JSON INPUT
========================= */
$data = json_decode(file_get_contents("php://input"), true);

// DEBUG: Log what we received
error_log("DEBUG: Raw data = " . json_encode($data));

$ad_id = isset($data['ad_id']) ? (int)$data['ad_id'] : 0;
$vendor_id = isset($data['vendor_id']) ? (int)$data['vendor_id'] : 0;
$message   = trim($data['message'] ?? '');
$terms     = trim($data['terms'] ?? '');

$cafe_owner_id = (int)$_SESSION['user_id'];

error_log("DEBUG: ad_id=$ad_id, vendor_id=$vendor_id, message=$message, cafe_owner_id=$cafe_owner_id");

if ($ad_id === 0 || $vendor_id === 0 || $message === '') {
    echo json_encode(["success" => false, "message" => "Missing required fields (ad_id=$ad_id, vendor_id=$vendor_id, message='$message')"]);
    exit;
}

try {
    $database = new Database();
    $pdo = $database->getConnection(); // ✅ IMPORTANT

    $stmt = $pdo->prepare("
        INSERT INTO partnership_requests
        (ad_id, cafe_owner_id, vendor_id, message, proposed_terms, status, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, 'pending', 1, NOW())
    ");

    $stmt->execute([
        $ad_id,
        $cafe_owner_id,
        $vendor_id,
        $message,
        $terms
    ]);

    echo json_encode(["success" => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
