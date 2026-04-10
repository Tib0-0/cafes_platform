<?php
header('Content-Type: application/json');

@session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

if ($_SESSION['role'] !== 'cafe_owner') {
    http_response_code(403);
    echo json_encode(["error" => "Not a cafe owner"]);
    exit;
}

require_once __DIR__ . "/../config/database.php";

$cafe_owner_id = $_SESSION['user_id'];

try {
    $db = (new Database())->getConnection();
    
    // Stats
    $statsStmt = $db->prepare("
        SELECT 
            COUNT(*) as totalRequests,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pendingRequests,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approvedRequests,
            SUM(CASE WHEN status = 'vendor_approved' THEN 1 ELSE 0 END) as vendorApprovedRequests,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejectedRequests
        FROM partnership_requests
        WHERE cafe_owner_id = ?
    ");
    $statsStmt->execute([$cafe_owner_id]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Recent Partnerships (pending, approved, vendor_approved)
    $recentStmt = $db->prepare("
        SELECT 
            pr.request_id,
            pr.status,
            pr.created_at,
            u.username as vendor_name,
            pa.product_name,
            pa.price,
            pa.category,
            pa.image_url
        FROM partnership_requests pr
        JOIN users u ON pr.vendor_id = u.user_id
        JOIN product_ads pa ON pr.ad_id = pa.ad_id
        WHERE pr.cafe_owner_id = ? AND pr.status IN ('pending', 'approved', 'vendor_approved')
        ORDER BY pr.created_at DESC
        LIMIT 5
    ");
    $recentStmt->execute([$cafe_owner_id]);
    $recentPartnerships = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for frontend
    $formattedRecentPartnerships = array_map(function($p) {
        return [
            'request_id' => $p['request_id'],
            'status' => $p['status'],
            'created_at' => date('Y-m-d', strtotime($p['created_at'])),
            'vendor_name' => $p['vendor_name'],
            'product_name' => $p['product_name'],
            'price' => $p['price'],
            'category' => $p['category'],
            'image_url' => $p['image_url'] ? "../" . $p['image_url'] : "https://source.unsplash.com/200x100/?coffee"
        ];
    }, $recentPartnerships);
    
    echo json_encode([
        "stats" => $stats,
        "recentPartnerships" => $formattedRecentPartnerships
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
    exit;
}
