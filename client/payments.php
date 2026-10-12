<?php
/**
 * GrihGO Digital & Tech — Client Payment History
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/includes/auth.php';

$portalPageTitle = 'Payment History';
$portalHeaderTitle = 'Transaction & Payment History';
$current_portal_page = 'payments';

$pdo = Database::getConnection();

// Scoped strictly to authenticated client
$stmt = $pdo->prepare("
    SELECT p.*, i.invoice_number, i.total_amount AS invoice_total
    FROM payments p
    LEFT JOIN invoices i ON p.invoice_id = i.id
    WHERE p.client_id = :cid
    ORDER BY p.payment_date DESC, p.id DESC
");
$stmt->execute([':cid' => $clientId]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total payments made successfully
$totalPaid = 0.0;
foreach ($payments as $pay) {
    if ($pay['status'] === 'Success') {
        $totalPaid += (float)$pay['amount'];
    }
}

include __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: gap: 12px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--portal-text-main); margin: 0 0 4px 0;">Payment Transactions</h2>
        <p style="font-size: 0.85rem; color: var(--portal-text-muted); margin: 0;">Verified bank transfers, wire transactions, and automated invoice remittances</p>
    </div>
    <div style="background: #ffffff; border: 1px solid var(--portal-border); border-radius: 8px; padding: 8px 16px; font-size: 0.88rem;">
        Total Remitted: <strong style="color: var(--portal-accent); font-size: 1.05rem;">₹<?= number_format($totalPaid, 2) ?></strong>
    </div>
</div>

<div class="portal-card">
    <div class="portal-card-header">
        <h3 class="portal-card-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
            <span>Payment Receipts (<?= count($payments) ?>)</span>
        </h3>
    </div>

    <div class="portal-card-body" style="padding: 0;">
        <?php if (empty($payments)): ?>
            <div class="portal-empty-state">
                <div class="portal-empty-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                </div>
                <div class="portal-empty-title">No Payments Recorded</div>
                <p class="portal-empty-desc">No transaction settlements have been recorded for your account.</p>
            </div>
        <?php else: ?>
            <div class="portal-table-container">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Invoice Reference</th>
                            <th>Amount Remitted</th>
                            <th>Payment Date</th>
                            <th>Payment Mode</th>
                            <th>Bank Reference / UTR</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $pay): ?>
                            <?php
                            $status = $pay['status'];
                            $badgeClass = 'badge-completed';
                            if ($status === 'Pending') $badgeClass = 'badge-pending';
                            elseif ($status === 'Failed') $badgeClass = 'badge-overdue';
                            elseif ($status === 'Refunded') $badgeClass = 'badge-medium';
                            ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--portal-primary);">#PAY-<?= str_pad((string)$pay['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($pay['invoice_number'])): ?>
                                        <a href="invoices.php?id=<?= (int)$pay['invoice_id'] ?>" style="font-weight: 600; color: var(--portal-text-main); text-decoration: none;">
                                            <?= htmlspecialchars($pay['invoice_number'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--portal-text-muted);">Direct Advance</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 700; font-size: 0.95rem;">
                                    ₹<?= number_format((float)$pay['amount'], 2) ?>
                                </td>
                                <td style="font-size: 0.82rem; color: #475569;">
                                    <?= date('d M Y, h:i A', strtotime($pay['payment_date'])) ?>
                                </td>
                                <td style="font-size: 0.85rem; color: #334155;">
                                    <?= htmlspecialchars($pay['payment_method'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <code style="font-size: 0.78rem; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #0f172a;">
                                        <?= htmlspecialchars($pay['transaction_id'], ENT_QUOTES, 'UTF-8') ?>
                                    </code>
                                </td>
                                <td>
                                    <span class="badge-status <?= $badgeClass ?>">
                                        <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
