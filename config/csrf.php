<?php
/**
 * GrihGO Digital & Tech — CSRF Protection Manager
 * Version: 2.0 (Part 2 - Security Architecture)
 * Technology: PHP 8+ Cryptographic Tokens & Session Hardening
 */

// Initialize hardened session if not already active
if (session_status() === PHP_SESSION_NONE) {
    // Session security headers & settings
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    @session_start();
}

/**
 * Generate or retrieve current active CSRF token.
 * 
 * @return string Cryptographically secure 64-character token
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    // Regenerate if older than 4 hours for added security
    if (time() - $_SESSION['csrf_token_time'] > 14400) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    return $_SESSION['csrf_token'];
}

/**
 * Get the current CSRF token string.
 * 
 * @return string
 */
function getCsrfToken(): string {
    return generateCsrfToken();
}

/**
 * Render standard HTML hidden input field for CSRF token.
 * 
 * @return string HTML input string
 */
function getCsrfField(): string {
    $token = htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Validate submitted CSRF token.
 * 
 * @param string|null $token Submitted token from POST/Headers
 * @return bool True if valid, false otherwise
 */
function validateCsrfToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require valid CSRF token or terminate with standardized security message.
 * 
 * @param string|null $token
 */
function verifyCsrfOrExit(?string $token, bool $isAjax = true): void {
    if (!validateCsrfToken($token)) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request. Please refresh the page and try again.',
                'errors'  => ['csrf' => 'Invalid security token.']
            ]);
            exit;
        } else {
            http_response_code(403);
            die('Invalid request. Please refresh the page and try again.');
        }
    }
}
