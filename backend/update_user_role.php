<?php
session_start();
header('Content-Type: application/json');

// ================= AUTH CHECK =================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - Admin access required']);
    exit;
}

// ================= METHOD CHECK =================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ================= INPUT =================
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['user_id']) || !isset($data['role'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user_id or role']);
    exit;
}

$user_id = (int)$data['user_id'];
$newRole = $data['role'];

// ================= VALIDATE ROLE =================
$validRoles = ['admin', 'vendor', 'cafe_owner'];

if (!in_array($newRole, $validRoles)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid role']);
    exit;
}

// ================= DB CONNECTION =================
require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection(); // ✅ FIX

    // ================= CHECK USER EXISTS =================
    $stmt = $db->prepare("SELECT user_id, role FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$targetUser) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    // ================= OPTIONAL SAFETY =================
    // Prevent admin from removing their own admin role
    if ($user_id === $_SESSION['user_id'] && $newRole !== 'admin') {
        http_response_code(400);
        echo json_encode(['error' => 'You cannot change your own admin role']);
        exit;
    }

    // ================= UPDATE ROLE =================
    $stmt = $db->prepare("UPDATE users SET role = ? WHERE user_id = ?");
    $stmt->execute([$newRole, $user_id]);

    echo json_encode([
        'success' => true,
        'message' => 'User role updated successfully'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}