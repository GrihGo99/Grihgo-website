<?php
/**
 * GrihGO Digital & Tech — Admin Logout Handler
 * Version: 4.0 (Part 4 - Complete Admin Panel & CRM Architecture)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['admin_id'])) {
    try {
        $pdo = Database::getConnection();
        logAdminActivity(
            $pdo,
            (int)$_SESSION['admin_id'],
            $_SESSION['admin_name'] ?? 'Admin',
            'Admin Logout',
            'admins',
            (int)$_SESSION['admin_id'],
            'Admin logged out session securely.'
        );
    } catch (Exception $e) {}
}

// Clear all admin session data
unset(
    $_SESSION['admin_id'],
    $_SESSION['admin_name'],
    $_SESSION['admin_email'],
    $_SESSION['admin_role'],
    $_SESSION['admin_last_activity']
);

// If client session is not present, clean up session completely
if (empty($_SESSION['user_id'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    @session_destroy();
}

header('Location: login.php?msg=logged_out');
exit;
