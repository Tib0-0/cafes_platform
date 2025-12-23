<?php
/**
 * Database Configuration
 * Cafes Platform - Database Connection Handler
 */

class Database {

    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Read from environment variables (CI) or fallback to local
        $this->host     = getenv('DB_HOST') ?: '127.0.0.1';
        $this->db_name  = getenv('DB_NAME') ?: 'cafes_platform';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
    }

    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";

            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );

        } catch (PDOException $e) {
            echo "DB CONNECTION ERROR: " . $e->getMessage() . PHP_EOL;
            exit(1);
        }

        return $this->conn;
    }
}
