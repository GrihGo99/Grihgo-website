<?php
/**
 * GrihGO Digital & Tech — Client Authentication Middleware
 * Version: 3.0 (Part 3 - Client Portal)
 * Technology: PHP 8+, PDO, Session Hardening, RBAC & Ownership Scoping
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../../config/functions.php';

// Ensure secure session is initialized
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

// Session timeout: 4 hours of inactivity
$sessionTimeout = 14400;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionTimeout)) {
    // Session expired
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: ../login.php?msg=timeout');
    exit;
}
$_SESSION['last_activity'] = time();

// Check Client Authentication & Role
if (empty($_SESSION['user_id']) || empty($_SESSION['client_id']) || ($_SESSION['role'] ?? '') !== 'client') {
    // If request is AJAX/API, return 401 JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Session expired or unauthorized. Please log in again.']);
        exit;
    }

    $redirectUrl = urlencode($_SERVER['REQUEST_URI'] ?? 'client/dashboard.php');
    header('Location: ../login.php?redirect=' . $redirectUrl);
    exit;
}

// Fetch Fresh Client Data from Database
$pdo = Database::getConnection();
$authStmt = $pdo->prepare("
    SELECT c.*, u.name as user_name, u.email as user_email, u.phone as user_phone, u.status as user_status, u.role
    FROM clients c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = :client_id AND u.id = :user_id
    LIMIT 1
");
$authStmt->execute([
    ':client_id' => $_SESSION['client_id'],
    ':user_id'   => $_SESSION['user_id']
]);
$authClient = $authStmt->fetch(PDO::FETCH_ASSOC);

if (!$authClient || $authClient['user_status'] !== 'active' || $authClient['status'] !== 'active') {
    // Account deactivated or suspended
    $_SESSION = [];
    session_destroy();
    header('Location: ../login.php?error=suspended');
    exit;
}

// Global active client details accessible across client templates
$clientId = (int)$authClient['id'];
$clientUserId = (int)$authClient['user_id'];
$clientName = htmlspecialchars($authClient['contact_person'] ?: $authClient['user_name'], ENT_QUOTES, 'UTF-8');
$clientCompany = htmlspecialchars($authClient['company_name'], ENT_QUOTES, 'UTF-8');
$clientEmail = htmlspecialchars($authClient['email'], ENT_QUOTES, 'UTF-8');
$clientPhone = htmlspecialchars($authClient['phone'], ENT_QUOTES, 'UTF-8');
$clientAddress = htmlspecialchars($authClient['address'] ?? '', ENT_QUOTES, 'UTF-8');
$clientGst = htmlspecialchars($authClient['gst_number'] ?? '', ENT_QUOTES, 'UTF-8');
$clientAvatar = $authClient['profile_image'] ?? null;

// Get unread notification count
$notifStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE client_id = :client_id AND is_read = 0");
$notifStmt->execute([':client_id' => $clientId]);
$unreadNotificationsCount = (int)$notifStmt->fetchColumn();

/**
 * Enforce strict resource ownership.
 * Terminates with 403 Forbidden if client attempts to access data belonging to another client.
 */
function enforceClientOwnership(int $resourceClientId, string $redirectOnError = 'dashboard.php'): void {
    global $clientId;
    if ($resourceClientId !== $clientId) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied. You do not have permission to view this resource.']);
            exit;
        }
        header("Location: {$redirectOnError}?error=unauthorized");
        exit;
    }
}
