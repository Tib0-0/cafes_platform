<?php
session_start();
require_once __DIR__ . "/../../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$db = (new Database())->getConnection();

$stmt = $db->prepare("
    SELECT 
        user_id,
        username,
        role,
        is_active,
        created_at
    FROM users
    ORDER BY created_at DESC
");
$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($users);
