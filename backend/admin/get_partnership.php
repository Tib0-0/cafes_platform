<?php
session_start();
require_once __DIR__ . "/../../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

if (!isset($_GET['request_id'])) {
    echo json_encode([]);
    exit;
}

$request_id = (int)$_GET['request_id'];

$db = (new Database())->getConnection();

$stmt = $db->prepare("
    SELECT 
        pr.request_id,
        pr.message,
        pr.proposed_terms,
        pr.status,
        pr.created_at,

        c.username AS cafe_name,
        v.username AS vendor_name

    FROM partnership_requests pr
    JOIN users c ON pr.cafe_owner_id = c.user_id
    JOIN users v ON pr.vendor_id = v.user_id
    WHERE pr.request_id = ?
");

$stmt->execute([$request_id]);
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
