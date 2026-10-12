<?php
/**
 * GrihGO Digital & Tech — Manager Portal Login
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in as manager
if (!empty($_SESSION['manager_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$emailVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh the page and try again.';
    } elseif (!checkRateLimit('manager_login', 5, 300)) {
        $error = 'Too many failed login attempts. Please wait 5 minutes before trying again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $emailVal = htmlspecialchars($email);

        if (empty($email) || empty($password)) {
            $error = 'Please provide both your registered work email and password.';
        } else {
            try {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role, status FROM admins WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $account = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$account || !password_verify($password, $account['password_hash'])) {
                    $error = 'Invalid manager credentials. Please verify your email and password.';
                } elseif ($account['status'] !== 'active') {
                    $error = 'Your manager account has been deactivated. Please contact Super Admin.';
                } elseif (!in_array($account['role'], ['manager', 'admin', 'super_admin'], true)) {
                    $error = 'Access restricted: This portal is exclusively for designated Project & Lead Managers.';
                } else {
                    // Successful authentication
                    session_regenerate_id(true);
                    $_SESSION['manager_id'] = $account['id'];
                    $_SESSION['manager_name'] = $account['full_name'];
                    $_SESSION['manager_email'] = $account['email'];
                    $_SESSION['manager_role'] = $account['role'];
                    $_SESSION['manager_last_activity'] = time();

                    // Clear manager rate limit on success
                    if (isset($_SESSION['rate_limits']['manager_login'])) {
                        unset($_SESSION['rate_limits']['manager_login']);
                    }

                    // Optional redirect
                    $redirect = 'dashboard.php';
                    if (!empty($_GET['redirect'])) {
                        $candidate = urldecode($_GET['redirect']);
                        if (!str_contains($candidate, '://') && !str_starts_with($candidate, '//')) {
                            $redirect = basename($candidate);
                        }
                    }
                    header('Location: ' . $redirect);
                    exit;
                }
            } catch (Exception $e) {
                error_log("Manager login error: " . $e->getMessage());
                $error = 'A temporary system exception occurred. Please try again shortly.';
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
    <title>Manager Portal Login | GrihGO Digital &amp; Tech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .manager-login-bg {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 15% 15%, rgba(16, 185, 129, 0.12) 0%, transparent 40%),
                        radial-gradient(circle at 85% 85%, rgba(2, 86, 196, 0.15) 0%, transparent 40%),
                        #0a1128;
            padding: 24px;
        }
        .manager-login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
            width: 100%;
            max-width: 440px;
            padding: 40px;
            position: relative;
        }
        .manager-brand-badge {
            display: inline-block;
            background: #ecfdf5;
            color: #059669;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 999px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body class="manager-login-bg">

<div class="manager-login-card">
    <div style="text-align: center; margin-bottom: 28px;">
        <span class="manager-brand-badge">Manager Console</span>
        <h1 style="font-family: var(--font-heading, 'Outfit', sans-serif); font-size: 1.6rem; margin: 0 0 6px 0; color: #0f172a;">
            GrihGO Digital &amp; Tech
        </h1>
        <p style="font-size: 0.875rem; color: #64748b; margin: 0;">
            Sign in to access your assigned leads, client projects &amp; support workflows.
        </p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px; font-size: 0.875rem; padding: 12px 16px; border-radius: 8px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'logout'): ?>
        <div class="alert alert-success" style="margin-bottom: 20px; font-size: 0.875rem; padding: 12px 16px; border-radius: 8px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;">
            You have successfully logged out of the Manager Console.
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php" autocomplete="on">
        <?= getCsrfField() ?>

        <div class="form-group" style="margin-bottom: 18px;">
            <label class="form-label" for="mgrEmail" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
                Work Email Address
            </label>
            <input type="email" id="mgrEmail" name="email" class="form-control" 
                   value="<?= htmlspecialchars($emailVal) ?>" 
                   placeholder="Manager email" required autofocus style="width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <label class="form-label" for="mgrPassword" style="font-size: 0.875rem; font-weight: 600; color: #1e293b; margin: 0;">
                    Password
                </label>
            </div>
            <input type="password" id="mgrPassword" name="password" class="form-control" 
                   placeholder="Enter your manager password" required style="width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
        </div>

        <button type="submit" class="btn btn-primary w-100" style="width: 100%; padding: 12px; background: #0256c4; color: #fff; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer;">
            Sign In to Manager Portal &rarr;
        </button>
    </form>

    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #f1f5f9; text-align: center; font-size: 0.825rem; color: #64748b;">
        <span>Need client access? <a href="../client/login.php" style="color: #0256c4; font-weight: 600; text-decoration: none;">Client Login</a></span>
        <span style="display: block; margin-top: 8px;"><a href="../index.php" style="color: #64748b; text-decoration: none;">&larr; Back to GrihGO Website</a></span>
    </div>
</div>

</body>
</html>
