<?php
@session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    $db = (new Database())->getConnection(); // ✅ FIX HERE

    // Get current user
    $stmt = $db->prepare("SELECT user_id, username, email, role FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $allUsers = [];
    if ($role === 'admin') {
        $stmt = $db->prepare("SELECT user_id, username, role FROM users ORDER BY username ASC");
        $stmt->execute();
        $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'success' => true,
        'user' => $user,
        'role' => $role,
        'allUsers' => $allUsers
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}