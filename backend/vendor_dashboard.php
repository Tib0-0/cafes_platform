<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$vendor_id = $_SESSION['user_id'];
$db = (new Database())->getConnection();

// Stats
$statsStmt = $db->prepare("
    SELECT 
        (SELECT COUNT(*) FROM product_ads WHERE vendor_id = ?) AS totalAds,
        (SELECT COUNT(*) FROM product_ads WHERE vendor_id = ? AND status = 'approved') AS activeAds,
        (SELECT COUNT(*) FROM partnership_requests WHERE vendor_id = ? AND (status = 'approved' OR status = 'vendor_approved')) AS approvedRequests
");
$statsStmt->execute([$vendor_id, $vendor_id, $vendor_id]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Recent Ads (all statuses, max 3, ordered by created_at DESC)
$recentAdsStmt = $db->prepare("
    SELECT 
        ad_id,
        product_name,
        status,
        price,
        created_at
    FROM product_ads
    WHERE vendor_id = ?
    ORDER BY created_at DESC
    LIMIT 3
");
$recentAdsStmt->execute([$vendor_id]);
$recentAds = $recentAdsStmt->fetchAll(PDO::FETCH_ASSOC);

// Recent Requests (approved only, max 3, recent)
$recentRequestsStmt = $db->prepare("
    SELECT 
        pr.request_id,
        pr.created_at,
        u.username AS cafe_name,
        pa.product_name
    FROM partnership_requests pr
    JOIN users u ON pr.cafe_owner_id = u.user_id
    LEFT JOIN product_ads pa ON pr.ad_id = pa.ad_id
    WHERE pr.vendor_id = ? AND pr.status = 'approved'
    ORDER BY pr.created_at DESC
    LIMIT 3
");
$recentRequestsStmt->execute([$vendor_id]);
$recentRequests = $recentRequestsStmt->fetchAll(PDO::FETCH_ASSOC);

// Related Products (approved only, with images)
$relatedProductsStmt = $db->prepare("
    SELECT 
        ad_id,
        product_name,
        price,
        category,
        image_url
    FROM product_ads
    WHERE status = 'approved'
    ORDER BY created_at DESC
    LIMIT 10
");
$relatedProductsStmt->execute();
$relatedProducts = $relatedProductsStmt->fetchAll(PDO::FETCH_ASSOC);

// Chart data - Requests (approved per day, last 7 days)
$requestsChartStmt = $db->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as count
    FROM partnership_requests
    WHERE vendor_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
");
$requestsChartStmt->execute([$vendor_id]);
$requestsData = $requestsChartStmt->fetchAll(PDO::FETCH_ASSOC);

// Chart data - Approvals (approved per day, last 7 days)
$approvalsChartStmt = $db->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as count
    FROM partnership_requests
    WHERE vendor_id = ? AND (status = 'approved' OR status = 'vendor_approved') AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
");
$approvalsChartStmt->execute([$vendor_id]);
$approvalsData = $approvalsChartStmt->fetchAll(PDO::FETCH_ASSOC);

// Format chart data for last 7 days with dates
$last7Days = [];
$last7Data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $last7Days[] = date('M d', strtotime($date)); // Format as "Jan 15"
    $last7Data[] = $date;
}

$requestsCounts = [];
$approvalsCounts = [];
foreach ($last7Data as $date) {
    $requestsCounts[] = (int)array_sum(array_map(fn($r) => $r['date'] === $date ? $r['count'] : 0, $requestsData));
    $approvalsCounts[] = (int)array_sum(array_map(fn($r) => $r['date'] === $date ? $r['count'] : 0, $approvalsData));
}

echo json_encode([
    "stats" => $stats,
    "recentAds" => $recentAds,
    "recentRequests" => $recentRequests,
    "relatedProducts" => $relatedProducts,
    "charts" => [
        "requests" => [
            "labels" => $last7Days,
            "data" => $requestsCounts
        ],
        "approvals" => [
            "labels" => $last7Days,
            "data" => $approvalsCounts
        ]
    ]
]);
?>
