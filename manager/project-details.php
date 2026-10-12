<?php
/**
 * GrihGO Digital & Tech — Manager Project Details & Sprint Tasks
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

require_once __DIR__ . '/includes/auth.php';

$projectId = (int)($_GET['id'] ?? 0);
if ($projectId <= 0) {
    header('Location: projects.php');
    exit;
}

$managerId = (int)$currentManager['id'];

// Check permission: manager can access assigned or unassigned projects
$stmt = $pdo->prepare("
    SELECT p.*, c.company_name, c.contact_person, c.email AS client_email, c.phone AS client_phone 
    FROM projects p 
    LEFT JOIN clients c ON p.client_id = c.id 
    WHERE p.id = ? AND (p.assigned_to = ? OR p.manager_id = ? OR p.assigned_to IS NULL OR ? IN ('admin', 'super_admin'))
    LIMIT 1
");
$stmt->execute([$projectId, $managerId, $managerId, $currentManager['role']]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header('Location: projects.php?error=not_found');
    exit;
}

$pageTitle = 'Project #' . $project['id'] . ' — ' . htmlspecialchars($project['name']);
$pageSection = 'projects';

$error = '';
$success = '';
$validStatuses = ['Planning', 'In Progress', 'Testing', 'Review', 'Completed', 'On Hold'];

// Handle Progress & Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_project') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $status       = in_array($_POST['status'] ?? '', $validStatuses, true) ? $_POST['status'] : $project['status'];
        $progress     = min(100, max(0, (int)($_POST['progress'] ?? $project['progress'])));
        $expectedComp = sanitizeInput($_POST['expected_completion'] ?? $project['expected_completion']);
        $description  = sanitizeInput($_POST['description'] ?? $project['description']);

        try {
            $upd = $pdo->prepare("
                UPDATE projects 
                SET status = ?, progress = ?, expected_completion = ?, description = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $upd->execute([$status, $progress, $expectedComp, $description, $projectId]);

            // Notify Client
            addSystemNotification(
                (int)$project['client_id'],
                "Project Progress Updated ({$progress}%)",
                "Your project '{$project['name']}' has been updated to {$progress}% completion ({$status}) by Manager {$currentManager['full_name']}.",
                'client',
                'client/projects.php?id=' . $projectId
            );

            // Notify Admin
            addSystemNotification(
                'admin',
                null,
                'project_update',
                "Project Updated: {$project['name']}",
                "Manager {$currentManager['full_name']} updated Project #{$projectId} to {$progress}% ({$status}).",
                $projectId
            );

            // Re-fetch project
            $stmt->execute([$projectId, $managerId, $managerId, $currentManager['role']]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            $success = 'Project progress and status successfully updated.';
        } catch (Exception $e) {
            $error = 'Failed to update project: ' . $e->getMessage();
        }
    }
}

// Handle Add Task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_task') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $taskName = sanitizeInput($_POST['task_name'] ?? '');
        $taskDesc = sanitizeInput($_POST['task_desc'] ?? '');
        $priority = in_array($_POST['priority'] ?? '', ['Low', 'Medium', 'High', 'Urgent'], true) ? $_POST['priority'] : 'Medium';
        $dueDate  = sanitizeInput($_POST['due_date'] ?? '');

        if (empty($taskName)) {
            $error = 'Task name is required.';
        } else {
            try {
                $taskStmt = $pdo->prepare("INSERT INTO project_tasks (project_id, name, description, assigned_team, due_date, priority, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
                $taskStmt->execute([$projectId, $taskName, $taskDesc, $currentManager['full_name'], $dueDate, $priority]);

                // Client Notification
                addSystemNotification(
                    (int)$project['client_id'],
                    'New Sprint Task Added',
                    "Task '{$taskName}' has been added to project '{$project['name']}'.",
                    'client',
                    'client/projects.php?id=' . $projectId
                );

                $success = "Sprint task '{$taskName}' added successfully.";
            } catch (Exception $e) {
                $error = 'Failed to add task: ' . $e->getMessage();
            }
        }
    }
}

// Handle Toggle Task Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_task') {
    if (validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $newStatus = ($_POST['current_status'] ?? '') === 'Completed' ? 'Pending' : 'Completed';

        $tUpd = $pdo->prepare("UPDATE project_tasks SET status = ? WHERE id = ? AND project_id = ?");
        $tUpd->execute([$newStatus, $taskId, $projectId]);

        // Auto-recalculate project progress if tasks exist
        $allTasksStmt = $pdo->prepare("SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed FROM project_tasks WHERE project_id = ?");
        $allTasksStmt->execute([$projectId]);
        $counts = $allTasksStmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($counts['total']) && $counts['total'] > 0) {
            $autoProg = round(($counts['completed'] / $counts['total']) * 100);
            $pdo->prepare("UPDATE projects SET progress = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                ->execute([$autoProg, $projectId]);

            $stmt->execute([$projectId, $managerId, $currentManager['role']]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $success = "Task status updated to {$newStatus}.";
    }
}

// Fetch all project tasks
$taskStmt = $pdo->prepare("SELECT * FROM project_tasks WHERE project_id = ? ORDER BY id DESC");
$taskStmt->execute([$projectId]);
$tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);

$doneTasks = count(array_filter($tasks, fn($t) => $t['status'] === 'Completed'));
$totalTasks = count($tasks);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-content">
    <div class="admin-header-bar">
        <div>
            <div style="font-size: 0.85rem; color: #6b7280; margin-bottom: 4px;">
                <a href="projects.php" style="color: #059669; text-decoration: none;">&larr; Back to Assigned Projects</a>
            </div>
            <h1 class="page-title"><?= htmlspecialchars($project['name']) ?></h1>
            <p class="page-subtitle">Client: <?= htmlspecialchars($project['company_name'] ?: $project['contact_person']) ?> &bull; Domain: <?= htmlspecialchars($project['service']) ?></p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="tel:<?= htmlspecialchars($project['client_phone']) ?>" class="btn-primary" style="background: #0256c4; text-decoration: none; padding: 8px 14px; font-size: 0.85rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                Contact Client
            </a>
            <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $project['client_phone']) ?>?text=<?= urlencode('Hello ' . $project['contact_person'] . ', this is ' . $currentManager['full_name'] . ' with an update regarding your project ' . $project['name'] . '.') ?>" target="_blank" class="btn-primary" style="background: #059669; text-decoration: none; padding: 8px 14px; font-size: 0.85rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                </svg>
                WhatsApp Update
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div style="background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- KPI Metrics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="admin-card" style="padding: 16px;">
            <div style="font-size: 0.8rem; text-transform: uppercase; color: #6b7280; font-weight: 600;">Overall Progress</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #059669; margin: 4px 0;"><?= (int)$project['progress'] ?>%</div>
            <div style="background: #e5e7eb; border-radius: 9999px; height: 6px; overflow: hidden;">
                <div style="background: #059669; width: <?= (int)$project['progress'] ?>%; height: 100%;"></div>
            </div>
        </div>

        <div class="admin-card" style="padding: 16px;">
            <div style="font-size: 0.8rem; text-transform: uppercase; color: #6b7280; font-weight: 600;">Project Status</div>
            <div style="font-size: 1.3rem; font-weight: 700; color: #111827; margin: 4px 0;"><?= htmlspecialchars($project['status']) ?></div>
            <div style="font-size: 0.8rem; color: #6b7280;">Live sync with Client Portal</div>
        </div>

        <div class="admin-card" style="padding: 16px;">
            <div style="font-size: 0.8rem; text-transform: uppercase; color: #6b7280; font-weight: 600;">Sprint Tasks</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #0256c4; margin: 4px 0;"><?= $doneTasks ?> / <?= $totalTasks ?></div>
            <div style="font-size: 0.8rem; color: #6b7280;">Completed tasks</div>
        </div>

        <div class="admin-card" style="padding: 16px;">
            <div style="font-size: 0.8rem; text-transform: uppercase; color: #6b7280; font-weight: 600;">Target Completion</div>
            <div style="font-size: 1.1rem; font-weight: 700; color: #111827; margin: 4px 0;">
                <?= !empty($project['expected_completion']) ? date('d M Y', strtotime($project['expected_completion'])) : 'Not scheduled' ?>
            </div>
            <div style="font-size: 0.8rem; color: #6b7280;">Delivery deadline</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Left: Tasks & Work Breakdown -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Sprint Tasks Section -->
            <div class="admin-card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 class="card-title">Project Sprint Tasks</h2>
                    <span style="font-size: 0.85rem; color: #6b7280;"><?= $totalTasks ?> Tasks Total</span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($tasks)): ?>
                        <div style="text-align: center; padding: 30px; color: #6b7280;">
                            No tasks created yet for this project. Add your first sprint task below!
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Done</th>
                                        <th>Task</th>
                                        <th>Priority</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tasks as $t): ?>
                                        <tr style="<?= $t['status'] === 'Completed' ? 'opacity: 0.65; background: #f9fafb;' : '' ?>">
                                            <td style="width: 50px;">
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                                    <input type="hidden" name="action" value="toggle_task">
                                                    <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                                                    <input type="hidden" name="current_status" value="<?= $t['status'] ?>">
                                                    <button type="submit" style="background: none; border: none; cursor: pointer; padding: 0;">
                                                        <?php if ($t['status'] === 'Completed'): ?>
                                                            <span style="display: inline-block; width: 20px; height: 20px; background: #10b981; color: white; border-radius: 4px; text-align: center; line-height: 20px; font-weight: bold; font-size: 13px;">✓</span>
                                                        <?php else: ?>
                                                            <span style="display: inline-block; width: 20px; height: 20px; border: 2px solid #9ca3af; border-radius: 4px;"></span>
                                                        <?php endif; ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <div style="font-weight: 600; color: #111827; <?= $t['status'] === 'Completed' ? 'text-decoration: line-through;' : '' ?>">
                                                    <?= htmlspecialchars($t['name']) ?>
                                                </div>
                                                <?php if (!empty($t['description'])): ?>
                                                    <div style="font-size: 0.8rem; color: #6b7280;"><?= htmlspecialchars($t['description']) ?></div>
                                                <?php endif; ?>
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
                                            <td style="font-size: 0.85rem; color: #6b7280;">
                                                <?= !empty($t['due_date']) ? date('d M Y', strtotime($t['due_date'])) : '—' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add Task Form -->
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 1rem;">+ Add New Sprint Task</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="add_task">

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 14px;">
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Task Title *</label>
                                <input type="text" name="task_name" placeholder="e.g. Implement payment gateway webhook" class="form-control" required style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Priority</label>
                                <select name="priority" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Description (Optional)</label>
                                <input type="text" name="task_desc" placeholder="Brief technical or delivery context..." class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Due Date</label>
                                <input type="date" name="due_date" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                            </div>
                        </div>

                        <button type="submit" class="btn-primary" style="background: #059669; border: none; padding: 8px 16px; font-weight: 600; border-radius: 6px; color: #fff; cursor: pointer;">
                            Add Task
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Project Controls & Client Card -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Progress & Status Update Form -->
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 1rem;">Update Project Progress</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="update_project">

                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Delivery Status</label>
                            <select name="status" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                                <?php foreach ($validStatuses as $st): ?>
                                    <option value="<?= $st ?>" <?= $project['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin-bottom: 14px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <label style="font-size: 0.8rem; font-weight: 600; color: #374151;">Progress Percentage</label>
                                <span id="progVal" style="font-weight: 700; color: #059669;"><?= (int)$project['progress'] ?>%</span>
                            </div>
                            <input type="range" name="progress" min="0" max="100" value="<?= (int)$project['progress'] ?>" oninput="document.getElementById('progVal').innerText = this.value + '%'" style="width: 100%;">
                        </div>

                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Target Completion Date</label>
                            <input type="date" name="expected_completion" value="<?= htmlspecialchars($project['expected_completion'] ?? '') ?>" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Scope &amp; Milestone Notes</label>
                            <textarea name="description" rows="4" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; font-size: 0.85rem;"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn-primary" style="width: 100%; background: #059669; border: none; padding: 10px; font-weight: 600; border-radius: 6px; color: #fff; cursor: pointer;">
                            Save &amp; Notify Client
                        </button>
                    </form>
                </div>
            </div>

            <!-- Client Info Card -->
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 1rem;">Client Contact</h3>
                </div>
                <div class="card-body" style="font-size: 0.9rem; color: #4b5563;">
                    <div style="margin-bottom: 8px;">
                        <strong>Organization:</strong><br>
                        <?= htmlspecialchars($project['company_name'] ?: 'Not recorded') ?>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>Key Contact:</strong><br>
                        <?= htmlspecialchars($project['contact_person']) ?>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>Email:</strong><br>
                        <a href="mailto:<?= htmlspecialchars($project['client_email']) ?>" style="color: #0256c4;"><?= htmlspecialchars($project['client_email']) ?></a>
                    </div>
                    <div>
                        <strong>Phone:</strong><br>
                        <a href="tel:<?= htmlspecialchars($project['client_phone']) ?>" style="color: #059669; font-weight: 600;"><?= htmlspecialchars($project['client_phone']) ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
