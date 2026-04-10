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
$senderId = $_SESSION['user_id'];

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$receiverId = $input['receiver_id'] ?? null;
$messageText = $input['message'] ?? null;

if (!$receiverId || !is_numeric($receiverId)) {
    http_response_code(400);
    echo json_encode(["error" => "validation_error", "message" => "Valid receiver_id required"]);
    exit;
}

if (!$messageText || empty(trim($messageText))) {
    http_response_code(400);
    echo json_encode(["error" => "validation_error", "message" => "Message text cannot be empty"]);
    exit;
}

try {
    $messageService = new MessageService();
    $messageId = $messageService->sendMessage($senderId, $receiverId, $messageText);

    if ($messageId === false) {
        http_response_code(400);
        echo json_encode(["error" => "validation_error", "message" => implode(", ", $messageService->getErrors())]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message_id" => $messageId,
        "message" => "Message sent successfully"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "server_error", "message" => "Internal server error"]);
}
?>