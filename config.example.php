<?php
/**
 * Flipbook Plugin - Example Configuration
 *
 * Copy this file to src/config.php and update with your settings
 * DO NOT commit src/config.php to version control!
 */

// Database connection settings
define('FLIPBOOK_DB_HOST', 'localhost');
define('FLIPBOOK_DB_NAME', 'your_database_name');
define('FLIPBOOK_DB_USER', 'your_database_user');
define('FLIPBOOK_DB_PASS', 'your_database_password');
define('FLIPBOOK_DB_CHARSET', 'utf8mb4');

// Table prefix (allows multiple instances in same database)
define('FLIPBOOK_TABLE_PREFIX', 'flipbook_');

// Application settings
define('FLIPBOOK_UPLOAD_DIR', __DIR__ . '/uploads/');
define('FLIPBOOK_MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB in bytes
define('FLIPBOOK_ALLOWED_PDF_TYPES', ['application/pdf']);
define('FLIPBOOK_ALLOWED_AUDIO_TYPES', ['audio/mpeg', 'audio/mp3']);

// Admin credentials
// IMPORTANT: Change these and use strong passwords!
define('FLIPBOOK_ADMIN_USER', 'admin');
define('FLIPBOOK_ADMIN_PASS', password_hash('change_this_password', PASSWORD_BCRYPT));

// Security settings
define('FLIPBOOK_SESSION_NAME', 'flipbook_admin_session');
define('FLIPBOOK_SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Application paths
define('FLIPBOOK_BASE_PATH', __DIR__);
define('FLIPBOOK_SRC_PATH', __DIR__ . '/src');

// Debug mode (set to 0 in production!)
define('FLIPBOOK_DEBUG', 1);

if (FLIPBOOK_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>
