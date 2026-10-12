<?php
/**
 * GrihGO Digital & Tech — Client Profile Update API
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method. Only POST allowed.', [], [], 405);
}

// 1. Verify Authentication & Role
if (empty($_SESSION['user_id']) || empty($_SESSION['client_id']) || ($_SESSION['role'] ?? '') !== 'client') {
    jsonResponse(false, 'Unauthorized. Please sign in.', ['auth' => 'Unauthorized'], [], 401);
}

// 2. Validate CSRF Token
$token = $_POST['csrf_token'] ?? '';
if (!validateCsrfToken($token)) {
    jsonResponse(false, 'Security token validation failed. Please refresh and try again.', ['csrf' => 'Invalid token'], [], 403);
}

// 3. Sanitize and Validate Allowed Fields
$fullName    = sanitizeInput($_POST['contact_person'] ?? '');
$companyName = sanitizeInput($_POST['company_name'] ?? '');
$phone       = sanitizePhone($_POST['phone'] ?? '');
$address     = sanitizeInput($_POST['address'] ?? '');

$errors = [];
if (!validateName($fullName, 2, 100)) {
    $errors['contact_person'] = 'Full name must be between 2 and 100 characters.';
}
if (!validateName($companyName, 2, 150)) {
    $errors['company_name'] = 'Business/Company name must be between 2 and 150 characters.';
}
if (!validatePhone($phone)) {
    $errors['phone'] = 'Please enter a valid phone number (7-15 digits).';
}

if (!empty($errors)) {
    jsonResponse(false, 'Please correct the validation errors below.', $errors, [], 422);
}

try {
    $pdo = Database::getConnection();
    $clientId = (int)$_SESSION['client_id'];
    $userId = (int)$_SESSION['user_id'];

    // Update clients table
    $stmtClient = $pdo->prepare("
        UPDATE clients 
        SET contact_person = :person, company_name = :company, phone = :phone, address = :address, updated_at = CURRENT_TIMESTAMP
        WHERE id = :cid AND user_id = :uid
    ");
    $stmtClient->execute([
        ':person'  => $fullName,
        ':company' => $companyName,
        ':phone'   => $phone,
        ':address' => $address,
        ':cid'     => $clientId,
        ':uid'     => $userId
    ]);

    // Update users table name and phone
    $stmtUser = $pdo->prepare("
        UPDATE users 
        SET name = :name, phone = :phone, updated_at = CURRENT_TIMESTAMP
        WHERE id = :uid AND role = 'client'
    ");
    $stmtUser->execute([
        ':name'  => $fullName,
        ':phone' => $phone,
        ':uid'   => $userId
    ]);

    // Update session user name
    $_SESSION['user_name'] = $fullName;

    jsonResponse(true, 'Profile updated successfully.', [], [
        'contact_person' => $fullName,
        'company_name'   => $companyName,
        'phone'          => $phone,
        'address'        => $address
    ]);

} catch (Exception $e) {
    jsonResponse(false, 'Unable to update profile due to an internal error.', [], [], 500);
}
