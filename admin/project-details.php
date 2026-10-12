<?php
/**
 * GrihGO Digital & Tech — Project Delivery Dossier & Progress Engine
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$projectId = (int)($_GET['id'] ?? 0);
if ($projectId <= 0) {
    header('Location: projects.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.company_name, c.contact_person, c.email AS client_email, c.phone AS client_phone 
    FROM projects p 
    LEFT JOIN clients c ON p.client_id = c.id 
    WHERE p.id = ? LIMIT 1
");
$stmt->execute([$projectId]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header('Location: projects.php?error=not_found');
    exit;
}

$pageTitle = htmlspecialchars($project['name']) . ' — Project Management';
$pageSection = 'projects';

$error = '';
$success = '';

$validStatuses = ['Planning', 'In Progress', 'Testing', 'Review', 'Completed', 'On Hold'];

// Handle Progress & Status Update (Section 14 & 43)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_project') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $status       = in_array($_POST['status'] ?? '', $validStatuses) ? $_POST['status'] : $project['status'];
        $progress     = min(100, max(0, (int)($_POST['progress'] ?? $project['progress'])));
        $assignedTeam = sanitizeInput($_POST['assigned_team'] ?? $project['assigned_team']);
        $description  = sanitizeInput($_POST['description'] ?? $project['description']);
        $expectedComp = sanitizeInput($_POST['expected_completion'] ?? $project['expected_completion']);

        try {
            $upd = $pdo->prepare("
                UPDATE projects 
                SET status = ?, progress = ?, assigned_team = ?, description = ?, expected_completion = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $upd->execute([$status, $progress, $assignedTeam, $description, $expectedComp, $projectId]);

            // Notify client of milestone change
            $pdo->prepare("INSERT INTO notifications (client_id, type, title, message, reference_id, is_read) VALUES (?, 'Project Update', ?, ?, ?, 0)")
                ->execute([
                    $project['client_id'],
                    "Project Progress Updated ({$progress}%)",
                    "Your project '{$project['name']}' has been updated to {$progress}% completion ({$status}).",
                    $projectId
                ]);

            logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Project Progress Updated', 'projects', $projectId, "Updated '{$project['name']}' to {$progress}% ({$status})");

            $stmt->execute([$projectId]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            $success = "Project progress and status updated successfully! Changes are live on the Client Portal.";
        } catch (Exception $e) {
            $error = 'Failed to update project progress.';
        }
    }
}

// Handle Add Task Shortcut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_task') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $taskName = sanitizeInput($_POST['task_name'] ?? '');
        $taskDesc = sanitizeInput($_POST['task_desc'] ?? '');
        $priority = in_array($_POST['priority'] ?? '', ['Low', 'Medium', 'High', 'Urgent']) ? $_POST['priority'] : 'Medium';
        $dueDate  = sanitizeInput($_POST['due_date'] ?? '');
        $taskTeam = sanitizeInput($_POST['assigned_team'] ?? $project['assigned_team']);

        if (empty($taskName)) {
            $error = 'Please enter task title.';
        } else {
            try {
                $taskStmt = $pdo->prepare("INSERT INTO project_tasks (project_id, name, description, assigned_team, due_date, priority, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
                $taskStmt->execute([$projectId, $taskName, $taskDesc, $taskTeam, $dueDate, $priority]);
                $taskId = (int)$pdo->lastInsertId();

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Task Created', 'project_tasks', $taskId, "Added task '{$taskName}' to project #{$projectId}");
                $success = "Sprint task '{$taskName}' added successfully!";
            } catch (Exception $e) {
                $error = 'Failed to create task.';
            }
        }
    }
}

// Fetch Tasks
$taskStmt = $pdo->prepare("SELECT * FROM project_tasks WHERE project_id = ? ORDER BY id DESC");
$taskStmt->execute([$projectId]);
$tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Documents
$docStmt = $pdo->prepare("SELECT * FROM documents WHERE project_id = ? ORDER BY id DESC");
$docStmt->execute([$projectId]);
$docs = $docStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Invoices
$invStmt = $pdo->prepare("SELECT * FROM invoices WHERE project_id = ? ORDER BY id DESC");
$invStmt->execute([$projectId]);
$invoices = $invStmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <div style="font-size: 13px; color: var(--admin-text-muted); margin-bottom: 4px;">
            <a href="projects.php" style="color: var(--admin-primary); text-decoration: none;">&larr; Back to Projects</a>
        </div>
        <h1><?= htmlspecialchars($project['name']) ?></h1>
        <p class="page-subtitle">Client: <a href="client-details.php?id=<?= $project['client_id'] ?>" style="color: var(--admin-primary); font-weight: 600; text-decoration: none;"><?= htmlspecialchars($project['company_name']) ?></a> &bull; Service: <?= htmlspecialchars($project['service']) ?></p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-outline" onclick="openAdminModal('addTaskModal')">+ Add Sprint Task</button>
        <button type="button" class="btn btn-primary" onclick="openAdminModal('updateProgressModal')">Update Progress</button>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Progress & Metrics Header -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="kpi-card" style="--kpi-accent: var(--admin-primary);">
        <div class="kpi-card-content">
            <span class="kpi-title">Current Progress</span>
            <span class="kpi-value"><?= (int)$project['progress'] ?>%</span>
            <span class="kpi-subtext">Live on Client Portal</span>
        </div>
    </div>
    <div class="kpi-card" style="--kpi-accent: #0ea5e9;">
        <div class="kpi-card-content">
            <span class="kpi-title">Delivery Status</span>
            <span class="kpi-value" style="font-size: 20px;"><?= htmlspecialchars($project['status']) ?></span>
            <span class="kpi-subtext">Active milestone</span>
        </div>
    </div>
    <div class="kpi-card" style="--kpi-accent: var(--admin-accent);">
        <div class="kpi-card-content">
            <span class="kpi-title">Tasks Completed</span>
            <?php 
                $doneTasks = count(array_filter($tasks, fn($t) => $t['status'] === 'Completed'));
                $totalTasks = count($tasks);
            ?>
            <span class="kpi-value"><?= $doneTasks ?> / <?= $totalTasks ?></span>
            <span class="kpi-subtext">Sprint velocity</span>
        </div>
    </div>
    <div class="kpi-card" style="--kpi-accent: var(--admin-purple);">
        <div class="kpi-card-content">
            <span class="kpi-title">Project Delivery</span>
            <span class="kpi-value" style="font-size: 18px;"><?= !empty($project['expected_completion']) ? date('d M Y', strtotime($project['expected_completion'])) : 'Ongoing' ?></span>
            <span class="kpi-subtext">Target finish</span>
        </div>
    </div>
</div>

<!-- Project Timeline Ribbon (Section 14) -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">Project Milestone Timeline</h3>
    </div>
    <div class="admin-card-body" style="padding: 28px 24px;">
        <?php
            $stages = [
                ['name' => 'Requirement Gathering', 'thresh' => 15],
                ['name' => 'UI/UX Prototyping', 'thresh' => 35],
                ['name' => 'Core Development', 'thresh' => 70],
                ['name' => 'QA & Security Testing', 'thresh' => 90],
                ['name' => 'Final Deployment & Handoff', 'thresh' => 100],
            ];
            $currentProg = (int)$project['progress'];
        ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px;">
            <?php foreach ($stages as $i => $stg): 
                $isDone = $currentProg >= $stg['thresh'];
                $isCurrent = !$isDone && ($i === 0 || $currentProg >= $stages[$i - 1]['thresh']);
            ?>
                <div style="background: <?= $isDone ? '#ecfdf5' : ($isCurrent ? '#eff6ff' : '#f8fafc') ?>; border: 1px solid <?= $isDone ? '#a7f3d0' : ($isCurrent ? '#bfdbfe' : '#e2e8f0') ?>; border-radius: var(--admin-radius-sm); padding: 14px; text-align: center;">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: <?= $isDone ? 'var(--admin-accent)' : ($isCurrent ? 'var(--admin-primary)' : 'var(--admin-text-muted)') ?>; margin-bottom: 4px;">
                        Phase <?= $i + 1 ?> &bull; <?= $isDone ? 'Completed' : ($isCurrent ? 'In Progress' : 'Pending') ?>
                    </div>
                    <div style="font-weight: 700; font-size: 13.5px; color: var(--admin-text-main);"><?= $stg['name'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Two Column Layout: Tasks & Dossier Info -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- Left Column: Tasks -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Sprint Tasks</h3>
                <button type="button" class="btn btn-sm btn-outline" onclick="openAdminModal('addTaskModal')">+ Add Task</button>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Priority</th>
                            <th>Assigned Team</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                            <tr><td colspan="5" class="empty-state">No sprint tasks created yet for this project.</td></tr>
                        <?php else: foreach ($tasks as $t): 
                            $tStatusClass = match($t['status']) {
                                'Completed' => 'badge-success',
                                'In Progress' => 'badge-primary',
                                'Blocked' => 'badge-danger',
                                default => 'badge-neutral'
                            };
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($t['name']) ?></strong>
                                    <?php if (!empty($t['description'])): ?>
                                        <div style="font-size: 11px; color: var(--admin-text-muted);"><?= htmlspecialchars($t['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= in_array($t['priority'], ['High', 'Urgent']) ? 'badge-danger' : 'badge-neutral' ?>">
                                        <?= htmlspecialchars($t['priority']) ?>
                                    </span>
                                </td>
                                <td><span style="font-size: 12px;"><?= htmlspecialchars($t['assigned_team'] ?? 'Team') ?></span></td>
                                <td><span style="font-size: 12px;"><?= !empty($t['due_date']) ? date('d M Y', strtotime($t['due_date'])) : '-' ?></span></td>
                                <td>
                                    <span class="badge <?= $tStatusClass ?>"><span class="badge-dot"></span><?= htmlspecialchars($t['status']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Invoices for this Project -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Project Invoices</h3>
                <a href="invoices.php?project_id=<?= $projectId ?>&client_id=<?= $project['client_id'] ?>" class="btn btn-sm btn-outline">+ Create Invoice</a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                            <tr><td colspan="5" class="empty-state">No invoices associated with this project.</td></tr>
                        <?php else: foreach ($invoices as $inv): ?>
                            <tr>
                                <td><strong><a href="invoice-details.php?id=<?= $inv['id'] ?>" style="color: var(--admin-primary); text-decoration: none;"><?= htmlspecialchars($inv['invoice_number']) ?></a></strong></td>
                                <td><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                                <td>₹<?= number_format((float)$inv['total_amount'], 2) ?></td>
                                <td>₹<?= number_format((float)$inv['paid_amount'], 2) ?></td>
                                <td><span class="badge <?= $inv['status'] === 'Paid' ? 'badge-success' : 'badge-warning' ?>"><?= htmlspecialchars($inv['status']) ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Project Meta & Client Info -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Project Specifications</h3>
            </div>
            <div class="admin-card-body" style="font-size: 13.5px; line-height: 1.8;">
                <div><strong style="color: var(--admin-text-muted);">Assigned Client:</strong><br><?= htmlspecialchars($project['company_name']) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Lead Representative:</strong><br><?= htmlspecialchars($project['contact_person']) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Core Service:</strong><br><?= htmlspecialchars($project['service']) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Engineering Pod:</strong><br><?= htmlspecialchars($project['assigned_team'] ?? 'GrihGO Tech Group') ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Kickoff Date:</strong><br><?= date('d M Y', strtotime($project['start_date'])) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Scope Notes:</strong><br><?= nl2br(htmlspecialchars($project['description'] ?: 'Standard architecture agreement.')) ?></div>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Update Progress & Status -->
<div id="updateProgressModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 500px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Update Project Delivery Progress</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('updateProgressModal')">&times;</button>
        </div>
        <form method="POST" action="project-details.php?id=<?= $projectId ?>">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="update_project">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Completion Percentage (0 - 100%)</label>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="range" name="progress" min="0" max="100" step="5" value="<?= (int)$project['progress'] ?>" style="flex: 1;" oninput="document.getElementById('progNumDisplay').innerText = this.value + '%'">
                        <span id="progNumDisplay" style="font-weight: 800; font-size: 16px; color: var(--admin-primary); width: 48px; text-align: right;"><?= (int)$project['progress'] ?>%</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Delivery Status</label>
                    <select name="status" class="form-control">
                        <?php foreach ($validStatuses as $st): ?>
                            <option value="<?= $st ?>" <?= $project['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Target Completion Date</label>
                    <input type="date" name="expected_completion" class="form-control" value="<?= htmlspecialchars($project['expected_completion'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Assigned Engineering Pod</label>
                    <input type="text" name="assigned_team" class="form-control" value="<?= htmlspecialchars($project['assigned_team'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Delivery Scope / Update Notes</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('updateProgressModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Publish Progress Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Sprint Task -->
<div id="addTaskModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 500px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Add Sprint Task</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('addTaskModal')">&times;</button>
        </div>
        <form method="POST" action="project-details.php?id=<?= $projectId ?>">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="add_task">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Task Title *</label>
                    <input type="text" name="task_name" class="form-control" placeholder="e.g. Implement Payment Gateway Webhooks" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-control">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Target Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Assigned Pod / Member</label>
                    <input type="text" name="assigned_team" class="form-control" value="<?= htmlspecialchars($project['assigned_team'] ?? 'Backend Team') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Task Technical Description</label>
                    <textarea name="task_desc" class="form-control" rows="2" placeholder="Acceptance criteria, branch name, or specific checklist..."></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('addTaskModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Task</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
