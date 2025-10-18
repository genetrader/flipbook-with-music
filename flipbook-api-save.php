<?php
/**
 * API Endpoint: Save Flipbook
 * Receives flipbook data and saves to database
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

    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    if (empty($data['title'])) {
        throw new Exception('Title is required');
    }

    if (empty($data['pages']) || !is_array($data['pages'])) {
        throw new Exception('Pages are required');
    }

    // Initialize database
    $db = new FlipbookDB();

    // Create flipbook
    $flipbookId = $db->createFlipbook(
        $data['title'],
        $data['description'] ?? '',
        $data['orientation'] ?? 'portrait'
    );

    if (!$flipbookId) {
        throw new Exception('Failed to create flipbook');
    }

    // Save pages
    foreach ($data['pages'] as $page) {
        $success = $db->addPage(
            $flipbookId,
            $page['pageNumber'],
            $page['data']
        );

        if (!$success) {
            throw new Exception('Failed to save page ' . $page['pageNumber']);
        }
    }

    // Update page count
    $db->updatePageCount($flipbookId, count($data['pages']));

    // Save audio files
    $audioIdMap = []; // Maps array index to database ID
    if (!empty($data['audioLibrary']) && is_array($data['audioLibrary'])) {
        foreach ($data['audioLibrary'] as $index => $audio) {
            $audioId = $db->addAudioFile(
                $flipbookId,
                $audio['name'],
                $audio['data']
            );

            if ($audioId) {
                $audioIdMap[$index] = $audioId;
            }
        }
    }

    // Save audio assignments
    if (!empty($data['audioAssignments']) && is_array($data['audioAssignments'])) {
        // Get all pages for this flipbook
        $pages = $db->getPages($flipbookId);

        foreach ($data['audioAssignments'] as $pageIndex => $audioIndex) {
            if (isset($audioIdMap[$audioIndex]) && isset($pages[$pageIndex])) {
                $db->assignAudioToPage(
                    $pages[$pageIndex]['id'],
                    $audioIdMap[$audioIndex]
                );
            }
        }
    }

    // Success
    echo json_encode([
        'success' => true,
        'flipbookId' => $flipbookId,
        'message' => 'Flipbook saved successfully'
    ]);

} catch (Exception $e) {
    error_log('Error saving flipbook: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
