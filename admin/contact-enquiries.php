<?php
/**
 * GrihGO Digital & Tech — Contact Enquiries Console
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Contact Enquiries';
$pageSection = 'contact-enquiries';

$error = '';
$success = '';

$validStatuses = ['New', 'Read', 'Replied', 'Closed'];

// Handle Status Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $msgId = (int)($_POST['message_id'] ?? 0);
        $newStatus = in_array($_POST['new_status'] ?? '', $validStatuses) ? $_POST['new_status'] : '';
        if ($msgId > 0 && !empty($newStatus)) {
            try {
                $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?")->execute([$newStatus, $msgId]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Contact Status Updated', 'contact_messages', $msgId, "Set message #{$msgId} to {$newStatus}");
                $success = "Inquiry marked as {$newStatus}.";
            } catch (Exception $e) {
                $error = 'Failed to update message status.';
            }
        }
    }
}

// Handle Convert to Lead (Section 46)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'convert_to_lead') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $msgId = (int)($_POST['message_id'] ?? 0);
        if ($msgId > 0) {
            try {
                $msg = $pdo->query("SELECT * FROM contact_messages WHERE id = {$msgId}")->fetch(PDO::FETCH_ASSOC);
                if ($msg) {
                    $stmt = $pdo->prepare("INSERT INTO leads (name, business_name, phone, email, service, source, notes, status) VALUES (?, ?, ?, ?, ?, 'Contact Form', ?, 'New')");
                    $stmt->execute([$msg['name'], $msg['business_name'] ?? '', $msg['phone'], $msg['email'], $msg['service'] ?? 'General', $msg['message']]);
                    $newLeadId = (int)$pdo->lastInsertId();

                    $pdo->prepare("UPDATE contact_messages SET status = 'Replied' WHERE id = ?")->execute([$msgId]);
                    logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Contact Converted to Lead', 'leads', $newLeadId, "Converted message #{$msgId} to Lead #{$newLeadId}");
                    header("Location: lead-details.php?id={$newLeadId}&from_contact=1");
                    exit;
                }
            } catch (Exception $e) {
                $error = 'Failed to convert enquiry to lead.';
            }
        }
    }
}

// Search & Filter
$search = sanitizeInput($_GET['search'] ?? '');
$statusFilter = in_array($_GET['status'] ?? '', $validStatuses) ? $_GET['status'] : '';

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(name LIKE ? OR business_name LIKE ? OR email LIKE ? OR phone LIKE ? OR message LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
}

if (!empty($statusFilter)) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

$whereClause = implode(' AND ', $where);

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages WHERE {$whereClause}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE {$whereClause} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Contact Enquiries</h1>
        <p class="page-subtitle">Submissions received via public Contact Us form.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Filter Bar -->
<form method="GET" action="contact-enquiries.php" class="filter-bar">
    <div class="filter-group">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--admin-text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="search" class="filter-input" placeholder="Search inquiries by name, company, email, message..." value="<?= htmlspecialchars($search) ?>">
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
        <a href="contact-enquiries.php" class="btn btn-sm btn-outline" style="color: var(--admin-danger);">Clear</a>
    <?php endif; ?>
</form>

<!-- Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sender</th>
                    <th>Company</th>
                    <th>Service</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr><td colspan="8" class="empty-state">No inquiries found.</td></tr>
                <?php else: foreach ($messages as $m): 
                    $mStatusClass = match($m['status']) {
                        'Replied', 'Closed' => 'badge-success',
                        'Read' => 'badge-primary',
                        default => 'badge-warning'
                    };
                ?>
                    <tr>
                        <td><code>#CE-<?= str_pad($m['id'], 3, '0', STR_PAD_LEFT) ?></code></td>
                        <td>
                            <strong><?= htmlspecialchars($m['name']) ?></strong>
                            <div style="font-size: 11px; color: var(--admin-text-muted);">
                                <a href="mailto:<?= htmlspecialchars($m['email']) ?>" style="color: var(--admin-primary);"><?= htmlspecialchars($m['email']) ?></a> &bull; <?= htmlspecialchars($m['phone']) ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($m['business_name'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($m['service'] ?? 'General') ?></td>
                        <td>
                            <div style="max-width: 280px; font-size: 12.5px; color: #475569; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= htmlspecialchars($m['message']) ?>
                            </div>
                        </td>
                        <td>
                            <form method="POST" action="contact-enquiries.php" style="margin: 0;">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                                <select name="new_status" onchange="this.form.submit()" style="font-size: 11.5px; font-weight: 600; padding: 3px 8px; border-radius: 9999px; border: 1px solid var(--admin-border); background: #ffffff; cursor: pointer;">
                                    <?php foreach ($validStatuses as $st): ?>
                                        <option value="<?= $st ?>" <?= $m['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td style="font-size: 12px; color: var(--admin-text-muted);"><?= date('d M Y', strtotime($m['created_at'])) ?></td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <form method="POST" action="contact-enquiries.php" style="margin: 0;">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="convert_to_lead">
                                    <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline" title="Push into Lead Pipeline">To Lead</button>
                                </form>
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
            <div>Showing <?= ($offset + 1) ?> to <?= min($totalRecords, $offset + $perPage) ?> of <?= $totalRecords ?> inquiries</div>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
