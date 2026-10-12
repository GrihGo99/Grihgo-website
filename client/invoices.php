<?php
/**
 * GrihGO Digital & Tech — Client Invoices & Billing Management
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/includes/auth.php';

$portalPageTitle = 'Invoices';
$portalHeaderTitle = 'Invoices & Billing Statements';
$current_portal_page = 'invoices';

$pdo = Database::getConnection();

// Check if viewing a specific invoice
$selectedInvId = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$activeInvoice = null;
$invoiceItems = [];

if ($selectedInvId > 0) {
    // Strictly verify client ownership!
    $stmtInv = $pdo->prepare("
        SELECT i.*, p.name AS project_name, c.company_name, c.contact_person, c.address, c.gst_number
        FROM invoices i
        LEFT JOIN projects p ON i.project_id = p.id
        JOIN clients c ON i.client_id = c.id
        WHERE i.id = :iid AND i.client_id = :cid
        LIMIT 1
    ");
    $stmtInv->execute([':iid' => $selectedInvId, ':cid' => $clientId]);
    $activeInvoice = $stmtInv->fetch(PDO::FETCH_ASSOC);

    if ($activeInvoice) {
        $stmtItems = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = :iid ORDER BY id ASC");
        $stmtItems->execute([':iid' => $selectedInvId]);
        $invoiceItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Fetch all invoices strictly belonging to this client
$stmtAll = $pdo->prepare("
    SELECT i.*, p.name AS project_name 
    FROM invoices i
    LEFT JOIN projects p ON i.project_id = p.id
    WHERE i.client_id = :cid 
    ORDER BY i.invoice_date DESC, i.id DESC
");
$stmtAll->execute([':cid' => $clientId]);
$invoices = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$totalBilled = 0.0;
$totalPaid = 0.0;
foreach ($invoices as $inv) {
    $totalBilled += (float)$inv['total_amount'];
    $totalPaid += (float)$inv['paid_amount'];
}
$totalOutstanding = max(0.0, $totalBilled - $totalPaid);

include __DIR__ . '/includes/header.php';
?>

<!-- Financial Summary Cards -->
<div class="portal-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
    <div class="portal-stat-card">
        <span class="portal-stat-label">Total Invoiced</span>
        <div class="portal-stat-value">₹<?= number_format($totalBilled, 2) ?></div>
        <div class="portal-stat-footer">Cumulative billing</div>
    </div>
    <div class="portal-stat-card">
        <span class="portal-stat-label">Total Paid</span>
        <div class="portal-stat-value" style="color: var(--portal-accent, #10b981);">₹<?= number_format($totalPaid, 2) ?></div>
        <div class="portal-stat-footer">Confirmed credits</div>
    </div>
    <div class="portal-stat-card">
        <span class="portal-stat-label">Outstanding Balance</span>
        <div class="portal-stat-value" style="color: <?= $totalOutstanding > 0 ? '#b91c1c' : 'var(--portal-accent)' ?>;">
            ₹<?= number_format($totalOutstanding, 2) ?>
        </div>
        <div class="portal-stat-footer">Pending clearance</div>
    </div>
</div>

<?php if ($activeInvoice): ?>
    <!-- ================= SINGLE INVOICE DETAILED VIEW / PRINT PREVIEW ================= -->
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <a href="invoices.php" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--portal-primary); text-decoration: none; font-weight: 600;">
            &larr; Back to All Invoices
        </a>
        <button type="button" onclick="window.print();" class="btn-portal-outline btn-portal-sm" style="background: #ffffff;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            <span>Print / Save as PDF</span>
        </button>
    </div>

    <div class="portal-card" id="printableInvoice" style="background: #ffffff; padding: 36px;">
        <!-- Invoice Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--portal-border); padding-bottom: 24px; margin-bottom: 24px;">
            <div>
                <img src="../assets/images/logo/logo.png" alt="GrihGO Digital & Tech" style="height: 44px; margin-bottom: 8px;">
                <div style="font-size: 0.85rem; color: #475569; line-height: 1.5;">
                    GrihGO Tech Park, Innovation Zone<br>
                    Sector 62, Metro City — 201301<br>
                    Email: billing@grihgodigital.com &bull; Phone: +91 99999 99999<br>
                    GSTIN: 07AABCG1234F1Z9
                </div>
            </div>
            <div style="text-align: right;">
                <h2 style="font-size: 1.8rem; font-weight: 800; color: var(--portal-primary); margin: 0;">TAX INVOICE</h2>
                <div style="font-size: 1.1rem; font-weight: 700; color: var(--portal-text-main); margin-top: 4px;">
                    <?= htmlspecialchars($activeInvoice['invoice_number'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div style="margin-top: 8px;">
                    <span class="badge-status badge-<?= strtolower(str_replace(' ', '-', $activeInvoice['status'])) ?>" style="font-size: 0.85rem; padding: 4px 12px;">
                        <?= htmlspecialchars($activeInvoice['status'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Bill To & Invoice Info -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 28px;">
            <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #94a3b8; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">
                    BILLED TO:
                </span>
                <div style="font-weight: 700; font-size: 1.1rem; color: var(--portal-text-main);">
                    <?= htmlspecialchars($activeInvoice['company_name'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div style="font-size: 0.88rem; color: #475569; line-height: 1.5; margin-top: 4px;">
                    Attn: <?= htmlspecialchars($activeInvoice['contact_person'], ENT_QUOTES, 'UTF-8') ?><br>
                    <?= nl2br(htmlspecialchars($activeInvoice['address'] ?? 'Registered Office Address', ENT_QUOTES, 'UTF-8')) ?><br>
                    <?php if (!empty($activeInvoice['gst_number'])): ?>
                        GSTIN: <strong><?= htmlspecialchars($activeInvoice['gst_number'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <?php endif; ?>
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.88rem; color: #475569; line-height: 1.6;">
                    <div><strong>Invoice Date:</strong> <?= htmlspecialchars($activeInvoice['invoice_date'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div><strong>Payment Due:</strong> <?= htmlspecialchars($activeInvoice['due_date'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div><strong>Project:</strong> <?= htmlspecialchars($activeInvoice['project_name'] ?? 'Direct Service', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="portal-table" style="margin-bottom: 24px;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th>Item Description</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Rate</th>
                    <th style="text-align: right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoiceItems)): ?>
                    <tr>
                        <td colspan="4">Digital Engineering Services &bull; Milestone Contract</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoiceItems as $item): ?>
                        <tr>
                            <td>
                                <strong style="color: var(--portal-text-main);"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </td>
                            <td style="text-align: center;"><?= (int)$item['quantity'] ?></td>
                            <td style="text-align: right;">₹<?= number_format((float)$item['unit_price'], 2) ?></td>
                            <td style="text-align: right; font-weight: 700;">₹<?= number_format((float)$item['total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Totals & Payment Details -->
        <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 32px; border-top: 1px solid var(--portal-border); padding-top: 20px;">
            <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #94a3b8; display: block; margin-bottom: 6px;">
                    PAYMENT REMITTANCE INFORMATION:
                </span>
                <div style="font-size: 0.82rem; color: #475569; background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid var(--portal-border); line-height: 1.6;">
                    <strong>Bank Name:</strong> HDFC Bank Ltd.<br>
                    <strong>Account Name:</strong> GrihGO Digital &amp; Tech Pvt Ltd<br>
                    <strong>A/C Number:</strong> 50200088991122<br>
                    <strong>IFSC Code:</strong> HDFC0001234<br>
                    <strong>Branch:</strong> Sector 62 Corporate Tech Park
                </div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.9rem; color: #475569;">
                    <span>Total Invoiced:</span>
                    <strong>₹<?= number_format((float)$activeInvoice['total_amount'], 2) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.9rem; color: var(--portal-accent);">
                    <span>Amount Paid:</span>
                    <strong>₹<?= number_format((float)$activeInvoice['paid_amount'], 2) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-top: 2px solid var(--portal-border); font-size: 1.15rem; font-weight: 800; color: var(--portal-text-main);">
                    <span>Balance Due:</span>
                    <span style="color: <?= ((float)$activeInvoice['total_amount'] - (float)$activeInvoice['paid_amount'] > 0) ? '#b91c1c' : 'var(--portal-accent)' ?>;">
                        ₹<?= number_format(max(0, (float)$activeInvoice['total_amount'] - (float)$activeInvoice['paid_amount']), 2) ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if (!empty($activeInvoice['notes'])): ?>
            <div style="margin-top: 24px; padding: 12px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; font-size: 0.82rem; color: #92400e;">
                <strong>Invoice Note:</strong> <?= htmlspecialchars($activeInvoice['notes'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- ================= ALL INVOICES LIST TABLE ================= -->
    <div class="portal-card">
        <div class="portal-card-header">
            <h3 class="portal-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                <span>Invoice Ledger (<?= count($invoices) ?>)</span>
            </h3>
        </div>

        <div class="portal-card-body" style="padding: 0;">
            <?php if (empty($invoices)): ?>
                <div class="portal-empty-state">
                    <div class="portal-empty-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div class="portal-empty-title">No Invoices Yet</div>
                    <p class="portal-empty-desc">Your account has no billing invoices at this stage.</p>
                </div>
            <?php else: ?>
                <div class="portal-table-container">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Invoice Number</th>
                                <th>Related Project</th>
                                <th>Invoice Date</th>
                                <th>Due Date</th>
                                <th>Total Amount</th>
                                <th>Paid Amount</th>
                                <th>Pending Balance</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $inv): ?>
                                <?php
                                $total = (float)$inv['total_amount'];
                                $paid = (float)$inv['paid_amount'];
                                $pending = max(0, $total - $paid);
                                $statusClass = strtolower(str_replace(' ', '-', $inv['status']));
                                ?>
                                <tr>
                                    <td>
                                        <strong style="color: var(--portal-primary);">
                                            <?= htmlspecialchars($inv['invoice_number'], ENT_QUOTES, 'UTF-8') ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($inv['project_name'] ?? 'General Service', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="font-size: 0.82rem; color: #475569;">
                                        <?= htmlspecialchars($inv['invoice_date'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="font-size: 0.82rem; color: #475569;">
                                        <?= htmlspecialchars($inv['due_date'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="font-weight: 700;">
                                        ₹<?= number_format($total, 2) ?>
                                    </td>
                                    <td style="color: var(--portal-accent); font-weight: 600;">
                                        ₹<?= number_format($paid, 2) ?>
                                    </td>
                                    <td style="font-weight: 700; color: <?= $pending > 0 ? '#b91c1c' : 'var(--portal-accent)' ?>;">
                                        ₹<?= number_format($pending, 2) ?>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-<?= $statusClass ?>">
                                            <?= htmlspecialchars($inv['status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="invoices.php?id=<?= (int)$inv['id'] ?>" class="btn-portal-outline btn-portal-sm">
                                            View Invoice
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
