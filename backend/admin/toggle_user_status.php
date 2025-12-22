<?php
session_start();
require_once __DIR__ . "/../../config/database.php";

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

/* Toggle is_active */
$stmt = $db->prepare("
    UPDATE users
    SET is_active = IF(is_active = 1, 2, 1)
    WHERE user_id = ?
");
$stmt->execute([$userId]);

echo json_encode(["success" => true]);
