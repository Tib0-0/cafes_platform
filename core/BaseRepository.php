<?php
/**
 * Base Repository Class
 * Provides common database operations for all repositories
 * Demonstrates: Encapsulation, Abstraction, Inheritance
 */

abstract class BaseRepository {
    
    protected $db;
    protected $table;
    protected $errors = [];

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    /**
     * Get single record by ID
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? OR user_id = ? LIMIT 1");
            $stmt->execute([$id, $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->addError("Find by ID failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all records
     * @return array
     */
    public function findAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table}");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        } catch (PDOException $e) {
            $this->addError("Find all failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create record
     * @param array $data
     * @return bool|int
     */
    public function create(array $data) {
        try {
            $columns = implode(',', array_keys($data));
            $placeholders = implode(',', array_fill(0, count($data), '?'));
            
            $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
            $stmt->execute(array_values($data));
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->addError("Create failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update record
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data) {
        try {
            $setClause = implode(',', array_map(fn($key) => "{$key} = ?", array_keys($data)));
            $values = array_values($data);
            $values[] = $id;
            
            $stmt = $this->db->prepare("UPDATE {$this->table} SET {$setClause} WHERE id = ? OR user_id = ?");
            $stmt->execute($values);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->addError("Update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete record
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ? OR user_id = ?");
            $stmt->execute([$id, $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->addError("Delete failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Execute custom query
     * @param string $query
     * @param array $params
     * @return mixed
     */
    protected function executeQuery($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->addError("Query execution failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add error message
     * @param string $error
     */
    protected function addError($error) {
        $this->errors[] = $error;
    }

    /**
     * Get all errors
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Clear errors
     */
    public function clearErrors() {
        $this->errors = [];
    }

    /**
     * Get last error
     * @return string|null
     */
    public function getLastError() {
        return end($this->errors) ?: null;
    }

    /**
     * Get database connection
     * @return PDO
     */
    public function getConnection() {
        return $this->db;
    }
}
?>
