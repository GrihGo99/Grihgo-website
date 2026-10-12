<?php
/**
 * GrihGO Digital & Tech — Admin Support Ticket Metadata Update API
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

$ticketId = (int)($_POST['ticket_id'] ?? $_POST['id'] ?? 0);
if ($ticketId <= 0) {
    jsonResponse(false, 'Valid ticket ID is required.', ['ticket_id' => 'Required'], [], 422);
}

$chk = $pdo->prepare("SELECT * FROM support_tickets WHERE id = ? LIMIT 1");
$chk->execute([$ticketId]);
$ticket = $chk->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    jsonResponse(false, 'Ticket not found.', [], [], 404);
}

$validStatuses = ['Open', 'In Progress', 'Waiting for Client', 'Answered', 'Resolved', 'Closed'];
$validPriorities = ['Low', 'Medium', 'High', 'Critical'];

$newStatus   = in_array($_POST['status'] ?? '', $validStatuses, true) ? $_POST['status'] : $ticket['status'];
$newPriority = in_array($_POST['priority'] ?? '', $validPriorities, true) ? $_POST['priority'] : $ticket['priority'];
$newDept     = !empty($_POST['department']) ? sanitizeInput($_POST['department']) : $ticket['department'];

try {
    $upd = $pdo->prepare("
        UPDATE support_tickets 
        SET status = :status, priority = :priority, department = :dept, updated_at = CURRENT_TIMESTAMP 
        WHERE id = :id
    ");
    $upd->execute([
        ':status'   => $newStatus,
        ':priority' => $newPriority,
        ':dept'     => $newDept,
        ':id'       => $ticketId
    ]);

    // Notify client if resolved
    if (($newStatus === 'Resolved' || $newStatus === 'Closed') && !empty($ticket['client_id']) && function_exists('createClientNotification')) {
        createClientNotification(
            $pdo, 
            (int)$ticket['client_id'], 
            "Ticket {$ticket['ticket_number']} {$newStatus}", 
            "Your support ticket has been marked as {$newStatus}.", 
            'tickets', 
            $ticketId
        );
    }

    logAdminActivity(
        $pdo, 
        (int)$currentAdmin['id'], 
        $currentAdmin['full_name'], 
        'Ticket Meta Updated', 
        'support_tickets', 
        $ticketId, 
        "Updated ticket {$ticket['ticket_number']} (Status: {$newStatus}, Priority: {$newPriority})"
    );

    jsonResponse(true, "Ticket {$ticket['ticket_number']} updated successfully.", [], [
        'ticket_id' => $ticketId,
        'status'    => $newStatus,
        'priority'  => $newPriority,
        'department'=> $newDept
    ]);
} catch (Exception $e) {
    jsonResponse(false, 'Database error while updating ticket: ' . $e->getMessage(), [], [], 500);
}
