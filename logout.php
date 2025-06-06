<?php
session_start(); // Start the session first to access session variables

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie (if using cookies for session IDs)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session data on the server
session_destroy();

// Redirect to the login page after logout
require_once 'includes/config.php'; // We need config.php for BASE_URL
header("Location: " . BASE_URL . "login.php");
exit(); // Ensure no further code is executed
?>