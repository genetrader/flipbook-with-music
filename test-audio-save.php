<?php
/**
 * Test script to check if audio assignments are being saved correctly
 */

require_once 'flipbook-config.php';
require_once 'flipbook-db.php';

echo "<h2>Audio Assignment Test for Flipbook ID 4</h2>";

$flipbookId = 4;
$db = new FlipbookDB();

// Get pages
$pages = $db->getPages($flipbookId);
echo "<h3>Pages Retrieved</h3>";
echo "Total pages: " . count($pages) . "<br><br>";

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Array Index</th><th>DB ID</th><th>Page Number</th><th>Has image_path</th></tr>";
foreach ($pages as $index => $page) {
    echo "<tr>";
    echo "<td>$index</td>";
    echo "<td>{$page['id']}</td>";
    echo "<td>{$page['page_number']}</td>";
    echo "<td>" . (!empty($page['image_path']) ? 'Yes' : 'No') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Get audio files
$audioFiles = $db->getAudioFiles($flipbookId);
echo "<h3>Audio Files</h3>";
echo "Total audio files: " . count($audioFiles) . "<br><br>";

if (count($audioFiles) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Array Index</th><th>DB ID</th><th>Name</th></tr>";
    foreach ($audioFiles as $index => $file) {
        echo "<tr>";
        echo "<td>$index</td>";
        echo "<td>{$file['id']}</td>";
        echo "<td>{$file['name']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Get audio assignments
$assignments = $db->getAudioAssignments($flipbookId);
echo "<h3>Audio Assignments</h3>";
echo "Total assignments: " . count($assignments) . "<br><br>";

if (count($assignments) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Page ID</th><th>Page Number</th><th>Audio ID</th><th>Audio Name</th></tr>";
    foreach ($assignments as $assignment) {
        echo "<tr>";
        echo "<td>{$assignment['page_id']}</td>";
        echo "<td>{$assignment['page_number']}</td>";
        echo "<td>" . ($assignment['audio_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($assignment['audio_name'] ?? 'None') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No audio assignments found!</p>";
}

// Check the page_audio_assignments table directly
echo "<h3>Direct Database Query</h3>";
try {
    $stmt = $db->getAllFlipbooks(); // Just to get access to connection... hacky but works
    echo "<p>Checking page_audio_assignments table for all flipbook 4 pages...</p>";
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
