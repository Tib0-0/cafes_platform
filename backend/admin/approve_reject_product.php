<?php
session_start();
require_once __DIR__ . "/../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if ($_SESSION['role'] !== 'admin') {
  echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
  exit;
}

$status = $data['action'] === 'approve' ? 'approved' : 'rejected';

$db = (new Database())->getConnection();
$stmt = $db->prepare("UPDATE product_ads SET status=? WHERE ad_id=?");
$stmt->execute([$status, $data['ad_id']]);

echo json_encode([
  "success" => true,
  "message" => "Product $status"
]);
