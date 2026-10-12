<?php
/**
 * GrihGO Digital & Tech — Admin Payment Recording API
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

$invoiceId = (int)($_POST['invoice_id'] ?? 0);
$amount    = (float)($_POST['amount'] ?? 0.0);
$method    = sanitizeInput($_POST['payment_method'] ?? 'Bank Transfer');
$txId      = sanitizeInput($_POST['transaction_id'] ?? ('TXN-' . strtoupper(bin2hex(random_bytes(5)))));
$payDate   = sanitizeInput($_POST['payment_date'] ?? date('Y-m-d H:i:s'));
$notes     = sanitizeInput($_POST['notes'] ?? '');

if ($invoiceId <= 0 || $amount <= 0) {
    jsonResponse(false, 'Valid invoice assignment and positive payment amount are required.', [], [], 422);
}

// Fetch invoice
$chkInv = $pdo->prepare("SELECT * FROM invoices WHERE id = ? LIMIT 1");
$chkInv->execute([$invoiceId]);
$invoice = $chkInv->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    jsonResponse(false, 'Invoice not found.', [], [], 404);
}

try {
    $pdo->beginTransaction();

    // Insert Payment
    $ins = $pdo->prepare("
        INSERT INTO payments (invoice_id, client_id, amount, payment_method, transaction_id, status, payment_date, created_at) 
        VALUES (:inv, :cid, :amt, :meth, :tx, 'Success', :pdate, CURRENT_TIMESTAMP)
    ");
    $ins->execute([
        ':inv'   => $invoiceId,
        ':cid'   => (int)$invoice['client_id'],
        ':amt'   => $amount,
        ':meth'  => $method,
        ':tx'    => $txId,
        ':pdate' => $payDate
    ]);
    $paymentId = (int)$pdo->lastInsertId();

    // Sum total paid so far
    $sumStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) AS total_paid FROM payments WHERE invoice_id = ? AND status IN ('Completed', 'Success')");
    $sumStmt->execute([$invoiceId]);
    $totalPaid = (float)$sumStmt->fetchColumn();

    // Adjust Invoice Status
    $newInvStatus = $invoice['status'];
    if ($totalPaid >= (float)$invoice['total_amount']) {
        $newInvStatus = 'Paid';
    } elseif ($totalPaid > 0) {
        $newInvStatus = 'Partially Paid';
    }

    $updInv = $pdo->prepare("UPDATE invoices SET paid_amount = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $updInv->execute([$totalPaid, $newInvStatus, $invoiceId]);

    // Dispatch Client Notification
    if (function_exists('createClientNotification')) {
        createClientNotification(
            $pdo, 
            (int)$invoice['client_id'], 
            "Payment Recorded: ₹" . number_format($amount, 2), 
            "Payment received for invoice {$invoice['invoice_number']} via {$method}. Current status: {$newInvStatus}.", 
            'invoices', 
            $invoiceId
        );
    }

    logAdminActivity(
        $pdo, 
        (int)$currentAdmin['id'], 
        $currentAdmin['full_name'], 
        'Payment Recorded', 
        'payments', 
        $paymentId, 
        "Recorded payment of ₹" . number_format($amount, 2) . " for invoice {$invoice['invoice_number']} (TXN: {$txId})"
    );

    $pdo->commit();

    jsonResponse(true, "Payment of ₹" . number_format($amount, 2) . " recorded successfully.", [], [
        'payment_id'     => $paymentId,
        'invoice_id'     => $invoiceId,
        'invoice_status' => $newInvStatus,
        'total_paid'     => $totalPaid,
        'transaction_id' => $txId
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(false, 'Database error while recording payment: ' . $e->getMessage(), [], [], 500);
}
