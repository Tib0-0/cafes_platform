<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        die("Email and password are required.");
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
            die("Account not found.");
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ((int)$user["is_active"] !== 1) {
            die("Account is disabled.");
        }

        if (!password_verify($password, $user["password_hash"])) {
            die("Invalid password.");
        }

        // ✅ Login success
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["email"]   = $user["email"];
        $_SESSION["role"]    = $user["role"];

        // 🔀 Redirect by role
        switch ($user["role"]) {
            case "vendor":
                header("Location: ../pages/6.C4F3_Vendor_Dashboard.html");
                break;

            case "cafe_owner":
                header("Location: ../pages/10.C4F3_Owner_Dashboard.html");
                break;

            case "admin":
                header("Location: ../pages/14.C4F3_Admin_Dashboard_Page.html");
                break;

        }
        exit;

    } catch (PDOException $e) {
        die("Login error: " . $e->getMessage());
    }
}
