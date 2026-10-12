<?php
/**
 * GrihGO Digital & Tech — Project Delivery Management
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Project Delivery';
$pageSection = 'projects';

$error = '';
$success = '';

$validStatuses = ['Planning', 'In Progress', 'Testing', 'Review', 'Completed', 'On Hold'];

// Handle Create Project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_project') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $clientId           = (int)($_POST['client_id'] ?? 0);
        $name               = sanitizeInput($_POST['name'] ?? '');
        $service            = sanitizeInput($_POST['service'] ?? '');
        $description        = sanitizeInput($_POST['description'] ?? '');
        $startDate          = sanitizeInput($_POST['start_date'] ?? date('Y-m-d'));
        $expectedCompletion = sanitizeInput($_POST['expected_completion'] ?? '');
        $status             = in_array($_POST['status'] ?? '', $validStatuses) ? $_POST['status'] : 'Planning';
        $progress           = min(100, max(0, (int)($_POST['progress'] ?? 0)));
        $assignedTeam       = sanitizeInput($_POST['assigned_team'] ?? 'GrihGO Engineering Team');

        if ($clientId <= 0 || empty($name) || empty($service)) {
            $error = 'Please specify Client, Project Name, and Core Service.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO projects (client_id, name, service, description, start_date, expected_completion, status, progress, assigned_team)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$clientId, $name, $service, $description, $startDate, $expectedCompletion, $status, $progress, $assignedTeam]);
                $projId = (int)$pdo->lastInsertId();

                // Create client notification
                $pdo->prepare("INSERT INTO notifications (client_id, type, title, message, reference_id, is_read) VALUES (?, 'Project', 'New Project Initialized', ?, ?, 0)")
                    ->execute([$clientId, "Your project '{$name}' has been created in our delivery pipeline.", $projId]);

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Project Created', 'projects', $projId, "Created project '{$name}' for client #{$clientId}");
                $success = "Project '{$name}' initialized successfully!";
            } catch (Exception $e) {
                $error = 'Failed to create project record.';
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
    $where[] = "(p.name LIKE ? OR p.service LIKE ? OR c.company_name LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like]);
}

if (!empty($statusFilter)) {
    $where[] = "p.status = ?";
    $params[] = $statusFilter;
}

if ($clientFilter > 0) {
    $where[] = "p.client_id = ?";
    $params[] = $clientFilter;
}

$whereClause = implode(' AND ', $where);

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM projects p LEFT JOIN clients c ON p.client_id = c.id WHERE {$whereClause}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT p.*, c.company_name, c.contact_person,
           (SELECT COUNT(*) FROM project_tasks pt WHERE pt.project_id = p.id) AS total_tasks,
           (SELECT COUNT(*) FROM project_tasks pt WHERE pt.project_id = p.id AND pt.status = 'Completed') AS completed_tasks
    FROM projects p
    LEFT JOIN clients c ON p.client_id = c.id
    WHERE {$whereClause}
    ORDER BY p.id DESC
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch clients for new project modal
$clientsList = $pdo->query("SELECT id, company_name, contact_person FROM clients WHERE status = 'active' ORDER BY company_name ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Project Delivery</h1>
        <p class="page-subtitle">Track project progress, milestone sprints, and client delivery schedules.</p>
    </div>
    <div class="page-actions">
        <a href="project-tasks.php" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
            <span>All Tasks</span>
        </a>
        <button type="button" class="btn btn-primary" onclick="openAdminModal('newProjectModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>+ Create Project</span>
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
<form method="GET" action="projects.php" class="filter-bar">
    <div class="filter-group">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--admin-text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="search" class="filter-input" placeholder="Search project name, service or client..." value="<?= htmlspecialchars($search) ?>">
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
        <a href="projects.php" class="btn btn-sm btn-outline" style="color: var(--admin-danger);">Clear</a>
    <?php endif; ?>
</form>

<!-- Projects Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Timeline</th>
                    <th>Sprint Tasks</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                    <tr><td colspan="8" class="empty-state">No projects found.</td></tr>
                <?php else: foreach ($projects as $p): 
                    $statusClass = match($p['status']) {
                        'Completed' => 'badge-success',
                        'In Progress' => 'badge-primary',
                        'Testing', 'Review' => 'badge-purple',
                        'On Hold' => 'badge-danger',
                        default => 'badge-warning'
                    };
                ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($p['name']) ?></strong>
                            <div style="font-size: 11px; color: var(--admin-text-muted);"><?= htmlspecialchars($p['assigned_team'] ?? 'Team') ?></div>
                        </td>
                        <td>
                            <div><strong><?= htmlspecialchars($p['company_name'] ?? 'Client #' . $p['client_id']) ?></strong></div>
                            <div style="font-size: 11px; color: var(--admin-text-muted);"><?= htmlspecialchars($p['contact_person'] ?? '') ?></div>
                        </td>
                        <td><?= htmlspecialchars($p['service']) ?></td>
                        <td>
                            <div style="font-size: 12px;"><?= date('d M Y', strtotime($p['start_date'])) ?></div>
                            <div style="font-size: 11px; color: var(--admin-text-muted);">Due: <?= !empty($p['expected_completion']) ? date('d M Y', strtotime($p['expected_completion'])) : 'Ongoing' ?></div>
                        </td>
                        <td>
                            <span style="font-size: 12px; font-weight: 600; color: #475569;">
                                <?= (int)$p['completed_tasks'] ?> / <?= (int)$p['total_tasks'] ?> done
                            </span>
                        </td>
                        <td style="min-width: 140px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                    <div style="width: <?= min(100, max(0, (int)$p['progress'])) ?>%; height: 100%; background: var(--admin-primary); border-radius: 3px;"></div>
                                </div>
                                <span style="font-size: 11.5px; font-weight: 700;"><?= (int)$p['progress'] ?>%</span>
                            </div>
                        </td>
                        <td><span class="badge <?= $statusClass ?>"><span class="badge-dot"></span><?= htmlspecialchars($p['status']) ?></span></td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="project-details.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Manage</a>
                                <a href="project-tasks.php?project_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline" title="Tasks">Tasks</a>
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
            <div>Showing <?= ($offset + 1) ?> to <?= min($totalRecords, $offset + $perPage) ?> of <?= $totalRecords ?> projects</div>
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

<!-- Modal: New Project -->
<div id="newProjectModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 600px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Initialize New Delivery Project</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('newProjectModal')">&times;</button>
        </div>
        <form method="POST" action="projects.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="create_project">
            <div class="admin-modal-body">
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
                    <label class="form-label">Project Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. NextGen Web Portal & App Engine" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Core Solution Category *</label>
                    <input type="text" name="service" class="form-control" placeholder="e.g. Website Development / WhatsApp CRM" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Kickoff Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Target Completion</label>
                        <input type="date" name="expected_completion" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Initial Status</label>
                        <select name="status" class="form-control">
                            <?php foreach ($validStatuses as $st): ?>
                                <option value="<?= $st ?>" <?= $st === 'In Progress' ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assigned Delivery Team</label>
                        <input type="text" name="assigned_team" class="form-control" value="GrihGO Core Tech Group">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Scope Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Brief technical deliverables, architectural scope..."></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('newProjectModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Initialize Project</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
