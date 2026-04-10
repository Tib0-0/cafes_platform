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

try {
    $messageService = new MessageService();
    $conversations = $messageService->getConversations($userId);

    if ($conversations === false) {
        http_response_code(400);
        echo json_encode(["error" => "validation_error", "message" => implode(", ", $messageService->getErrors())]);
        exit;
    }

    // Format conversations for frontend
    $formattedConversations = array_map(function($conv) {
        return [
            'user_id' => $conv['other_user_id'],
            'username' => $conv['username'],
            'role' => $conv['role'],
            'last_message_time' => $conv['last_message_time'],
            'unread_count' => (int) $conv['unread_count'],
            'display_name' => $conv['role'] === 'admin' ? 'Admin' :
                           ($conv['role'] === 'vendor' ? 'Vendor ' . $conv['username'] :
                           ($conv['role'] === 'cafe_owner' ? 'Cafe ' . $conv['username'] : $conv['username']))
        ];
    }, $conversations);

    echo json_encode([
        "success" => true,
        "conversations" => $formattedConversations
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "server_error", "message" => "Internal server error"]);
}
?>