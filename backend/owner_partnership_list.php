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
$filter = $_GET['filter'] ?? 'pending';

// Validate filter
$validFilters = ['all', 'pending', 'rejected', 'vendor_approved', 'approved'];
if (!in_array($filter, $validFilters)) {
    $filter = 'pending';
}

try {
    $db = (new Database())->getConnection();
    
    // Get filtered partnership requests
    if ($filter === 'all') {
        $stmt = $db->prepare("
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
            WHERE pr.cafe_owner_id = ?
            ORDER BY pr.created_at DESC
        ");
        $stmt->execute([$cafe_owner_id]);
    } else {
        $stmt = $db->prepare("
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
            WHERE pr.cafe_owner_id = ? AND pr.status = ?
            ORDER BY pr.created_at DESC
        ");
        $stmt->execute([$cafe_owner_id, $filter]);
    }
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for frontend
    $formattedRequests = array_map(function($r) {
        return [
            'request_id' => $r['request_id'],
            'status' => $r['status'],
            'created_at' => date('Y-m-d', strtotime($r['created_at'])),
            'vendor_name' => $r['vendor_name'],
            'product_name' => $r['product_name'],
            'price' => $r['price'],
            'category' => $r['category'],
            'image_url' => $r['image_url'] ? "../" . $r['image_url'] : "https://source.unsplash.com/200x100/?coffee"
        ];
    }, $requests);
    
    echo json_encode([
        "requests" => $formattedRequests,
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
