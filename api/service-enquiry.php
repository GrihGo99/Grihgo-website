<?php
/**
 * GrihGO Digital & Tech — Service Requirement / Quote Enquiry Endpoint
 * Version: 2.0 (Part 2 - Backend & Lead Architecture)
 * Technology: PHP 8+, PDO, CSRF, Prepared Statements, JSON API
 */

require_once dirname(__DIR__) . '/config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', ['method' => 'POST required'], [], 405);
}

// Check rate limit (Max 5 requests per 60 seconds)
if (!checkRateLimit('service_enquiry', 5, 60)) {
    jsonResponse(false, 'Too many requests. Please wait a minute and try again.', ['rate_limit' => 'Rate limit exceeded'], [], 429);
}

// 1. Validate CSRF Token
$csrfToken = $_POST['csrf_token'] ?? null;
if (!validateCsrfToken($csrfToken)) {
    jsonResponse(false, 'Invalid request. Please refresh the page and try again.', ['csrf' => 'Invalid security token'], [], 403);
}

// 2. Extract & Sanitize
$fullName     = sanitizeInput($_POST['full_name'] ?? '');
$businessName = sanitizeInput($_POST['business_name'] ?? '');
$email        = sanitizeEmail($_POST['email'] ?? '');
$phone        = sanitizePhone($_POST['phone'] ?? '');
$service      = sanitizeInput($_POST['service'] ?? '');
$budget       = sanitizeInput($_POST['budget'] ?? '');
$message      = sanitizeInput($_POST['message'] ?? '');

// 3. Validation
$errors = [];

if (!validateName($fullName, 2, 150)) {
    $errors['full_name'] = 'Please enter your full name (at least 2 characters).';
}

if (!validateEmail($email)) {
    $errors['email'] = 'Please enter a valid work email address.';
}

if (!validatePhone($phone)) {
    $errors['phone'] = 'Please enter a valid phone number.';
}

if (empty($service)) {
    $errors['service'] = 'Service selection is required.';
}

if (!empty($errors)) {
    jsonResponse(false, 'Please correct the highlighted form errors.', $errors, [], 422);
}

// 4. Database Operations
try {
    $pdo = Database::getConnection();

    // Map service title if slug passed
    $found = findService($service);
    $serviceTitle = $found ? $found['name'] : $service;

    // A. Save Lead into CRM
    $leadNotes = "Service Quote Requirement: " . $serviceTitle;
    if ($budget) {
        $leadNotes .= " | Budget Range: " . $budget;
    }
    if ($message) {
        $leadNotes .= " | Project Scope: " . $message;
    }

    $leadId = createLead($pdo, [
        'name'          => $fullName,
        'business_name' => $businessName ?: null,
        'phone'         => $phone,
        'email'         => $email,
        'service'       => $serviceTitle,
        'source'        => 'Service Enquiry',
        'status'        => 'New',
        'notes'         => $leadNotes
    ]);

    // B. Save into contact_messages for cross-reference
    $contactSql = "INSERT INTO contact_messages (name, business_name, email, phone, service, message, status, created_at)
                   VALUES (:name, :business, :email, :phone, :service, :message, 'New', CURRENT_TIMESTAMP)";
    $contactStmt = $pdo->prepare($contactSql);
    $contactStmt->execute([
        ':name'     => $fullName,
        ':business' => $businessName ?: null,
        ':email'    => $email,
        ':phone'    => $phone,
        ':service'  => $serviceTitle,
        ':message'  => "Quote Request. Budget: {$budget}. Scope: {$message}"
    ]);
    $contactMsgId = (int)$pdo->lastInsertId();

    // C. Create Notification
    createNotification(
        $pdo,
        'New Service Enquiry',
        "New Quote Request: {$serviceTitle}",
        "From {$fullName} ({$businessName}) | Budget: {$budget} | Tel: {$phone}",
        $leadId
    );

    // D. Email Notification Architecture
    sendNotificationEmail('New Service Enquiry', "New Quote Request: {$serviceTitle} by {$fullName}", [
        'lead_id'      => $leadId,
        'contact_id'   => $contactMsgId,
        'full_name'    => $fullName,
        'business'     => $businessName,
        'email'        => $email,
        'phone'        => $phone,
        'service'      => $serviceTitle,
        'budget'       => $budget,
        'message'      => $message
    ]);

    // E. Return Success Message
    jsonResponse(
        true,
        "Thank you for contacting GrihGO Digital & Tech. Your requirement for {$serviceTitle} has been received. Our solutions team will get in touch shortly.",
        [],
        [
            'lead_id' => $leadId,
            'service' => $serviceTitle,
            'name'    => $fullName
        ]
    );

} catch (Exception $e) {
    error_log("Service Enquiry Error: " . $e->getMessage());
    jsonResponse(false, 'Unable to submit your requirement right now. Please connect with us directly via WhatsApp.', ['server' => 'Database error'], [], 500);
}
