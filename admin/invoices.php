<?php
/**
 * GrihGO Digital & Tech — Invoices & Accounts Receivable
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Invoices & Billing';
$pageSection = 'invoices';

$error = '';
$success = '';

$validStatuses = ['Pending', 'Partially Paid', 'Paid', 'Overdue', 'Cancelled'];

// Handle Create Invoice with Line Items (Section 23 & 24)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_invoice') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $clientId    = (int)($_POST['client_id'] ?? 0);
        $projectId   = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
        $invoiceDate = sanitizeInput($_POST['invoice_date'] ?? date('Y-m-d'));
        $dueDate     = sanitizeInput($_POST['due_date'] ?? date('Y-m-d', strtotime('+15 days')));
        $notes       = sanitizeInput($_POST['notes'] ?? '');
        $taxPercent  = max(0, (float)($_POST['tax_percent'] ?? 18.0));
        $discount    = max(0, (float)($_POST['discount_amount'] ?? 0.0));

        // Line Items arrays
        $descriptions = $_POST['item_desc'] ?? [];
        $quantities   = $_POST['item_qty'] ?? [];
        $rates        = $_POST['item_rate'] ?? [];

        if ($clientId <= 0 || empty($descriptions)) {
            $error = 'Please specify Client and at least one billable line item.';
        } else {
            try {
                $pdo->beginTransaction();

                // 1. Calculate Server-side Subtotal
                $serverSubtotal = 0.0;
                $validItems = [];
                for ($i = 0; $i < count($descriptions); $i++) {
                    $desc = sanitizeInput($descriptions[$i] ?? '');
                    $qty = max(1, (int)($quantities[$i] ?? 1));
                    $rate = max(0, (float)($rates[$i] ?? 0));
                    if (!empty($desc) && $rate > 0) {
                        $lineTotal = $qty * $rate;
                        $serverSubtotal += $lineTotal;
                        $validItems[] = [
                            'desc' => $desc,
                            'qty' => $qty,
                            'rate' => $rate,
                            'total' => $lineTotal
                        ];
                    }
                }

                if (empty($validItems)) {
                    throw new Exception('Please enter valid line items with quantities and positive rates.');
                }

                $taxAmount = ($serverSubtotal * $taxPercent) / 100.0;
                $grandTotal = max(0, $serverSubtotal + $taxAmount - $discount);

                // 2. Generate Unique Invoice Number (INV-YYYY-XXXX)
                $year = date('Y');
                $nextSeq = (int)$pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn() + 1;
                $invNumber = sprintf('INV-%s-%04d', $year, $nextSeq);

                // Insert into invoices
                $invStmt = $pdo->prepare("
                    INSERT INTO invoices (invoice_number, client_id, project_id, invoice_date, due_date, subtotal, tax_amount, discount_amount, total_amount, paid_amount, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0.00, 'Pending', ?)
                ");
                $invStmt->execute([$invNumber, $clientId, $projectId, $invoiceDate, $dueDate, $serverSubtotal, $taxAmount, $discount, $grandTotal, $notes]);
                $invId = (int)$pdo->lastInsertId();

                // Insert line items
                $itemStmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)");
                foreach ($validItems as $item) {
                    $itemStmt->execute([$invId, $item['desc'], $item['qty'], $item['rate'], $item['total']]);
                }

                // Create client notification
                $pdo->prepare("INSERT INTO notifications (client_id, type, title, message, reference_id, is_read) VALUES (?, 'Invoice Generated', ?, ?, ?, 0)")
                    ->execute([
                        $clientId,
                        "New Invoice: {$invNumber}",
                        "Invoice {$invNumber} for ₹" . number_format($grandTotal, 2) . " has been issued with due date {$dueDate}.",
                        $invId
                    ]);

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Invoice Created', 'invoices', $invId, "Created invoice {$invNumber} (₹" . number_format($grandTotal, 2) . ") for client #{$clientId}");

                $pdo->commit();
                $success = "Invoice {$invNumber} generated successfully!";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Failed to generate invoice: ' . $e->getMessage();
            }
        }
    }
}

// Handle Invoice Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_invoice_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $invId = (int)($_POST['invoice_id'] ?? 0);
        $newStatus = in_array($_POST['new_status'] ?? '', $validStatuses) ? $_POST['new_status'] : '';
        if ($invId > 0 && !empty($newStatus)) {
            try {
                $pdo->prepare("UPDATE invoices SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$newStatus, $invId]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Invoice Status Updated', 'invoices', $invId, "Updated invoice #{$invId} status to {$newStatus}");
                $success = "Invoice status updated to {$newStatus}!";
            } catch (Exception $e) {
                $error = 'Failed to update invoice status.';
            }
        }
    }
}

// Search and Filter
$search = sanitizeInput($_GET['search'] ?? '');
$statusFilter = in_array($_GET['status'] ?? '', $validStatuses) ? $_GET['status'] : '';
$clientFilter = (int)($_GET['client_id'] ?? 0);

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(i.invoice_number LIKE ? OR c.company_name LIKE ? OR c.contact_person LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like]);
}

if (!empty($statusFilter)) {
    $where[] = "i.status = ?";
    $params[] = $statusFilter;
}

if ($clientFilter > 0) {
    $where[] = "i.client_id = ?";
    $params[] = $clientFilter;
}

$whereClause = implode(' AND ', $where);

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM invoices i LEFT JOIN clients c ON i.client_id = c.id WHERE {$whereClause}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT i.*, c.company_name, c.contact_person, p.name AS project_name
    FROM invoices i
    LEFT JOIN clients c ON i.client_id = c.id
    LEFT JOIN projects p ON i.project_id = p.id
    WHERE {$whereClause}
    ORDER BY i.id DESC
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch clients & projects for modals
$clientsList = $pdo->query("SELECT id, company_name, contact_person FROM clients WHERE status = 'active' ORDER BY company_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$projectsList = $pdo->query("SELECT id, name, client_id FROM projects ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Invoices & Billing</h1>
        <p class="page-subtitle">Commercial invoices, tax calculations, receivable tracking, and client billing.</p>
    </div>
    <div class="page-actions">
        <a href="payments.php" class="btn btn-outline">View Payments</a>
        <button type="button" class="btn btn-primary" onclick="openAdminModal('newInvoiceModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>+ Create Invoice</span>
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
<form method="GET" action="invoices.php" class="filter-bar">
    <div class="filter-group">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--admin-text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="search" class="filter-input" placeholder="Search invoice # or client..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div style="min-width: 150px;">
        <select name="status" class="filter-input" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach ($validStatuses as $st): ?>
                <option value="<?= $st ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= $st ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="min-width: 180px;">
        <select name="client_id" class="filter-input" onchange="this.form.submit()">
            <option value="0">All Clients</option>
            <?php foreach ($clientsList as $cl): ?>
                <option value="<?= $cl['id'] ?>" <?= $clientFilter === (int)$cl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['company_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-outline">Filter</button>
    <?php if (!empty($search) || !empty($statusFilter) || $clientFilter > 0): ?>
        <a href="invoices.php" class="btn btn-sm btn-outline" style="color: var(--admin-danger);">Clear</a>
    <?php endif; ?>
</form>

<!-- Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Project</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Grand Total</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="10" class="empty-state">No invoices found.</td></tr>
                <?php else: foreach ($invoices as $inv): 
                    $balance = max(0, (float)$inv['total_amount'] - (float)$inv['paid_amount']);
                    $invClass = match($inv['status']) {
                        'Paid' => 'badge-success',
                        'Partially Paid' => 'badge-purple',
                        'Cancelled' => 'badge-danger',
                        default => 'badge-warning'
                    };
                ?>
                    <tr>
                        <td><strong><code><?= htmlspecialchars($inv['invoice_number']) ?></code></strong></td>
                        <td>
                            <strong><a href="client-details.php?id=<?= $inv['client_id'] ?>" style="color: var(--admin-primary); text-decoration: none;"><?= htmlspecialchars($inv['company_name'] ?? 'Client #' . $inv['client_id']) ?></a></strong>
                            <div style="font-size: 11px; color: var(--admin-text-muted);"><?= htmlspecialchars($inv['contact_person'] ?? '') ?></div>
                        </td>
                        <td><?= htmlspecialchars($inv['project_name'] ?: 'Direct Solution') ?></td>
                        <td><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                        <td><?= date('d M Y', strtotime($inv['due_date'])) ?></td>
                        <td><strong>₹<?= number_format((float)$inv['total_amount'], 2) ?></strong></td>
                        <td style="color: var(--admin-accent);">₹<?= number_format((float)$inv['paid_amount'], 2) ?></td>
                        <td style="color: <?= $balance > 0 ? 'var(--admin-danger)' : 'var(--admin-text-muted)' ?>; font-weight: 600;">
                            ₹<?= number_format($balance, 2) ?>
                        </td>
                        <td>
                            <form method="POST" action="invoices.php" style="margin: 0;">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="update_invoice_status">
                                <input type="hidden" name="invoice_id" value="<?= $inv['id'] ?>">
                                <select name="new_status" onchange="this.form.submit()" style="font-size: 11.5px; font-weight: 600; padding: 3px 8px; border-radius: 9999px; border: 1px solid var(--admin-border); background: #ffffff; cursor: pointer;">
                                    <?php foreach ($validStatuses as $st): ?>
                                        <option value="<?= $st ?>" <?= $inv['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <a href="invoice-details.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline">View & Receipt</a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <div>Showing <?= ($offset + 1) ?> to <?= min($totalRecords, $offset + $perPage) ?> of <?= $totalRecords ?> invoices</div>
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= ($page - 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&client_id=<?= $clientFilter ?>" class="pagination-item">&laquo; Prev</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&client_id=<?= $clientFilter ?>" class="pagination-item <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= ($page + 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&client_id=<?= $clientFilter ?>" class="pagination-item">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: New Invoice Builder (Section 23 & 24) -->
<div id="newInvoiceModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 780px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Create Itemized Tax Invoice</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('newInvoiceModal')">&times;</button>
        </div>
        <form method="POST" action="invoices.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="create_invoice">

            <div class="admin-modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Client Account *</label>
                        <select name="client_id" class="form-control" required>
                            <option value="">Select Client...</option>
                            <?php foreach ($clientsList as $cl): ?>
                                <option value="<?= $cl['id'] ?>" <?= $clientFilter === (int)$cl['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cl['company_name']) ?> (<?= htmlspecialchars($cl['contact_person']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Associated Delivery Project</label>
                        <select name="project_id" class="form-control">
                            <option value="">None / General Architecture</option>
                            <?php foreach ($projectsList as $pr): ?>
                                <option value="<?= $pr['id'] ?>"><?= htmlspecialchars($pr['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Invoice Date *</label>
                        <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Due Date *</label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+15 days')) ?>" required>
                    </div>
                </div>

                <!-- Dynamic Line Items Table (Section 24) -->
                <div style="margin-top: 16px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label class="form-label" style="margin: 0; font-size: 14px;">Billable Line Items</label>
                        <button type="button" id="addInvoiceRowBtn" class="btn btn-sm btn-outline">+ Add Item Row</button>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table" id="invoiceItemsTable">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th style="width: 80px;">Qty</th>
                                    <th style="width: 130px;">Rate (₹)</th>
                                    <th style="width: 130px;">Amount (₹)</th>
                                    <th style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="item-row">
                                    <td><input type="text" name="item_desc[]" class="form-control" placeholder="e.g. Milestone 1 - UI/UX Architecture & Prototyping" required></td>
                                    <td><input type="number" name="item_qty[]" class="form-control item-qty" value="1" min="1" step="1" required style="width: 80px;"></td>
                                    <td><input type="number" name="item_rate[]" class="form-control item-rate" value="25000" min="0" step="0.01" required style="width: 130px;"></td>
                                    <td><input type="text" name="item_total[]" class="form-control item-total" value="25000.00" readonly style="width: 130px; background: #f8fafc;"></td>
                                    <td style="text-align: center;"><button type="button" class="btn btn-icon btn-remove-row" title="Remove Item">&times;</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tax, Discount and Subtotal Calculator -->
                <div style="background: #f8fafc; border: 1px solid var(--admin-border); border-radius: 8px; padding: 18px; margin-bottom: 16px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px; align-items: flex-end;">
                        <div>
                            <label class="form-label" style="font-size: 12px;">Subtotal (₹)</label>
                            <input type="text" id="invSubtotal" class="form-control" value="25000.00" readonly style="background: #ffffff;">
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 12px;">GST / Tax Rate (%)</label>
                            <input type="number" name="tax_percent" id="invTaxPercent" class="form-control" value="18" min="0" max="100" step="0.5">
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 12px;">Discount (₹)</label>
                            <input type="number" name="discount_amount" id="invDiscount" class="form-control" value="0" min="0" step="1">
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 12px; color: var(--admin-primary); font-weight: 700;">Grand Total</label>
                            <div id="invGrandDisplay" style="font-size: 20px; font-weight: 800; color: var(--admin-primary);">₹29,500.00</div>
                        </div>
                    </div>
                    <input type="hidden" name="tax_amount" id="invTaxAmount" value="4500.00">
                    <input type="hidden" name="grand_total" id="invGrandTotal" value="29500.00">
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Terms & Client Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Bank details, payment instructions, milestone signoff reference..."></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('newInvoiceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Generate Official Invoice</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
