<?php
/**
 * Partnership Repository Class
 * Extends: BaseRepository
 * Demonstrates: Inheritance, Encapsulation
 * Specific operations for partnerships table
 */

class PartnershipRepository extends BaseRepository {
    
    protected $table = 'partnerships';

    /**
     * Find partnership requests by vendor
     * @param int $vendorId
     * @return array
     */
    public function findByVendor($vendorId) {
        $stmt = $this->executeQuery(
            "SELECT * FROM {$this->table} WHERE vendor_id = ? ORDER BY created_at DESC",
            [$vendorId]
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Find partnership requests by cafe owner
     * @param int $cafeOwnerId
     * @return array
     */
    public function findByOwner($cafeOwnerId) {
        $stmt = $this->executeQuery(
            "SELECT * FROM {$this->table} WHERE cafe_owner_id = ? ORDER BY created_at DESC",
            [$cafeOwnerId]
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Find partnership requests by status
     * @param string $status
     * @return array
     */
    public function findByStatus($status) {
        $stmt = $this->executeQuery(
            "SELECT p.*, v.username AS vendor_name, c.username AS cafe_name
             FROM {$this->table} p
             LEFT JOIN users v ON p.vendor_id = v.user_id
             LEFT JOIN users c ON p.cafe_owner_id = c.user_id
             WHERE p.status = ?
             ORDER BY p.created_at DESC",
            [$status]
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Update partnership status
     * @param int $partnershipId
     * @param string $status
     * @return bool
     */
    public function updateStatus($partnershipId, $status) {
        return $this->update($partnershipId, ['status' => $status]);
    }

    /**
     * Check if partnership exists
     * @param int $vendorId
     * @param int $cafeOwnerId
     * @return bool
     */
    public function existsBetween($vendorId, $cafeOwnerId) {
        $stmt = $this->executeQuery(
            "SELECT id FROM {$this->table} WHERE vendor_id = ? AND cafe_owner_id = ? LIMIT 1",
            [$vendorId, $cafeOwnerId]
        );
        return $stmt ? $stmt->rowCount() > 0 : false;
    }
}
?>
