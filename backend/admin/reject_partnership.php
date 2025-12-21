<?php
session_start();
require_once "../../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$requestId = $data['request_id'] ?? null;

if (!$requestId) {
    echo json_encode(["success" => false]);
    exit;
}

$db = (new Database())->getConnection();

$stmt = $db->prepare("
    UPDATE partnership_requests
    SET status = 'rejected'
    WHERE request_id = ?
");
$stmt->execute([$requestId]);

echo json_encode(["success" => true]);
