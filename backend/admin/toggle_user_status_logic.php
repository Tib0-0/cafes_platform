<?php
require_once __DIR__ . "/../../config/database.php";

function toggleUserStatus(PDO $db, int $userId): bool
{
    // 1. Check user role
    $stmt = $db->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. BLOCK admin accounts
    if ($user && $user['role'] === 'admin') {
        return false;
    }

    // 3. Toggle status
    $stmt = $db->prepare("
        UPDATE users
        SET is_active = IF(is_active = 1, 2, 1)
        WHERE user_id = ?
    ");

    return $stmt->execute([$userId]);
}