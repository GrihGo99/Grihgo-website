<?php
/**
 * GrihGO Digital & Tech — Admin Appointment Update API
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

$apptId = (int)($_POST['id'] ?? $_POST['appointment_id'] ?? 0);
if ($apptId <= 0) {
    jsonResponse(false, 'Valid appointment ID is required.', ['id' => 'Required'], [], 422);
}

// Fetch Appointment
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? LIMIT 1");
$stmt->execute([$apptId]);
$appt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appt) {
    jsonResponse(false, 'Appointment not found.', [], [], 404);
}

$validStatuses = ['Pending', 'Confirmed', 'Rescheduled', 'Completed', 'Cancelled'];
$newStatus = isset($_POST['status']) && in_array($_POST['status'], $validStatuses, true) ? $_POST['status'] : $appt['status'];
$prefDate  = !empty($_POST['preferred_date']) ? sanitizeInput($_POST['preferred_date']) : $appt['preferred_date'];
$prefTime  = !empty($_POST['preferred_time']) ? sanitizeInput($_POST['preferred_time']) : $appt['preferred_time'];
$clientId  = isset($_POST['client_id']) ? (int)$_POST['client_id'] : (int)$appt['client_id'];
$adminNote = !empty($_POST['admin_notes']) ? sanitizeInput($_POST['admin_notes']) : '';

try {
    $msg = $appt['message'];
    if (!empty($adminNote)) {
        $msg .= "\n[Admin Note - " . date('d M Y') . "]: " . $adminNote;
    }

    $upd = $pdo->prepare("
        UPDATE appointments 
        SET status = :status, preferred_date = :pdate, preferred_time = :ptime, client_id = :cid, message = :msg, updated_at = CURRENT_TIMESTAMP 
        WHERE id = :id
    ");
    $upd->execute([
        ':status' => $newStatus,
        ':pdate'  => $prefDate,
        ':ptime'  => $prefTime,
        ':cid'    => $clientId > 0 ? $clientId : null,
        ':msg'    => $msg,
        ':id'     => $apptId
    ]);

    // Notify client if linked
    if ($clientId > 0 && function_exists('createClientNotification')) {
        createClientNotification(
            $pdo, 
            $clientId, 
            'Appointment ' . $newStatus, 
            "Your consultation scheduled for {$prefDate} {$prefTime} is now marked as {$newStatus}.", 
            'appointments', 
            $apptId
        );
    }

    logAdminActivity(
        $pdo, 
        (int)$currentAdmin['id'], 
        $currentAdmin['full_name'], 
        'Appointment Updated', 
        'appointments', 
        $apptId, 
        "Updated appointment #{$apptId} to {$newStatus} ({$prefDate} {$prefTime})"
    );

    jsonResponse(true, "Appointment #{$apptId} updated successfully.", [], [
        'id'             => $apptId,
        'status'         => $newStatus,
        'preferred_date' => $prefDate,
        'preferred_time' => $prefTime,
        'client_id'      => $clientId
    ]);
} catch (Exception $e) {
    jsonResponse(false, 'Database error while updating appointment: ' . $e->getMessage(), [], [], 500);
}
