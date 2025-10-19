<?php
/**
 * Check PHP error log for recent entries
 */

echo "<h2>Recent PHP Error Log Entries</h2>";
echo "<pre>";

// Try common error log locations
$logPaths = [
    __DIR__ . '/error_log',
    __DIR__ . '/../error_log',
    ini_get('error_log'),
    '/var/log/php_errors.log'
];

$found = false;
foreach ($logPaths as $path) {
    if ($path && file_exists($path)) {
        echo "=== Log file: $path ===\n\n";
        $lines = file($path);
        // Show last 100 lines
        $recentLines = array_slice($lines, -100);
        echo implode('', $recentLines);
        $found = true;
        break;
    }
}

if (!$found) {
    echo "Error log not found. Tried:\n";
    foreach ($logPaths as $path) {
        echo "- $path\n";
    }
}

echo "</pre>";
?>
