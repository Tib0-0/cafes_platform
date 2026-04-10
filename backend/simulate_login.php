<?php
// Simulate login for testing messaging
session_start();

// Clear any existing session data
session_unset();
session_destroy();
session_start();

// Simulate user login (user ID 2 - vendor_a)
$_SESSION["user_id"] = 2;
$_SESSION["email"] = "vendor@example.com";
$_SESSION["role"] = "vendor";

// Set session cookie parameters explicitly
session_set_cookie_params([
    'lifetime' => 3600, // 1 hour
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => false,
    'samesite' => 'Lax'
]);

echo "Session set for user ID 2 (vendor_a). Session ID: " . session_id();
echo "<br>Redirecting to messaging page in 2 seconds...";

// Use header redirect instead of JavaScript
header("Refresh: 2; url=../pages/12.C4F3_Messaging_Page.html");
?>