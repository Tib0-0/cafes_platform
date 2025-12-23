<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../backend/admin/get_users_logic.php";

echo "▶ Running GetUsersTest.php\n";

$db = (new Database())->getConnection();
$users = getAllUsers($db);

if (is_array($users)) {
    echo "✅ PASS: getAllUsers() returns an array\n";
} else {
    echo "❌ FAIL: getAllUsers() did not return array\n";
}

if (count($users) > 0 && isset($users[0]['username'])) {
    echo "✅ PASS: User data structure is valid\n";
} else {
    echo "⚠️ WARN: No users found or missing fields\n";
}
