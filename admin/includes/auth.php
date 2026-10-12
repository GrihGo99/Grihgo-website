<?php
/**
 * GrihGO Digital & Tech — Master Admin Authentication Middleware
 * Version: 4.0 (Part 4 - Complete Admin Panel & CRM Architecture)
 * Technology: PHP 8+, PDO, CSRF, RBAC (super_admin, admin, manager, staff), Session Hardening
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

// Admin Session Timeout: 2 hours of inactivity
$adminTimeout = 7200;
if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > $adminTimeout)) {
    // Destroy admin session
    unset($_SESSION['admin_id'], $_SESSION['admin_email'], $_SESSION['admin_name'], $_SESSION['admin_role'], $_SESSION['admin_last_activity']);
    header('Location: login.php?msg=timeout');
    exit;
}
$_SESSION['admin_last_activity'] = time();

// Check if authenticated admin
if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_role'])) {
    // If a manager attempts to access admin portal, strictly forbid with 403
    if (!empty($_SESSION['manager_id'])) {
        http_response_code(403);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><head><title>403 Forbidden | GrihGO Admin</title></head><body style="font-family: sans-serif; text-align: center; padding: 50px;">';
        echo '<h1 style="color: #ef4444; font-size: 32px;">403 Forbidden</h1>';
        echo '<p style="color: #64748b; font-size: 16px;">Managers are strictly restricted from accessing administrative configuration and system settings.</p>';
        echo '<p><a href="../manager/dashboard.php" style="color: #0284c7; text-decoration: none; font-weight: 600;">&larr; Return to Manager Portal</a></p>';
        echo '</body></html>';
        exit;
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Admin session expired or unauthorized.']);
        exit;
    }
    $redirect = urlencode($_SERVER['REQUEST_URI'] ?? 'admin/dashboard.php');
    header('Location: login.php?redirect=' . $redirect);
    exit;
}

// Verify active status in database
$pdo = Database::getConnection();
$authAdminStmt = $pdo->prepare("SELECT id, full_name, email, role, status, phone FROM admins WHERE id = ? LIMIT 1");
$authAdminStmt->execute([$_SESSION['admin_id']]);
$currentAdmin = $authAdminStmt->fetch(PDO::FETCH_ASSOC);

if (!$currentAdmin || $currentAdmin['status'] !== 'active') {
    // Revoke access immediately
    unset($_SESSION['admin_id'], $_SESSION['admin_email'], $_SESSION['admin_name'], $_SESSION['admin_role'], $_SESSION['admin_last_activity']);
    header('Location: login.php?msg=suspended');
    exit;
}

// Keep session refreshed
$_SESSION['admin_name'] = $currentAdmin['full_name'];
$_SESSION['admin_role'] = $currentAdmin['role'];
$_SESSION['admin_email'] = $currentAdmin['email'];

/**
 * Check if the logged-in admin has any of the specified roles.
 */
function hasAdminRole(array $roles): bool {
    global $currentAdmin;
    if (empty($currentAdmin['role'])) return false;
    if ($currentAdmin['role'] === 'super_admin') return true; // Super admin possesses universal access
    return in_array($currentAdmin['role'], $roles, true);
}

/**
 * Require specific role or abort with 403 Forbidden.
 */
function requireAdminRole(array $roles): void {
    if (!hasAdminRole($roles)) {
        http_response_code(403);
        include __DIR__ . '/header.php';
        echo '<div class="admin-card text-center" style="margin: 40px auto; max-width: 600px; padding: 40px;">';
        echo '<h2 style="color: var(--admin-danger); font-size: 24px; margin-bottom: 12px;">403 Access Denied</h2>';
        echo '<p style="color: var(--admin-muted); margin-bottom: 24px;">Your current administrative role (' . htmlspecialchars($_SESSION['admin_role'] ?? 'Staff') . ') does not hold permission to access this module.</p>';
        echo '<a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>';
        echo '</div>';
        include __DIR__ . '/footer.php';
        exit;
    }
}
