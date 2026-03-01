<?php
/**
 * Comprehensive Example: Admin Partnership Review
 * Uses: PartnershipService, AuthHelper, ResponseHelper
 * Demonstrates: Multiple operations with single service
 */

header('Content-Type: application/json');
require_once "../../core/bootstrap.php";

// Check if user is authenticated and is an admin
AuthHelper::requireLogin();
AuthHelper::requireAdmin();

try {
    $action = RequestValidator::get('action', 'list');
    $status = RequestValidator::get('status', 'pending');

    // Use PartnershipService 
    $partnershipService = new PartnershipService();
    
    switch ($action) {
        case 'list':
            $partnerships = $partnershipService->getRequestsByStatus($status);
            break;
        
        case 'approve':
            $partnershipId = RequestValidator::get('partnership_id', '');
            if (empty($partnershipId)) {
                ResponseHelper::error("Validation failed", ["Partnership ID required"], 400);
            }
            $success = $partnershipService->approveRequest($partnershipId);
            $partnerships = ['success' => $success];
            break;
        
        case 'reject':
            $partnershipId = RequestValidator::get('partnership_id', '');
            if (empty($partnershipId)) {
                ResponseHelper::error("Validation failed", ["Partnership ID required"], 400);
            }
            $success = $partnershipService->rejectRequest($partnershipId);
            $partnerships = ['success' => $success];
            break;
        
        default:
            ResponseHelper::error("Invalid action", ["Action not recognized"], 400);
    }

    ResponseHelper::json($partnerships);

} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
