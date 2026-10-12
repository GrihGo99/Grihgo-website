<?php
/**
 * GrihGO Digital & Tech — Admin Project Save (Create/Update) API
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

$projectId   = (int)($_POST['project_id'] ?? $_POST['id'] ?? 0);
$clientId    = (int)($_POST['client_id'] ?? 0);
$name        = sanitizeInput($_POST['name'] ?? '');
$service     = sanitizeInput($_POST['service'] ?? 'Enterprise Development');
$description = sanitizeInput($_POST['description'] ?? '');
$validStatuses = ['Planning', 'In Progress', 'Testing', 'Review', 'Completed', 'On Hold'];
$status      = in_array($_POST['status'] ?? '', $validStatuses, true) ? $_POST['status'] : 'In Progress';
$progress    = max(0, min(100, (int)($_POST['progress'] ?? 0)));
$startDate   = !empty($_POST['start_date']) ? sanitizeInput($_POST['start_date']) : date('Y-m-d');
$expCompletion = !empty($_POST['expected_completion']) ? sanitizeInput($_POST['expected_completion']) : (!empty($_POST['end_date']) ? sanitizeInput($_POST['end_date']) : date('Y-m-d', strtotime('+30 days')));
$assignedTeam = sanitizeInput($_POST['assigned_team'] ?? 'GrihGO Engineering Team');

$managerId = (int)($_POST['manager_id'] ?? $_POST['assigned_to'] ?? 0);
if ($managerId <= 0) $managerId = null;

$errors = [];
if (empty($name))     $errors['name'] = 'Project title is required.';
if ($clientId <= 0)   $errors['client_id'] = 'Valid client assignment is required.';

if (!empty($errors)) {
    jsonResponse(false, 'Validation failed. Please correct input fields.', $errors, [], 422);
}

try {
    if ($projectId > 0) {
        // Update Project
        $chk = $pdo->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
        $chk->execute([$projectId]);
        $project = $chk->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            jsonResponse(false, 'Project not found.', [], [], 404);
        }

        $upd = $pdo->prepare("
            UPDATE projects 
            SET client_id = :cid, name = :name, project_name = :pname, service = :svc, description = :desc, 
                status = :status, progress = :prog, start_date = :sdate, expected_completion = :edate, end_date = :enddate,
                manager_id = :mid, assigned_to = :assto, assigned_team = :team, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $upd->execute([
            ':cid'     => $clientId,
            ':name'    => $name,
            ':pname'   => $name,
            ':svc'     => $service,
            ':desc'    => $description,
            ':status'  => $status,
            ':prog'    => $progress,
            ':sdate'   => $startDate,
            ':edate'   => $expCompletion,
            ':enddate' => $expCompletion,
            ':mid'     => $managerId,
            ':assto'   => $managerId,
            ':team'    => $assignedTeam,
            ':id'      => $projectId
        ]);

        if (!empty($managerId) && $managerId != ($project['manager_id'] ?? $project['assigned_to'] ?? null)) {
            addSystemNotification(
                'manager',
                $managerId,
                'Project Assigned',
                'Project Assigned: ' . $name,
                "You have been assigned to manage '{$name}'.",
                $projectId
            );
        }

        // Notify client if progress or status changed
        if (function_exists('createClientNotification')) {
            createClientNotification(
                $pdo, 
                $clientId, 
                'Project Updated: ' . $name, 
                "Project progress updated to {$progress}% (Status: {$status}).", 
                'projects', 
                $projectId
            );
        }

        logAdminActivity(
            $pdo, 
            (int)$currentAdmin['id'], 
            $currentAdmin['full_name'], 
            'Project Updated', 
            'projects', 
            $projectId, 
            "Updated project '{$name}' (Progress: {$progress}%, Status: {$status})"
        );

        jsonResponse(true, "Project '{$name}' updated successfully.", [], [
            'project_id' => $projectId,
            'progress'   => $progress,
            'status'     => $status
        ]);
    } else {
        // Create Project
        $ins = $pdo->prepare("
            INSERT INTO projects (client_id, name, project_name, service, description, status, progress, start_date, expected_completion, end_date, manager_id, assigned_to, assigned_team, created_at, updated_at) 
            VALUES (:cid, :name, :pname, :svc, :desc, :status, :prog, :sdate, :edate, :enddate, :mid, :assto, :team, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $ins->execute([
            ':cid'     => $clientId,
            ':name'    => $name,
            ':pname'   => $name,
            ':svc'     => $service,
            ':desc'    => $description,
            ':status'  => $status,
            ':prog'    => $progress,
            ':sdate'   => $startDate,
            ':edate'   => $expCompletion,
            ':enddate' => $expCompletion,
            ':mid'     => $managerId,
            ':assto'   => $managerId,
            ':team'    => $assignedTeam
        ]);
        $newProjectId = (int)$pdo->lastInsertId();

        if (!empty($managerId)) {
            addSystemNotification(
                'manager',
                $managerId,
                'Project Assigned',
                'New Project Assigned: ' . $name,
                "You have been assigned as Project Manager for '{$name}'.",
                $newProjectId
            );
        }

        // Notify client
        if (function_exists('createClientNotification')) {
            createClientNotification(
                $pdo, 
                $clientId, 
                'New Project Initiated: ' . $name, 
                "A new enterprise project has been initialized in your portal.", 
                'projects', 
                $newProjectId
            );
        }

        logAdminActivity(
            $pdo, 
            (int)$currentAdmin['id'], 
            $currentAdmin['full_name'], 
            'Project Created', 
            'projects', 
            $newProjectId, 
            "Created project '{$name}' for Client #CL-{$clientId}"
        );

        jsonResponse(true, "Project '{$name}' created successfully.", [], [
            'project_id' => $newProjectId
        ]);
    }
} catch (Exception $e) {
    jsonResponse(false, 'Database error while saving project: ' . $e->getMessage(), [], [], 500);
}
