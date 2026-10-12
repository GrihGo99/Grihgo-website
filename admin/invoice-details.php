<?php
/**
 * GrihGO Digital & Tech — Invoice Details & Settlement Record
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$invoiceId = (int)($_GET['id'] ?? 0);
if ($invoiceId <= 0) {
    header('Location: invoices.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT i.*, c.company_name, c.contact_person, c.email AS client_email, c.phone AS client_phone, c.address AS client_address, c.gst_number AS client_gst,
           p.name AS project_name
    FROM invoices i
    LEFT JOIN clients c ON i.client_id = c.id
    LEFT JOIN projects p ON i.project_id = p.id
    WHERE i.id = ? LIMIT 1
");
$stmt->execute([$invoiceId]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    header('Location: invoices.php?error=not_found');
    exit;
}

$pageTitle = 'Invoice ' . $invoice['invoice_number'];
$pageSection = 'invoices';

$error = '';
$success = '';

// Handle Direct Payment Recording (Section 25)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'record_payment') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $amount     = max(0.01, (float)($_POST['amount'] ?? 0.0));
        $payMethod  = sanitizeInput($_POST['payment_method'] ?? 'Bank Transfer (NEFT/RTGS)');
        $payDate    = sanitizeInput($_POST['payment_date'] ?? date('Y-m-d H:i:s'));
        $txnId      = sanitizeInput($_POST['transaction_id'] ?? ('TXN-' . strtoupper(bin2hex(random_bytes(5)))));
        $payStatus  = in_array($_POST['payment_status'] ?? '', ['Success', 'Pending', 'Failed', 'Refunded']) ? $_POST['payment_status'] : 'Success';

        if ($amount <= 0) {
            $error = 'Payment amount must be greater than zero.';
        } else {
            try {
                $pdo->beginTransaction();

                // 1. Insert payment record
                $payStmt = $pdo->prepare("
                    INSERT INTO payments (invoice_id, client_id, amount, payment_date, payment_method, transaction_id, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $payStmt->execute([$invoiceId, $invoice['client_id'], $amount, $payDate, $payMethod, $txnId, $payStatus]);
                $payId = (int)$pdo->lastInsertId();

                // 2. Re-calculate total paid on invoice
                $sumPaidStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = ? AND status = 'Success'");
                $sumPaidStmt->execute([$invoiceId]);
                $newPaidTotal = (float)$sumPaidStmt->fetchColumn();

                // 3. Determine new invoice status
                $newInvStatus = $invoice['status'];
                if ($newPaidTotal >= (float)$invoice['total_amount']) {
                    $newInvStatus = 'Paid';
                } elseif ($newPaidTotal > 0) {
                    $newInvStatus = 'Partially Paid';
                }

                // Update invoice
                $pdo->prepare("UPDATE invoices SET paid_amount = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                    ->execute([$newPaidTotal, $newInvStatus, $invoiceId]);

                // Create client notification
                $pdo->prepare("
                    INSERT INTO notifications (client_id, type, title, message, reference_id, is_read)
                    VALUES (?, 'Payment Recorded', ?, ?, ?, 0)
                ")->execute([
                    $invoice['client_id'],
                    "Payment Recorded: ₹" . number_format($amount, 2),
                    "Payment of ₹" . number_format($amount, 2) . " for invoice {$invoice['invoice_number']} has been recorded. Current status: {$newInvStatus}.",
                    $payId
                ]);

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Payment Recorded', 'payments', $payId, "Recorded payment of ₹" . number_format($amount, 2) . " for invoice {$invoice['invoice_number']}");

                $pdo->commit();

                // Refresh invoice
                $stmt->execute([$invoiceId]);
                $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
                $success = "Payment of ₹" . number_format($amount, 2) . " recorded successfully!";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Failed to record payment.';
            }
        }
    }
}

// Fetch Invoice Items
$itemStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC");
$itemStmt->execute([$invoiceId]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Payments for this invoice
$payQuery = $pdo->prepare("SELECT * FROM payments WHERE invoice_id = ? ORDER BY id DESC");
$payQuery->execute([$invoiceId]);
$payments = $payQuery->fetchAll(PDO::FETCH_ASSOC);

$balanceRemaining = max(0, (float)$invoice['total_amount'] - (float)$invoice['paid_amount']);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <div style="font-size: 13px; color: var(--admin-text-muted); margin-bottom: 4px;">
            <a href="invoices.php" style="color: var(--admin-primary); text-decoration: none;">&larr; Back to Invoices</a>
        </div>
        <h1>Invoice: <?= htmlspecialchars($invoice['invoice_number']) ?></h1>
        <p class="page-subtitle">Issued to <a href="client-details.php?id=<?= $invoice['client_id'] ?>" style="color: var(--admin-primary); font-weight: 600; text-decoration: none;"><?= htmlspecialchars($invoice['company_name']) ?></a> &bull; Due: <?= date('d M Y', strtotime($invoice['due_date'])) ?></p>
    </div>
    <div class="page-actions">
        <?php if ($balanceRemaining > 0): ?>
            <button type="button" class="btn btn-success" onclick="openAdminModal('recordPaymentModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                <span>Record Payment</span>
            </button>
        <?php else: ?>
            <span class="badge badge-success" style="padding: 8px 14px; font-size: 13px;">Fully Settled</span>
        <?php endif; ?>
        <button type="button" class="btn btn-outline" onclick="window.print()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            <span>Print Invoice</span>
        </button>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Invoice Formal Sheet -->
<div class="admin-card" style="padding: 40px; background: #ffffff;">
    <!-- Sheet Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--admin-border); padding-bottom: 24px; margin-bottom: 28px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <span class="admin-brand-icon" style="width: 32px; height: 32px; font-size: 16px;">G</span>
                <span style="font-size: 20px; font-weight: 800; color: var(--admin-text-main);">GrihGO Digital & Tech</span>
            </div>
            <div style="font-size: 12.5px; color: var(--admin-text-muted); line-height: 1.6;">
                Plot 42, Cyber City, Phase II, Gurugram, Haryana - 122002<br>
                contact@grihgo.com &bull; +91 98765 43210<br>
                GSTIN: 07AAACG9999Z1Z8
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 22px; font-weight: 800; color: var(--admin-primary); margin-bottom: 4px;">TAX INVOICE</div>
            <div style="font-size: 14px; font-weight: 700; color: #334155;"># <?= htmlspecialchars($invoice['invoice_number']) ?></div>
            <div style="font-size: 12.5px; color: var(--admin-text-muted); margin-top: 6px;">
                Date: <strong><?= date('d M Y', strtotime($invoice['invoice_date'])) ?></strong><br>
                Due: <strong><?= date('d M Y', strtotime($invoice['due_date'])) ?></strong>
            </div>
            <div style="margin-top: 8px;">
                <span class="badge <?= $invoice['status'] === 'Paid' ? 'badge-success' : 'badge-warning' ?>" style="font-size: 12px; padding: 4px 10px;">
                    <span class="badge-dot"></span><?= htmlspecialchars($invoice['status']) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Client & Project Info -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px; font-size: 13.5px; line-height: 1.6;">
        <div style="background: #f8fafc; padding: 18px; border-radius: 8px; border: 1px solid var(--admin-border);">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--admin-text-muted); margin-bottom: 6px;">Billed To:</div>
            <strong><?= htmlspecialchars($invoice['company_name']) ?></strong><br>
            Attn: <?= htmlspecialchars($invoice['contact_person']) ?><br>
            <?= nl2br(htmlspecialchars($invoice['client_address'] ?: 'Office address on file')) ?><br>
            GSTIN: <?= htmlspecialchars($invoice['client_gst'] ?: 'Not Provided') ?>
        </div>
        <div style="background: #f8fafc; padding: 18px; border-radius: 8px; border: 1px solid var(--admin-border);">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--admin-text-muted); margin-bottom: 6px;">Scope Association:</div>
            <strong><?= htmlspecialchars($invoice['project_name'] ?: 'Corporate Retainer / Custom Development') ?></strong><br>
            Client Contact: <?= htmlspecialchars($invoice['client_email']) ?><br>
            Phone: <?= htmlspecialchars($invoice['client_phone']) ?>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="table-responsive" style="margin-bottom: 24px;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Item Description</th>
                    <th style="text-align: right; width: 80px;">Qty</th>
                    <th style="text-align: right; width: 140px;">Unit Rate (₹)</th>
                    <th style="text-align: right; width: 140px;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td>1</td>
                        <td><?= htmlspecialchars($invoice['notes'] ?: 'Technical Services Agreement') ?></td>
                        <td style="text-align: right;">1</td>
                        <td style="text-align: right;">₹<?= number_format((float)$invoice['total_amount'], 2) ?></td>
                        <td style="text-align: right;"><strong>₹<?= number_format((float)$invoice['total_amount'], 2) ?></strong></td>
                    </tr>
                <?php else: foreach ($items as $idx => $it): ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td><strong><?= htmlspecialchars($it['description']) ?></strong></td>
                        <td style="text-align: right;"><?= (int)$it['quantity'] ?></td>
                        <td style="text-align: right;">₹<?= number_format((float)$it['unit_price'], 2) ?></td>
                        <td style="text-align: right;"><strong>₹<?= number_format((float)$it['total'], 2) ?></strong></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Financial Breakdown -->
    <div style="display: flex; justify-content: flex-end; margin-bottom: 28px;">
        <div style="width: 320px; font-size: 13.5px; line-height: 2;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--admin-border-subtle);">
                <span style="color: var(--admin-text-muted);">Subtotal:</span>
                <span>₹<?= number_format((float)$invoice['subtotal'], 2) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--admin-border-subtle);">
                <span style="color: var(--admin-text-muted);">Tax / GST:</span>
                <span>₹<?= number_format((float)$invoice['tax_amount'], 2) ?></span>
            </div>
            <?php if ((float)$invoice['discount_amount'] > 0): ?>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--admin-border-subtle); color: var(--admin-accent);">
                    <span>Discount Applied:</span>
                    <span>- ₹<?= number_format((float)$invoice['discount_amount'], 2) ?></span>
                </div>
            <?php endif; ?>
            <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; color: var(--admin-primary); border-bottom: 2px solid var(--admin-border); padding: 4px 0;">
                <span>Grand Total:</span>
                <span>₹<?= number_format((float)$invoice['total_amount'], 2) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; color: var(--admin-accent);">
                <span>Amount Paid:</span>
                <span>₹<?= number_format((float)$invoice['paid_amount'], 2) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: 700; color: <?= $balanceRemaining > 0 ? 'var(--admin-danger)' : 'var(--admin-accent)' ?>;">
                <span>Balance Due:</span>
                <span>₹<?= number_format($balanceRemaining, 2) ?></span>
            </div>
        </div>
    </div>

    <!-- Notes & Bank Info -->
    <div style="background: #f8fafc; border: 1px solid var(--admin-border); border-radius: 8px; padding: 18px; font-size: 12.5px; color: #475569; line-height: 1.6;">
        <strong>Payment Instructions:</strong><br>
        Kindly remit payment via RTGS/NEFT to HDFC Bank (A/C: 50200012345678, IFSC: HDFC0000123) in favor of <strong>GrihGO Digital & Tech Pvt Ltd</strong>. Mention invoice number <?= htmlspecialchars($invoice['invoice_number']) ?> in the transaction remarks.
        <?php if (!empty($invoice['notes'])): ?>
            <div style="margin-top: 8px;"><strong>Terms & Remarks:</strong> <?= htmlspecialchars($invoice['notes']) ?></div>
        <?php endif; ?>
    </div>
</div>

<!-- Payments Audit Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">Recorded Payments History</h3>
        <?php if ($balanceRemaining > 0): ?>
            <button type="button" class="btn btn-sm btn-success" onclick="openAdminModal('recordPaymentModal')">+ Record Payment</button>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Transaction ID</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="6" class="empty-state">No payment transactions recorded for this invoice yet.</td></tr>
                <?php else: foreach ($payments as $p): ?>
                    <tr>
                        <td><code>#PAY-<?= str_pad($p['id'], 3, '0', STR_PAD_LEFT) ?></code></td>
                        <td><?= date('d M Y, h:i A', strtotime($p['payment_date'] ?? $p['created_at'])) ?></td>
                        <td><strong>₹<?= number_format((float)$p['amount'], 2) ?></strong></td>
                        <td><?= htmlspecialchars($p['payment_method']) ?></td>
                        <td><code><?= htmlspecialchars($p['transaction_id']) ?></code></td>
                        <td><span class="badge badge-success"><?= htmlspecialchars($p['status']) ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Record Payment -->
<div id="recordPaymentModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 500px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Record Payment for Invoice</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('recordPaymentModal')">&times;</button>
        </div>
        <form method="POST" action="invoice-details.php?id=<?= $invoiceId ?>">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="record_payment">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Payment Amount (₹) *</label>
                    <input type="number" name="amount" class="form-control" value="<?= $balanceRemaining ?>" min="0.01" step="0.01" required>
                    <small style="color: var(--admin-text-muted); font-size: 11.5px;">Outstanding balance: ₹<?= number_format($balanceRemaining, 2) ?></small>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-control">
                        <option value="Bank Transfer (NEFT/RTGS)">Bank Transfer (NEFT/RTGS)</option>
                        <option value="UPI / QR Payment">UPI / QR Payment</option>
                        <option value="Corporate Credit Card">Corporate Credit Card</option>
                        <option value="Cheque Deposit">Cheque Deposit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Transaction / UTR Reference ID *</label>
                    <input type="text" name="transaction_id" class="form-control" placeholder="e.g. UTR-9823419082" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Received Date & Time</label>
                    <input type="text" name="payment_date" class="form-control" value="<?= date('Y-m-d H:i:s') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-control">
                        <option value="Success" selected>Success (Immediately Settles Balance)</option>
                        <option value="Pending">Pending Bank Clearance</option>
                    </select>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('recordPaymentModal')">Cancel</button>
                <button type="submit" class="btn btn-success">Save Payment & Settle</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
