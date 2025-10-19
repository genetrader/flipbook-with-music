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

    // Check if this is an update (flipbookId provided) or new creation
    $isUpdate = !empty($data['flipbookId']);
    $flipbookId = $isUpdate ? (int)$data['flipbookId'] : null;

    if ($isUpdate) {
        // Update existing flipbook
        $success = $db->updateFlipbook(
            $flipbookId,
            $data['title'],
            $data['description'] ?? ''
        );

        if (!$success) {
            throw new Exception('Failed to update flipbook');
        }

        // Clear existing audio assignments
        $db->clearAudioAssignments($flipbookId);
    } else {
        // Create new flipbook
        $flipbookId = $db->createFlipbook(
            $data['title'],
            $data['description'] ?? '',
            $data['orientation'] ?? 'portrait'
        );

        if (!$flipbookId) {
            throw new Exception('Failed to create flipbook');
        }
    }

    // Create directory for flipbook images
    $imageDir = __DIR__ . '/flipbook-images/' . $flipbookId;
    if (!file_exists($imageDir)) {
        if (!mkdir($imageDir, 0755, true)) {
            throw new Exception('Failed to create image directory');
        }
    }

    // Save pages as actual image files
    foreach ($data['pages'] as $page) {
        $pageNumber = $page['pageNumber'];
        $imageData = $page['data'];

        // Extract base64 data and save as JPG file
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $base64Data = substr($imageData, strpos($imageData, ',') + 1);
            $decodedImage = base64_decode($base64Data);

            if ($decodedImage === false) {
                throw new Exception('Failed to decode image for page ' . $pageNumber);
            }

            // Save as JPG file
            $filename = 'page-' . $pageNumber . '.jpg';
            $filepath = $imageDir . '/' . $filename;

            if (file_put_contents($filepath, $decodedImage) === false) {
                throw new Exception('Failed to write image file for page ' . $pageNumber);
            }

            error_log("Successfully saved image file: $filepath (" . filesize($filepath) . " bytes)");

            // Store the relative path in database (not base64)
            $imagePath = 'flipbook-images/' . $flipbookId . '/' . $filename;
            error_log("Storing image path in database: $imagePath");

            $success = $db->addPage(
                $flipbookId,
                $pageNumber,
                '', // Empty base64 data
                $imagePath // File path
            );

            if (!$success) {
                throw new Exception('Failed to save page ' . $pageNumber);
            }
        } else {
            throw new Exception('Invalid image format for page ' . $pageNumber);
        }
    }

    // Update page count
    $db->updatePageCount($flipbookId, count($data['pages']));

    // Save audio files
    $audioIdMap = []; // Maps array index to database ID
    if (!empty($data['audioLibrary']) && is_array($data['audioLibrary'])) {
        foreach ($data['audioLibrary'] as $index => $audio) {
            // Check if this audio file already exists (has an ID from database)
            if (!empty($audio['id'])) {
                // Reuse existing audio file
                $audioIdMap[$index] = $audio['id'];
            } else {
                // Create new audio file
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
    }

    // Save audio assignments
    error_log('Audio assignments received: ' . json_encode($data['audioAssignments']));
    error_log('Audio ID map: ' . json_encode($audioIdMap));

    if (!empty($data['audioAssignments']) && is_array($data['audioAssignments'])) {
        // Get all pages for this flipbook
        $pages = $db->getPages($flipbookId);
        error_log('Pages retrieved: ' . count($pages));

        $assignmentCount = 0;
        foreach ($data['audioAssignments'] as $pageIndex => $audioIndex) {
            error_log("Processing assignment: page $pageIndex -> audio $audioIndex");

            if (isset($audioIdMap[$audioIndex]) && isset($pages[$pageIndex])) {
                $result = $db->assignAudioToPage(
                    $pages[$pageIndex]['id'],
                    $audioIdMap[$audioIndex]
                );
                if ($result) {
                    $assignmentCount++;
                    error_log("Successfully assigned audio {$audioIdMap[$audioIndex]} to page {$pages[$pageIndex]['id']}");
                } else {
                    error_log("Failed to assign audio to page $pageIndex");
                }
            } else {
                error_log("Missing audio or page: audioIndex=$audioIndex, pageIndex=$pageIndex");
            }
        }
        error_log("Total assignments saved: $assignmentCount");
    } else {
        error_log('No audio assignments to save');
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
