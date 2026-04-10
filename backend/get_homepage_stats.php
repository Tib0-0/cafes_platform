<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/database.php";

try {
    $db = (new Database())->getConnection();

    // Get vendor count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'vendor' AND is_active = 1");
    $stmt->execute();
    $vendorCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get cafe owner count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'cafe_owner' AND is_active = 1");
    $stmt->execute();
    $cafeOwnerCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get product count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM product_ads WHERE status = 'approved'");
    $stmt->execute();
    $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        "success" => true,
        "stats" => [
            "vendors" => (int)$vendorCount,
            "cafe_owners" => (int)$cafeOwnerCount,
            "products" => (int)$productCount
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Failed to load homepage stats"
    ]);
}
?>