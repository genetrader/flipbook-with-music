<?php
/**
 * API Endpoint: Delete Flipbook
 * Soft deletes a flipbook from the database
 */

session_start();

// Check authentication
if (!isset($_SESSION['flipbook_admin_logged_in']) || !$_SESSION['flipbook_admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Include dependencies
require_once 'flipbook-config.php';
require_once 'flipbook-db.php';

// Set JSON response header
header('Content-Type: application/json');

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || empty($data['id'])) {
        throw new Exception('Flipbook ID is required');
    }

    $flipbookId = (int)$data['id'];

    // Initialize database
    $db = new FlipbookDB();

    // Delete flipbook (soft delete)
    $success = $db->deleteFlipbook($flipbookId);

    if (!$success) {
        throw new Exception('Failed to delete flipbook');
    }

    // Success
    echo json_encode([
        'success' => true,
        'message' => 'Flipbook deleted successfully'
    ]);

} catch (Exception $e) {
    error_log('Error deleting flipbook: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
