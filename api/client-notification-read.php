<?php
/**
 * GrihGO Digital & Tech — Mark Single Notification As Read API
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method Not Allowed', [], [], 405);
}

if (empty($_SESSION['user_id']) || empty($_SESSION['client_id']) || ($_SESSION['role'] ?? '') !== 'client') {
    jsonResponse(false, 'Unauthorized', [], [], 401);
}

$token = $_POST['csrf_token'] ?? '';
if (!validateCsrfToken($token)) {
    jsonResponse(false, 'Security token validation failed.', ['csrf' => 'Invalid token'], [], 403);
}

$notifId = (int)($_POST['notification_id'] ?? 0);
$clientId = (int)$_SESSION['client_id'];

if ($notifId <= 0) {
    jsonResponse(false, 'Invalid notification ID.', [], [], 422);
}

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = :nid AND (client_id = :cid OR client_id IS NULL)
    ");
    $stmt->execute([':nid' => $notifId, ':cid' => $clientId]);

    jsonResponse(true, 'Notification marked as read.', [], ['id' => $notifId]);

} catch (Exception $e) {
    jsonResponse(false, 'Failed to update notification status.', [], [], 500);
}
