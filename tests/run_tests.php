<?php

echo "Running tests...\n\n";

$tests = [
   // "DatabaseTest.php",
    "GetUsersTest.php",
    "ToggleUserStatusTest.php"
];

foreach ($tests as $test) {
    echo "▶ Running $test\n";
    include __DIR__ . "/$test";
    echo "\n";
}

echo "All tests finished.\n";
