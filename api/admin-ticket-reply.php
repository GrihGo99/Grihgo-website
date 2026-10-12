<?php
/**
 * GrihGO Digital & Tech — Admin Support Ticket Reply API
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
$message  = sanitizeInput($_POST['message'] ?? '');
$newStatus = sanitizeInput($_POST['status'] ?? 'Answered');

if ($ticketId <= 0 || empty($message)) {
    jsonResponse(false, 'Valid ticket ID and non-empty reply message required.', [], [], 422);
}

// Fetch Ticket
$chk = $pdo->prepare("SELECT * FROM support_tickets WHERE id = ? LIMIT 1");
$chk->execute([$ticketId]);
$ticket = $chk->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    jsonResponse(false, 'Support ticket not found.', [], [], 404);
}

try {
    $attachmentPath = null;
    if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['attachment'];
        if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= 10 * 1024 * 1024) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['pdf', 'png', 'jpg', 'jpeg', 'zip', 'doc', 'docx', 'txt'];
            if (in_array($ext, $allowedExts, true)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                $allowedMimes = [
                    'application/pdf', 'image/png', 'image/jpeg', 'image/jpg',
                    'application/zip', 'application/x-zip-compressed',
                    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'text/plain'
                ];
                if (in_array($mime, $allowedMimes, true)) {
                    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tickets';
                    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
                    $safeFilename = 'staff_' . bin2hex(random_bytes(10)) . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $safeFilename)) {
                        $attachmentPath = 'uploads/tickets/' . $safeFilename;
                    }
                }
            }
        }
    }

    $validStatuses = ['Open', 'In Progress', 'Waiting for Client', 'Answered', 'Resolved', 'Closed'];
    $finalStatus = in_array($newStatus, $validStatuses, true) ? $newStatus : 'Answered';

    // Insert Staff Reply
    $insMsg = $pdo->prepare("
        INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, sender_name, message, attachment_path, created_at) 
        VALUES (:tid, 'admin', :sid, :sname, :msg, :att, CURRENT_TIMESTAMP)
    ");
    $insMsg->execute([
        ':tid'   => $ticketId,
        ':sid'   => (int)$currentAdmin['id'],
        ':sname' => $currentAdmin['full_name'],
        ':msg'   => $message,
        ':att'   => $attachmentPath
    ]);
    $msgId = (int)$pdo->lastInsertId();

    // Update Ticket status
    $updTicket = $pdo->prepare("UPDATE support_tickets SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
    $updTicket->execute([':status' => $finalStatus, ':id' => $ticketId]);

    // Dispatch Client Notification
    if (!empty($ticket['client_id']) && function_exists('createClientNotification')) {
        createClientNotification(
            $pdo, 
            (int)$ticket['client_id'], 
            "Support Reply: {$ticket['ticket_number']}", 
            "Support team replied: " . mb_substr($message, 0, 80) . "...", 
            'tickets', 
            $ticketId
        );
    }

    logAdminActivity(
        $pdo, 
        (int)$currentAdmin['id'], 
        $currentAdmin['full_name'], 
        'Ticket Replied', 
        'support_tickets', 
        $ticketId, 
        "Staff replied on ticket {$ticket['ticket_number']} and set status to {$finalStatus}"
    );

    jsonResponse(true, "Reply posted on ticket {$ticket['ticket_number']}.", [], [
        'message_id'      => $msgId,
        'ticket_id'       => $ticketId,
        'sender_name'     => $currentAdmin['full_name'],
        'status'          => $finalStatus,
        'attachment_path' => $attachmentPath,
        'created_at'      => date('d M, h:i A')
    ]);
} catch (Exception $e) {
    jsonResponse(false, 'Database error while posting reply: ' . $e->getMessage(), [], [], 500);
}
