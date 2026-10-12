<?php
/**
 * GrihGO Digital & Tech — Create Support Ticket API Endpoint
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method Not Allowed', [], [], 405);
}

// 1. Verify Authentication
if (empty($_SESSION['user_id']) || empty($_SESSION['client_id']) || ($_SESSION['role'] ?? '') !== 'client') {
    jsonResponse(false, 'Unauthorized. Please log in.', [], [], 401);
}

// 2. Validate CSRF Token
$token = $_POST['csrf_token'] ?? '';
if (!validateCsrfToken($token)) {
    jsonResponse(false, 'Security validation failed. Please refresh the page and try again.', ['csrf' => 'Invalid token'], [], 403);
}

// 3. Extract & Sanitize
$subject  = sanitizeInput($_POST['subject'] ?? '');
$category = sanitizeInput($_POST['category'] ?? 'General');
$priority = sanitizeInput($_POST['priority'] ?? 'Medium');
$message  = sanitizeInput($_POST['message'] ?? '');

$allowedCategories = ['Technical Issue', 'Billing', 'Project', 'Website', 'Software', 'CRM', 'General', 'Other'];
$allowedPriorities = ['Low', 'Medium', 'High', 'Urgent'];

$errors = [];
if (mb_strlen($subject) < 3 || mb_strlen($subject) > 200) {
    $errors['subject'] = 'Subject must be between 3 and 200 characters.';
}
if (!in_array($category, $allowedCategories, true)) {
    $category = 'General';
}
if (!in_array($priority, $allowedPriorities, true)) {
    $priority = 'Medium';
}
if (mb_strlen($message) < 5 || mb_strlen($message) > 4000) {
    $errors['message'] = 'Message must be between 5 and 4000 characters.';
}

// 4. Secure File Upload Handling
$attachmentPath = null;
if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['attachment'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors['attachment'] = 'File upload error code: ' . $file['error'];
    } else {
        // Size validation (Max 8MB)
        if ($file['size'] > 8 * 1024 * 1024) {
            $errors['attachment'] = 'File exceeds maximum allowed size of 8 MB.';
        }

        // Extension & MIME validation
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'png', 'jpg', 'jpeg', 'zip', 'doc', 'docx', 'txt'];
        $dangerousExts = ['php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd', 'js', 'py', 'pl'];

        if (in_array($ext, $dangerousExts, true) || !in_array($ext, $allowedExts, true)) {
            $errors['attachment'] = 'File type not allowed. Permitted formats: PDF, PNG, JPG, ZIP, DOCX, TXT.';
        } else {
            // Check MIME
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = [
                'application/pdf', 'image/png', 'image/jpeg', 'image/jpg',
                'application/zip', 'application/x-zip-compressed',
                'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain'
            ];

            if (!in_array($mime, $allowedMimes, true)) {
                $errors['attachment'] = 'File MIME type validation failed.';
            } else {
                $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tickets';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }

                $safeFilename = 'tck_' . bin2hex(random_bytes(10)) . '.' . $ext;
                $destPath = $uploadDir . DIRECTORY_SEPARATOR . $safeFilename;

                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $attachmentPath = 'uploads/tickets/' . $safeFilename;
                } else {
                    $errors['attachment'] = 'Failed to securely store uploaded file.';
                }
            }
        }
    }
}

if (!empty($errors)) {
    jsonResponse(false, 'Validation failed. Please correct the errors.', $errors, [], 422);
}

try {
    $pdo = Database::getConnection();
    $clientId = (int)$_SESSION['client_id'];
    $clientName = $_SESSION['user_name'] ?? 'Client';

    // Generate unique Ticket ID: TCK-XXXX
    $ticketNumber = 'TCK-' . mt_rand(1000, 9999);

    // Insert Ticket
    $stmtTck = $pdo->prepare("
        INSERT INTO support_tickets (ticket_number, client_id, subject, category, priority, status, created_at, updated_at)
        VALUES (:tnum, :cid, :subj, :cat, :prio, 'Open', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ");
    $stmtTck->execute([
        ':tnum' => $ticketNumber,
        ':cid'  => $clientId,
        ':subj' => $subject,
        ':cat'  => $category,
        ':prio' => $priority
    ]);
    $ticketId = (int)$pdo->lastInsertId();

    // Insert Initial Message into ticket_messages
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

    // Create Notification
    createNotification($pdo, 'Support Ticket', "New Ticket: {$ticketNumber} ({$subject})", "Priority: {$priority} - From: {$clientName}", $ticketId);

    jsonResponse(true, 'Support ticket created successfully.', [], [
        'ticket_id'     => $ticketId,
        'ticket_number' => $ticketNumber,
        'redirect_url'  => 'support.php?ticket_id=' . $ticketId
    ]);

} catch (Exception $e) {
    jsonResponse(false, 'Database error while registering support ticket.', [], [], 500);
}
