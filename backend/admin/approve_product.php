<?php
session_start();
require_once __DIR__ . "/../../config/database.php";

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['ad_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing ad_id"]);
    exit;
}

$ad_id = (int)$data['ad_id'];

$db = (new Database())->getConnection();

$stmt = $db->prepare("
    UPDATE product_ads
    SET status = 'approved', is_active = 1
    WHERE ad_id = ?
");

$stmt->execute([$ad_id]);

echo json_encode(["success" => true]);
