<?php
/**
 * GrihGO Digital & Tech — Client Password Change API Endpoint
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method Not Allowed', [], [], 405);
}

// 1. Authentication Check
if (empty($_SESSION['user_id']) || empty($_SESSION['client_id']) || ($_SESSION['role'] ?? '') !== 'client') {
    jsonResponse(false, 'Unauthorized. Please sign in.', [], [], 401);
}

// 2. CSRF Token Validation
$token = $_POST['csrf_token'] ?? '';
if (!validateCsrfToken($token)) {
    jsonResponse(false, 'Security validation failed. Please refresh the page.', ['csrf' => 'Invalid token'], [], 403);
}

// 3. Extract Passwords
$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$errors = [];
if (empty($currentPassword)) {
    $errors['current_password'] = 'Current password is required.';
}
if (empty($newPassword)) {
    $errors['new_password'] = 'New password is required.';
} elseif (strlen($newPassword) < 8) {
    $errors['new_password'] = 'New password must be at least 8 characters in length.';
}
if ($newPassword !== $confirmPassword) {
    $errors['confirm_password'] = 'New password and confirmation do not match.';
}

if (!empty($errors)) {
    jsonResponse(false, 'Please correct the highlighted errors.', $errors, [], 422);
}

try {
    $pdo = Database::getConnection();
    $userId = (int)$_SESSION['user_id'];

    // Fetch existing password hash
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :uid AND role = 'client' LIMIT 1");
    $stmt->execute([':uid' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        jsonResponse(false, 'Current password entered is incorrect.', ['current_password' => 'Incorrect password'], [], 400);
    }

    if (password_verify($newPassword, $user['password_hash'])) {
        jsonResponse(false, 'New password cannot be the same as your current password.', ['new_password' => 'Cannot reuse password'], [], 400);
    }

    // Securely hash new password
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);

    $upd = $pdo->prepare("UPDATE users SET password_hash = :hash, remember_token = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = :uid");
    $upd->execute([':hash' => $newHash, ':uid' => $userId]);

    // Regenerate session id
    session_regenerate_id(true);

    jsonResponse(true, 'Your account password has been updated successfully.', [], []);

} catch (Exception $e) {
    jsonResponse(false, 'An internal error occurred while updating password.', [], [], 500);
}
