<?php
/**
 * GrihGO Digital & Tech — Admin Portal Login
 * Version: 4.0 (Part 4 - Complete Admin Panel & CRM Architecture)
 * Technology: PHP 8+, PDO, CSRF, Secure Sessions, Rate Limiting, Vanilla CSS3
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/functions.php';

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
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

// If already logged in as admin, redirect directly to dashboard
if (!empty($_SESSION['admin_id']) && !empty($_SESSION['admin_role'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'logged_out') {
        $success = 'You have been logged out securely.';
    } elseif ($_GET['msg'] === 'timeout') {
        $error = 'Session expired due to inactivity. Please log in again.';
    } elseif ($_GET['msg'] === 'suspended') {
        $error = 'This administrative account is currently suspended. Please contact Super Admin.';
    }
}

// Process Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF Validation
    $submittedCsrf = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($submittedCsrf)) {
        $error = 'Security session validation expired. Please refresh the page and try again.';
    } else {
        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // 2. Rate Limiting Check: Max 5 failed attempts per IP within 15 minutes
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $lockKey = 'admin_login_fails_' . md5($ip);
        $attempts = $_SESSION[$lockKey]['count'] ?? 0;
        $firstAttemptTime = $_SESSION[$lockKey]['time'] ?? time();

        if ($attempts >= 5 && (time() - $firstAttemptTime < 900)) {
            $waitTime = ceil((900 - (time() - $firstAttemptTime)) / 60);
            $error = "Too many failed login attempts. Please wait {$waitTime} minute(s) before trying again.";
        } elseif (empty($email) || empty($password)) {
            $error = 'Please enter both your administrative email and password.';
        } else {
            try {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role, status FROM admins WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin && password_verify($password, $admin['password_hash'])) {
                    if ($admin['status'] !== 'active') {
                        $error = 'Your administrator account has been deactivated. Please contact Super Admin.';
                    } else {
                        // Reset rate limiter on successful authentication
                        unset($_SESSION[$lockKey]);

                        // Regenerate Session ID to prevent session fixation attacks
                        session_regenerate_id(true);

                        $_SESSION['admin_id'] = (int)$admin['id'];
                        $_SESSION['admin_name'] = $admin['full_name'];
                        $_SESSION['admin_email'] = $admin['email'];
                        $_SESSION['admin_role'] = $admin['role'];
                        $_SESSION['admin_last_activity'] = time();

                        // Update last login timestamp
                        $upd = $pdo->prepare("UPDATE admins SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?");
                        $upd->execute([$admin['id']]);

                        // Log Activity
                        logAdminActivity($pdo, (int)$admin['id'], $admin['full_name'], 'Admin Login', 'admins', (int)$admin['id'], 'Admin logged in successfully from ' . $ip);

                        // Redirect to intended URL or Dashboard
                        $redirect = $_GET['redirect'] ?? 'dashboard.php';
                        if (str_contains($redirect, 'http://') || str_contains($redirect, 'https://') || str_starts_with($redirect, '//')) {
                            $redirect = 'dashboard.php';
                        }
                        header('Location: ' . $redirect);
                        exit;
                    }
                } else {
                    // Record failure attempt for rate limiting
                    if ($attempts === 0 || (time() - $firstAttemptTime >= 900)) {
                        $_SESSION[$lockKey] = ['count' => 1, 'time' => time()];
                    } else {
                        $_SESSION[$lockKey]['count']++;
                    }
                    $error = 'Invalid administrative credentials. Please verify your email and password.';
                }
            } catch (Exception $e) {
                $error = 'Authentication service is temporarily unavailable. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Login | GrihGO Digital & Tech</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%231e5ba8'><path d='M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'/></svg>">
</head>
<body class="admin-login-body">
    <div class="admin-login-container">
        <div class="admin-login-card">
            <div class="admin-login-brand">
                <a href="../index.php" class="brand-link" title="GrihGO Digital & Tech Home">
                    <span class="brand-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </span>
                    <span class="brand-text">GrihGO<span class="brand-highlight">.Admin</span></span>
                </a>
                <p class="brand-subtext">Enterprise Operations, CRM & Management Console</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="admin-alert admin-alert-danger" role="alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="admin-alert admin-alert-success" role="alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php<?= !empty($_GET['redirect']) ? '?redirect=' . htmlspecialchars(urlencode($_GET['redirect'])) : '' ?>" method="POST" class="admin-login-form">
                <?= getCsrfField() ?>

                <div class="form-group">
                    <label for="adminEmail" class="form-label">Admin Email Address</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <input type="email" id="adminEmail" name="email" class="form-control" placeholder="Administrator email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label for="adminPassword" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" id="adminPassword" name="password" class="form-control" placeholder="••••••••••••" required autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-admin-login">
                    <span>Authenticate & Access Console</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </form>

            <div class="admin-login-footer">
                <div class="login-links">
                    <a href="../login.php">Switch to Client Portal Login</a>
                    <span class="sep">•</span>
                    <a href="../index.php">Back to Main Website</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
