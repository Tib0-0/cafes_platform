<?php
/**
 * Product Service Class
 * Extends: BaseService
 * Demonstrates: Polymorphism, Inheritance, Encapsulation, Abstraction
 * Handles product business logic
 */

class ProductService extends BaseService {
    
    public function __construct() {
        parent::__construct(new ProductRepository());
    }

    /**
     * Create product advertisement
     * @param array $data
     * @return int|bool product_id or false
     */
    public function createProduct(array $data) {
        // Validate required fields
        if (!$this->validator->required($data['product_name'] ?? '', 'Product name')) {
            $this->errors = array_merge($this->errors, $this->validator->getErrors());
            return false;
        }

        $this->validator->clearErrors();
        if (!$this->validator->numeric($data['price'] ?? 0, 'Price')) {
            $this->errors = array_merge($this->errors, $this->validator->getErrors());
            return false;
        }

        $productData = [
            'vendor_id' => $data['vendor_id'],
            'product_name' => trim($data['product_name']),
            'description' => trim($data['description'] ?? ''),
            'price' => (float)$data['price'],
            'category' => trim($data['category'] ?? ''),
            'image_url' => $data['image_url'] ?? '',
            'status' => 'pending'
        ];

        return $this->repository->create($productData);
    }

    /**
     * Get products by vendor
     * @param int $vendorId
     * @return array
     */
    public function getVendorProducts($vendorId) {
        return $this->repository->findByVendor($vendorId);
    }

    /**
     * Get approved products (public)
     * @return array
     */
    public function getApprovedProducts() {
        return $this->repository->findApproved();
    }

    /**
     * Get products by status (admin)
     * @param string $status
     * @return array
     */
    public function getProductsByStatus($status) {
        $validStatuses = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            $this->addError("Invalid status");
            return [];
        }
        return $this->repository->findByStatus($status);
    }

    /**
     * Get products by category
     * @param string $category
     * @return array
     */
    public function getProductsByCategory($category) {
        return $this->repository->findByCategory($category);
    }

    /**
     * Approve product
     * @param int $productId
     * @return bool
     */
    public function approveProduct($productId) {
        return $this->repository->updateStatus($productId, 'approved');
    }

    /**
     * Reject product
     * @param int $productId
     * @return bool
     */
    public function rejectProduct($productId) {
        return $this->repository->updateStatus($productId, 'rejected');
    }

    /**
     * Get all categories
     * @return array
     */
    public function getCategories() {
        return $this->repository->getCategories();
    }

    /**
     * Validate product data (override abstract method)
     */
    public function validate(array $data) {
        if (empty($data['product_name'])) {
            $this->addError("Product name is required");
            return false;
        }
        if (!isset($data['price']) || !is_numeric($data['price'])) {
            $this->addError("Valid price is required");
            return false;
        }
        return true;
    }

    /**
     * Sanitize product data (override abstract method)
     */
    public function sanitize(array $data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if ($key === 'price') {
                $sanitized[$key] = (float)$value;
            } else {
                $sanitized[$key] = trim($value);
            }
        }
        return $sanitized;
    }
}
?>
