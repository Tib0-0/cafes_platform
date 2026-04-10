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
$limit = (int) ($_GET['limit'] ?? 50);
$offset = (int) ($_GET['offset'] ?? 0);

if (!$otherUserId || !is_numeric($otherUserId)) {
    http_response_code(400);
    echo json_encode(["error" => "validation_error", "message" => "Valid user_id parameter required"]);
    exit;
}

try {
    $messageService = new MessageService();
    $messages = $messageService->getMessages($userId, $otherUserId, $limit, $offset);

    if ($messages === false) {
        http_response_code(400);
        echo json_encode(["error" => "validation_error", "message" => implode(", ", $messageService->getErrors())]);
        exit;
    }

    // Format messages for frontend
    $formattedMessages = array_map(function($msg) use ($userId) {
        return [
            'message_id' => $msg['message_id'],
            'text' => $msg['message_text'],
            'me' => $msg['sender_id'] == $userId,
            'time' => date('H:i', strtotime($msg['sent_at'])),
            'is_read' => (bool) $msg['is_read'],
            'sender_name' => $msg['sender_name']
        ];
    }, $messages);

    echo json_encode([
        "success" => true,
        "messages" => $formattedMessages,
        "has_more" => count($messages) === $limit
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "server_error", "message" => "Internal server error"]);
}
?>