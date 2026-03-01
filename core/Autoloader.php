<?php
/**
 * Autoloader Class
 * Demonstrates: Encapsulation
 * Automatically loads classes from core folder
 */

class Autoloader {
    
    private static $classDirs = [];

    /**
     * Initialize autoloader
     */
    public static function init() {
        spl_autoload_register([self::class, 'load']);
    }

    /**
     * Auto-load class
     * @param string $class
     */
    public static function load($class) {
        // Core classes location
        $coreDir = __DIR__ . '/';
        $filePath = $coreDir . $class . '.php';

        if (file_exists($filePath)) {
            require_once $filePath;
            return;
        }
    }
}

// Initialize autoloader
Autoloader::init();
?>
