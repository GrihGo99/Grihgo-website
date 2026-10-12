<?php
/**
 * GrihGO Digital & Tech — Dedicated Manager Portal Authentication Middleware
 * Version: 4.5 (Antigravity 2.0 Integration)
 * Technology: PHP 8+, PDO, CSRF, Session Hardening, RBAC
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../../config/functions.php';

// Ensure hardened session is initialized
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    @session_start();
}

// Session Timeout: 2 hours of inactivity
$managerTimeout = 7200;
if (isset($_SESSION['manager_last_activity']) && (time() - $_SESSION['manager_last_activity'] > $managerTimeout)) {
    unset($_SESSION['manager_id'], $_SESSION['manager_email'], $_SESSION['manager_name'], $_SESSION['manager_role'], $_SESSION['manager_last_activity']);
    header('Location: login.php?msg=timeout');
    exit;
}
$_SESSION['manager_last_activity'] = time();

// Allow seamless bridge if already logged in via admin session as manager or super_admin
if (empty($_SESSION['manager_id']) && !empty($_SESSION['admin_id']) && in_array($_SESSION['admin_role'] ?? '', ['manager', 'super_admin', 'admin'], true)) {
    $_SESSION['manager_id'] = $_SESSION['admin_id'];
    $_SESSION['manager_name'] = $_SESSION['admin_name'];
    $_SESSION['manager_email'] = $_SESSION['admin_email'];
    $_SESSION['manager_role'] = $_SESSION['admin_role'];
    $_SESSION['manager_last_activity'] = time();
}

// Check if authenticated
if (empty($_SESSION['manager_id'])) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Manager session expired or unauthorized.']);
        exit;
    }
    $redirect = urlencode($_SERVER['REQUEST_URI'] ?? 'manager/dashboard.php');
    header('Location: login.php?redirect=' . $redirect);
    exit;
}

// Verify active status in database
$pdo = Database::getConnection();
$authStmt = $pdo->prepare("SELECT id, full_name, email, role, status, phone FROM admins WHERE id = ? LIMIT 1");
$authStmt->execute([$_SESSION['manager_id']]);
$currentManager = $authStmt->fetch(PDO::FETCH_ASSOC);

if (!$currentManager || $currentManager['status'] !== 'active' || !in_array($currentManager['role'], ['manager', 'admin', 'super_admin'], true)) {
    unset($_SESSION['manager_id'], $_SESSION['manager_email'], $_SESSION['manager_name'], $_SESSION['manager_role'], $_SESSION['manager_last_activity']);
    header('Location: login.php?msg=unauthorized');
    exit;
}

// Keep session refreshed
$_SESSION['manager_name'] = $currentManager['full_name'];
$_SESSION['manager_role'] = $currentManager['role'];
$_SESSION['manager_email'] = $currentManager['email'];
