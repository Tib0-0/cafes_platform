<?php
session_start();
require_once __DIR__ . "/../../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$request_id = $data['request_id'] ?? null;
$action = $data['action'] ?? null;

if (!$request_id || !in_array($action, ['approve','reject'])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$status = $action === 'approve' ? 'approved' : 'rejected';

$db = (new Database())->getConnection();

$stmt = $db->prepare("
    UPDATE partnership_requests
    SET status = ?
    WHERE request_id = ?
");

$stmt->execute([$status, $request_id]);

echo json_encode([
    "success" => true,
    "message" => "Request $status"
]);
