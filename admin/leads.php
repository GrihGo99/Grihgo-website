<?php
/**
 * GrihGO Digital & Tech — Enterprise Lead CRM
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Lead Pipeline & CRM';
$pageSection = 'leads';

$error = '';
$success = '';

// Allowed statuses and sources
$validStatuses = ['New', 'Contacted', 'Interested', 'Proposal Sent', 'Negotiation', 'Converted', 'Lost'];
$validSources = ['Website', 'Appointment', 'Contact Form', 'Request Demo', 'Service Enquiry'];

// Handle Create Lead
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_lead') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $name         = sanitizeInput($_POST['name'] ?? '');
        $businessName = sanitizeInput($_POST['business_name'] ?? '');
        $phone        = sanitizePhone($_POST['phone'] ?? '');
        $email        = sanitizeEmail($_POST['email'] ?? '');
        $service      = sanitizeInput($_POST['service'] ?? 'General');
        $source       = in_array($_POST['source'] ?? '', $validSources) ? $_POST['source'] : 'Website';
        $status       = in_array($_POST['status'] ?? '', $validStatuses) ? $_POST['status'] : 'New';
        $notes        = sanitizeInput($_POST['notes'] ?? '');
        $assignedTo   = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;

        if (empty($name) || empty($phone) || empty($email)) {
            $error = 'Please provide Lead Contact Name, Phone, and Email.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO leads (name, business_name, phone, email, service, source, status, notes, assigned_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $businessName, $phone, $email, $service, $source, $status, $notes, $assignedTo]);
                $leadId = (int)$pdo->lastInsertId();

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Lead Created', 'leads', $leadId, "Created lead for {$name} ({$businessName})");
                $success = "Lead for '{$name}' created successfully in CRM pipeline!";
            } catch (Exception $e) {
                $error = 'Failed to create lead record.';
            }
        }
    }
}

// Handle Quick Status Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $leadId = (int)($_POST['lead_id'] ?? 0);
        $newStatus = in_array($_POST['new_status'] ?? '', $validStatuses) ? $_POST['new_status'] : '';
        if ($leadId > 0 && !empty($newStatus)) {
            try {
                $leadInfo = $pdo->query("SELECT name, status FROM leads WHERE id = {$leadId}")->fetch(PDO::FETCH_ASSOC);
                $pdo->prepare("UPDATE leads SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$newStatus, $leadId]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Lead Status Updated', 'leads', $leadId, "Changed status of {$leadInfo['name']} from {$leadInfo['status']} to {$newStatus}");
                $success = "Lead '{$leadInfo['name']}' status updated to {$newStatus}.";
            } catch (Exception $e) {
                $error = 'Failed to update lead status.';
            }
        }
    }
}

// Search and Filter
$search = sanitizeInput($_GET['search'] ?? '');
$statusFilter = in_array($_GET['status'] ?? '', $validStatuses) ? $_GET['status'] : '';
$sourceFilter = in_array($_GET['source'] ?? '', $validSources) ? $_GET['source'] : '';

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(l.name LIKE ? OR l.business_name LIKE ? OR l.phone LIKE ? OR l.email LIKE ? OR l.service LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
}

if (!empty($statusFilter)) {
    $where[] = "l.status = ?";
    $params[] = $statusFilter;
}

if (!empty($sourceFilter)) {
    $where[] = "l.source = ?";
    $params[] = $sourceFilter;
}

$whereClause = implode(' AND ', $where);

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM leads l WHERE {$whereClause}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));
$offset = ($page - 1) * $perPage;

// Fetch Leads
$sql = "
    SELECT l.*, a.full_name AS assigned_admin_name
    FROM leads l
    LEFT JOIN admins a ON l.assigned_to = a.id
    WHERE {$whereClause}
    ORDER BY l.id DESC
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch admin staff list for assignment dropdown
$adminStaff = $pdo->query("SELECT id, full_name, role FROM admins WHERE status = 'active' ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Lead CRM Pipeline</h1>
        <p class="page-subtitle">Track incoming business inquiries, qualify prospects, and drive sales conversions.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" onclick="openAdminModal('newLeadModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>+ Create Lead</span>
        </button>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Pipeline Stage Ribbon -->
<div style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 16px;">
    <?php 
    foreach ($validStatuses as $st): 
        $cnt = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status = '{$st}'")->fetchColumn();
        $isActiveTab = $statusFilter === $st;
    ?>
        <a href="leads.php?status=<?= urlencode($st) ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: var(--admin-radius-sm); border: 1px solid <?= $isActiveTab ? 'var(--admin-primary)' : 'var(--admin-border)' ?>; background: <?= $isActiveTab ? 'var(--admin-primary-light)' : '#ffffff' ?>; text-decoration: none; color: <?= $isActiveTab ? 'var(--admin-primary)' : 'var(--admin-text-main)' ?>; font-size: 13px; font-weight: 600; white-space: nowrap;">
            <span><?= $st ?></span>
            <span style="background: <?= $isActiveTab ? 'var(--admin-primary)' : '#e2e8f0' ?>; color: <?= $isActiveTab ? '#ffffff' : '#475569' ?>; font-size: 11px; padding: 2px 6px; border-radius: 10px;"><?= $cnt ?></span>
        </a>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<form method="GET" action="leads.php" class="filter-bar">
    <div class="filter-group">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--admin-text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="search" class="filter-input" placeholder="Search by lead name, business, phone, email, or service..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div style="min-width: 150px;">
        <select name="status" class="filter-input" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach ($validStatuses as $st): ?>
                <option value="<?= $st ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= $st ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="min-width: 150px;">
        <select name="source" class="filter-input" onchange="this.form.submit()">
            <option value="">All Sources</option>
            <?php foreach ($validSources as $src): ?>
                <option value="<?= $src ?>" <?= $sourceFilter === $src ? 'selected' : '' ?>><?= $src ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-outline">Filter</button>
    <?php if (!empty($search) || !empty($statusFilter) || !empty($sourceFilter)): ?>
        <a href="leads.php" class="btn btn-sm btn-outline" style="color: var(--admin-danger);">Clear</a>
    <?php endif; ?>
</form>

<!-- Leads Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Lead #</th>
                    <th>Prospect / Business</th>
                    <th>Contact Details</th>
                    <th>Service Interest</th>
                    <th>Source</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leads)): ?>
                    <tr><td colspan="9" class="empty-state">No leads found in this pipeline segment.</td></tr>
                <?php else: foreach ($leads as $lead): 
                    $statusClass = match($lead['status']) {
                        'Converted' => 'badge-success',
                        'Proposal Sent', 'Negotiation' => 'badge-purple',
                        'Contacted', 'Interested' => 'badge-primary',
                        'Lost' => 'badge-danger',
                        default => 'badge-warning'
                    };
                ?>
                    <tr>
                        <td><code>#LD-<?= str_pad($lead['id'], 3, '0', STR_PAD_LEFT) ?></code></td>
                        <td>
                            <strong><?= htmlspecialchars($lead['name']) ?></strong>
                            <?php if (!empty($lead['business_name'])): ?>
                                <div style="font-size: 11.5px; color: var(--admin-text-muted);"><?= htmlspecialchars($lead['business_name']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><a href="mailto:<?= htmlspecialchars($lead['email']) ?>" style="color: var(--admin-primary); text-decoration: none;"><?= htmlspecialchars($lead['email']) ?></a></div>
                            <div style="font-size: 11.5px; color: var(--admin-text-muted);"><?= htmlspecialchars($lead['phone']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($lead['service'] ?? 'General') ?></td>
                        <td><span class="badge badge-neutral"><?= htmlspecialchars($lead['source'] ?? 'Website') ?></span></td>
                        <td>
                            <span style="font-size: 12px; color: #475569;">
                                <?= htmlspecialchars($lead['assigned_admin_name'] ?? 'Unassigned') ?>
                            </span>
                        </td>
                        <td>
                            <!-- Quick Status Switch Form -->
                            <form method="POST" action="leads.php" style="margin: 0;">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                                <select name="new_status" onchange="this.form.submit()" style="font-size: 11.5px; font-weight: 600; padding: 3px 8px; border-radius: 9999px; border: 1px solid var(--admin-border); background: #ffffff; cursor: pointer;">
                                    <?php foreach ($validStatuses as $s): ?>
                                        <option value="<?= $s ?>" <?= $lead['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td style="font-size: 12px; color: var(--admin-text-muted);"><?= date('d M Y', strtotime($lead['created_at'])) ?></td>
                        <td>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <a href="lead-details.php?id=<?= $lead['id'] ?>" class="btn btn-sm btn-outline" title="Open Full Lead Dossier">Review & Dossier</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <div>Showing <?= ($offset + 1) ?> to <?= min($totalRecords, $offset + $perPage) ?> of <?= $totalRecords ?> leads</div>
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= ($page - 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&source=<?= urlencode($sourceFilter) ?>" class="pagination-item">&laquo; Prev</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&source=<?= urlencode($sourceFilter) ?>" class="pagination-item <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= ($page + 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&source=<?= urlencode($sourceFilter) ?>" class="pagination-item">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Create Lead -->
<div id="newLeadModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 600px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Log New Lead</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('newLeadModal')">&times;</button>
        </div>
        <form method="POST" action="leads.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="create_lead">
            <div class="admin-modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Contact Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Alok Verma" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Business / Organization Name</label>
                        <input type="text" name="business_name" class="form-control" placeholder="e.g. Verma Retail">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="alok@verma.in" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Interested Service / Product</label>
                        <input type="text" name="service" class="form-control" placeholder="e.g. WhatsApp CRM / Web Dev">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Acquisition Source</label>
                        <select name="source" class="form-control">
                            <?php foreach ($validSources as $src): ?>
                                <option value="<?= $src ?>"><?= $src ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Initial Status</label>
                        <select name="status" class="form-control">
                            <?php foreach ($validStatuses as $st): ?>
                                <option value="<?= $st ?>"><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assign To Team Member</label>
                        <select name="assigned_to" class="form-control">
                            <option value="">Unassigned</option>
                            <?php foreach ($adminStaff as $staff): ?>
                                <option value="<?= $staff['id'] ?>"><?= htmlspecialchars($staff['full_name']) ?> (<?= ucfirst($staff['role']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Discussion Notes & Requirements</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Key prospect objectives, timeline, budget expectations..."></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('newLeadModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Lead</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
