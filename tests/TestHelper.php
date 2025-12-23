<?php

function assertEquals($expected, $actual, $message) {
    if ($expected === $actual) {
        echo "✅ PASS: $message\n";
    } else {
        echo "❌ FAIL: $message\n";
        echo "   Expected: ";
        var_dump($expected);
        echo "   Got: ";
        var_dump($actual);
    }
}
