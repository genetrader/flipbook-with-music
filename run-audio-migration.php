<?php
/**
 * Database Migration Script
 * Run this once to add audio_path column to audio_files table
 */

require_once 'flipbook-config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Running audio migration...\n";

    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM audio_files LIKE 'audio_path'");
    if ($stmt->rowCount() > 0) {
        echo "Column 'audio_path' already exists. Migration not needed.\n";
    } else {
        // Add audio_path column
        $pdo->exec("ALTER TABLE audio_files ADD COLUMN audio_path VARCHAR(500) AFTER audio_data");
        echo "Successfully added 'audio_path' column to audio_files table.\n";
    }

    echo "Migration complete!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
