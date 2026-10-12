<?php
/**
 * GrihGO Digital & Tech — Admin Client Save (Create/Update) API
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

$clientId      = (int)($_POST['client_id'] ?? $_POST['id'] ?? 0);
$companyName   = sanitizeInput($_POST['company_name'] ?? '');
$contactPerson = sanitizeInput($_POST['contact_person'] ?? '');
$email         = sanitizeInput($_POST['email'] ?? '');
$phone         = sanitizeInput($_POST['phone'] ?? '');
$address       = sanitizeInput($_POST['address'] ?? '');
$status        = in_array($_POST['status'] ?? '', ['active', 'inactive', 'suspended'], true) ? $_POST['status'] : 'active';
$rawPassword   = trim($_POST['password'] ?? '');

$errors = [];
if (empty($companyName))   $errors['company_name'] = 'Company name is required.';
if (empty($contactPerson)) $errors['contact_person'] = 'Contact person name is required.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required.';
if ($clientId === 0 && strlen($rawPassword) < 12) $errors['password'] = 'Initial password must contain at least 12 characters.';

if (!empty($errors)) {
    jsonResponse(false, 'Validation failed. Please correct input fields.', $errors, [], 422);
}

try {
    $pdo->beginTransaction();

    if ($clientId > 0) {
        // Update Existing Client
        $chkClient = $pdo->prepare("SELECT * FROM clients WHERE id = ? LIMIT 1");
        $chkClient->execute([$clientId]);
        $client = $chkClient->fetch(PDO::FETCH_ASSOC);

        if (!$client) {
            $pdo->rollBack();
            jsonResponse(false, 'Client not found.', [], [], 404);
        }

        $updClient = $pdo->prepare("
            UPDATE clients 
            SET company_name = :cname, contact_person = :cperson, email = :email, phone = :phone, address = :addr, status = :status, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $updClient->execute([
            ':cname'   => $companyName,
            ':cperson' => $contactPerson,
            ':email'   => $email,
            ':phone'   => $phone,
            ':addr'    => $address,
            ':status'  => $status,
            ':id'      => $clientId
        ]);

        // Sync with users table
        if (!empty($client['user_id'])) {
            $updUserSql = "UPDATE users SET name = ?, email = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $updUserParams = [$contactPerson, $email, $status, $client['user_id']];
            if (!empty($rawPassword) && strlen($rawPassword) >= 6) {
                $updUserSql = "UPDATE users SET name = ?, email = ?, password_hash = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $updUserParams = [$contactPerson, $email, password_hash($rawPassword, PASSWORD_DEFAULT), $status, $client['user_id']];
            }
            $updUserStmt = $pdo->prepare($updUserSql);
            $updUserStmt->execute($updUserParams);
        }

        logAdminActivity(
            $pdo, 
            (int)$currentAdmin['id'], 
            $currentAdmin['full_name'], 
            'Client Updated', 
            'clients', 
            $clientId, 
            "Updated client #{$clientId} ({$companyName})"
        );

        $pdo->commit();
        jsonResponse(true, "Client {$companyName} updated successfully.", [], ['client_id' => $clientId]);
    } else {
        // Create New Client
        $chkUser = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $chkUser->execute([$email]);
        $existingUser = $chkUser->fetch(PDO::FETCH_ASSOC);

        $userId = 0;
        if ($existingUser) {
            $userId = (int)$existingUser['id'];
        } else {
            $insUser = $pdo->prepare("
                INSERT INTO users (name, email, password_hash, role, status, created_at, updated_at) 
                VALUES (?, ?, ?, 'client', ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");
            $insUser->execute([$contactPerson, $email, password_hash($rawPassword, PASSWORD_DEFAULT), $status]);
            $userId = (int)$pdo->lastInsertId();
        }

        $insClient = $pdo->prepare("
            INSERT INTO clients (user_id, company_name, contact_person, email, phone, address, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $insClient->execute([$userId, $companyName, $contactPerson, $email, $phone, $address, $status]);
        $newClientId = (int)$pdo->lastInsertId();

        logAdminActivity(
            $pdo, 
            (int)$currentAdmin['id'], 
            $currentAdmin['full_name'], 
            'Client Created', 
            'clients', 
            $newClientId, 
            "Created client #{$newClientId} ({$companyName})"
        );

        $pdo->commit();
        jsonResponse(true, "Client {$companyName} created successfully.", [], ['client_id' => $newClientId, 'user_id' => $userId]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(false, 'Database error while saving client: ' . $e->getMessage(), [], [], 500);
}
