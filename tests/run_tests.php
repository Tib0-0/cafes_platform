<?php

echo "Running tests...\n\n";

$tests = [
    "GetUsersTest.php",
];

foreach ($tests as $test) {
    echo "▶ Running $test\n";
    include __DIR__ . "/$test";
    echo "\n";
}

echo "All tests finished.\n";
