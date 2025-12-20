<?php
/**
 * Batch Flipbook Upload Script
 *
 * This script automatically uploads PDFs and creates flipbooks in the database.
 * It bypasses the normal authentication requirement for batch operations.
 *
 * Usage: php flipbook-batch-upload.php
 *
 * IMPORTANT: Only run this script from a secure environment!
 * Delete or rename this file after use for security.
 */

// Include dependencies
require_once 'flipbook-config.php';
require_once 'flipbook-db.php';

// Configuration
$pdfDirectory = __DIR__ . '/flipbook-pdfs/';
$flipbooks = [
    [
        'filename' => 'Cork_1.pdf',
        'title' => 'Cork Chapter 1',
        'description' => 'The beginning of Cork\'s adventure',
        'orientation' => 'portrait'
    ],
    [
        'filename' => 'Cork_2.pdf',
        'title' => 'Cork Chapter 2',
        'description' => 'Cork\'s journey continues',
        'orientation' => 'portrait'
    ],
    [
        'filename' => 'Cork_3.pdf',
        'title' => 'Cork Chapter 3 - Dark, Damp & Dusty',
        'description' => 'Cork faces new challenges in the darkness',
        'orientation' => 'portrait'
    ],
    [
        'filename' => 'Cork_5.pdf',
        'title' => 'Cork Chapter 5',
        'description' => 'Cork\'s adventure deepens',
        'orientation' => 'portrait'
    ],
    [
        'filename' => 'Cork_6_Prodigy.pdf',
        'title' => 'Prodigy (Cork Chapter 6)',
        'description' => 'The tale of a young prodigy in Cork\'s world',
        'orientation' => 'portrait'
    ]
];

echo "==============================================\n";
echo "   Batch Flipbook Upload Script\n";
echo "==============================================\n\n";

// Initialize database
try {
    $db = new FlipbookDB();
    echo "✓ Database connection established\n\n";
} catch (Exception $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Process each flipbook
foreach ($flipbooks as $index => $flipbook) {
    $num = $index + 1;
    echo "[$num/" . count($flipbooks) . "] Processing: {$flipbook['title']}\n";
    echo str_repeat("-", 50) . "\n";

    $pdfPath = $pdfDirectory . $flipbook['filename'];

    // Check if PDF exists
    if (!file_exists($pdfPath)) {
        echo "✗ PDF file not found: $pdfPath\n\n";
        continue;
    }

    $fileSize = filesize($pdfPath);
    echo "  File: {$flipbook['filename']} (" . round($fileSize / 1024 / 1024, 2) . " MB)\n";

    // Check if flipbook already exists
    $existingFlipbooks = $db->getAllFlipbooks();
    $exists = false;
    foreach ($existingFlipbooks as $existing) {
        if ($existing['title'] === $flipbook['title']) {
            echo "  ⚠ Flipbook already exists (ID: {$existing['id']})\n";
            echo "  Skipping to avoid duplicates\n\n";
            $exists = true;
            break;
        }
    }

    if ($exists) {
        continue;
    }

    try {
        // Create flipbook entry
        echo "  Creating flipbook entry...\n";
        $flipbookId = $db->createFlipbook(
            $flipbook['title'],
            $flipbook['description'],
            $flipbook['orientation']
        );

        if (!$flipbookId) {
            throw new Exception("Failed to create flipbook entry");
        }

        echo "  ✓ Flipbook created (ID: $flipbookId)\n";

        // Create directories
        $imageDir = __DIR__ . '/flipbook-images/' . $flipbookId;
        if (!file_exists($imageDir)) {
            if (!mkdir($imageDir, 0755, true)) {
                throw new Exception("Failed to create image directory");
            }
        }

        // Note: We're not converting the PDF here because it requires heavy processing
        // The PDF file is ready, and the user should upload it through the admin interface
        // This script just creates the database entry

        echo "  ✓ Image directory created\n";
        echo "  📁 Directory: flipbook-images/$flipbookId/\n";
        echo "  \n";
        echo "  ⚠ NEXT STEPS:\n";
        echo "     1. Log into the admin panel\n";
        echo "     2. Edit this flipbook (ID: $flipbookId)\n";
        echo "     3. Upload the PDF: $pdfPath\n";
        echo "     4. Convert pages and save\n\n";

    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
        continue;
    }
}

echo "\n==============================================\n";
echo "   Upload Complete!\n";
echo "==============================================\n\n";
echo "Next Steps:\n";
echo "1. Visit: https://largerthanlifecomics.com/flipbook-admin-login.php\n";
echo "2. Login with your credentials\n";
echo "3. Click on each flipbook to upload and convert the PDFs\n\n";
echo "All PDFs are ready in: $pdfDirectory\n\n";
echo "SECURITY NOTE: Delete this script after use!\n";
echo "  Run: del flipbook-batch-upload.php\n\n";
?>
