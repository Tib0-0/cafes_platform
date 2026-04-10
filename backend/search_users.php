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
    $searchTerm = $_GET['search'] ?? '';
    $searchTerm = trim($searchTerm);

    if (empty($searchTerm)) {
        echo json_encode([
            "success" => true,
            "users" => []
        ]);
        exit;
    }

    $userService = new UserService();
    $users = $userService->searchUsers($searchTerm, $userId);

    if ($users === false) {
        http_response_code(400);
        echo json_encode(["error" => "validation_error", "message" => implode(", ", $userService->getErrors())]);
        exit;
    }

    // Format users for frontend
    $formattedUsers = array_map(function($user) {
        return [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'email' => $user['email'],
            'display_name' => $user['role'] === 'admin' ? 'Admin ' . $user['username'] :
                           ($user['role'] === 'vendor' ? 'Vendor ' . $user['username'] :
                           ($user['role'] === 'cafe_owner' ? 'Cafe ' . $user['username'] : $user['username']))
        ];
    }, $users);

    echo json_encode([
        "success" => true,
        "users" => $formattedUsers
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "server_error", "message" => "Internal server error"]);
}
?>