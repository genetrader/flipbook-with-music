<?php
/**
 * Database Migration Script
 * Run this once to add image_path column to pages table
 */

require_once 'flipbook-config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Running migration...\n";

    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM pages LIKE 'image_path'");
    if ($stmt->rowCount() > 0) {
        echo "Column 'image_path' already exists. Migration not needed.\n";
    } else {
        // Add image_path column
        $pdo->exec("ALTER TABLE pages ADD COLUMN image_path VARCHAR(500) AFTER image_data");
        echo "Successfully added 'image_path' column to pages table.\n";
    }

    echo "Migration complete!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
