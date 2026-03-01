<?php
/**
 * Partnership Service Class
 * Extends: BaseService
 * Demonstrates: Polymorphism, Inheritance, Encapsulation, Abstraction
 * Handles partnership business logic
 */

class PartnershipService extends BaseService {
    
    public function __construct() {
        parent::__construct(new PartnershipRepository());
    }

    /**
     * Create partnership request
     * @param array $data
     * @return int|bool partnership_id or false
     */
    public function createRequest(array $data) {
        // Validate
        if (!isset($data['vendor_id']) || !isset($data['cafe_owner_id'])) {
            $this->addError("Vendor and cafe owner are required");
            return false;
        }

        // Check for duplicate request
        if ($this->repository->existsBetween($data['vendor_id'], $data['cafe_owner_id'])) {
            $this->addError("Partnership already requested");
            return false;
        }

        $partnershipData = [
            'vendor_id' => (int)$data['vendor_id'],
            'cafe_owner_id' => (int)$data['cafe_owner_id'],
            'status' => 'pending',
            'message' => trim($data['message'] ?? ''),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->repository->create($partnershipData);
    }

    /**
     * Get requests for vendor
     * @param int $vendorId
     * @return array
     */
    public function getVendorRequests($vendorId) {
        return $this->repository->findByVendor($vendorId);
    }

    /**
     * Get requests for cafe owner
     * @param int $cafeOwnerId
     * @return array
     */
    public function getOwnerRequests($cafeOwnerId) {
        return $this->repository->findByOwner($cafeOwnerId);
    }

    /**
     * Get requests by status (admin)
     * @param string $status
     * @return array
     */
    public function getRequestsByStatus($status) {
        $validStatuses = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            $this->addError("Invalid status");
            return [];
        }
        return $this->repository->findByStatus($status);
    }

    /**
     * Approve partnership
     * @param int $partnershipId
     * @return bool
     */
    public function approveRequest($partnershipId) {
        return $this->repository->updateStatus($partnershipId, 'approved');
    }

    /**
     * Reject partnership
     * @param int $partnershipId
     * @return bool
     */
    public function rejectRequest($partnershipId) {
        return $this->repository->updateStatus($partnershipId, 'rejected');
    }

    /**
     * Validate partnership data (override abstract method)
     */
    public function validate(array $data) {
        if (empty($data['vendor_id']) || empty($data['cafe_owner_id'])) {
            $this->addError("Vendor and cafe owner IDs are required");
            return false;
        }
        return true;
    }

    /**
     * Sanitize partnership data (override abstract method)
     */
    public function sanitize(array $data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['vendor_id', 'cafe_owner_id'])) {
                $sanitized[$key] = (int)$value;
            } else {
                $sanitized[$key] = trim($value);
            }
        }
        return $sanitized;
    }
}
?>
