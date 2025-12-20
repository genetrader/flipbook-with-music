<?php
/**
 * API Endpoint: Get Flipbook Data
 * Returns complete flipbook data for editing
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
    // Get flipbook ID from query string
    $flipbookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($flipbookId <= 0) {
        throw new Exception('Invalid flipbook ID');
    }

    // Initialize database
    $db = new FlipbookDB();

    // Get complete flipbook data
    $flipbook = $db->getFlipbook($flipbookId);
    if (!$flipbook) {
        throw new Exception('Flipbook not found');
    }

    $pages = $db->getPages($flipbookId);
    $audioFiles = $db->getAudioFiles($flipbookId);
    $assignmentData = $db->getAudioAssignments($flipbookId);

    // Build assignments array
    $assignments = [];
    foreach ($assignmentData as $assignment) {
        if ($assignment['audio_id']) {
            $assignments[] = [
                'page_id' => $assignment['page_id'],
                'page_number' => $assignment['page_number'],
                'audio_id' => $assignment['audio_id'],
                'audio_name' => $assignment['audio_name']
            ];
        }
    }

    // Success
    echo json_encode([
        'success' => true,
        'data' => [
            'flipbook' => $flipbook,
            'pages' => $pages,
            'audioFiles' => $audioFiles,
            'assignments' => $assignments
        ]
    ]);

} catch (Exception $e) {
    error_log('Error getting flipbook: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
