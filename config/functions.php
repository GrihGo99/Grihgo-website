<?php
/**
 * GrihGO Digital & Tech — Core Backend Utilities & Security Engine
 * Version: 2.0 (Part 2 - Backend, Database & Lead Architecture)
 * Technology: PHP 8+, PDO, CSRF, Sanitization, Validation & Notifications
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/product-data.php';
require_once __DIR__ . '/service-data.php';

/* --------------------------------------------------------------------------
   0. Production Error Handling & Security Headers
   -------------------------------------------------------------------------- */
// In production, log errors securely and do not display raw stack traces to visitors
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('log_errors', '1');
$logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
ini_set('error_log', $logDir . DIRECTORY_SEPARATOR . 'php_error.log');

/**
 * Send standard enterprise security headers.
 */
function sendSecurityHeaders(): void {
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
}
sendSecurityHeaders();

/* --------------------------------------------------------------------------
   1. Input Sanitization & Normalization
   -------------------------------------------------------------------------- */

/**
 * Sanitize plain string input (names, text, messages).
 */
function sanitizeInput(?string $data): string {
    if ($data === null) return '';
    $data = trim($data);
    $data = strip_tags($data);
    // Remove invisible control characters except newline
    $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $data);
    return $data;
}

/**
 * Sanitize email address.
 */
function sanitizeEmail(?string $email): string {
    if ($email === null) return '';
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL) ?: '';
}

/**
 * Sanitize telephone number.
 */
function sanitizePhone(?string $phone): string {
    if ($phone === null) return '';
    $phone = trim($phone);
    // Keep digits, plus sign, spaces, hyphens and parenthesis
    return preg_replace('/[^\d\+\-\s\(\)]/', '', $phone);
}

/* --------------------------------------------------------------------------
   2. Input Validation Rules
   -------------------------------------------------------------------------- */

/**
 * Validate person or organization name.
 */
function validateName(?string $name, int $min = 2, int $max = 150): bool {
    if ($name === null) return false;
    $len = mb_strlen(trim($name));
    return $len >= $min && $len <= $max;
}

/**
 * Validate email syntax.
 */
function validateEmail(?string $email): bool {
    if ($email === null) return false;
    $email = trim($email);
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false && mb_strlen($email) <= 150;
}

/**
 * Validate phone number format (international and Indian standards).
 */
function validatePhone(?string $phone): bool {
    if ($phone === null) return false;
    $clean = preg_replace('/[^\d]/', '', $phone);
    $len = strlen($clean);
    return $len >= 7 && $len <= 15;
}

/**
 * Validate date format (YYYY-MM-DD) and ensure it is not in the past.
 */
function validateBookingDate(?string $date): bool {
    if (empty($date)) return true; // Optional field
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        return false;
    }
    $today = new DateTime('today');
    return $d >= $today;
}

/**
 * Validate required field is not empty.
 */
function validateRequired(?string $val): bool {
    return $val !== null && trim($val) !== '';
}

/* --------------------------------------------------------------------------
   3. Rate Limiting (Session & IP based)
   -------------------------------------------------------------------------- */

/**
 * Basic rate limiting to prevent automated spam and bots.
 * 
 * @param string $action Unique action key
 * @param int $maxAttempts Max allowed submissions in window
 * @param int $windowSeconds Window duration in seconds
 * @return bool True if within rate limit, false if exceeded
 */
function checkRateLimit(string $action = 'submit', int $maxAttempts = 5, int $windowSeconds = 60): bool {
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }

    $now = time();
    $limits = $_SESSION['rate_limits'][$action] ?? [];

    // Filter out attempts older than the window
    $limits = array_filter($limits, fn($timestamp) => ($now - $timestamp) < $windowSeconds);

    if (count($limits) >= $maxAttempts) {
        return false;
    }

    $limits[] = $now;
    $_SESSION['rate_limits'][$action] = $limits;
    return true;
}

/* --------------------------------------------------------------------------
   4. Centralized CRM Lead Capture
   -------------------------------------------------------------------------- */

/**
 * Save lead into the central `leads` CRM table.
 *
 * @param PDO $pdo
 * @param array $data Lead attributes
 * @return int Inserted Lead ID
 */
function createLead(PDO $pdo, array $data): int {
    $leadName = $data['name'] ?? ($data['full_name'] ?? 'Website Visitor');
    $leadNotes = $data['notes'] ?? ($data['message'] ?? null);
    $leadBudget = $data['budget'] ?? null;

    $sql = "INSERT INTO leads (
                name, full_name, business_name, phone, email, service, budget, source, 
                appointment_id, status, notes, message, created_at, updated_at
            ) VALUES (
                :name, :full_name, :business_name, :phone, :email, :service, :budget, :source,
                :appointment_id, :status, :notes, :message, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'           => $leadName,
        ':full_name'      => $leadName,
        ':business_name'  => $data['business_name'] ?? null,
        ':phone'          => $data['phone'] ?? '',
        ':email'          => $data['email'] ?? '',
        ':service'        => $data['service'] ?? 'General Inquiry',
        ':budget'         => $leadBudget,
        ':source'         => $data['source'] ?? 'Website',
        ':appointment_id' => $data['appointment_id'] ?? null,
        ':status'         => $data['status'] ?? 'New',
        ':notes'          => $leadNotes,
        ':message'        => $leadNotes,
    ]);

    return (int)$pdo->lastInsertId();
}

/* --------------------------------------------------------------------------
   5. Admin Notification Engine
   -------------------------------------------------------------------------- */

/**
 * Store an administrative notification in the `notifications` table.
 *
 * @param PDO $pdo
 * @param string $type E.g. 'New Appointment', 'New Lead', 'New Demo Request'
 * @param string $title
 * @param string $message
 * @param int|null $refId
 * @return int Inserted Notification ID
 */
function createNotification(PDO $pdo, string $type, string $title, string $message, ?int $refId = null): int {
    $sql = "INSERT INTO notifications (type, title, message, reference_id, is_read, created_at)
            VALUES (:type, :title, :message, :ref_id, 0, CURRENT_TIMESTAMP)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':type'    => $type,
        ':title'   => $title,
        ':message' => $message,
        ':ref_id'  => $refId
    ]);

    return (int)$pdo->lastInsertId();
}

/* --------------------------------------------------------------------------
   6. Email-Ready Architecture (Section 20)
   -------------------------------------------------------------------------- */

/**
 * Send administrative or customer notification email.
 * Configured so external SMTP can be seamlessly integrated in Part 4.
 *
 * @param string $type Notification type
 * @param string $subject Email subject line
 * @param array $data Key-value data of the submission
 * @param string|null $recipient Optional override recipient
 * @return bool
 */
function sendNotificationEmail(string $type, string $subject, array $data, ?string $recipient = null): bool {
    $to = $recipient ?: 'admin@grihgodigital.com';
    $timestamp = date('Y-m-d H:i:s');

    // Build structured log message
    $body = "=== GrihGO System Notification ===\n";
    $body .= "Type: {$type}\n";
    $body .= "Subject: {$subject}\n";
    $body .= "Recipient: {$to}\n";
    $body .= "Time: {$timestamp}\n";
    $body .= "Data Payload:\n";
    foreach ($data as $k => $v) {
        $body .= "  - " . ucfirst(str_replace('_', ' ', $k)) . ": " . (is_array($v) ? json_encode($v) : $v) . "\n";
    }
    $body .= "==================================\n\n";

    // Write to audit notification log
    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    @file_put_contents($logDir . DIRECTORY_SEPARATOR . 'notifications.log', $body, FILE_APPEND);

    // If native PHP mail is enabled and production configured:
    if (getenv('SMTP_ENABLED') === 'true' && function_exists('mail')) {
        $headers = "From: no-reply@grihgodigital.com\r\n";
        $headers .= "Reply-To: " . ($data['email'] ?? 'contact@grihgodigital.com') . "\r\n";
        $headers .= "X-Mailer: GrihGO-Tech-Engine/2.0\r\n";
        @mail($to, $subject, $body, $headers);
    }

    return true;
}

/* --------------------------------------------------------------------------
   7. Standard JSON API Response Dispatcher
   -------------------------------------------------------------------------- */

/**
 * Output standardized JSON response and exit cleanly.
 *
 * @param bool $success
 * @param string $message
 * @param array $errors
 * @param array $data
 * @param int $httpCode
 */
function jsonResponse(bool $success, string $message, array $errors = [], array $data = [], int $httpCode = 200): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($httpCode);
    }

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'errors'  => $errors,
        'data'    => $data,
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/* --------------------------------------------------------------------------
   8. Session Flash Alerts (For Non-AJAX Fallbacks)
   -------------------------------------------------------------------------- */

function setFlashMessage(string $type, string $message): void {
    $_SESSION['flash_message'] = [
        'type'    => $type,
        'message' => $message
    ];
}

function getFlashMessage(): ?array {
    if (!empty($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/* --------------------------------------------------------------------------
   9. Global Settings & Metadata Resolver
   -------------------------------------------------------------------------- */

/**
 * Fetch all website settings as key-value map with robust default fallbacks.
 */
function getAllSettings(): array {
    static $settingsCache = null;
    if ($settingsCache !== null) {
        return $settingsCache;
    }

    $defaults = [
        'company_name'      => 'GrihGO Digital & Tech',
        'tagline'           => 'Technology • Innovation • Growth',
        'phone'             => '8092932060',
        'whatsapp'          => '8092932060',
        'email'             => 'contact@grihgo.com',
        'office_address'    => 'AIIMS Patna, Phulwari Sharif, Patna, Bihar, India',
        'website'           => 'https://grihgodigital.com',
        'footer_copyright'  => '© 2026 GrihGO Digital & Tech. All Rights Reserved.',
        'social_instagram'  => 'https://instagram.com/grihgodigital',
        'social_facebook'   => 'https://facebook.com/grihgodigital',
        'social_linkedin'   => 'https://linkedin.com/company/grihgodigital',
        'social_youtube'    => 'https://youtube.com/@grihgodigital',
        'social_x'          => 'https://x.com/grihgodigital',
        'default_seo_title' => 'GrihGO Digital & Tech | Website, Software, AI & Digital Marketing Solutions',
        'default_seo_desc'  => 'GrihGO Digital & Tech provides website development, software solutions, application development, AI automation, SEO, social media marketing and performance marketing services for businesses in Patna, Bihar and across India.'
    ];

    try {
        $pdo = Database::getConnection();
        $rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        if ($rows) {
            foreach ($rows as $k => $v) {
                if ($v !== null && trim($v) !== '') {
                    $defaults[$k] = $v;
                }
            }
        }
    } catch (Exception $e) {
        // Fallback to defaults
    }

    $settingsCache = $defaults;
    return $settingsCache;
}

/**
 * Retrieve a specific corporate setting with default fallback.
 */
function getSetting(string $key, string $default = ''): string {
    $settings = getAllSettings();
    return $settings[$key] ?? $default;
}

/** Return a digits-only WhatsApp number, adding India's country code to local numbers. */
function getWhatsAppNumber(): string {
    $number = preg_replace('/\D+/', '', getSetting('whatsapp', '8092932060')) ?? '';
    if (strlen($number) === 10) {
        return '91' . $number;
    }
    return str_starts_with($number, '00') ? substr($number, 2) : $number;
}

/* --------------------------------------------------------------------------
   10. System Role-Based Notifications
   -------------------------------------------------------------------------- */

/**
 * Dispatch role-targeted in-app notification (admin, manager, or client).
 * Polymorphic handler supporting both (role, id, type, title, message, refId)
 * and (targetId, title, message, role, link) parameter conventions.
 */
function addSystemNotification($arg1, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null): bool {
    try {
        $pdo = Database::getConnection();
        
        $recipientRole = 'admin';
        $recipientId   = null;
        $type          = 'system';
        $title         = '';
        $message       = '';
        $referenceId   = null;
        $link          = null;

        // Signature A: (string $recipientRole, ?int $recipientId, string $type, string $title, string $message, ?int $referenceId = null)
        if (is_string($arg1) && in_array(strtolower($arg1), ['admin', 'manager', 'client', 'super_admin'], true)) {
            $recipientRole = strtolower($arg1);
            $recipientId   = is_numeric($arg2) ? (int)$arg2 : null;
            $type          = (string)($arg3 ?? 'system');
            $title         = (string)($arg4 ?? '');
            $message       = (string)($arg5 ?? '');
            $referenceId   = is_numeric($arg6) ? (int)$arg6 : null;
        } 
        // Signature B: (?int $targetUserId, string $title, string $message, string $role = 'admin', ?string $link = null)
        else {
            $recipientId   = is_numeric($arg1) ? (int)$arg1 : null;
            $title         = (string)($arg2 ?? '');
            $message       = (string)($arg3 ?? '');
            $recipientRole = is_string($arg4) ? strtolower($arg4) : 'admin';
            $link          = is_string($arg5) ? $arg5 : null;
            $type          = 'system';
        }

        $stmt = $pdo->prepare("
            INSERT INTO notifications (client_id, user_role, type, title, message, reference_id, user_id, user_type, link, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP)
        ");
        return $stmt->execute([
            $recipientId,
            $recipientRole,
            $type,
            $title,
            $message,
            $referenceId,
            $recipientId,
            $recipientRole,
            $link
        ]);
    } catch (Exception $e) {
        return false;
    }
}

