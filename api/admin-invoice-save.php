<?php
/**
 * GrihGO Digital & Tech — Admin Invoice Save (Create/Update) API
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

$invoiceId = (int)($_POST['invoice_id'] ?? $_POST['id'] ?? 0);
$clientId  = (int)($_POST['client_id'] ?? 0);
$projectId = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
$issueDate = sanitizeInput($_POST['issue_date'] ?? date('Y-m-d'));
$dueDate   = sanitizeInput($_POST['due_date'] ?? date('Y-m-d', strtotime('+15 days')));
$notes     = sanitizeInput($_POST['notes'] ?? '');
$validStatuses = ['Draft', 'Sent', 'Paid', 'Partially Paid', 'Overdue', 'Cancelled'];
$status    = in_array($_POST['status'] ?? '', $validStatuses, true) ? $_POST['status'] : 'Sent';

if ($clientId <= 0) {
    jsonResponse(false, 'Client selection is required.', ['client_id' => 'Required'], [], 422);
}

// Process Line Items
$descriptions = $_POST['item_desc'] ?? [];
$quantities   = $_POST['item_qty'] ?? [];
$rates        = $_POST['item_rate'] ?? [];

$items = [];
$subtotal = 0.0;

if (is_array($descriptions)) {
    for ($i = 0; $i < count($descriptions); $i++) {
        $desc = trim($descriptions[$i] ?? '');
        $qty  = max(1, (float)($quantities[$i] ?? 1));
        $rate = max(0, (float)($rates[$i] ?? 0));
        if (!empty($desc)) {
            $rowTotal = $qty * $rate;
            $items[] = [
                'description' => sanitizeInput($desc),
                'quantity'    => $qty,
                'unit_price'  => $rate,
                'total_price' => $rowTotal
            ];
            $subtotal += $rowTotal;
        }
    }
}

// If no dynamic items submitted, allow direct total_amount
if (empty($items)) {
    $directAmount = (float)($_POST['amount'] ?? $_POST['total_amount'] ?? 0.0);
    if ($directAmount > 0) {
        $items[] = [
            'description' => 'Professional Software Development & Cloud Engineering Services',
            'quantity'    => 1,
            'unit_price'  => $directAmount,
            'total_price' => $directAmount
        ];
        $subtotal = $directAmount;
    } else {
        jsonResponse(false, 'At least one billable line item or positive amount is required.', [], [], 422);
    }
}

$taxRate  = max(0, (float)($_POST['tax_percent'] ?? 18.0));
$taxAmt   = ($subtotal * $taxRate) / 100.0;
$discount = max(0, (float)($_POST['discount_amount'] ?? 0.0));
$grandTotal = max(0, $subtotal + $taxAmt - $discount);

try {
    $pdo->beginTransaction();

    if ($invoiceId > 0) {
        // Update Invoice
        $chk = $pdo->prepare("SELECT * FROM invoices WHERE id = ? LIMIT 1");
        $chk->execute([$invoiceId]);
        $inv = $chk->fetch(PDO::FETCH_ASSOC);

        if (!$inv) {
            $pdo->rollBack();
            jsonResponse(false, 'Invoice not found.', [], [], 404);
        }

        $upd = $pdo->prepare("
            UPDATE invoices 
            SET client_id = :cid, project_id = :pid, subtotal = :sub, tax_amount = :tax, discount_amount = :disc, 
                total_amount = :tot, amount = :sub, tax = :tax, status = :status, invoice_date = :iss, due_date = :due, notes = :notes, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $upd->execute([
            ':cid'    => $clientId,
            ':pid'    => $projectId,
            ':sub'    => $subtotal,
            ':tax'    => $taxAmt,
            ':disc'   => $discount,
            ':tot'    => $grandTotal,
            ':status' => $status,
            ':iss'    => $issueDate,
            ':due'    => $dueDate,
            ':notes'  => $notes,
            ':id'     => $invoiceId
        ]);

        // Replace Line Items
        $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$invoiceId]);
        $insItem = $pdo->prepare("
            INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total) 
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($items as $it) {
            $insItem->execute([$invoiceId, $it['description'], $it['quantity'], $it['unit_price'], $it['total_price']]);
        }

        logAdminActivity(
            $pdo, 
            (int)$currentAdmin['id'], 
            $currentAdmin['full_name'], 
            'Invoice Updated', 
            'invoices', 
            $invoiceId, 
            "Updated invoice {$inv['invoice_number']} (Total: ₹" . number_format($grandTotal, 2) . ")"
        );

        $pdo->commit();
        jsonResponse(true, "Invoice {$inv['invoice_number']} updated successfully.", [], [
            'invoice_id'     => $invoiceId,
            'invoice_number' => $inv['invoice_number'],
            'total_amount'   => $grandTotal
        ]);
    } else {
        // Create Invoice
        $invNumber = 'INV-' . date('Y') . '-' . str_pad((string)mt_rand(100, 9999), 4, '0', STR_PAD_LEFT);

        $ins = $pdo->prepare("
            INSERT INTO invoices (invoice_number, client_id, project_id, subtotal, tax_amount, discount_amount, total_amount, paid_amount, amount, tax, status, invoice_date, due_date, notes, created_at, updated_at) 
            VALUES (:num, :cid, :pid, :sub, :tax, :disc, :tot, 0.00, :sub, :tax, :status, :iss, :due, :notes, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $ins->execute([
            ':num'    => $invNumber,
            ':cid'    => $clientId,
            ':pid'    => $projectId,
            ':sub'    => $subtotal,
            ':tax'    => $taxAmt,
            ':disc'   => $discount,
            ':tot'    => $grandTotal,
            ':status' => $status,
            ':iss'    => $issueDate,
            ':due'    => $dueDate,
            ':notes'  => $notes
        ]);
        $newInvId = (int)$pdo->lastInsertId();

        // Insert Line Items
        $insItem = $pdo->prepare("
            INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total) 
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($items as $it) {
            $insItem->execute([$newInvId, $it['description'], $it['quantity'], $it['unit_price'], $it['total_price']]);
        }

        // Notify client
        if (function_exists('createClientNotification')) {
            createClientNotification(
                $pdo, 
                $clientId, 
                "New Invoice: {$invNumber}", 
                "An invoice of ₹" . number_format($grandTotal, 2) . " has been issued with due date {$dueDate}.", 
                'invoices', 
                $newInvId
            );
        }

        logAdminActivity(
            $pdo, 
            (int)$currentAdmin['id'], 
            $currentAdmin['full_name'], 
            'Invoice Created', 
            'invoices', 
            $newInvId, 
            "Created invoice {$invNumber} (₹" . number_format($grandTotal, 2) . ") for Client #CL-{$clientId}"
        );

        $pdo->commit();
        jsonResponse(true, "Invoice {$invNumber} created successfully.", [], [
            'invoice_id'     => $newInvId,
            'invoice_number' => $invNumber,
            'total_amount'   => $grandTotal
        ]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(false, 'Database error while saving invoice: ' . $e->getMessage(), [], [], 500);
}
