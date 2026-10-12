<?php
/**
 * GrihGO Digital & Tech — Manager Project Workspace
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Assigned Projects';
$pageSection = 'projects';
$managerId = (int)$currentManager['id'];

$validStatuses = ['Planning', 'In Progress', 'Testing', 'Review', 'Completed', 'On Hold'];
$statusFilter = in_array($_GET['status'] ?? '', $validStatuses, true) ? $_GET['status'] : '';
$search = trim($_GET['search'] ?? '');

// Build query for projects assigned to this manager or unassigned
$params = [$managerId];
$sql = "
    SELECT p.*, c.company_name, c.contact_person, c.email AS client_email, c.phone AS client_phone
    FROM projects p
    LEFT JOIN clients c ON p.client_id = c.id
    WHERE (p.assigned_to = ? OR p.assigned_to IS NULL)
";

if ($statusFilter) {
    $sql .= " AND p.status = ?";
    $params[] = $statusFilter;
}

if ($search) {
    $sql .= " AND (p.name LIKE ? OR c.company_name LIKE ? OR c.contact_person LIKE ? OR p.service LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-content">
    <div class="admin-header-bar">
        <div>
            <h1 class="page-title">Assigned Projects &amp; Delivery</h1>
            <p class="page-subtitle">Track project milestones, update sprint progress, manage delivery tasks, and sync with clients.</p>
        </div>
        <div style="font-size: 0.9rem; color: #4b5563; background: #fff; padding: 8px 14px; border-radius: 8px; border: 1px solid #e5e7eb;">
            Active Projects: <strong style="color: #059669;"><?= count($projects) ?></strong>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="admin-card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 16px 20px;">
            <form method="GET" action="" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 240px;">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search project, client, or service..." class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
                <div style="min-width: 180px;">
                    <select name="status" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <?php foreach ($validStatuses as $st): ?>
                            <option value="<?= $st ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="background: #059669; border: none; padding: 8px 16px; border-radius: 6px; color: #fff; cursor: pointer; font-weight: 500;">
                    Filter
                </button>
                <?php if ($search || $statusFilter): ?>
                    <a href="projects.php" style="color: #6b7280; text-decoration: none; font-size: 0.9rem; padding: 8px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="admin-card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Project &amp; Client</th>
                            <th>Service Domain</th>
                            <th>Progress</th>
                            <th>Timeline</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($projects)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                                    No assigned projects found matching your criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($projects as $p): ?>
                                <tr>
                                    <td style="font-weight: 600; color: #6b7280;">#<?= $p['id'] ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: #111827;">
                                            <a href="project-details.php?id=<?= $p['id'] ?>" style="color: #111827; text-decoration: none;">
                                                <?= htmlspecialchars($p['name']) ?>
                                            </a>
                                        </div>
                                        <div style="font-size: 0.85rem; color: #6b7280;">
                                            Client: <?= htmlspecialchars($p['company_name'] ?: ($p['contact_person'] ?: 'Unassigned')) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; background: #f3f4f6; color: #374151; padding: 3px 8px; border-radius: 4px; font-weight: 500;">
                                            <?= htmlspecialchars($p['service']) ?>
                                        </span>
                                    </td>
                                    <td style="min-width: 140px;">
                                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 4px; font-weight: 600; color: #374151;">
                                            <span>Progress</span>
                                            <span><?= (int)$p['progress'] ?>%</span>
                                        </div>
                                        <div style="background: #e5e7eb; border-radius: 9999px; height: 7px; overflow: hidden;">
                                            <div style="background: <?= $p['progress'] == 100 ? '#10b981' : '#0256c4' ?>; width: <?= (int)$p['progress'] ?>%; height: 100%;"></div>
                                        </div>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #4b5563;">
                                        <div>Start: <?= !empty($p['start_date']) ? date('d M Y', strtotime($p['start_date'])) : 'TBD' ?></div>
                                        <div style="color: #6b7280;">Target: <?= !empty($p['expected_completion']) ? date('d M Y', strtotime($p['expected_completion'])) : 'TBD' ?></div>
                                    </td>
                                    <td>
                                        <span class="badge" style="
                                            <?php
                                            switch ($p['status']) {
                                                case 'Completed': echo 'background: #d1fae5; color: #065f46;'; break;
                                                case 'In Progress': echo 'background: #dbeafe; color: #1e40af;'; break;
                                                case 'Testing': echo 'background: #e0e7ff; color: #3730a3;'; break;
                                                case 'Review': echo 'background: #fef3c7; color: #92400e;'; break;
                                                case 'On Hold': echo 'background: #fee2e2; color: #991b1b;'; break;
                                                default: echo 'background: #f3f4f6; color: #374151;';
                                            }
                                            ?>
                                            font-size: 0.8rem; padding: 4px 10px; border-radius: 9999px; font-weight: 600;
                                        ">
                                            <?= htmlspecialchars($p['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="project-details.php?id=<?= $p['id'] ?>" class="btn-primary" style="background: #059669; text-decoration: none; padding: 6px 12px; font-size: 0.85rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            Manage &rarr;
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
