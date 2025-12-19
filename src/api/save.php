<?php
/**
 * API Endpoint: Save Flipbook
 * Receives flipbook data and saves to database
 */

session_name(FLIPBOOK_SESSION_NAME);
session_start();

// Check authentication
if (!isset($_SESSION['flipbook_admin_logged_in']) || !$_SESSION['flipbook_admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Include dependencies
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../FlipbookDB.php';

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
    $imageDir = FLIPBOOK_FLIPBOOK_UPLOAD_DIR . 'pages/' . $flipbookId;
    if (!file_exists($imageDir)) {
        if (!mkdir($imageDir, 0755, true)) {
            throw new Exception('Failed to create image directory');
        }
    }

    // Save pages as actual image files (only for new flipbooks or base64 data)
    // If editing an existing flipbook with file paths, skip page re-saving
    $hasFilePaths = false;
    if (!empty($data['pages'][0]['data'])) {
        $firstPageData = $data['pages'][0]['data'];
        $hasFilePaths = (strpos($firstPageData, 'flipbook-images/') === 0);
    }

    if (!$hasFilePaths) {
        // New flipbook or old base64 flipbook - save pages as files
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
    } else {
        // Editing flipbook with file paths - pages already exist, skip re-saving
        error_log("Editing flipbook with file paths - skipping page re-save");
    }

    // Save audio files
    $audioIdMap = []; // Maps array index to database ID
    if (!empty($data['audioLibrary']) && is_array($data['audioLibrary'])) {
        // Create audio directory
        $audioDir = __DIR__ . '/flipbook-audio/' . $flipbookId;
        if (!file_exists($audioDir)) {
            if (!mkdir($audioDir, 0755, true)) {
                throw new Exception('Failed to create audio directory');
            }
        }

        foreach ($data['audioLibrary'] as $index => $audio) {
            // Check if this audio file already exists (has an ID from database)
            if (!empty($audio['id'])) {
                // Reuse existing audio file
                $audioIdMap[$index] = $audio['id'];
                error_log("Reusing existing audio ID {$audio['id']} for: {$audio['name']}");
            } else {
                // Save new audio file
                $audioData = $audio['data'];

                // Check if already a file path
                if (strpos($audioData, 'flipbook-audio/') === 0) {
                    // Already saved, just add to database
                    $audioId = $db->addAudioFile(
                        $flipbookId,
                        $audio['name'],
                        '', // Empty base64
                        $audioData // File path
                    );
                } else {
                    // Decode and save base64 audio
                    if (preg_match('/^data:audio\/(\w+);base64,/', $audioData, $type)) {
                        $base64Data = substr($audioData, strpos($audioData, ',') + 1);
                        $decodedAudio = base64_decode($base64Data);

                        if ($decodedAudio === false) {
                            error_log("Failed to decode audio: {$audio['name']}");
                            continue;
                        }

                        // Save as MP3 file (no optimization for now - browser already compressed)
                        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $audio['name']) . '.mp3';
                        $filepath = $audioDir . '/' . $filename;

                        if (file_put_contents($filepath, $decodedAudio) === false) {
                            error_log("Failed to write audio file: $filepath");
                            continue;
                        }

                        error_log("Successfully saved audio file: $filepath (" . filesize($filepath) . " bytes)");

                        // Store the relative path in database
                        $audioPath = 'flipbook-audio/' . $flipbookId . '/' . $filename;

                        $audioId = $db->addAudioFile(
                            $flipbookId,
                            $audio['name'],
                            '', // Empty base64
                            $audioPath // File path
                        );
                    } else {
                        error_log("Invalid audio format for: {$audio['name']}");
                        continue;
                    }
                }

                if ($audioId) {
                    $audioIdMap[$index] = $audioId;
                }
            }
        }
    }

    // Save audio assignments
    error_log('=== AUDIO ASSIGNMENT DEBUG START ===');
    error_log('Audio assignments received: ' . json_encode($data['audioAssignments']));
    error_log('Audio ID map: ' . json_encode($audioIdMap));
    error_log('audioAssignments is array: ' . (is_array($data['audioAssignments']) ? 'yes' : 'no'));
    error_log('audioAssignments is empty: ' . (empty($data['audioAssignments']) ? 'yes' : 'no'));

    if (!empty($data['audioAssignments']) && is_array($data['audioAssignments'])) {
        // Get all pages for this flipbook
        $pages = $db->getPages($flipbookId);
        error_log('Pages retrieved for assignments: ' . count($pages));
        error_log('Sample page data: ' . json_encode($pages[0] ?? 'none'));

        $assignmentCount = 0;
        foreach ($data['audioAssignments'] as $pageIndex => $audioIndex) {
            error_log("Processing assignment: page index $pageIndex -> audio index $audioIndex");
            error_log("  - audioIdMap[$audioIndex] exists: " . (isset($audioIdMap[$audioIndex]) ? 'yes - ID=' . $audioIdMap[$audioIndex] : 'NO'));
            error_log("  - pages[$pageIndex] exists: " . (isset($pages[$pageIndex]) ? 'yes - Page ID=' . $pages[$pageIndex]['id'] : 'NO'));

            if (isset($audioIdMap[$audioIndex]) && isset($pages[$pageIndex])) {
                $pageId = $pages[$pageIndex]['id'];
                $audioId = $audioIdMap[$audioIndex];
                error_log("  - Calling assignAudioToPage(pageId=$pageId, audioId=$audioId)");

                $result = $db->assignAudioToPage($pageId, $audioId);

                if ($result) {
                    $assignmentCount++;
                    error_log("  - SUCCESS: Assigned audio $audioId to page $pageId");
                } else {
                    error_log("  - FAILED: Could not assign audio to page");
                }
            } else {
                error_log("  - SKIPPED: Missing audio or page reference");
                if (!isset($audioIdMap[$audioIndex])) {
                    error_log("    -> Audio index $audioIndex not found in audioIdMap");
                }
                if (!isset($pages[$pageIndex])) {
                    error_log("    -> Page index $pageIndex not found in pages array (total pages: " . count($pages) . ")");
                }
            }
        }
        error_log("Total assignments saved: $assignmentCount");
    } else {
        error_log('No audio assignments to save - array is empty or not set');
    }
    error_log('=== AUDIO ASSIGNMENT DEBUG END ===');

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
