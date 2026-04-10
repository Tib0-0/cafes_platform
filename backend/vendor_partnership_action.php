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

$data = json_decode(file_get_contents("php://input"), true);
$request_id = $data['request_id'] ?? null;
$action = $data['action'] ?? null;
$vendor_id = $_SESSION['user_id'];

try {
    $db = (new Database())->getConnection();
    
    if (!$request_id || !$action) {
        echo json_encode(["error" => "Missing request_id or action"]);
        exit;
    }
    
    // Verify ownership
    $checkStmt = $db->prepare("
        SELECT pr.request_id, pa.vendor_id 
        FROM partnership_requests pr
        JOIN product_ads pa ON pr.ad_id = pa.ad_id
        WHERE pr.request_id = ?
    ");
    $checkStmt->execute([$request_id]);
    $request = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request || $request['vendor_id'] != $vendor_id) {
        http_response_code(403);
        echo json_encode(["error" => "Not authorized"]);
        exit;
    }
    
    // Map action to status
    $statusMap = ['approve' => 'vendor_approved', 'reject' => 'rejected'];
    $newStatus = $statusMap[$action] ?? null;
    
    if (!$newStatus) {
        echo json_encode(["error" => "Invalid action"]);
        exit;
    }
    
    // Update status
    $updateStmt = $db->prepare("
        UPDATE partnership_requests 
        SET status = ? 
        WHERE request_id = ?
    ");
    
    if ($updateStmt->execute([$newStatus, $request_id])) {
        echo json_encode(["success" => true, "status" => $newStatus]);
    } else {
        echo json_encode(["error" => "Failed to update status"]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
    exit;
}
