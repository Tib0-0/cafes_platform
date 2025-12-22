<?php
session_start();
require_once __DIR__ . "/../../config/database.php";

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$db = (new Database())->getConnection();

/* =========================
   STATS
========================= */

// Pending product ads
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM product_ads 
    WHERE status = 'pending'
");
$stmt->execute();
$pendingProducts = $stmt->fetchColumn();

// Total users
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM users
");
$stmt->execute();
$totalUsers = $stmt->fetchColumn();

// Open partnership requests
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM partnership_requests 
    WHERE status = 'pending'
");
$stmt->execute();
$openRequests = $stmt->fetchColumn();

// Flags (not implemented yet)
$flags = 0;

/* =========================
   RECENT PRODUCT ADS
========================= */
$stmt = $db->prepare("
    SELECT 
        pa.ad_id,
        pa.product_name,
        pa.created_at,
        u.username AS vendor_name
    FROM product_ads pa
    JOIN users u ON pa.vendor_id = u.user_id
    WHERE pa.status = 'pending'
    ORDER BY pa.created_at DESC
    LIMIT 5
");
$stmt->execute();
$recentProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   RECENT PARTNERSHIP REQUESTS
========================= */
$stmt = $db->prepare("
    SELECT 
        pr.request_id,
        pr.created_at,
        pr.status,
        cafe.username AS cafe_owner,
        vendor.username AS vendor_name
    FROM partnership_requests pr
    JOIN users cafe ON pr.cafe_owner_id = cafe.user_id
    JOIN users vendor ON pr.vendor_id = vendor.user_id
    WHERE pr.status = 'pending'
    ORDER BY pr.created_at DESC
    LIMIT 5
");
$stmt->execute();
$recentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   RESPONSE
========================= */
echo json_encode([
    "stats" => [
        "pendingProducts" => $pendingProducts,
        "users" => $totalUsers,
        "openRequests" => $openRequests,
        "flags" => $flags
    ],
    "recentProducts" => $recentProducts,
    "recentRequests" => $recentRequests
]);
