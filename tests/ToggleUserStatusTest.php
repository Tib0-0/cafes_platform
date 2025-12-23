<?php
require_once __DIR__ . "/../backend/admin/toggle_user_status_logic.php";
require_once __DIR__ . "/../config/database.php";

echo "▶ Running ToggleUserStatusTest.php\n";

$db = (new Database())->getConnection();

/* Create a fake user for testing */
$stmt = $db->prepare("
    INSERT INTO users (username, role, is_active, created_at)
    VALUES (?, ?, ?, NOW())
");
$stmt->execute(["test_toggle_user", "vendor", 1]);

$userId = $db->lastInsertId();

/* Toggle status */
$result = toggleUserStatus($db, (int)$userId);

/* Verify */
$stmt = $db->prepare("SELECT is_active FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$status = $stmt->fetchColumn();

if ($result && $status == 2) {
    echo "✅ PASS: User status toggled to Suspended\n";
} else {
    echo "❌ FAIL: User status not toggled\n";
}

/* Cleanup */
$db->prepare("DELETE FROM users WHERE user_id = ?")->execute([$userId]);
