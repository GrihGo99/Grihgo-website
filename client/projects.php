<?php
/**
 * GrihGO Digital & Tech — Client Projects Directory
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/includes/auth.php';

$portalPageTitle = 'My Projects';
$portalHeaderTitle = 'Active Projects & Engineering Deliverables';
$current_portal_page = 'projects';

$pdo = Database::getConnection();

// Fetch all projects strictly scoped to this authenticated client
$stmt = $pdo->prepare("
    SELECT p.*,
           (SELECT COUNT(*) FROM project_tasks WHERE project_id = p.id) AS total_tasks,
           (SELECT COUNT(*) FROM project_tasks WHERE project_id = p.id AND status = 'Completed') AS completed_tasks
    FROM projects p
    WHERE p.client_id = :cid
    ORDER BY CASE WHEN p.status = 'In Progress' THEN 1 WHEN p.status = 'Planning' THEN 2 ELSE 3 END, p.updated_at DESC
");
$stmt->execute([':cid' => $clientId]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--portal-text-main); margin: 0 0 4px 0;">Assigned Projects</h2>
        <p style="font-size: 0.85rem; color: var(--portal-text-muted); margin: 0;">Real-time engineering progress, deliverables, and team sprint trackers</p>
    </div>
    <a href="services.php" class="btn-portal-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        <span>Request New Project</span>
    </a>
</div>

<?php if (empty($projects)): ?>
    <div class="portal-card">
        <div class="portal-empty-state">
            <div class="portal-empty-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <div class="portal-empty-title">No Projects Found</div>
            <p class="portal-empty-desc">No active or archived projects are currently assigned to your client account.</p>
            <a href="services.php" class="btn-portal-primary">Browse GrihGO Services &rarr;</a>
        </div>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 24px;">
        <?php foreach ($projects as $proj): ?>
            <?php
            $statusClass = 'badge-in-progress';
            if ($proj['status'] === 'Completed') $statusClass = 'badge-completed';
            elseif ($proj['status'] === 'On Hold') $statusClass = 'badge-pending';
            elseif ($proj['status'] === 'Review' || $proj['status'] === 'Testing') $statusClass = 'badge-open';
            ?>
            <div class="portal-project-card">
                <div class="portal-project-top">
                    <div>
                        <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--portal-primary); letter-spacing: 0.5px;">
                            <?= htmlspecialchars($proj['service'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <a href="project-details.php?id=<?= (int)$proj['id'] ?>" class="portal-project-name" style="margin-top: 4px; display: inline-block;">
                            <?= htmlspecialchars($proj['name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </div>
                    <span class="badge-status <?= $statusClass ?>">
                        <?= htmlspecialchars($proj['status'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>

                <p style="font-size: 0.85rem; color: #475569; margin: 8px 0 16px 0; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?= htmlspecialchars($proj['description'] ?? 'Custom enterprise digital implementation.', ENT_QUOTES, 'UTF-8') ?>
                </p>

                <!-- Progress Meter -->
                <div style="margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 6px;">
                        <span style="color: var(--portal-text-muted);">Overall Completion</span>
                        <strong style="color: var(--portal-text-main);"><?= (int)$proj['progress'] ?>%</strong>
                    </div>
                    <div class="portal-progress-bar-bg">
                        <div class="portal-progress-bar-fill" style="width: <?= (int)$proj['progress'] ?>%;"></div>
                    </div>
                </div>

                <!-- Metadata Row -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.78rem; padding: 12px 0; border-top: 1px solid var(--portal-border); border-bottom: 1px solid var(--portal-border); margin-bottom: 16px; color: var(--portal-text-muted);">
                    <div>
                        <span>Start:</span>
                        <strong style="color: var(--portal-text-main); display: block;"><?= htmlspecialchars($proj['start_date'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div>
                        <span>Expected:</span>
                        <strong style="color: var(--portal-text-main); display: block;"><?= htmlspecialchars($proj['expected_completion'] ?? 'Ongoing', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div>
                        <span>Assigned:</span>
                        <strong style="color: var(--portal-text-main); display: block;"><?= htmlspecialchars($proj['assigned_team'] ?? 'GrihGO Tech', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div>
                        <span>Tasks Done:</span>
                        <strong style="color: var(--portal-text-main); display: block;"><?= (int)$proj['completed_tasks'] ?> / <?= (int)$proj['total_tasks'] ?></strong>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: #94a3b8;">
                        Updated <?= date('d M Y', strtotime($proj['updated_at'])) ?>
                    </span>
                    <a href="project-details.php?id=<?= (int)$proj['id'] ?>" class="btn-portal-outline btn-portal-sm" style="font-weight: 600;">
                        <span>View Details</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
