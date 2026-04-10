<?php
// Test session persistence
session_start();

echo "<h1>Session Test</h1>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";

if (isset($_SESSION["user_id"])) {
    echo "<p>User ID: " . $_SESSION["user_id"] . "</p>";
    echo "<p>Email: " . $_SESSION["email"] . "</p>";
    echo "<p>Role: " . $_SESSION["role"] . "</p>";
    echo "<p style='color: green;'>✅ Session is working!</p>";
} else {
    echo "<p style='color: red;'>❌ No session data found</p>";
}

echo "<br><a href='12.C4F3_Messaging_Page.html'>Go to Messaging Page</a>";
echo "<br><a href='../backend/simulate_login.php'>Set Session</a>";
?>