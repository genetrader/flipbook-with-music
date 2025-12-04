<?php
/**
 * Flipbook Admin - Logout
 * Destroys the session and redirects to login page
 */

session_start();

// Destroy all session data
$_SESSION = [];

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: flipbook-admin-login.php');
exit;
?>
