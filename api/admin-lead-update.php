<?php
/**
 * GrihGO Digital & Tech — Admin Lead Update & Convert API
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

$leadId = (int)($_POST['id'] ?? $_POST['lead_id'] ?? 0);
if ($leadId <= 0) {
    jsonResponse(false, 'Valid lead ID is required.', ['id' => 'Required'], [], 422);
}

$stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ? LIMIT 1");
$stmt->execute([$leadId]);
$lead = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lead) {
    jsonResponse(false, 'Lead not found.', [], [], 404);
}

$action = sanitizeInput($_POST['action'] ?? 'update_status');

try {
    if ($action === 'convert') {
        // Convert Lead to Registered Client
        $companyName   = sanitizeInput($_POST['company_name'] ?? $lead['business_name'] ?? $lead['name']);
        $contactPerson = sanitizeInput($_POST['contact_person'] ?? $lead['name']);
        $email         = sanitizeInput($_POST['email'] ?? $lead['email']);
        $phone         = sanitizeInput($_POST['phone'] ?? $lead['phone']);
        $rawPassword   = trim($_POST['password'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(false, 'Valid email required for client account creation.', ['email' => 'Invalid'], [], 422);
        }

        // Check user existence
        $chkUser = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $chkUser->execute([$email]);
        $existingUser = $chkUser->fetch(PDO::FETCH_ASSOC);

        if (!$existingUser && strlen($rawPassword) < 12) {
            jsonResponse(false, 'Set an initial password with at least 12 characters.', ['password' => 'Too short'], [], 422);
        }

        $pdo->beginTransaction();

        $userId = 0;
        if ($existingUser) {
            $userId = (int)$existingUser['id'];
        } else {
            $hash = password_hash($rawPassword, PASSWORD_DEFAULT);
            $insUser = $pdo->prepare("
                INSERT INTO users (name, email, password_hash, role, status, created_at, updated_at) 
                VALUES (?, ?, ?, 'client', 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");
            $insUser->execute([$contactPerson, $email, $hash]);
            $userId = (int)$pdo->lastInsertId();
        }

        // Check client profile
        $chkClient = $pdo->prepare("SELECT id FROM clients WHERE user_id = ? OR email = ? LIMIT 1");
        $chkClient->execute([$userId, $email]);
        $existingClient = $chkClient->fetch(PDO::FETCH_ASSOC);

        $clientId = 0;
        if ($existingClient) {
            $clientId = (int)$existingClient['id'];
        } else {
            $insClient = $pdo->prepare("
                INSERT INTO clients (user_id, company_name, contact_person, email, phone, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 'active', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");
            $insClient->execute([$userId, $companyName, $contactPerson, $email, $phone]);
            $clientId = (int)$pdo->lastInsertId();
        }

        // Mark lead as Converted
        $updLead = $pdo->prepare("UPDATE leads SET status = 'Converted', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $updLead->execute([$leadId]);

        // Link any appointments with matching email
        $linkAppt = $pdo->prepare("UPDATE appointments SET client_id = ? WHERE email = ? AND client_id IS NULL");
        $linkAppt->execute([$clientId, $email]);

        // Client notification
        if (function_exists('createClientNotification')) {
            createClientNotification(
                $pdo, 
                $clientId, 
                'Welcome to GrihGO Client Portal', 
                "Your enterprise client portal account has been provisioned. Access all your projects, documents, and invoices here.", 
                'profile', 
                $clientId
            );
        }

        logAdminActivity(
            $pdo, 
            (int)$currentAdmin['id'], 
            $currentAdmin['full_name'], 
            'Lead Converted', 
            'leads', 
            $leadId, 
            "Converted lead #{$leadId} ({$lead['name']}) to Client #CL-{$clientId}"
        );

        $pdo->commit();

        jsonResponse(true, "Lead successfully converted to Client #CL-{$clientId}.", [], [
            'lead_id'   => $leadId,
            'client_id' => $clientId,
            'user_id'   => $userId,
            'email'     => $email
        ]);
    } else {
        // Standard Lead Update
        $validStatuses = ['New', 'Contacted', 'Interested', 'Proposal Sent', 'Negotiation', 'Converted', 'Lost'];
        $status  = isset($_POST['status']) && in_array($_POST['status'], $validStatuses, true) ? $_POST['status'] : $lead['status'];
        $service = sanitizeInput($_POST['service'] ?? $_POST['service_interested'] ?? $lead['service'] ?? '');
        $notes   = sanitizeInput($_POST['notes'] ?? $lead['notes'] ?? '');

        $assignedTo = isset($_POST['assigned_to']) ? ((int)$_POST['assigned_to'] > 0 ? (int)$_POST['assigned_to'] : null) : $lead['assigned_to'];

        $upd = $pdo->prepare("
            UPDATE leads 
            SET status = :status, service = :service, notes = :notes, assigned_to = :assigned_to, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $upd->execute([
            ':status'      => $status,
            ':service'     => $service,
            ':notes'       => $notes,
            ':assigned_to' => $assignedTo,
            ':id'          => $leadId
        ]);

        if (!empty($assignedTo) && $assignedTo != $lead['assigned_to']) {
            addSystemNotification(
                'manager',
                $assignedTo,
                'Lead Assigned',
                "New Lead Assigned: {$lead['name']}",
                "Lead #{$leadId} ({$lead['name']}) has been assigned to you by Admin.",
                $leadId
            );
        }

        logAdminActivity(
            $pdo, 
            (int)$currentAdmin['id'], 
            $currentAdmin['full_name'], 
            'Lead Updated', 
            'leads', 
            $leadId, 
            "Updated lead #{$leadId} ({$lead['name']}) status to {$status}" . ($assignedTo ? " (Assigned to #{$assignedTo})" : '')
        );

        jsonResponse(true, "Lead #{$leadId} updated successfully.", [], [
            'id'     => $leadId,
            'status' => $status
        ]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(false, 'Database error while updating lead: ' . $e->getMessage(), [], [], 500);
}
