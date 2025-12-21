<?php
session_start();
require_once "../config/database.php";

// 🔐 Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: ../pages/1.C4F3_login.html");
    exit;
}

$vendor_id = $_SESSION['user_id'];

$db = (new Database())->getConnection();

/* =========================
   DASHBOARD METRICS
========================= */

// Total ads
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM product_ads 
    WHERE vendor_id = ?
");
$stmt->execute([$vendor_id]);
$totalAds = $stmt->fetchColumn();

// Active ads
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM product_ads 
    WHERE vendor_id = ? AND status = 'approved'
");
$stmt->execute([$vendor_id]);
$activeAds = $stmt->fetchColumn();

// Partnership requests
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM partnership_requests 
    WHERE vendor_id = ?
");
$stmt->execute([$vendor_id]);
$totalRequests = $stmt->fetchColumn();

// Approved requests
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM partnership_requests 
    WHERE vendor_id = ? AND status = 'approved'
");
$stmt->execute([$vendor_id]);
$approvedRequests = $stmt->fetchColumn();

/* =========================
   RECENT ADS
========================= */
$stmt = $db->prepare("
    SELECT product_name, status, created_at
    FROM product_ads
    WHERE vendor_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute([$vendor_id]);
$recentAds = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   RECENT PARTNERSHIP REQUESTS
========================= */
$stmt = $db->prepare("
    SELECT pr.request_id, pr.status, pr.created_at, u.username AS cafe_owner
    FROM partnership_requests pr
    JOIN users u ON pr.cafe_owner_id = u.user_id
    WHERE pr.vendor_id = ?
    ORDER BY pr.created_at DESC
    LIMIT 5
");
$stmt->execute([$vendor_id]);
$recentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   TEMPORARY CHART DATA
   (until ad_views/ad_clicks exist)
========================= */
$chartData = [
    'views' => [12, 18, 25, 30, 22, 28, 35],
    'clicks' => [4, 6, 9, 10, 8, 11, 14],
    'requests' => [1, 2, 1, 3, 2, 4, 3],
    'approvals' => [1, 1, 0, 2, 1, 2, 2]
];

echo json_encode([
    "stats" => [
        "totalAds" => $totalAds,
        "activeAds" => $activeAds,
        "totalRequests" => $totalRequests,
        "approvedRequests" => $approvedRequests
    ],
    "recentAds" => $recentAds,
    "recentRequests" => $recentRequests,
    "charts" => $chartData
]);
