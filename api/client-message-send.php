<?php
/**
 * GrihGO Digital & Tech — Client Direct Message Send API
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
    jsonResponse(false, 'Unauthorized. Please log in.', [], [], 401);
}

$token = $_POST['csrf_token'] ?? '';
if (!validateCsrfToken($token)) {
    jsonResponse(false, 'Security validation failed. Please refresh.', ['csrf' => 'Invalid token'], [], 403);
}

$message = sanitizeInput($_POST['message'] ?? '');
if (empty($message)) {
    jsonResponse(false, 'Message cannot be empty.', ['message' => 'Required'], [], 422);
}
if (mb_strlen($message) > 3000) {
    jsonResponse(false, 'Message is too long (maximum 3000 characters).', ['message' => 'Too long'], [], 422);
}

$projectId = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
$ticketId  = !empty($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : null;
$clientId  = (int)$_SESSION['client_id'];
$clientName = $_SESSION['user_name'] ?? 'Client';

try {
    $pdo = Database::getConnection();

    // Verify Project Ownership if project_id is passed
    if ($projectId !== null) {
        $chkProj = $pdo->prepare("SELECT id FROM projects WHERE id = :pid AND client_id = :cid");
        $chkProj->execute([':pid' => $projectId, ':cid' => $clientId]);
        if (!$chkProj->fetch()) {
            jsonResponse(false, 'Project not found or unauthorized access.', [], [], 403);
        }
    }

    // Verify Ticket Ownership if ticket_id is passed
    if ($ticketId !== null) {
        $chkTck = $pdo->prepare("SELECT id FROM support_tickets WHERE id = :tid AND client_id = :cid");
        $chkTck->execute([':tid' => $ticketId, ':cid' => $clientId]);
        if (!$chkTck->fetch()) {
            jsonResponse(false, 'Ticket not found or unauthorized access.', [], [], 403);
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO messages (client_id, project_id, ticket_id, sender_type, sender_name, message, created_at)
        VALUES (:cid, :pid, :tid, 'client', :name, :msg, CURRENT_TIMESTAMP)
    ");
    $stmt->execute([
        ':cid'  => $clientId,
        ':pid'  => $projectId,
        ':tid'  => $ticketId,
        ':name' => $clientName,
        ':msg'  => $message
    ]);
    $msgId = (int)$pdo->lastInsertId();

    // Log admin notification
    createNotification($pdo, 'Client Message', "New Message from {$clientName}", mb_substr($message, 0, 100), $msgId);

    jsonResponse(true, 'Message sent successfully.', [], [
        'id'          => $msgId,
        'sender_name' => $clientName,
        'created_at'  => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    jsonResponse(false, 'Unable to send message at this time.', [], [], 500);
}
