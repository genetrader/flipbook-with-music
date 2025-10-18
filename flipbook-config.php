<?php
/**
 * Flipbook Admin - Database Configuration
 *
 * IMPORTANT: Update these settings with your actual database credentials
 * before deploying to production.
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'flipbook_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('UPLOAD_DIR', __DIR__ . '/flipbook-uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB in bytes
define('ALLOWED_PDF_TYPES', ['application/pdf']);
define('ALLOWED_AUDIO_TYPES', ['audio/mpeg', 'audio/mp3']);

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
    mkdir(UPLOAD_DIR . 'pdfs/', 0755, true);
    mkdir(UPLOAD_DIR . 'audio/', 0755, true);
    mkdir(UPLOAD_DIR . 'pages/', 0755, true);
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
