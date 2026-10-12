<?php
/**
 * GrihGO Digital & Tech — Appointment Management Console
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Appointments & Consultations';
$pageSection = 'appointments';

$error = '';
$success = '';

$validStatuses = ['Pending', 'Confirmed', 'Rescheduled', 'Completed', 'Cancelled'];

// Handle Quick Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $apptId = (int)($_POST['appointment_id'] ?? 0);
        $newStatus = in_array($_POST['new_status'] ?? '', $validStatuses) ? $_POST['new_status'] : '';

        if ($apptId > 0 && !empty($newStatus)) {
            try {
                $appt = $pdo->query("SELECT * FROM appointments WHERE id = {$apptId}")->fetch(PDO::FETCH_ASSOC);
                if ($appt) {
                    $pdo->prepare("UPDATE appointments SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                        ->execute([$newStatus, $apptId]);

                    // If linked to a client, notify client
                    if (!empty($appt['client_id'])) {
                        $pdo->prepare("INSERT INTO notifications (client_id, type, title, message, reference_id, is_read) VALUES (?, 'Appointment', ?, ?, ?, 0)")
                            ->execute([
                                $appt['client_id'],
                                "Appointment {$newStatus}",
                                "Your consultation on {$appt['preferred_date']} has been marked as {$newStatus}.",
                                $apptId
                            ]);
                    }

                    logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Appointment Status Updated', 'appointments', $apptId, "Changed appointment #{$apptId} status to {$newStatus}");
                    $success = "Appointment #{$apptId} updated to {$newStatus}.";
                }
            } catch (Exception $e) {
                $error = 'Failed to update appointment status.';
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
    $where[] = "(a.full_name LIKE ? OR a.business_name LIKE ? OR a.email LIKE ? OR a.phone LIKE ? OR a.service LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
}

if (!empty($statusFilter)) {
    $where[] = "a.status = ?";
    $params[] = $statusFilter;
}

$whereClause = implode(' AND ', $where);

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments a WHERE {$whereClause}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT a.*, c.company_name AS client_company_name
    FROM appointments a
    LEFT JOIN clients c ON a.client_id = c.id
    WHERE {$whereClause}
    ORDER BY a.id DESC
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Consultations & Appointments</h1>
        <p class="page-subtitle">Schedule, review, confirm, and complete technical discovery meetings.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Filters -->
<form method="GET" action="appointments.php" class="filter-bar">
    <div class="filter-group">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--admin-text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="search" class="filter-input" placeholder="Search by name, company, email, phone..." value="<?= htmlspecialchars($search) ?>">
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
        <a href="appointments.php" class="btn btn-sm btn-outline" style="color: var(--admin-danger);">Clear</a>
    <?php endif; ?>
</form>

<!-- Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Appt #</th>
                    <th>Client / Prospect</th>
                    <th>Service</th>
                    <th>Budget</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appointments)): ?>
                    <tr><td colspan="8" class="empty-state">No appointments recorded yet.</td></tr>
                <?php else: foreach ($appointments as $app): 
                    $statusClass = match($app['status']) {
                        'Confirmed' => 'badge-success',
                        'Completed' => 'badge-primary',
                        'Cancelled' => 'badge-danger',
                        'Rescheduled' => 'badge-warning',
                        default => 'badge-neutral'
                    };
                ?>
                    <tr>
                        <td><code>#APT-<?= str_pad($app['id'], 3, '0', STR_PAD_LEFT) ?></code></td>
                        <td>
                            <strong><?= htmlspecialchars($app['full_name']) ?></strong>
                            <?php if (!empty($app['business_name'])): ?>
                                <div style="font-size: 11.5px; color: var(--admin-text-muted);"><?= htmlspecialchars($app['business_name']) ?></div>
                            <?php endif; ?>
                            <div style="font-size: 11px; color: #64748b;"><?= htmlspecialchars($app['email']) ?> &bull; <?= htmlspecialchars($app['phone']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($app['service']) ?></td>
                        <td><span style="font-size: 12px; font-weight: 600; color: #334155;"><?= htmlspecialchars($app['budget_range'] ?: 'Not Specified') ?></span></td>
                        <td>
                            <strong><?= htmlspecialchars($app['preferred_date'] ?? 'N/A') ?></strong>
                            <div style="font-size: 11px; color: var(--admin-text-muted);"><?= htmlspecialchars($app['preferred_time'] ?? '') ?></div>
                        </td>
                        <td>
                            <form method="POST" action="appointments.php" style="margin: 0;">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                <select name="new_status" onchange="this.form.submit()" style="font-size: 11.5px; font-weight: 600; padding: 3px 8px; border-radius: 9999px; border: 1px solid var(--admin-border); background: #ffffff; cursor: pointer;">
                                    <?php foreach ($validStatuses as $st): ?>
                                        <option value="<?= $st ?>" <?= $app['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td style="font-size: 12px; color: var(--admin-text-muted);"><?= date('d M Y', strtotime($app['created_at'])) ?></td>
                        <td>
                            <a href="appointment-details.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-outline">Details & Notes</a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <div>Showing <?= ($offset + 1) ?> to <?= min($totalRecords, $offset + $perPage) ?> of <?= $totalRecords ?> appointments</div>
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
