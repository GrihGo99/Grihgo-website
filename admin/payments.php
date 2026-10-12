<?php
/**
 * GrihGO Digital & Tech — Payments Console
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Payments & Settlements';
$pageSection = 'payments';

$error = '';
$success = '';

$validStatuses = ['Success', 'Pending', 'Failed', 'Refunded'];
$validMethods = ['Bank Transfer (NEFT/RTGS)', 'UPI / QR Payment', 'Corporate Credit Card', 'Cheque Deposit', 'Cash / Direct Settlement'];

// Handle Record New Payment (Section 25)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_payment') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $amount    = max(0.01, (float)($_POST['amount'] ?? 0.0));
        $payMethod = in_array($_POST['payment_method'] ?? '', $validMethods) ? $_POST['payment_method'] : 'Bank Transfer (NEFT/RTGS)';
        $payDate   = sanitizeInput($_POST['payment_date'] ?? date('Y-m-d H:i:s'));
        $txnId     = sanitizeInput($_POST['transaction_id'] ?? ('TXN-' . strtoupper(bin2hex(random_bytes(5)))));
        $status    = in_array($_POST['status'] ?? '', $validStatuses) ? $_POST['status'] : 'Success';

        if ($invoiceId <= 0 || $amount <= 0) {
            $error = 'Please select a valid invoice and positive amount.';
        } else {
            try {
                $invStmt = $pdo->prepare("SELECT id, client_id, invoice_number, total_amount, paid_amount FROM invoices WHERE id = ? LIMIT 1");
                $invStmt->execute([$invoiceId]);
                $inv = $invStmt->fetch(PDO::FETCH_ASSOC);

                if (!$inv) {
                    $error = 'Selected invoice not found.';
                } else {
                    $pdo->beginTransaction();

                    // Insert payment
                    $payStmt = $pdo->prepare("
                        INSERT INTO payments (invoice_id, client_id, amount, payment_date, payment_method, transaction_id, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $payStmt->execute([$invoiceId, $inv['client_id'], $amount, $payDate, $payMethod, $txnId, $status]);
                    $newPayId = (int)$pdo->lastInsertId();

                    // Re-calculate settled amount on invoice
                    $sumStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = ? AND status = 'Success'");
                    $sumStmt->execute([$invoiceId]);
                    $newPaidSum = (float)$sumStmt->fetchColumn();

                    $newInvStatus = ($newPaidSum >= (float)$inv['total_amount']) ? 'Paid' : (($newPaidSum > 0) ? 'Partially Paid' : 'Pending');

                    $pdo->prepare("UPDATE invoices SET paid_amount = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                        ->execute([$newPaidSum, $newInvStatus, $invoiceId]);

                    // Client Notification
                    $pdo->prepare("INSERT INTO notifications (client_id, type, title, message, reference_id, is_read) VALUES (?, 'Payment Recorded', ?, ?, ?, 0)")
                        ->execute([
                            $inv['client_id'],
                            "Payment Received: ₹" . number_format($amount, 2),
                            "We have received ₹" . number_format($amount, 2) . " for invoice {$inv['invoice_number']} ({$txnId}).",
                            $newPayId
                        ]);

                    logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Payment Added', 'payments', $newPayId, "Recorded payment ₹" . number_format($amount, 2) . " for invoice {$inv['invoice_number']}");

                    $pdo->commit();
                    $success = "Payment transaction recorded successfully!";
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Failed to record payment.';
            }
        }
    }
}

// Search and Filter
$search = sanitizeInput($_GET['search'] ?? '');
$statusFilter = in_array($_GET['status'] ?? '', $validStatuses) ? $_GET['status'] : '';

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(p.transaction_id LIKE ? OR i.invoice_number LIKE ? OR c.company_name LIKE ? OR c.contact_person LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if (!empty($statusFilter)) {
    $where[] = "p.status = ?";
    $params[] = $statusFilter;
}

$whereClause = implode(' AND ', $where);

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM payments p LEFT JOIN invoices i ON p.invoice_id = i.id LEFT JOIN clients c ON p.client_id = c.id WHERE {$whereClause}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT p.*, i.invoice_number, i.total_amount AS invoice_total, c.company_name, c.contact_person
    FROM payments p
    LEFT JOIN invoices i ON p.invoice_id = i.id
    LEFT JOIN clients c ON p.client_id = c.id
    WHERE {$whereClause}
    ORDER BY p.id DESC
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch open invoices for payment modal
$openInvoices = $pdo->query("
    SELECT i.id, i.invoice_number, i.total_amount, i.paid_amount, c.company_name
    FROM invoices i
    LEFT JOIN clients c ON i.client_id = c.id
    WHERE i.status NOT IN ('Paid', 'Cancelled')
    ORDER BY i.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Payments & Settlements</h1>
        <p class="page-subtitle">Track incoming bank deposits, reconcile invoices, and review transaction ledgers.</p>
    </div>
    <div class="page-actions">
        <a href="invoices.php" class="btn btn-outline">View Invoices</a>
        <button type="button" class="btn btn-primary" onclick="openAdminModal('newPaymentModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>+ Record Payment</span>
        </button>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Filters -->
<form method="GET" action="payments.php" class="filter-bar">
    <div class="filter-group">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--admin-text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="search" class="filter-input" placeholder="Search transaction ID, invoice #, client..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div style="min-width: 150px;">
        <select name="status" class="filter-input" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach ($validStatuses as $st): ?>
                <option value="<?= $st ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= $st ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-outline">Filter</button>
    <?php if (!empty($search) || !empty($statusFilter)): ?>
        <a href="payments.php" class="btn btn-sm btn-outline" style="color: var(--admin-danger);">Clear</a>
    <?php endif; ?>
</form>

<!-- Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Transaction Reference</th>
                    <th>Invoice #</th>
                    <th>Client Entity</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="8" class="empty-state">No payment records found.</td></tr>
                <?php else: foreach ($payments as $p): 
                    $pStatusClass = match($p['status']) {
                        'Success' => 'badge-success',
                        'Pending' => 'badge-warning',
                        default => 'badge-danger'
                    };
                ?>
                    <tr>
                        <td><code>#PAY-<?= str_pad($p['id'], 3, '0', STR_PAD_LEFT) ?></code></td>
                        <td><strong><code><?= htmlspecialchars($p['transaction_id']) ?></code></strong></td>
                        <td>
                            <?php if (!empty($p['invoice_number'])): ?>
                                <a href="invoice-details.php?id=<?= $p['invoice_id'] ?>" style="color: var(--admin-primary); font-weight: 600; text-decoration: none;">
                                    <?= htmlspecialchars($p['invoice_number']) ?>
                                </a>
                            <?php else: ?>
                                <span style="color: var(--admin-text-muted);">Direct Advance</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><a href="client-details.php?id=<?= $p['client_id'] ?>" style="color: var(--admin-primary); text-decoration: none;"><?= htmlspecialchars($p['company_name'] ?? 'Client #' . $p['client_id']) ?></a></strong>
                            <div style="font-size: 11px; color: var(--admin-text-muted);"><?= htmlspecialchars($p['contact_person'] ?? '') ?></div>
                        </td>
                        <td><strong style="color: var(--admin-accent); font-size: 14px;">₹<?= number_format((float)$p['amount'], 2) ?></strong></td>
                        <td><span style="font-size: 12.5px;"><?= htmlspecialchars($p['payment_method']) ?></span></td>
                        <td style="font-size: 12px; color: var(--admin-text-muted);"><?= date('d M Y, h:i A', strtotime($p['payment_date'] ?? $p['created_at'])) ?></td>
                        <td>
                            <span class="badge <?= $pStatusClass ?>"><span class="badge-dot"></span><?= htmlspecialchars($p['status']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <div>Showing <?= ($offset + 1) ?> to <?= min($totalRecords, $offset + $perPage) ?> of <?= $totalRecords ?> payments</div>
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= ($page - 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="pagination-item">&laquo; Prev</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="pagination-item <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= ($page + 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="pagination-item">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Record Payment -->
<div id="newPaymentModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 520px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Record Payment Settlement</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('newPaymentModal')">&times;</button>
        </div>
        <form method="POST" action="payments.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="add_payment">

            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Target Invoice *</label>
                    <select name="invoice_id" class="form-control" required>
                        <option value="">Select Open Invoice...</option>
                        <?php foreach ($openInvoices as $oi): 
                            $bal = max(0, (float)$oi['total_amount'] - (float)$oi['paid_amount']);
                        ?>
                            <option value="<?= $oi['id'] ?>">
                                <?= htmlspecialchars($oi['invoice_number']) ?> &bull; <?= htmlspecialchars($oi['company_name']) ?> (Bal: ₹<?= number_format($bal, 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Amount (₹) *</label>
                    <input type="number" name="amount" class="form-control" placeholder="e.g. 35000.00" min="0.01" step="0.01" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Remittance Channel / Method *</label>
                    <select name="payment_method" class="form-control">
                        <?php foreach ($validMethods as $m): ?>
                            <option value="<?= $m ?>"><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Transaction / UTR Number *</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g. UTR-9843219082" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="Success" selected>Success (Settled)</option>
                            <option value="Pending">Pending Clearance</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Received Date & Time</label>
                    <input type="text" name="payment_date" class="form-control" value="<?= date('Y-m-d H:i:s') ?>">
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('newPaymentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Record Payment</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
