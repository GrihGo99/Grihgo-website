<?php
/**
 * GrihGO Digital & Tech — Request Product Demo Endpoint
 * Version: 2.0 (Part 2 - Backend & Lead Architecture)
 * Technology: PHP 8+, PDO, CSRF, Prepared Statements, JSON API
 */

require_once dirname(__DIR__) . '/config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', ['method' => 'POST required'], [], 405);
}

// Check rate limit (Max 5 requests per 60 seconds)
if (!checkRateLimit('demo', 5, 60)) {
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
$product      = sanitizeInput($_POST['product'] ?? '');
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

if (empty($product)) {
    $errors['product'] = 'Product selection is required.';
}

if (!empty($errors)) {
    jsonResponse(false, 'Please correct the highlighted form errors.', $errors, [], 422);
}

// 4. Database Operations
try {
    $pdo = Database::getConnection();

    // Map product if passed as slug
    $found = findProduct($product);
    $productTitle = $found ? $found['name'] : $product;

    // A. Store in leads table as requested in Section 12
    $leadNotes = "Product Demo Requested: " . $productTitle;
    if ($message) {
        $leadNotes .= " | Specific Requirements: " . $message;
    }

    $leadId = createLead($pdo, [
        'name'          => $fullName,
        'business_name' => $businessName ?: null,
        'phone'         => $phone,
        'email'         => $email,
        'service'       => $productTitle,
        'source'        => 'Request Demo',
        'status'        => 'New',
        'notes'         => $leadNotes
    ]);

    // B. Create Notification
    createNotification(
        $pdo,
        'New Demo Request',
        "New Demo Request: {$productTitle}",
        "Requested by {$fullName} ({$businessName}) | Tel: {$phone} | Email: {$email}",
        $leadId
    );

    // C. Email Notification Architecture
    sendNotificationEmail('New Demo Request', "Live Demo Request: {$productTitle} by {$fullName}", [
        'lead_id'      => $leadId,
        'full_name'    => $fullName,
        'business'     => $businessName,
        'email'        => $email,
        'phone'        => $phone,
        'product'      => $productTitle,
        'message'      => $message
    ]);

    // D. Return UX Response (Section 29)
    $responseMsg = "Demo Request Received. Thank you for your interest in GrihGO {$productTitle}. Our team will contact you shortly.";

    jsonResponse(
        true,
        $responseMsg,
        [],
        [
            'lead_id' => $leadId,
            'product' => $productTitle,
            'name'    => $fullName
        ]
    );

} catch (Exception $e) {
    error_log("Demo Request Error: " . $e->getMessage());
    jsonResponse(false, 'Unable to schedule demo request at this moment. Please reach out to our team directly on WhatsApp.', ['server' => 'Database error'], [], 500);
}
