<?php
/**
 * Refactored Partnership Create Request Script
 * Uses: PartnershipService, AuthHelper, ResponseHelper
 * Demonstrates: OOP usage with authentication
 */

header('Content-Type: application/json');
require_once "../../core/bootstrap.php";

// Check if user is authenticated
AuthHelper::requireLogin();

// Only allow POST requests
if (!RequestValidator::isPost()) {
    ResponseHelper::error("Invalid request method", ["Only POST requests allowed"], 405);
}

try {
    $vendorId = RequestValidator::post('vendor_id', '');
    $cafeOwnerId = RequestValidator::post('cafe_owner_id', '');
    $message = RequestValidator::post('message', '');

    if (empty($vendorId) || empty($cafeOwnerId)) {
        ResponseHelper::error("Validation failed", ["Vendor and cafe owner IDs are required"], 400);
    }

    // Use PartnershipService to create request
    $partnershipService = new PartnershipService();
    $partnershipId = $partnershipService->createRequest([
        'vendor_id' => $vendorId,
        'cafe_owner_id' => $cafeOwnerId,
        'message' => $message
    ]);

    if (!$partnershipId) {
        $errors = $partnershipService->getErrors();
        ResponseHelper::error("Partnership request failed", $errors, 400);
    }

    ResponseHelper::success(['partnership_id' => $partnershipId], "Partnership request created");

} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
