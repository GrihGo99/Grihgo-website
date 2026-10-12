<?php
/**
 * GrihGO Digital & Tech — Contact Inquiry Submission Endpoint
 * Version: 2.0 (Part 2 - Backend, Database & Lead Architecture)
 * Technology: PHP 8+, PDO, CSRF, Prepared Statements, JSON API
 */

require_once dirname(__DIR__) . '/config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', ['method' => 'POST required'], [], 405);
}

// Check rate limit (Max 5 requests per 60 seconds)
if (!checkRateLimit('contact', 5, 60)) {
    jsonResponse(false, 'Too many requests. Please wait a minute and try again.', ['rate_limit' => 'Rate limit exceeded'], [], 429);
}

// 1. Validate CSRF Token
$csrfToken = $_POST['csrf_token'] ?? null;
if (!validateCsrfToken($csrfToken)) {
    jsonResponse(false, 'Invalid request. Please refresh the page and try again.', ['csrf' => 'Invalid security token'], [], 403);
}

// 2. Extract & Sanitize
$name         = sanitizeInput($_POST['name'] ?? $_POST['full_name'] ?? '');
$businessName = sanitizeInput($_POST['business_name'] ?? '');
$email        = sanitizeEmail($_POST['email'] ?? '');
$phone        = sanitizePhone($_POST['phone'] ?? '');
$service      = sanitizeInput($_POST['service'] ?? 'General Inquiry');
$message      = sanitizeInput($_POST['message'] ?? '');

// 3. Validation
$errors = [];

if (!validateName($name, 2, 150)) {
    $errors['name'] = 'Please enter your name (at least 2 characters).';
}

if (!validateEmail($email)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if (!validatePhone($phone)) {
    $errors['phone'] = 'Please enter a valid phone number.';
}

if (empty($message) || mb_strlen($message) < 5) {
    $errors['message'] = 'Please provide details regarding your inquiry (at least 5 characters).';
}

if (!empty($errors)) {
    jsonResponse(false, 'Please correct the highlighted form errors.', $errors, [], 422);
}

// 4. Database Operations
try {
    $pdo = Database::getConnection();

    // A. Insert into contact_messages
    $sql = "INSERT INTO contact_messages (
                name, business_name, email, phone, service, message, status, created_at
            ) VALUES (
                :name, :business_name, :email, :phone, :service, :message, 'New', CURRENT_TIMESTAMP
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'          => $name,
        ':business_name' => $businessName ?: null,
        ':email'         => $email,
        ':phone'         => $phone,
        ':service'       => $service,
        ':message'       => $message
    ]);

    $contactId = (int)$pdo->lastInsertId();

    // B. Create Lead in CRM Table
    $leadNotes = "Contact Message (#{$contactId}): " . $message;
    $leadId = createLead($pdo, [
        'name'          => $name,
        'business_name' => $businessName,
        'phone'         => $phone,
        'email'         => $email,
        'service'       => $service,
        'source'        => 'Contact Form',
        'status'        => 'New',
        'notes'         => $leadNotes
    ]);

    // C. Create Notification
    createNotification(
        $pdo,
        'New Contact Enquiry',
        "New Contact Message: {$name}",
        "Inquiry regarding {$service}. Message: " . mb_substr($message, 0, 120) . "...",
        $contactId
    );

    // D. Email Notification Architecture
    sendNotificationEmail('New Contact Message', "New Message from {$name} ({$service})", [
        'contact_id'    => $contactId,
        'lead_id'       => $leadId,
        'name'          => $name,
        'business'      => $businessName,
        'email'         => $email,
        'phone'         => $phone,
        'service'       => $service,
        'message'       => $message
    ]);

    // E. Return Success
    jsonResponse(
        true,
        'Thank You! Your enquiry has been successfully submitted. Our team will get back to you shortly.',
        [],
        [
            'contact_id' => $contactId,
            'lead_id'    => $leadId,
            'name'       => $name
        ]
    );

} catch (Exception $e) {
    error_log("Contact Submission Error: " . $e->getMessage());
    jsonResponse(false, 'Unable to submit your message at this time. Please try again or reach out on WhatsApp.', ['server' => 'Database error'], [], 500);
}
