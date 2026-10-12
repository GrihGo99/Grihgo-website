<?php
/**
 * GrihGO Digital & Tech — Secure Client Logout
 * Version: 3.0 (Part 3 - Client Portal)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = [];

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session entirely
session_destroy();

// Redirect to login with logout notice
header('Location: ../login.php?msg=logged_out');
exit;
