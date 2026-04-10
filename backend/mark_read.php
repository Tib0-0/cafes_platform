<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../core/bootstrap.php";

// Check if user is logged in
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "unauthorized", "message" => "User not logged in"]);
    exit;
}
$userId = $_SESSION['user_id'];
$otherUserId = $_GET['user_id'] ?? null;

if (!$otherUserId || !is_numeric($otherUserId)) {
    http_response_code(400);
    echo json_encode(["error" => "validation_error", "message" => "Valid user_id parameter required"]);
    exit;
}

try {
    $messageService = new MessageService();
    $result = $messageService->markAsRead($userId, $otherUserId);

    if ($result === false) {
        http_response_code(400);
        echo json_encode(["error" => "validation_error", "message" => implode(", ", $messageService->getErrors())]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Messages marked as read"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "server_error", "message" => "Internal server error"]);
}
?>