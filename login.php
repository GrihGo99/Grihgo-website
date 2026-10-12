<?php
/**
 * GrihGO Digital & Tech — Client Portal Authentication
 * Version: 3.0 (Part 3 - Client Authentication)
 * Technology: PHP 8+, PDO, password_verify(), CSRF, Rate Limiting & Session Hardening
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/csrf.php';
require_once __DIR__ . '/config/functions.php';

// If client is already authenticated, redirect to dashboard
if (!empty($_SESSION['client_id']) && ($_SESSION['role'] ?? '') === 'client') {
    header('Location: client/dashboard.php');
    exit;
}

$errorMessage = '';
$successMessage = '';

// Check query messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'logged_out') {
        $successMessage = 'You have been securely signed out.';
    } elseif ($_GET['msg'] === 'timeout') {
        $errorMessage = 'Your session has timed out due to inactivity. Please sign in again.';
    }
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'unauthorized') {
        $errorMessage = 'Access denied. Please log in with an authorized client account.';
    } elseif ($_GET['error'] === 'suspended') {
        $errorMessage = 'This account has been deactivated. Please contact GrihGO support.';
    }
}

// Process POST Login Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
              (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        if ($isAjax) {
            jsonResponse(false, 'Invalid security token. Please refresh the page and try again.', ['csrf' => 'Invalid token'], [], 403);
        } else {
            $errorMessage = 'Security validation failed. Please try again.';
        }
    }

    if (empty($errorMessage)) {
        // Rate limiting check: max 6 attempts per 5 minutes
        if (!checkRateLimit('client_login', 6, 300)) {
            $rateLimitMsg = 'Too many failed login attempts. Please wait 5 minutes before trying again.';
            if ($isAjax) {
                jsonResponse(false, $rateLimitMsg, ['rate_limit' => $rateLimitMsg], [], 429);
            } else {
                $errorMessage = $rateLimitMsg;
            }
        }
    }

    if (empty($errorMessage)) {
        $identifier = sanitizeInput($_POST['identifier'] ?? $_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';
        $remember   = !empty($_POST['remember']);

        if (empty($identifier) || empty($password)) {
            $emptyMsg = 'Please enter both your email / phone and password.';
            if ($isAjax) {
                jsonResponse(false, $emptyMsg, ['identifier' => 'Required', 'password' => 'Required'], [], 422);
            } else {
                $errorMessage = $emptyMsg;
            }
        } else {
            try {
                $pdo = Database::getConnection();

                // Clean phone number if input consists mostly of numbers
                $cleanPhone = preg_replace('/[^\d]/', '', $identifier);

                // Find user by Email OR Phone
                $stmt = $pdo->prepare("
                    SELECT u.*, c.id AS client_record_id, c.company_name, c.status AS client_status
                    FROM users u
                    LEFT JOIN clients c ON c.user_id = u.id
                    WHERE (LOWER(u.email) = LOWER(:identifier) OR u.phone = :raw_identifier OR (u.phone = :clean_phone AND :clean_phone != ''))
                    LIMIT 1
                ");
                $stmt->execute([
                    ':identifier'     => $identifier,
                    ':raw_identifier' => $identifier,
                    ':clean_phone'    => $cleanPhone
                ]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user || !password_verify($password, $user['password_hash'])) {
                    $invalidMsg = 'Invalid email/phone or password. Please verify your credentials.';
                    if ($isAjax) {
                        jsonResponse(false, $invalidMsg, ['credentials' => 'Mismatch'], [], 401);
                    } else {
                        $errorMessage = $invalidMsg;
                    }
                } elseif ($user['role'] !== 'client') {
                    $roleMsg = 'Access denied. This login portal is reserved for GrihGO clients only.';
                    if ($isAjax) {
                        jsonResponse(false, $roleMsg, ['role' => 'Forbidden'], [], 403);
                    } else {
                        $errorMessage = $roleMsg;
                    }
                } elseif ($user['status'] !== 'active' || ($user['client_status'] && $user['client_status'] !== 'active')) {
                    $statusMsg = 'Your client portal account is inactive. Please reach out to GrihGO support.';
                    if ($isAjax) {
                        jsonResponse(false, $statusMsg, ['status' => 'Inactive'], [], 403);
                    } else {
                        $errorMessage = $statusMsg;
                    }
                } else {
                    // Successful Authentication
                    session_regenerate_id(true);

                    // If client record doesn't exist yet, create one automatically
                    $clientId = $user['client_record_id'];
                    if (!$clientId) {
                        $createClientStmt = $pdo->prepare("
                            INSERT INTO clients (user_id, company_name, contact_person, email, phone, status)
                            VALUES (?, ?, ?, ?, ?, 'active')
                        ");
                        $createClientStmt->execute([
                            $user['id'],
                            $user['name'] . ' Company',
                            $user['name'],
                            $user['email'],
                            $user['phone'] ?: ''
                        ]);
                        $clientId = (int)$pdo->lastInsertId();
                    }

                    $_SESSION['user_id']       = (int)$user['id'];
                    $_SESSION['client_id']     = (int)$clientId;
                    $_SESSION['user_name']     = $user['name'];
                    $_SESSION['user_email']    = $user['email'];
                    $_SESSION['role']          = 'client';
                    $_SESSION['last_activity'] = time();

                    // Handle Remember Me
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $updToken = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                        $updToken->execute([$token, $user['id']]);
                        $cookieExpiry = time() + (86400 * 30); // 30 days
                        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
                        setcookie('grihgo_client_remember', $token, [
                            'expires'  => $cookieExpiry,
                            'path'     => '/',
                            'secure'   => $isHttps,
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                    }

                    // Determine redirect target
                    $redirect = 'client/dashboard.php';
                    if (!empty($_GET['redirect'])) {
                        $candidate = urldecode($_GET['redirect']);
                        // Only allow relative paths within client/
                        if (str_starts_with($candidate, 'client/') || str_starts_with($candidate, '/client/')) {
                            $redirect = ltrim($candidate, '/');
                        }
                    }

                    if ($isAjax) {
                        jsonResponse(true, 'Login successful! Redirecting to dashboard...', [], ['redirect_url' => $redirect]);
                    } else {
                        header('Location: ' . $redirect);
                        exit;
                    }
                }
            } catch (Exception $e) {
                $dbMsg = 'Authentication service is temporarily unavailable. Please try again.';
                if ($isAjax) {
                    jsonResponse(false, $dbMsg, ['database' => 'Service error'], [], 500);
                } else {
                    $errorMessage = $dbMsg;
                }
            }
        }
    }
}

$page_title = 'Client Login | GrihGO Digital & Tech';
$meta_desc = 'Client Portal login for GrihGO Digital & Tech customers to track active projects, review invoices, and access support.';
$current_page = 'login';
$base_url = './';
include 'includes/header.php';
?>

<section class="login-page-section" style="min-height: calc(100vh - 160px); display: flex; align-items: center; justify-content: center; padding: 60px 20px; background: radial-gradient(circle at 10% 20%, rgba(30, 91, 168, 0.04) 0%, rgba(248, 250, 252, 1) 90%);">
    <div class="login-card-container" style="width: 100%; max-width: 480px; background: #ffffff; border-radius: var(--radius-lg, 16px); padding: 40px; box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(226, 232, 240, 0.8);">
        <div class="login-header-block" style="text-align: center; margin-bottom: 28px;">
            <a href="index.php" aria-label="GrihGO Digital & Tech Home" style="display: inline-block; margin-bottom: 16px;">
                <img src="assets/images/logo/logo.png" alt="GrihGO Digital & Tech" class="login-logo-img" style="height: 44px; width: auto; object-fit: contain;">
            </a>
            <h1 class="login-title" style="font-size: 1.6rem; font-weight: 700; color: var(--navy-dark, #0f172a); margin: 0 0 6px 0;">Client Portal Login</h1>
            <p class="login-subtitle" style="font-size: 0.9rem; color: var(--text-muted, #64748b); margin: 0;">Access your live projects, milestones, invoices & tickets</p>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <div class="form-alert" id="loginAlert" style="display: none; margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem;"></div>

        <form id="loginForm" method="POST" action="login.php<?= !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" novalidate>
            <?= getCsrfField() ?>

            <div class="form-group" style="margin-bottom: 18px;">
                <label for="loginIdentifier" class="form-label" style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Email Address or Phone Number <span style="color: #ef4444;">*</span>
                </label>
                <div style="position: relative;">
                    <input type="text" class="form-control" id="loginIdentifier" name="identifier" 
                           placeholder="Email address or phone number" 
                           value="<?= htmlspecialchars($_POST['identifier'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                           required autocomplete="username"
                           style="width: 100%; padding: 11px 14px 11px 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; background: #fff; transition: border-color 0.2s, box-shadow 0.2s;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="position: absolute; left: 14px; top: 13px;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label for="loginPassword" class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin: 0;">
                        Password <span style="color: #ef4444;">*</span>
                    </label>
                    <a href="#forgot" id="forgotPassBtn" style="font-weight: 500; font-size: 0.82rem; color: var(--primary-blue, #1e5ba8); text-decoration: none;">Forgot Password?</a>
                </div>
                <div style="position: relative;">
                    <input type="password" class="form-control" id="loginPassword" name="password" 
                           placeholder="••••••••••••" required autocomplete="current-password"
                           style="width: 100%; padding: 11px 40px 11px 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; background: #fff; transition: border-color 0.2s, box-shadow 0.2s;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="position: absolute; left: 14px; top: 13px;">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <button type="button" id="togglePasswordBtn" aria-label="Toggle password visibility" style="position: absolute; right: 12px; top: 11px; background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="eyeIcon">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="login-options-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px;">
                <label class="remember-check" style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #475569; cursor: pointer;">
                    <input type="checkbox" name="remember" id="rememberMe" style="width: 16px; height: 16px; accent-color: var(--primary-blue, #1e5ba8);">
                    <span>Remember my session</span>
                </label>
                <a href="index.php" style="font-size: 0.82rem; color: #64748b; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                    &larr; Back to Website
                </a>
            </div>

            <button type="submit" id="submitLoginBtn" class="btn btn-primary btn-lg w-100" style="width: 100%; padding: 12px; font-weight: 600; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;">
                <span>Sign In to Client Portal</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 0.85rem; color: var(--text-muted, #64748b);">
            Don't have an enterprise account? <a href="appointment.php" style="font-weight: 600; color: var(--primary-blue, #1e5ba8); text-decoration: none;">Schedule Consultation &rarr;</a>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginAlert = document.getElementById('loginAlert');
    const submitBtn = document.getElementById('submitLoginBtn');
    const togglePass = document.getElementById('togglePasswordBtn');
    const passInput = document.getElementById('loginPassword');
    const forgotBtn = document.getElementById('forgotPassBtn');

    // Toggle Password Visibility
    if (togglePass && passInput) {
        togglePass.addEventListener('click', function() {
            const isPass = passInput.type === 'password';
            passInput.type = isPass ? 'text' : 'password';
            togglePass.style.color = isPass ? 'var(--primary-blue, #1e5ba8)' : '#94a3b8';
        });
    }

    // Forgot password notification modal/alert
    if (forgotBtn) {
        forgotBtn.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Password Recovery Notice:\nPlease contact your dedicated GrihGO Account Manager or email support@grihgodigital.com with your registered email/phone number to receive an authorized password reset authorization code.');
        });
    }

    // AJAX Login Handler
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const identifier = document.getElementById('loginIdentifier').value.trim();
            const password = passInput.value;

            if (!identifier || !password) {
                showAlert('Please provide both your email/phone and password.', 'error');
                return;
            }

            // Disable button during request
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Verifying Credentials...</span>';

            const formData = new FormData(loginForm);

            fetch(loginForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
            .then(result => {
                if (result.ok && result.body.success) {
                    showAlert(result.body.message || 'Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = result.body.data.redirect_url || 'client/dashboard.php';
                    }, 600);
                } else {
                    showAlert(result.body.message || 'Invalid credentials. Please try again.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>Sign In to Client Portal</span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
                }
            })
            .catch(err => {
                showAlert('Network or server error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Sign In to Client Portal</span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
            });
        });
    }

    function showAlert(msg, type) {
        if (!loginAlert) return;
        loginAlert.style.display = 'block';
        if (type === 'success') {
            loginAlert.style.background = '#f0fdf4';
            loginAlert.style.border = '1px solid #bbf7d0';
            loginAlert.style.color = '#15803d';
        } else {
            loginAlert.style.background = '#fef2f2';
            loginAlert.style.border = '1px solid #fecaca';
            loginAlert.style.color = '#b91c1c';
        }
        loginAlert.textContent = msg;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
