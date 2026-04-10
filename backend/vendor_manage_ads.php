<?php
header('Content-Type: application/json');

// Start session with error suppression to avoid output before headers
@session_start();

// Check session early
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

// Now include database
require_once __DIR__ . "/../config/database.php";

$vendor_id = $_SESSION['user_id'];

try {
    $db = (new Database())->getConnection();
    
    // Get all vendor ads
    $adsStmt = $db->prepare("
        SELECT 
            ad_id,
            product_name as title,
            status,
            price,
            category,
            image_url,
            created_at as created
        FROM product_ads
        WHERE vendor_id = ?
        ORDER BY created_at DESC
    ");
    $adsStmt->execute([$vendor_id]);
    $ads = $adsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Status counts (without archived)
    $statusCounts = ["pending" => 0, "approved" => 0, "rejected" => 0];
    foreach ($ads as $ad) {
        if (isset($statusCounts[$ad['status']])) {
            $statusCounts[$ad['status']]++;
        }
    }

    // Most requested products (top 5 by partnership requests with ads)
    $requestsStmt = $db->prepare("
        SELECT 
            pa.product_name,
            COUNT(pr.request_id) as request_count
        FROM partnership_requests pr
        JOIN product_ads pa ON pr.ad_id = pa.ad_id
        WHERE pa.vendor_id = ?
        GROUP BY pa.ad_id, pa.product_name
        ORDER BY request_count DESC
        LIMIT 5
    ");
    $requestsStmt->execute([$vendor_id]);
    $mostRequested = $requestsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format ads for frontend
    $formattedAds = array_map(function($ad) {
        return [
            'ad_id' => $ad['ad_id'],
            'title' => $ad['title'],
            'status' => $ad['status'],
            'price' => $ad['price'],
            'category' => $ad['category'],
            'image' => $ad['image_url'] ? "../" . $ad['image_url'] : "https://source.unsplash.com/200x100/?coffee",
            'created' => date('Y-m-d', strtotime($ad['created']))
        ];
    }, $ads);

    echo json_encode([
        "ads" => $formattedAds,
        "statusCounts" => $statusCounts,
        "mostRequested" => $mostRequested
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
?>
