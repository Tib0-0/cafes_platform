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
    $adId = RequestValidator::post('ad_id', '');
    $vendorId = RequestValidator::post('vendor_id', '');
    $cafeOwnerId = RequestValidator::post('cafe_owner_id', '');
    $message = RequestValidator::post('message', '');
    $proposedTerms = RequestValidator::post('proposed_terms', '');

    if (empty($adId) || empty($vendorId) || empty($cafeOwnerId)) {
        ResponseHelper::error("Validation failed", ["Product, vendor, and cafe owner IDs are required"], 400);
    }

    // Use PartnershipService to create request
    $partnershipService = new PartnershipService();
    $partnershipId = $partnershipService->createRequest([
        'ad_id' => $adId,
        'vendor_id' => $vendorId,
        'cafe_owner_id' => $cafeOwnerId,
        'message' => $message,
        'proposed_terms' => $proposedTerms
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
