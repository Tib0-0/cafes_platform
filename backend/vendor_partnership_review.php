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
$request_id = $_GET['request_id'] ?? null;

try {
    $db = (new Database())->getConnection();
    
    if (!$request_id) {
        echo json_encode(["error" => "No request_id provided"]);
        exit;
    }
    
    // Get partnership request with joined data
    $stmt = $db->prepare("
        SELECT 
            pr.request_id,
            pr.cafe_owner_id,
            pr.ad_id,
            pr.proposed_terms,
            pr.message,
            pr.status,
            u.username as cafe_name,
            pa.product_name,
            pa.vendor_id
        FROM partnership_requests pr
        JOIN users u ON pr.cafe_owner_id = u.user_id
        JOIN product_ads pa ON pr.ad_id = pa.ad_id
        WHERE pr.request_id = ?
    ");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        http_response_code(404);
        echo json_encode(["error" => "Request not found"]);
        exit;
    }
    
    // Verify ownership - this request should be for this vendor's ad
    if ($request['vendor_id'] != $vendor_id) {
        http_response_code(403);
        echo json_encode(["error" => "Not authorized to view this request"]);
        exit;
    }
    
    echo json_encode($request);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
    exit;
}
