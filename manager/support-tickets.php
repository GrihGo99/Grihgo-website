<?php
/**
 * GrihGO Digital & Tech — Manager Support Desk & Ticket Queue
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Support Tickets';
$pageSection = 'support-tickets';
$managerId = (int)$currentManager['id'];

$validStatuses = ['Open', 'In Progress', 'Waiting for Client', 'Resolved', 'Closed'];
$validPriorities = ['Low', 'Medium', 'High', 'Urgent'];

$statusFilter = in_array($_GET['status'] ?? '', $validStatuses, true) ? $_GET['status'] : '';
$priorityFilter = in_array($_GET['priority'] ?? '', $validPriorities, true) ? $_GET['priority'] : '';
$search = trim($_GET['search'] ?? '');

$params = [$managerId];
$sql = "
    SELECT t.*, c.company_name, c.contact_person
    FROM support_tickets t
    LEFT JOIN clients c ON t.client_id = c.id
    WHERE (t.assigned_to = ? OR t.assigned_to IS NULL)
";

if ($statusFilter) {
    $sql .= " AND t.status = ?";
    $params[] = $statusFilter;
}

if ($priorityFilter) {
    $sql .= " AND t.priority = ?";
    $params[] = $priorityFilter;
}

if ($search) {
    $sql .= " AND (t.ticket_number LIKE ? OR t.subject LIKE ? OR c.company_name LIKE ? OR c.contact_person LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY CASE WHEN t.status = 'Open' THEN 1 WHEN t.status = 'In Progress' THEN 2 ELSE 3 END, t.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-content">
    <div class="admin-header-bar">
        <div>
            <h1 class="page-title">Client Support Tickets</h1>
            <p class="page-subtitle">Troubleshoot technical issues, clarify project deliverables, and maintain high service satisfaction.</p>
        </div>
        <div style="font-size: 0.9rem; color: #4b5563; background: #fff; padding: 8px 14px; border-radius: 8px; border: 1px solid #e5e7eb;">
            Assigned Queue: <strong style="color: #f59e0b;"><?= count($tickets) ?></strong>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="admin-card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 16px 20px;">
            <form method="GET" action="" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 240px;">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search ticket #, subject, client..." class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
                <div style="min-width: 160px;">
                    <select name="status" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <?php foreach ($validStatuses as $st): ?>
                            <option value="<?= $st ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width: 140px;">
                    <select name="priority" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;" onchange="this.form.submit()">
                        <option value="">All Priorities</option>
                        <?php foreach ($validPriorities as $pr): ?>
                            <option value="<?= $pr ?>" <?= $priorityFilter === $pr ? 'selected' : '' ?>><?= $pr ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="background: #059669; border: none; padding: 8px 16px; border-radius: 6px; color: #fff; cursor: pointer; font-weight: 500;">
                    Filter
                </button>
                <?php if ($search || $statusFilter || $priorityFilter): ?>
                    <a href="support-tickets.php" style="color: #6b7280; text-decoration: none; font-size: 0.9rem; padding: 8px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Ticket List Table -->
    <div class="admin-card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Subject &amp; Client</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">
                                    No support tickets found in your queue.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $t): ?>
                                <tr>
                                    <td style="font-weight: 600; color: #0256c4;">
                                        <a href="ticket-details.php?id=<?= $t['id'] ?>" style="color: #0256c4; text-decoration: none;">
                                            #<?= htmlspecialchars($t['ticket_number']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #111827;">
                                            <a href="ticket-details.php?id=<?= $t['id'] ?>" style="color: #111827; text-decoration: none;">
                                                <?= htmlspecialchars($t['subject']) ?>
                                            </a>
                                        </div>
                                        <div style="font-size: 0.85rem; color: #6b7280;">
                                            Client: <?= htmlspecialchars($t['company_name'] ?: ($t['contact_person'] ?: 'Client Account')) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.75rem; font-weight: 600; padding: 2px 8px; border-radius: 4px;
                                            <?php
                                            switch ($t['priority']) {
                                                case 'Urgent': echo 'background: #fee2e2; color: #991b1b;'; break;
                                                case 'High': echo 'background: #fed7aa; color: #9a3412;'; break;
                                                case 'Medium': echo 'background: #dbeafe; color: #1e40af;'; break;
                                                default: echo 'background: #f3f4f6; color: #4b5563;';
                                            }
                                            ?>">
                                            <?= htmlspecialchars($t['priority']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="
                                            <?php
                                            switch ($t['status']) {
                                                case 'Open': echo 'background: #fee2e2; color: #991b1b;'; break;
                                                case 'In Progress': echo 'background: #dbeafe; color: #1e40af;'; break;
                                                case 'Waiting for Client': echo 'background: #fef3c7; color: #92400e;'; break;
                                                case 'Resolved': echo 'background: #d1fae5; color: #065f46;'; break;
                                                case 'Closed': echo 'background: #f3f4f6; color: #4b5563;'; break;
                                                default: echo 'background: #f3f4f6; color: #374151;';
                                            }
                                            ?>
                                            font-size: 0.8rem; padding: 4px 10px; border-radius: 9999px; font-weight: 600;
                                        ">
                                            <?= htmlspecialchars($t['status']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #6b7280;">
                                        <?= date('d M Y, h:i A', strtotime($t['created_at'])) ?>
                                    </td>
                                    <td>
                                        <a href="ticket-details.php?id=<?= $t['id'] ?>" class="btn-primary" style="background: #059669; text-decoration: none; padding: 6px 12px; font-size: 0.85rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            Reply &rarr;
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
