<?php
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/toggle_user_status_logic.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$userId = $data['user_id'] ?? null;

if (!$userId) {
    echo json_encode(["success" => false, "message" => "Missing user ID"]);
    exit;
}

$db = (new Database())->getConnection();

$success = toggleUserStatus($db, (int)$userId);

echo json_encode(["success" => $success]);
