<?php
session_start();
require_once __DIR__ . "/../../config/database.php";

// Only cafe owners can request partnerships
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cafe_owner') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$db = (new Database())->getConnection();

$stmt = $db->prepare("
    SELECT user_id, username
    FROM users
    WHERE role = 'vendor'
    ORDER BY username
");
$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
