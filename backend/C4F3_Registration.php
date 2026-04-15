<?php
header('Content-Type: application/json');
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
  "status" => "error",
  "message" => "Invalid request"
]);
exit;
}

$username = trim($_POST["business_name"]); // using business name as username
$email    = trim($_POST["email"]);
$password = $_POST["password"];
$confirm  = $_POST["confirm_password"];
$role     = $_POST["role"];

if ($password !== $confirm) {
    echo json_encode([
    "status" => "error",
    "message" => "Passwords do not match"
    ]);
    exit;
    }

if (empty($role)) {
    echo json_encode([
    "status" => "error",
    "message" => "Please select a role"
    ]);
    exit;
    }

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $db = (new Database())->getConnection();

    // Check if email already exists
    $check = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        echo json_encode([
    "status" => "error",
    "message" => "Email already registered"
    ]);
    exit;
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

    echo json_encode([
    "status" => "success",
    "message" => "Account created successfully"
    ]);
    exit;

} catch (PDOException $e) {
    exit("Registration error: " . $e->getMessage());
}
