<?php
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request");
}

$username = trim($_POST["business_name"]); // using business name as username
$email    = trim($_POST["email"]);
$password = $_POST["password"];
$confirm  = $_POST["confirm_password"];
$role     = $_POST["role"];

if ($password !== $confirm) {
    exit("Passwords do not match");
}

if (empty($role)) {
    exit("Please select a role");
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $db = (new Database())->getConnection();

    // Check if email already exists
    $check = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        exit("Email already registered");
    }

    // Insert user
    $stmt = $db->prepare("
        INSERT INTO users (username, email, password_hash, role, is_active)
        VALUES (?, ?, ?, ?, 1)
    ");

    $stmt->execute([
        $username,
        $email,
        $hashedPassword,
        $role
    ]);

    header("Location: ../pages/1.C4F3_login.html");
    exit;

} catch (PDOException $e) {
    exit("Registration error: " . $e->getMessage());
}
