<?php
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/get_users_logic.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$db = (new Database())->getConnection();
$users = getAllUsers($db);

echo json_encode($users);
