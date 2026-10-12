<?php
/**
 * GrihGO Digital & Tech — Manager Leads Console
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Assigned Leads';
$pageSection = 'leads';
$managerId = (int)$currentManager['id'];

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$validStatuses = ['New', 'Contacted', 'Interested', 'Proposal Sent', 'Negotiation', 'Converted', 'Lost'];

// Quick status update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_status') {
    if (validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $leadId = (int)($_POST['lead_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        if (in_array($newStatus, $validStatuses, true)) {
            $stmt = $pdo->prepare("UPDATE leads SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$newStatus, $leadId]);

            // Add notification for admin
            addSystemNotification(
                null,
                'Lead Status Updated',
                "Manager {$currentManager['full_name']} updated Lead #{$leadId} status to '{$newStatus}'.",
                'admin',
                'admin/leads.php'
            );
        }
    }
    header('Location: leads.php' . ($statusFilter ? '?status=' . urlencode($statusFilter) : ''));
    exit;
}

// Build query
$params = [$managerId];
$sql = "SELECT * FROM leads WHERE (assigned_to = ? OR assigned_to IS NULL)";

if ($statusFilter && in_array($statusFilter, $validStatuses, true)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

if ($search) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR company_name LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card" style="background: #ffffff; border-radius: 12px; border: 1px solid var(--admin-border); padding: 24px; box-shadow: var(--admin-shadow-sm);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <h2 style="font-size: 1.25rem; margin: 0 0 4px 0; color: #0f172a;">Assigned Lead Pipeline</h2>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Manage and follow up on customer leads assigned to your desk.</p>
        </div>

        <!-- Filter bar -->
        <form method="GET" action="leads.php" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <select name="status" class="form-control" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 6px; border: 1px solid var(--admin-border); font-size: 0.85rem;">
                <option value="">All Statuses</option>
                <?php foreach ($validStatuses as $st): ?>
                    <option value="<?= $st ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Search name/phone..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid var(--admin-border); font-size: 0.85rem;">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <?php if ($statusFilter || $search): ?>
                <a href="leads.php" class="btn btn-sm btn-ghost">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($leads)): ?>
        <p style="text-align: center; color: #64748b; padding: 40px; font-size: 0.95rem;">No leads found matching current filter.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--admin-border); text-align: left; color: #64748b;">
                        <th style="padding: 12px 10px;">ID</th>
                        <th style="padding: 12px 10px;">Lead Contact</th>
                        <th style="padding: 12px 10px;">Service / Product</th>
                        <th style="padding: 12px 10px;">Budget</th>
                        <th style="padding: 12px 10px;">Status</th>
                        <th style="padding: 12px 10px;">Quick Status</th>
                        <th style="padding: 12px 10px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $l): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px 10px; font-weight: 700; color: #64748b;">#<?= $l['id'] ?></td>
                            <td style="padding: 12px 10px;">
                                <strong style="color: #0f172a; display: block;"><?= htmlspecialchars($l['name']) ?></strong>
                                <span style="font-size: 0.8rem; color: #059669; font-weight: 600;">📞 <?= htmlspecialchars($l['phone']) ?></span>
                                <?php if ($l['email']): ?>
                                    <span style="font-size: 0.775rem; color: #64748b; display: block;">✉️ <?= htmlspecialchars($l['email']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 10px; color: #334155;">
                                <?= htmlspecialchars(($l['service_interest'] ?? ($l['service'] ?? 'General Inquiry'))) ?>
                            </td>
                            <td style="padding: 12px 10px; color: #64748b; font-size: 0.8rem;">
                                <?= htmlspecialchars(($l['budget_range'] ?? ($l['budget'] ?? 'Not specified'))) ?>
                            </td>
                            <td style="padding: 12px 10px;">
                                <span class="status-badge" style="padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; 
                                    background: <?= $l['status'] === 'Converted' ? '#ecfdf5' : ($l['status'] === 'New' ? '#eff6ff' : '#f1f5f9') ?>;
                                    color: <?= $l['status'] === 'Converted' ? '#059669' : ($l['status'] === 'New' ? '#0256c4' : '#334155') ?>;">
                                    <?= htmlspecialchars($l['status'] ?? 'New') ?>
                                </span>
                            </td>
                            <td style="padding: 12px 10px;">
                                <form method="POST" action="leads.php" style="display: inline-block;">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="quick_status">
                                    <input type="hidden" name="lead_id" value="<?= $l['id'] ?>">
                                    <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.775rem;">
                                        <?php foreach ($validStatuses as $st): ?>
                                            <option value="<?= $st ?>" <?= ($l['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td style="padding: 12px 10px; text-align: right;">
                                <a href="lead-details.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-primary" style="padding: 4px 12px; font-size: 0.775rem;">
                                    View &amp; Notes
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
