<?php
/**
 * Product Repository Class
 * Extends: BaseRepository
 * Demonstrates: Inheritance, Encapsulation
 * Specific operations for product_ads table
 */

class ProductRepository extends BaseRepository {
    
    protected $table = 'product_ads';

    /**
     * Find products by vendor
     * @param int $vendorId
     * @return array
     */
    public function findByVendor($vendorId) {
        $stmt = $this->executeQuery(
            "SELECT * FROM {$this->table} WHERE vendor_id = ?",
            [$vendorId]
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Find approved products
     * @return array
     */
    public function findApproved() {
        $stmt = $this->executeQuery(
            "SELECT pa.*, u.username AS vendor_name
             FROM {$this->table} pa
             LEFT JOIN users u ON pa.vendor_id = u.user_id
             WHERE pa.status = 'approved'
             ORDER BY pa.product_name ASC"
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Find products by status
     * @param string $status
     * @return array
     */
    public function findByStatus($status) {
        $stmt = $this->executeQuery(
            "SELECT pa.*, u.username AS vendor_name
             FROM {$this->table} pa
             LEFT JOIN users u ON pa.vendor_id = u.user_id
             WHERE pa.status = ?",
            [$status]
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Find products by category
     * @param string $category
     * @return array
     */
    public function findByCategory($category) {
        $stmt = $this->executeQuery(
            "SELECT pa.*, u.username AS vendor_name
             FROM {$this->table} pa
             LEFT JOIN users u ON pa.vendor_id = u.user_id
             WHERE pa.category = ?",
            [$category]
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Update product status
     * @param int $productId
     * @param string $status
     * @return bool
     */
    public function updateStatus($productId, $status) {
        return $this->update($productId, ['status' => $status]);
    }

    /**
     * Get all categories
     * @return array
     */
    public function getCategories() {
        $stmt = $this->executeQuery("SELECT DISTINCT category FROM {$this->table}");
        return $stmt ? array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'category') : [];
    }
}
?>
