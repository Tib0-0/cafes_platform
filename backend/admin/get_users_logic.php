<?php
require_once __DIR__ . "/../../config/database.php";

/**
 * Fetch all users from database.
 * This file contains NO session logic.
 * Safe to use for unit tests.
 */
function getAllUsers(PDO $db): array {
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
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
