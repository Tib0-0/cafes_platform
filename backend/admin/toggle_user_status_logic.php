<?php
require_once __DIR__ . "/../../config/database.php";

function toggleUserStatus(PDO $db, int $userId): bool
{
    $stmt = $db->prepare("
        UPDATE users
        SET is_active = IF(is_active = 1, 2, 1)
        WHERE user_id = ?
    ");

    return $stmt->execute([$userId]);
}
