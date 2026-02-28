<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/database.php";

$db = (new Database())->getConnection();

$stmt = $db->prepare("SELECT DISTINCT TRIM(category) AS category
  FROM product_ads
  WHERE status = 'approved' AND category IS NOT NULL AND category != ''
  ORDER BY category ASC");

$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cats = array_map(function($r){ return $r['category']; }, $rows);

echo json_encode($cats);

?>
