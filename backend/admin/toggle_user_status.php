<?php
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/toggle_user_status_logic.php";

header("Content-Type: application/json");

// 1. Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

// 2. Get input
$data = json_decode(file_get_contents("php://input"), true);
$userId = $data['user_id'] ?? null;

if (!$userId) {
    echo json_encode([
        "success" => false,
        "message" => "Missing user ID"
    ]);
    exit;
}

// 3. DB connection
$db = (new Database())->getConnection();

// 4. Execute logic
$success = toggleUserStatus($db, (int)$userId);

// 5. Response
if (!$success) {
    echo json_encode([
        "success" => false,
        "message" => "Admin accounts cannot be suspended"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "User status updated"
]);