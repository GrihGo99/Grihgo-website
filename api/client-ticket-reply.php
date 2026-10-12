<?php
/**
 * GrihGO Digital & Tech — Support Ticket Reply API Endpoint
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
    jsonResponse(false, 'Security validation failed.', ['csrf' => 'Invalid token'], [], 403);
}

$ticketId = (int)($_POST['ticket_id'] ?? 0);
$message  = sanitizeInput($_POST['message'] ?? '');
$clientId = (int)$_SESSION['client_id'];
$clientName = $_SESSION['user_name'] ?? 'Client';

if ($ticketId <= 0 || empty($message)) {
    jsonResponse(false, 'Reply message cannot be empty.', ['message' => 'Required'], [], 422);
}

try {
    $pdo = Database::getConnection();

    // Verify Ticket Ownership & Current Status
    $stmtTck = $pdo->prepare("SELECT * FROM support_tickets WHERE id = :tid AND client_id = :cid");
    $stmtTck->execute([':tid' => $ticketId, ':cid' => $clientId]);
    $ticket = $stmtTck->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        jsonResponse(false, 'Ticket not found or unauthorized access.', [], [], 403);
    }

    if (in_array($ticket['status'], ['Resolved', 'Closed'], true)) {
        jsonResponse(false, 'This ticket is marked as ' . $ticket['status'] . '. Replies are disabled. Please create a new ticket if you need further assistance.', [], [], 400);
    }

    // Optional File Attachment
    $attachmentPath = null;
    if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['attachment'];
        if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= 8 * 1024 * 1024) {
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
                    $safeFilename = 'reply_' . bin2hex(random_bytes(10)) . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $safeFilename)) {
                        $attachmentPath = 'uploads/tickets/' . $safeFilename;
                    }
                }
            }
        }
    }

    // Insert Message
    $stmtMsg = $pdo->prepare("
        INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, sender_name, message, attachment_path, created_at)
        VALUES (:tid, 'client', :sid, :sname, :msg, :att, CURRENT_TIMESTAMP)
    ");
    $stmtMsg->execute([
        ':tid'   => $ticketId,
        ':sid'   => $clientId,
        ':sname' => $clientName,
        ':msg'   => $message,
        ':att'   => $attachmentPath
    ]);
    $msgId = (int)$pdo->lastInsertId();

    // Update Ticket status and timestamp
    $updStatus = $ticket['status'] === 'Waiting for Client' ? 'In Progress' : $ticket['status'];
    $updStmt = $pdo->prepare("UPDATE support_tickets SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :tid");
    $updStmt->execute([':status' => $updStatus, ':tid' => $ticketId]);

    createNotification($pdo, 'Ticket Reply', "Client reply on {$ticket['ticket_number']}", mb_substr($message, 0, 100), $ticketId);

    jsonResponse(true, 'Reply submitted successfully.', [], [
        'id'              => $msgId,
        'sender_name'     => $clientName,
        'message'         => $message,
        'attachment_path' => $attachmentPath,
        'created_at'      => date('d M, h:i A')
    ]);

} catch (Exception $e) {
    jsonResponse(false, 'Unable to post reply at this time.', [], [], 500);
}
