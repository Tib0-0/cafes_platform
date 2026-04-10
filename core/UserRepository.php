<?php
/**
 * User Repository Class
 * Extends: BaseRepository
 * Demonstrates: Inheritance, Encapsulation
 * Specific operations for users table
 */

class UserRepository extends BaseRepository {
    
    protected $table = 'users';

    /**
     * Find user by email
     * @param string $email
     * @return array|null
     */
    public function findByEmail($email) {
        $stmt = $this->executeQuery(
            "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1",
            [$email]
        );
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    }

    /**
     * Find users by role
     * @param string $role
     * @return array
     */
    public function findByRole($role) {
        $stmt = $this->executeQuery(
            "SELECT * FROM {$this->table} WHERE role = ?",
            [$role]
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Find active users by role
     * @param string $role
     * @return array
     */
    public function findActiveByRole($role) {
        $stmt = $this->executeQuery(
            "SELECT * FROM {$this->table} WHERE role = ? AND is_active = 1",
            [$role]
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Toggle user status
     * @param int $userId
     * @param int $status
     * @return bool
     */
    public function toggleStatus($userId, $status) {
        return $this->update($userId, ['is_active' => $status]);
    }

    /**
     * Check if email exists
     * @param string $email
     * @return bool
     */
    public function emailExists($email) {
        $stmt = $this->executeQuery(
            "SELECT user_id FROM {$this->table} WHERE email = ? LIMIT 1",
            [$email]
        );
        return $stmt ? $stmt->rowCount() > 0 : false;
    }

    /**
     * Search users for messaging
     * @param string $searchTerm
     * @param int $excludeUserId
     * @return array
     */
    public function searchUsers($searchTerm, $excludeUserId) {
        $stmt = $this->executeQuery(
            "SELECT user_id, username, email, role FROM {$this->table} 
             WHERE user_id != ? AND is_active = 1 
             AND (username LIKE ? OR email LIKE ?)
             ORDER BY username ASC LIMIT 20",
            [$excludeUserId, "%{$searchTerm}%", "%{$searchTerm}%"]
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
?>
