<?php
/**
 * API Endpoint: Save Flipbook Images as Files
 * Saves base64 images as actual JPG files on server
 */

session_name(FLIPBOOK_SESSION_NAME);
session_start();

// Check authentication
if (!isset($_SESSION['flipbook_admin_logged_in']) || !$_SESSION['flipbook_admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['flipbookId']) || !isset($data['pageNumber']) || !isset($data['imageData'])) {
        throw new Exception('Missing required data');
    }

    $flipbookId = (int)$data['flipbookId'];
    $pageNumber = (int)$data['pageNumber'];
    $imageData = $data['imageData'];

    // Create directory for this flipbook if it doesn't exist
    $uploadDir = FLIPBOOK_FLIPBOOK_UPLOAD_DIR . 'pages/' . $flipbookId;
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Extract base64 data
    if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
        $imageData = substr($imageData, strpos($imageData, ',') + 1);
        $type = strtolower($type[1]);

        $imageData = base64_decode($imageData);
        if ($imageData === false) {
            throw new Exception('Base64 decode failed');
        }

        // Save as JPG file
        $filename = 'page-' . $pageNumber . '.jpg';
        $filepath = $uploadDir . '/' . $filename;

        if (file_put_contents($filepath, $imageData) === false) {
            throw new Exception('Failed to save image file');
        }

        // Return the URL path
        $imageUrl = 'flipbook-images/' . $flipbookId . '/' . $filename;

        echo json_encode([
            'success' => true,
            'imageUrl' => $imageUrl
        ]);
    } else {
        throw new Exception('Invalid image data format');
    }

} catch (Exception $e) {
    error_log('Error saving image file: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
