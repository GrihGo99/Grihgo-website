<?php
/**
 * GrihGO Digital & Tech — Admin Project Task Save (Create/Update) API
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json; charset=UTF-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method Not Allowed', [], [], 405);
}

// Admin Authentication Check
if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_role'])) {
    jsonResponse(false, 'Unauthorized. Administrative session required.', [], [], 401);
}

$pdo = Database::getConnection();
$adminStmt = $pdo->prepare("SELECT id, full_name, role, status FROM admins WHERE id = ? LIMIT 1");
$adminStmt->execute([$_SESSION['admin_id']]);
$currentAdmin = $adminStmt->fetch(PDO::FETCH_ASSOC);

if (!$currentAdmin || $currentAdmin['status'] !== 'active') {
    jsonResponse(false, 'Administrative account suspended or invalid.', [], [], 403);
}

// CSRF Validation
$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCsrfToken($token)) {
    jsonResponse(false, 'Invalid security CSRF token.', ['csrf' => 'Invalid token'], [], 403);
}

$taskId      = (int)($_POST['task_id'] ?? $_POST['id'] ?? 0);
$projectId   = (int)($_POST['project_id'] ?? 0);
$name        = sanitizeInput($_POST['name'] ?? $_POST['title'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$rawStatus   = sanitizeInput($_POST['status'] ?? 'Pending');
$status      = ($rawStatus === 'To Do') ? 'Pending' : (in_array($rawStatus, ['Pending', 'In Progress', 'Completed', 'Blocked'], true) ? $rawStatus : 'Pending');
$priority    = in_array($_POST['priority'] ?? '', ['Low', 'Medium', 'High', 'Urgent'], true) ? $_POST['priority'] : 'Medium';
$dueDate     = !empty($_POST['due_date']) ? sanitizeInput($_POST['due_date']) : null;
$assignedTeam = sanitizeInput($_POST['assigned_team'] ?? 'GrihGO Tech Core');

if (empty($name)) {
    jsonResponse(false, 'Task title is required.', ['name' => 'Required'], [], 422);
}

try {
    if ($taskId > 0) {
        $chk = $pdo->prepare("SELECT * FROM project_tasks WHERE id = ? LIMIT 1");
        $chk->execute([$taskId]);
        $task = $chk->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            jsonResponse(false, 'Task not found.', [], [], 404);
        }

        $pId = $projectId > 0 ? $projectId : (int)$task['project_id'];

        $upd = $pdo->prepare("
            UPDATE project_tasks 
            SET project_id = :pid, name = :name, description = :desc, status = :status, priority = :priority, due_date = :due, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $upd->execute([
            ':pid'      => $pId,
            ':name'     => $name,
            ':desc'     => $description,
            ':status'   => $status,
            ':priority' => $priority,
            ':due'      => $dueDate,
            ':id'       => $taskId
        ]);

        logAdminActivity(
            $pdo, 
            (int)$currentAdmin['id'], 
            $currentAdmin['full_name'], 
            'Task Updated', 
            'project_tasks', 
            $taskId, 
            "Updated task #{$taskId} '{$name}' (Status: {$status})"
        );

        jsonResponse(true, "Task '{$name}' updated successfully.", [], [
            'task_id'  => $taskId,
            'status'   => $status,
            'priority' => $priority
        ]);
    } else {
        if ($projectId <= 0) {
            jsonResponse(false, 'Valid project assignment is required.', ['project_id' => 'Required'], [], 422);
        }

        $ins = $pdo->prepare("
            INSERT INTO project_tasks (project_id, name, description, assigned_team, status, priority, due_date, created_at, updated_at) 
            VALUES (:pid, :name, :desc, :team, :status, :priority, :due, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $ins->execute([
            ':pid'      => $projectId,
            ':name'     => $name,
            ':desc'     => $description,
            ':team'     => $assignedTeam,
            ':status'   => $status,
            ':priority' => $priority,
            ':due'      => $dueDate
        ]);
        $newTaskId = (int)$pdo->lastInsertId();

        logAdminActivity(
            $pdo, 
            (int)$currentAdmin['id'], 
            $currentAdmin['full_name'], 
            'Task Created', 
            'project_tasks', 
            $newTaskId, 
            "Created task '{$name}' for project #{$projectId}"
        );

        jsonResponse(true, "Task '{$name}' created successfully.", [], [
            'task_id'    => $newTaskId,
            'project_id' => $projectId
        ]);
    }
} catch (Exception $e) {
    jsonResponse(false, 'Database error while saving task: ' . $e->getMessage(), [], [], 500);
}
