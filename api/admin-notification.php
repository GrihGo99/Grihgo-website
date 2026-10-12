<?php
/**
 * GrihGO Digital & Tech — Admin Notification Management API
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json; charset=UTF-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method Not Allowed', [], [], 405);
}

// Admin Authentication Check
if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_role'])) {
    jsonResponse(false, 'Unauthorized. Administrative session required.', [], [], 401);
}

$pdo = Database::getConnection();
$adminStmt = $pdo->prepare("SELECT id, full_name, role, status FROM admins WHERE id = ? LIMIT 1");
$adminStmt->execute([$_SESSION['admin_id']]);
$currentAdmin = $adminStmt->fetch(PDO::FETCH_ASSOC);

if (!$currentAdmin || $currentAdmin['status'] !== 'active') {
    jsonResponse(false, 'Administrative account suspended or invalid.', [], [], 403);
}

// CSRF Validation
$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCsrfToken($token)) {
    jsonResponse(false, 'Invalid security CSRF token.', ['csrf' => 'Invalid token'], [], 403);
}

$action = sanitizeInput($_POST['action'] ?? 'mark_read');

try {
    if ($action === 'mark_all_read') {
        $pdo->exec("UPDATE notifications SET is_read = 1 WHERE client_id IS NULL OR client_id = 0");
        $unreadCount = 0;
        jsonResponse(true, 'All administrative notifications marked as read.', [], ['unread_count' => 0]);
    } else {
        $notifId = (int)($_POST['id'] ?? $_POST['notification_id'] ?? 0);
        if ($notifId <= 0) {
            jsonResponse(false, 'Valid notification ID is required.', [], [], 422);
        }

        $upd = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $upd->execute([$notifId]);

        $unreadCount = (int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE (client_id IS NULL OR client_id = 0) AND is_read = 0")->fetchColumn();

        jsonResponse(true, 'Notification marked as read.', [], [
            'notification_id' => $notifId,
            'unread_count'    => $unreadCount
        ]);
    }
} catch (Exception $e) {
    jsonResponse(false, 'Database error: ' . $e->getMessage(), [], [], 500);
}
