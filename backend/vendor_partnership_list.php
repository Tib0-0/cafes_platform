<?php
header('Content-Type: application/json');

@session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

if ($_SESSION['role'] !== 'vendor') {
    http_response_code(403);
    echo json_encode(["error" => "Not a vendor"]);
    exit;
}

require_once __DIR__ . "/../config/database.php";

$vendor_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'pending';

// Validate filter
$validFilters = ['all', 'pending', 'approved', 'rejected', 'vendor_approved'];
if (!in_array($filter, $validFilters)) {
    $filter = 'pending';
}

try {
    $db = (new Database())->getConnection();
    
    // Get filtered partnership requests for this vendor
    if ($filter === 'all') {
        $stmt = $db->prepare("
            SELECT 
                pr.request_id,
                pr.status,
                pr.created_at,
                u.username as cafe_name,
                pa.product_name,
                pa.ad_id
            FROM partnership_requests pr
            JOIN users u ON pr.cafe_owner_id = u.user_id
            JOIN product_ads pa ON pr.ad_id = pa.ad_id
            WHERE pa.vendor_id = ?
            ORDER BY pr.created_at DESC
        ");
        $stmt->execute([$vendor_id]);
    } else {
        $stmt = $db->prepare("
            SELECT 
                pr.request_id,
                pr.status,
                pr.created_at,
                u.username as cafe_name,
                pa.product_name,
                pa.ad_id
            FROM partnership_requests pr
            JOIN users u ON pr.cafe_owner_id = u.user_id
            JOIN product_ads pa ON pr.ad_id = pa.ad_id
            WHERE pa.vendor_id = ? AND pr.status = ?
            ORDER BY pr.created_at DESC
        ");
        $stmt->execute([$vendor_id, $filter]);
    }
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "requests" => $requests,
        "currentFilter" => $filter
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
