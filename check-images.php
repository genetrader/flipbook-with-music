<?php
/**
 * Diagnostic script to check if image files are being created
 */

require_once 'flipbook-config.php';
require_once 'flipbook-db.php';

echo "<h2>Flipbook Image Storage Diagnostic</h2>";

// Check if flipbook-images directory exists
$imageBaseDir = __DIR__ . '/flipbook-images';
echo "<h3>Base Directory Check</h3>";
echo "Directory: $imageBaseDir<br>";
echo "Exists: " . (file_exists($imageBaseDir) ? 'Yes' : 'No') . "<br>";
echo "Writable: " . (is_writable(__DIR__) ? 'Yes' : 'No') . "<br><br>";

// Check all flipbook subdirectories
if (file_exists($imageBaseDir)) {
    echo "<h3>Flipbook Image Directories</h3>";
    $dirs = glob($imageBaseDir . '/*', GLOB_ONLYDIR);
    if ($dirs) {
        foreach ($dirs as $dir) {
            $flipbookId = basename($dir);
            $files = glob($dir . '/*.jpg');
            echo "Flipbook ID: $flipbookId<br>";
            echo "Files: " . count($files) . " JPG files<br>";

            if ($files) {
                foreach ($files as $file) {
                    $size = filesize($file);
                    echo "&nbsp;&nbsp;- " . basename($file) . " (" . number_format($size) . " bytes)<br>";
                }
            }
            echo "<br>";
        }
    } else {
        echo "No flipbook directories found<br>";
    }
}

// Check database for image_path values
echo "<h3>Database Check</h3>";
$db = new FlipbookDB();
$flipbooks = $db->getAllFlipbooks();

foreach ($flipbooks as $flipbook) {
    $pages = $db->getPages($flipbook['id']);
    echo "<strong>" . htmlspecialchars($flipbook['title']) . "</strong> (ID: {$flipbook['id']})<br>";
    echo "Total pages: " . count($pages) . "<br>";

    $withPath = 0;
    $withData = 0;

    foreach ($pages as $page) {
        if (!empty($page['image_path'])) $withPath++;
        if (!empty($page['image_data'])) $withData++;
    }

    echo "Pages with image_path: $withPath<br>";
    echo "Pages with image_data: $withData<br>";

    if ($pages && !empty($pages[0]['image_path'])) {
        echo "Sample image_path: " . htmlspecialchars($pages[0]['image_path']) . "<br>";
    }

    echo "<br>";
}
?>
