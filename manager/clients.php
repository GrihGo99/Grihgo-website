<?php
/**
 * GrihGO Digital & Tech — Manager Client Directory
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Assigned Clients';
$pageSection = 'clients';
$managerId = (int)$currentManager['id'];

$search = trim($_GET['search'] ?? '');

// Query clients linked to manager's projects or leads, or active clients
$params = [$managerId, $managerId];
$sql = "
    SELECT DISTINCT c.*,
           (SELECT COUNT(*) FROM projects WHERE client_id = c.id) AS project_count,
           (SELECT COUNT(*) FROM support_tickets WHERE client_id = c.id AND status NOT IN ('Resolved', 'Closed')) AS open_tickets
    FROM clients c
    LEFT JOIN projects p ON p.client_id = c.id
    LEFT JOIN leads l ON l.email = c.email
    WHERE (p.assigned_to = ? OR l.assigned_to = ? OR ? IN ('admin', 'super_admin') OR 1=1)
";
$params[] = $currentManager['role'];

if ($search) {
    $sql .= " AND (c.company_name LIKE ? OR c.contact_person LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY c.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-content">
    <div class="admin-header-bar">
        <div>
            <h1 class="page-title">Client Directory</h1>
            <p class="page-subtitle">Access authorized client accounts, active delivery engagements, and direct communication lines.</p>
        </div>
        <div style="font-size: 0.9rem; color: #4b5563; background: #fff; padding: 8px 14px; border-radius: 8px; border: 1px solid #e5e7eb;">
            Total Clients: <strong style="color: #059669;"><?= count($clients) ?></strong>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="admin-card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 16px 20px;">
            <form method="GET" action="" style="display: flex; gap: 12px; align-items: center;">
                <div style="flex: 1;">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search client company, contact person, email, or phone..." class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
                <button type="submit" class="btn-primary" style="background: #059669; border: none; padding: 8px 16px; border-radius: 6px; color: #fff; cursor: pointer; font-weight: 500;">
                    Search
                </button>
                <?php if ($search): ?>
                    <a href="clients.php" style="color: #6b7280; text-decoration: none; font-size: 0.9rem; padding: 8px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Clients Table -->
    <div class="admin-card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Client #</th>
                            <th>Company &amp; Contact</th>
                            <th>Contact Info</th>
                            <th>Engagements</th>
                            <th>Status</th>
                            <th>Direct Connect</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clients)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">
                                    No clients found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clients as $c): ?>
                                <tr>
                                    <td style="font-weight: 600; color: #6b7280;">#<?= $c['id'] ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: #111827;"><?= htmlspecialchars($c['company_name'] ?: 'Independent Account') ?></div>
                                        <div style="font-size: 0.85rem; color: #4b5563;"><?= htmlspecialchars($c['contact_person']) ?></div>
                                    </td>
                                    <td style="font-size: 0.85rem;">
                                        <div><a href="mailto:<?= htmlspecialchars($c['email']) ?>" style="color: #0256c4;"><?= htmlspecialchars($c['email']) ?></a></div>
                                        <div><a href="tel:<?= htmlspecialchars($c['phone']) ?>" style="color: #059669; font-weight: 500;"><?= htmlspecialchars($c['phone']) ?></a></div>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; background: #ecfdf5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-weight: 600;">
                                            <?= (int)$c['project_count'] ?> Projects
                                        </span>
                                        <?php if ((int)$c['open_tickets'] > 0): ?>
                                            <span style="font-size: 0.85rem; background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px; font-weight: 600; margin-left: 4px;">
                                                <?= (int)$c['open_tickets'] ?> Tickets
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: #d1fae5; color: #065f46; font-size: 0.8rem; padding: 3px 8px; border-radius: 9999px; font-weight: 600;">
                                            <?= htmlspecialchars($c['status'] ?? 'Active') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 6px;">
                                            <a href="tel:<?= htmlspecialchars($c['phone']) ?>" title="Call Client" style="background: #0256c4; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; font-size: 0.75rem;">
                                                Call
                                            </a>
                                            <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $c['phone']) ?>?text=<?= urlencode('Hello ' . $c['contact_person'] . ', this is ' . $currentManager['full_name'] . ' from GrihGO Digital & Tech.') ?>" target="_blank" title="WhatsApp" style="background: #25D366; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; font-size: 0.75rem;">
                                                WhatsApp
                                            </a>
                                            <a href="projects.php?search=<?= urlencode($c['company_name'] ?: $c['contact_person']) ?>" style="background: #f3f4f6; color: #374151; padding: 5px 8px; border-radius: 4px; text-decoration: none; font-size: 0.75rem; font-weight: 500;">
                                                Projects &rarr;
                                            </a>
                                        </div>
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
