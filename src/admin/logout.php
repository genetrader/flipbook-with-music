<?php
/**
 * Flipbook Plugin - Admin Logout
 * Destroys the session and redirects to login page
 *
 * @version 1.0.0
 */

// Load configuration
require_once __DIR__ . '/../config.php';

// Start session with unique name
session_name(FLIPBOOK_SESSION_NAME);
session_start();

// Destroy all session data
$_SESSION = [];

// Destroy the session cookie
if (isset($_COOKIE[FLIPBOOK_SESSION_NAME])) {
    setcookie(FLIPBOOK_SESSION_NAME, '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit;
?>
