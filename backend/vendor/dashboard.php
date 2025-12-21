<?php
session_start();
require_once __DIR__ . "/../../config/database.php";

// ✅ Check login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$vendor_id = $_SESSION['user_id'];

$response = [
    "stats" => [],
    "recent_ads" => [],
    "recent_requests" => []
];

try {

    /* ==========================
       STATS
    ========================== */

    // Requests count
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM partnership_requests 
        WHERE vendor_id = ?
    ");
    $stmt->execute([$vendor_id]);
    $response['stats']['requests'] = $stmt->fetchColumn();

    // Approved requests
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM partnership_requests 
        WHERE vendor_id = ? AND status = 'approved'
    ");
    $stmt->execute([$vendor_id]);
    $response['stats']['approvals'] = $stmt->fetchColumn();

    // Views & Clicks (not implemented yet)
    $response['stats']['views'] = 0;
    $response['stats']['clicks'] = 0;


    /* ==========================
       RECENT ADS
    ========================== */
    $stmt = $conn->prepare("
        SELECT ad_id, product_name, status, is_active, created_at
        FROM product_ads
        WHERE vendor_id = ?
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$vendor_id]);
    $response['recent_ads'] = $stmt->fetchAll(PDO::FETCH_ASSOC);


    /* ==========================
       RECENT PARTNERSHIP REQUESTS
    ========================== */
    $stmt = $conn->prepare("
        SELECT request_id, cafe_owner_id, status, message, created_at
        FROM partnership_requests
        WHERE vendor_id = ?
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$vendor_id]);
    $response['recent_requests'] = $stmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
