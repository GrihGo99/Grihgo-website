<?php
/**
 * GrihGO Digital & Tech — Support Desk Console
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Support Desk';
$pageSection = 'support-tickets';

$error = '';
$success = '';

$validStatuses = ['Open', 'In Progress', 'Waiting for Client', 'Resolved', 'Closed'];
$validPriorities = ['Low', 'Medium', 'High', 'Urgent'];

// Handle Quick Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $newStatus = in_array($_POST['new_status'] ?? '', $validStatuses) ? $_POST['new_status'] : '';
        if ($ticketId > 0 && !empty($newStatus)) {
            try {
                $pdo->prepare("UPDATE support_tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                    ->execute([$newStatus, $ticketId]);

                $tInfo = $pdo->query("SELECT ticket_number, client_id FROM support_tickets WHERE id = {$ticketId}")->fetch(PDO::FETCH_ASSOC);
                if ($tInfo && !empty($tInfo['client_id'])) {
                    $pdo->prepare("INSERT INTO notifications (client_id, type, title, message, reference_id, is_read) VALUES (?, 'Support', ?, ?, ?, 0)")
                        ->execute([
                            $tInfo['client_id'],
                            "Ticket {$tInfo['ticket_number']} Status: {$newStatus}",
                            "Your support ticket {$tInfo['ticket_number']} has been marked as {$newStatus}.",
                            $ticketId
                        ]);
                }

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Ticket Status Updated', 'support_tickets', $ticketId, "Updated ticket #{$ticketId} to {$newStatus}");
                $success = "Ticket status updated to {$newStatus}!";
            } catch (Exception $e) {
                $error = 'Failed to update ticket status.';
            }
        }
    }
}

// Search and Filter
$search = sanitizeInput($_GET['search'] ?? '');
$statusFilter = in_array($_GET['status'] ?? '', $validStatuses) ? $_GET['status'] : '';
$priorityFilter = in_array($_GET['priority'] ?? '', $validPriorities) ? $_GET['priority'] : '';

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(t.ticket_number LIKE ? OR t.subject LIKE ? OR c.company_name LIKE ? OR c.contact_person LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if (!empty($statusFilter)) {
    $where[] = "t.status = ?";
    $params[] = $statusFilter;
}

if (!empty($priorityFilter)) {
    $where[] = "t.priority = ?";
    $params[] = $priorityFilter;
}

$whereClause = implode(' AND ', $where);

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets t LEFT JOIN clients c ON t.client_id = c.id WHERE {$whereClause}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT t.*, c.company_name, c.contact_person,
           (SELECT COUNT(*) FROM ticket_messages tm WHERE tm.ticket_id = t.id) AS total_messages
    FROM support_tickets t
    LEFT JOIN clients c ON t.client_id = c.id
    WHERE {$whereClause}
    ORDER BY t.id DESC
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Client Support Desk</h1>
        <p class="page-subtitle">Resolve client queries, technical tickets, and account maintenance requests.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Filters -->
<form method="GET" action="support-tickets.php" class="filter-bar">
    <div class="filter-group">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--admin-text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="search" class="filter-input" placeholder="Search ticket #, subject or client..." value="<?= htmlspecialchars($search) ?>">
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
        <select name="priority" class="filter-input" onchange="this.form.submit()">
            <option value="">All Priorities</option>
            <?php foreach ($validPriorities as $pr): ?>
                <option value="<?= $pr ?>" <?= $priorityFilter === $pr ? 'selected' : '' ?>><?= $pr ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-outline">Filter</button>
    <?php if (!empty($search) || !empty($statusFilter) || !empty($priorityFilter)): ?>
        <a href="support-tickets.php" class="btn btn-sm btn-outline" style="color: var(--admin-danger);">Clear</a>
    <?php endif; ?>
</form>

<!-- Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Client</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Last Active</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr><td colspan="8" class="empty-state">No support tickets found matching criteria.</td></tr>
                <?php else: foreach ($tickets as $t): 
                    $tStatusClass = match($t['status']) {
                        'Resolved', 'Closed' => 'badge-success',
                        'In Progress' => 'badge-primary',
                        'Waiting for Client' => 'badge-warning',
                        default => 'badge-danger'
                    };
                ?>
                    <tr>
                        <td><code><?= htmlspecialchars($t['ticket_number']) ?></code></td>
                        <td>
                            <strong><a href="client-details.php?id=<?= $t['client_id'] ?>" style="color: var(--admin-primary); text-decoration: none;"><?= htmlspecialchars($t['company_name'] ?? 'Client #' . $t['client_id']) ?></a></strong>
                            <div style="font-size: 11px; color: var(--admin-text-muted);"><?= htmlspecialchars($t['contact_person'] ?? '') ?></div>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($t['subject']) ?></strong>
                            <div style="font-size: 11px; color: var(--admin-text-muted);"><?= (int)$t['total_messages'] ?> message(s) in thread</div>
                        </td>
                        <td><span class="badge badge-neutral"><?= htmlspecialchars($t['category']) ?></span></td>
                        <td>
                            <span class="badge <?= in_array($t['priority'], ['High', 'Urgent']) ? 'badge-danger' : 'badge-neutral' ?>">
                                <?= htmlspecialchars($t['priority']) ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="support-tickets.php" style="margin: 0;">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                <select name="new_status" onchange="this.form.submit()" style="font-size: 11.5px; font-weight: 600; padding: 3px 8px; border-radius: 9999px; border: 1px solid var(--admin-border); background: #ffffff; cursor: pointer;">
                                    <?php foreach ($validStatuses as $st): ?>
                                        <option value="<?= $st ?>" <?= $t['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td style="font-size: 12px; color: var(--admin-text-muted);"><?= date('d M Y, h:i A', strtotime($t['updated_at'] ?? $t['created_at'])) ?></td>
                        <td>
                            <a href="ticket-details.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline">View & Reply</a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <div>Showing <?= ($offset + 1) ?> to <?= min($totalRecords, $offset + $perPage) ?> of <?= $totalRecords ?> tickets</div>
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
