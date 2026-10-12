<?php
/**
 * GrihGO Digital & Tech — Appointment Submission Endpoint
 * Version: 2.0 (Part 2 - Backend & Lead Architecture)
 * Technology: PHP 8+, PDO, CSRF, Prepared Statements, JSON API
 */

require_once dirname(__DIR__) . '/config/functions.php';

// Ensure only POST method is allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', ['method' => 'POST required'], [], 405);
}

// Check rate limit (Max 5 requests per 60 seconds)
if (!checkRateLimit('appointment', 5, 60)) {
    jsonResponse(false, 'Too many requests. Please wait a minute and try again.', ['rate_limit' => 'Rate limit exceeded'], [], 429);
}

// 1. Validate CSRF Token
$csrfToken = $_POST['csrf_token'] ?? null;
if (!validateCsrfToken($csrfToken)) {
    jsonResponse(false, 'Invalid request. Please refresh the page and try again.', ['csrf' => 'Invalid or expired token'], [], 403);
}

// 2. Extract & Sanitize Inputs
$fullName     = sanitizeInput($_POST['full_name'] ?? '');
$businessName = sanitizeInput($_POST['business_name'] ?? '');
$email        = sanitizeEmail($_POST['email'] ?? '');
$phone        = sanitizePhone($_POST['phone'] ?? '');
$service      = sanitizeInput($_POST['service'] ?? '');
$budgetRange  = sanitizeInput($_POST['budget_range'] ?? '');
$preferredDate = sanitizeInput($_POST['preferred_date'] ?? '');
$preferredTime = sanitizeInput($_POST['preferred_time'] ?? '');
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
    $errors['phone'] = 'Please enter a valid phone or WhatsApp number.';
}

if (!validateRequired($service)) {
    $errors['service'] = 'Please choose a service or product of interest.';
}

if (!empty($preferredDate) && !validateBookingDate($preferredDate)) {
    $errors['preferred_date'] = 'Preferred date cannot be in the past.';
}

if (!empty($errors)) {
    jsonResponse(false, 'Please correct the highlighted form errors.', $errors, [], 422);
}

// 4. Database Operations
try {
    $pdo = Database::getConnection();

    // Map service code to readable title
    $serviceList = [
        'website-development'    => 'Website Development',
        'mobile-app'             => 'Mobile App Development',
        'software-development'   => 'Custom Software Development',
        'whatsapp-crm'           => 'GrihGO WhatsApp CRM',
        'billing-software'       => 'GST Billing Software',
        'crm'                    => 'Enterprise CRM',
        'erp'                    => 'Enterprise ERP',
        'inventory'              => 'Inventory Management',
        'ai-solutions'           => 'AI Solutions & Chatbots',
        'digital-marketing'      => 'Digital Marketing',
        'seo'                    => 'Search Engine Optimization (SEO)',
        'performance-marketing'  => 'Performance Marketing',
        'other'                  => 'Other Inquiries'
    ];
    $serviceReadable = $serviceList[$service] ?? ucwords(str_replace('-', ' ', $service));

    // A. Insert Appointment
    $sql = "INSERT INTO appointments (
                full_name, business_name, email, phone, service, budget_range,
                preferred_date, preferred_time, appointment_date, appointment_time, message, status, created_at, updated_at
            ) VALUES (
                :full_name, :business_name, :email, :phone, :service, :budget_range,
                :preferred_date, :preferred_time, :appointment_date, :appointment_time, :message, 'Pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name'        => $fullName,
        ':business_name'    => $businessName ?: null,
        ':email'            => $email,
        ':phone'            => $phone,
        ':service'          => $serviceReadable,
        ':budget_range'     => $budgetRange ?: null,
        ':preferred_date'   => $preferredDate ?: null,
        ':preferred_time'   => $preferredTime ?: null,
        ':appointment_date' => $preferredDate ?: null,
        ':appointment_time' => $preferredTime ?: null,
        ':message'          => $message ?: null,
    ]);

    $appointmentId = (int)$pdo->lastInsertId();

    // B. Create Associated Lead in CRM
    $leadNotes = "Appointment Booked (#{$appointmentId})";
    if ($budgetRange) $leadNotes .= " | Budget: {$budgetRange}";
    if ($preferredDate) $leadNotes .= " | Date: {$preferredDate} ({$preferredTime})";
    if ($message) $leadNotes .= " | Notes: {$message}";

    $leadId = createLead($pdo, [
        'name'           => $fullName,
        'business_name'  => $businessName,
        'phone'          => $phone,
        'email'          => $email,
        'service'        => $serviceReadable,
        'source'         => 'Appointment',
        'appointment_id' => $appointmentId,
        'status'         => 'New',
        'notes'          => $leadNotes
    ]);

    // C. Create System Notification
    createNotification(
        $pdo,
        'New Appointment',
        "New Appointment: {$fullName}",
        "Booked for {$serviceReadable}" . ($preferredDate ? " on {$preferredDate}" : '') . ". Contact: {$phone} / {$email}",
        $appointmentId
    );

    // D. Email Notification Architecture
    sendNotificationEmail('New Appointment', "New Appointment Scheduled: {$fullName}", [
        'appointment_id' => $appointmentId,
        'lead_id'        => $leadId,
        'name'           => $fullName,
        'business'       => $businessName,
        'email'          => $email,
        'phone'          => $phone,
        'service'        => $serviceReadable,
        'budget'         => $budgetRange,
        'date'           => $preferredDate,
        'time'           => $preferredTime,
        'message'        => $message
    ]);

    // E. Return Professional Success Message
    jsonResponse(
        true,
        'Thank you for contacting GrihGO Digital & Tech. Your appointment request has been received. Our team will contact you shortly.',
        [],
        [
            'appointment_id' => $appointmentId,
            'lead_id'        => $leadId,
            'client_name'    => $fullName
        ]
    );

} catch (Exception $e) {
    // Log exception safely
    error_log("Appointment Submission Error: " . $e->getMessage());
    jsonResponse(false, 'Unable to process your request at this time. Please try again or contact us via WhatsApp.', ['server' => 'Database error'], [], 500);
}
