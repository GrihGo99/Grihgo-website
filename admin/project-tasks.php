<?php
/**
 * GrihGO Digital & Tech — Project Task Management Console
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Project Tasks & Sprints';
$pageSection = 'project-tasks';

$error = '';
$success = '';

$validStatuses = ['Pending', 'In Progress', 'Completed', 'Blocked'];
$validPriorities = ['Low', 'Medium', 'High', 'Urgent'];

// Handle Quick Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_task_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $newStatus = in_array($_POST['new_status'] ?? '', $validStatuses) ? $_POST['new_status'] : '';
        if ($taskId > 0 && !empty($newStatus)) {
            try {
                $compDate = ($newStatus === 'Completed') ? date('Y-m-d') : null;
                $pdo->prepare("UPDATE project_tasks SET status = ?, completed_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                    ->execute([$newStatus, $compDate, $taskId]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Task Status Updated', 'project_tasks', $taskId, "Updated task #{$taskId} status to {$newStatus}");
                $success = "Task status updated to {$newStatus}!";
            } catch (Exception $e) {
                $error = 'Failed to update task.';
            }
        }
    }
}

// Handle Create Task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_task') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $projectId    = (int)($_POST['project_id'] ?? 0);
        $name         = sanitizeInput($_POST['name'] ?? '');
        $description  = sanitizeInput($_POST['description'] ?? '');
        $assignedTeam = sanitizeInput($_POST['assigned_team'] ?? 'Dev Pod');
        $dueDate      = sanitizeInput($_POST['due_date'] ?? '');
        $priority     = in_array($_POST['priority'] ?? '', $validPriorities) ? $_POST['priority'] : 'Medium';
        $status       = in_array($_POST['status'] ?? '', $validStatuses) ? $_POST['status'] : 'Pending';

        if ($projectId <= 0 || empty($name)) {
            $error = 'Please select a project and enter task title.';
        } else {
            try {
                $pdo->prepare("INSERT INTO project_tasks (project_id, name, description, assigned_team, due_date, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$projectId, $name, $description, $assignedTeam, $dueDate, $priority, $status]);
                $newId = (int)$pdo->lastInsertId();
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Task Created', 'project_tasks', $newId, "Created task '{$name}' for project #{$projectId}");
                $success = "Task '{$name}' created successfully!";
            } catch (Exception $e) {
                $error = 'Failed to create task.';
            }
        }
    }
}

// Search and Filter
$search = sanitizeInput($_GET['search'] ?? '');
$projectFilter = (int)($_GET['project_id'] ?? 0);
$statusFilter = in_array($_GET['status'] ?? '', $validStatuses) ? $_GET['status'] : '';
$priorityFilter = in_array($_GET['priority'] ?? '', $validPriorities) ? $_GET['priority'] : '';

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(pt.name LIKE ? OR pt.description LIKE ? OR p.name LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like]);
}

if ($projectFilter > 0) {
    $where[] = "pt.project_id = ?";
    $params[] = $projectFilter;
}

if (!empty($statusFilter)) {
    $where[] = "pt.status = ?";
    $params[] = $statusFilter;
}

if (!empty($priorityFilter)) {
    $where[] = "pt.priority = ?";
    $params[] = $priorityFilter;
}

$whereClause = implode(' AND ', $where);

// Pagination
$perPage = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM project_tasks pt LEFT JOIN projects p ON pt.project_id = p.id WHERE {$whereClause}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT pt.*, p.name AS project_name, c.company_name
    FROM project_tasks pt
    LEFT JOIN projects p ON pt.project_id = p.id
    LEFT JOIN clients c ON p.client_id = c.id
    WHERE {$whereClause}
    ORDER BY pt.id DESC
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all active projects for dropdown
$projectsList = $pdo->query("SELECT id, name FROM projects ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Project Tasks & Sprints</h1>
        <p class="page-subtitle">Granular task tracking, sprint assignments, and cross-project backlog.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" onclick="openAdminModal('newTaskModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>+ Create Task</span>
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
<form method="GET" action="project-tasks.php" class="filter-bar">
    <div class="filter-group">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--admin-text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="search" class="filter-input" placeholder="Search tasks or descriptions..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div style="min-width: 170px;">
        <select name="project_id" class="filter-input" onchange="this.form.submit()">
            <option value="0">All Projects</option>
            <?php foreach ($projectsList as $proj): ?>
                <option value="<?= $proj['id'] ?>" <?= $projectFilter === (int)$proj['id'] ? 'selected' : '' ?>><?= htmlspecialchars($proj['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="min-width: 140px;">
        <select name="status" class="filter-input" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach ($validStatuses as $st): ?>
                <option value="<?= $st ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= $st ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="min-width: 140px;">
        <select name="priority" class="filter-input" onchange="this.form.submit()">
            <option value="">All Priorities</option>
            <?php foreach ($validPriorities as $pr): ?>
                <option value="<?= $pr ?>" <?= $priorityFilter === $pr ? 'selected' : '' ?>><?= $pr ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-outline">Filter</button>
    <?php if (!empty($search) || $projectFilter > 0 || !empty($statusFilter) || !empty($priorityFilter)): ?>
        <a href="project-tasks.php" class="btn btn-sm btn-outline" style="color: var(--admin-danger);">Clear</a>
    <?php endif; ?>
</form>

<!-- Tasks Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Task Name</th>
                    <th>Project & Client</th>
                    <th>Priority</th>
                    <th>Assigned Team</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tasks)): ?>
                    <tr><td colspan="6" class="empty-state">No sprint tasks found.</td></tr>
                <?php else: foreach ($tasks as $t): 
                    $priorityClass = match($t['priority']) {
                        'Urgent', 'High' => 'badge-danger',
                        'Medium' => 'badge-warning',
                        default => 'badge-neutral'
                    };
                ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($t['name']) ?></strong>
                            <?php if (!empty($t['description'])): ?>
                                <div style="font-size: 11.5px; color: var(--admin-text-muted); max-width: 320px;"><?= htmlspecialchars($t['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><a href="project-details.php?id=<?= $t['project_id'] ?>" style="color: var(--admin-primary); text-decoration: none;"><?= htmlspecialchars($t['project_name']) ?></a></strong>
                            <div style="font-size: 11px; color: var(--admin-text-muted);"><?= htmlspecialchars($t['company_name'] ?? '') ?></div>
                        </td>
                        <td>
                            <span class="badge <?= $priorityClass ?>"><?= htmlspecialchars($t['priority']) ?></span>
                        </td>
                        <td><span style="font-size: 12px;"><?= htmlspecialchars($t['assigned_team'] ?? 'Unassigned') ?></span></td>
                        <td><span style="font-size: 12px;"><?= !empty($t['due_date']) ? date('d M Y', strtotime($t['due_date'])) : '-' ?></span></td>
                        <td>
                            <form method="POST" action="project-tasks.php" style="margin: 0;">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="update_task_status">
                                <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                                <select name="new_status" onchange="this.form.submit()" style="font-size: 11.5px; font-weight: 600; padding: 3px 8px; border-radius: 9999px; border: 1px solid var(--admin-border); background: #ffffff; cursor: pointer;">
                                    <?php foreach ($validStatuses as $st): ?>
                                        <option value="<?= $st ?>" <?= $t['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <div>Showing <?= ($offset + 1) ?> to <?= min($totalRecords, $offset + $perPage) ?> of <?= $totalRecords ?> tasks</div>
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= ($page - 1) ?>&search=<?= urlencode($search) ?>&project_id=<?= $projectFilter ?>" class="pagination-item">&laquo; Prev</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&project_id=<?= $projectFilter ?>" class="pagination-item <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= ($page + 1) ?>&search=<?= urlencode($search) ?>&project_id=<?= $projectFilter ?>" class="pagination-item">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: New Task -->
<div id="newTaskModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 500px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Create Sprint Task</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('newTaskModal')">&times;</button>
        </div>
        <form method="POST" action="project-tasks.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="create_task">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Project *</label>
                    <select name="project_id" class="form-control" required>
                        <option value="">Select Project...</option>
                        <?php foreach ($projectsList as $pr): ?>
                            <option value="<?= $pr['id'] ?>"><?= htmlspecialchars($pr['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Task Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Implement Webhook Auth Verification" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-control">
                            <?php foreach ($validPriorities as $pr): ?>
                                <option value="<?= $pr ?>" <?= $pr === 'Medium' ? 'selected' : '' ?>><?= $pr ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Initial Status</label>
                        <select name="status" class="form-control">
                            <?php foreach ($validStatuses as $st): ?>
                                <option value="<?= $st ?>"><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Target Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assigned Pod / Member</label>
                        <input type="text" name="assigned_team" class="form-control" value="Backend Team">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Task Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Details and technical criteria..."></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('newTaskModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Task</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
