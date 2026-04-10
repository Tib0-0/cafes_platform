<?php
session_start();
header('Content-Type: application/json');

// ================= AUTH CHECK =================
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
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
$user_id = $_SESSION['user_id'];

// Validate input
if (!$data || (!isset($data['username']) && !isset($data['password']))) {
    http_response_code(400);
    echo json_encode(['error' => 'No data to update']);
    exit;
}

// ================= DB CONNECTION =================
require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();

    // ================= UPDATE USERNAME =================
    if (isset($data['username']) && !empty(trim($data['username']))) {
        $username = trim($data['username']);

        // Check if username already exists
        $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        $stmt->execute([$username, $user_id]);

        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Username already exists']);
            exit;
        }

        // Update username
        $stmt = $db->prepare("UPDATE users SET username = ? WHERE user_id = ?");
        $stmt->execute([$username, $user_id]);
    }

    // ================= UPDATE PASSWORD =================
    if (isset($data['password']) && !empty($data['password'])) {
        $password = $data['password'];

        // Validate password length
        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['error' => 'Password must be at least 6 characters']);
            exit;
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Update password (IMPORTANT: correct column name)
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->execute([$hashedPassword, $user_id]);
    }

    // ================= SUCCESS =================
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully'
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