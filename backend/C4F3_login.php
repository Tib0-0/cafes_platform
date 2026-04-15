<?php
header('Content-Type: application/json');
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Account not found"]);
        exit;
    }

    try {
        $db = (new Database())->getConnection();

        $stmt = $db->prepare("
            SELECT user_id, email, password_hash, role, is_active
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);

        if ($stmt->rowCount() === 0) {
            echo json_encode(["status" => "error", "message" => "Account not found"]);
            exit;
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ((int)$user["is_active"] !== 1) {
           echo json_encode(["status" => "error", "message" => "Account not found"]);
            exit;
        }

        if (!password_verify($password, $user["password_hash"])) {
           echo json_encode(["status" => "error", "message" => "Invalid password"]);
            exit;
        }

        // ✅ Login success
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["email"]   = $user["email"];
        $_SESSION["role"]    = $user["role"];

        // 🔀 Send redirect URL instead of redirecting
    switch ($user["role"]) {
    case "vendor":
        $redirect = "../pages/6.C4F3_Vendor_Dashboard.html";
        break;

    case "cafe_owner":
        $redirect = "../pages/10.C4F3_Owner_Dashboard.html";
        break;

    case "admin":
        $redirect = "../pages/14.C4F3_Admin_Dashboard_Page.html";
        break;

    default:
        $redirect = "../pages/dashboard.html";
        break;
}

echo json_encode([
    "status" => "success",
    "redirect" => $redirect
]);
exit;

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Account not found"]);
        exit;
    }
}
