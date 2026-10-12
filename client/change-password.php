<?php
/**
 * GrihGO Digital & Tech — Client Change Password Interface
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/includes/auth.php';

$portalPageTitle = 'Change Password';
$portalHeaderTitle = 'Account Security';
$current_portal_page = 'change-password';

$passSuccess = '';
$passError = '';

// Handle direct POST fallback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($token)) {
        $passError = 'Security validation failed. Please refresh.';
    } else {
        $curr = $_POST['current_password'] ?? '';
        $new  = $_POST['new_password'] ?? '';
        $conf = $_POST['confirm_password'] ?? '';

        if (empty($curr) || empty($new) || empty($conf)) {
            $passError = 'All password fields are required.';
        } elseif (strlen($new) < 8) {
            $passError = 'New password must be at least 8 characters long.';
        } elseif ($new !== $conf) {
            $passError = 'New password and confirmation do not match.';
        } else {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :uid LIMIT 1");
            $stmt->execute([':uid' => $clientUserId]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$u || !password_verify($curr, $u['password_hash'])) {
                $passError = 'Current password entered is incorrect.';
            } else {
                $newHash = password_hash($new, PASSWORD_BCRYPT);
                $upd = $pdo->prepare("UPDATE users SET password_hash = ?, remember_token = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $upd->execute([$newHash, $clientUserId]);
                session_regenerate_id(true);
                $passSuccess = 'Your account password has been updated successfully.';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div style="max-width: 680px; margin: 0 auto;">
    <div class="portal-card">
        <div class="portal-card-header">
            <h2 class="portal-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <span>Update Portal Password</span>
            </h2>
        </div>

        <div class="portal-card-body">
            <?php if (!empty($passSuccess)): ?>
                <div class="alert alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span style="font-weight: 600;"><?= htmlspecialchars($passSuccess, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($passError)): ?>
                <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span style="font-weight: 600;"><?= htmlspecialchars($passError, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <form id="changePassForm" method="POST" action="change-password.php">
                <?= getCsrfField() ?>

                <div class="portal-form-group">
                    <label for="current_password" class="portal-form-label">
                        Current Account Password <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="password" class="portal-form-input" id="current_password" name="current_password" required autocomplete="current-password">
                </div>

                <div class="portal-form-group">
                    <label for="new_password" class="portal-form-label">
                        New Password <span style="color: #ef4444;">*</span>
                        <span style="font-size: 0.75rem; color: var(--portal-text-muted); font-weight: normal;">(Minimum 8 characters)</span>
                    </label>
                    <input type="password" class="portal-form-input" id="new_password" name="new_password" required autocomplete="new-password">
                </div>

                <div class="portal-form-group">
                    <label for="confirm_password" class="portal-form-label">
                        Confirm New Password <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="password" class="portal-form-input" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                </div>

                <div style="background: #f8fafc; border: 1px solid var(--portal-border); border-radius: 8px; padding: 14px; margin-bottom: 20px; font-size: 0.8rem; color: #475569;">
                    <strong>Security Tip:</strong> For enterprise protection, choose a combination of uppercase letters, numbers, and symbols. Changing your password will invalidate active remember-me sessions on other devices.
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--portal-border);">
                    <button type="submit" id="btnUpdatePassword" class="btn-portal-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('changePassForm');
    const btn = document.getElementById('btnUpdatePassword');

    if (form && btn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            btn.disabled = true;
            btn.innerHTML = '<span>Updating Password...</span>';

            const formData = new FormData(form);

            fetch('../api/client-password-change.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.showPortalToast(data.message || 'Password updated successfully.', 'success');
                    form.reset();
                } else {
                    window.showPortalToast(data.message || 'Password update failed.', 'error');
                }
                btn.disabled = false;
                btn.innerHTML = '<span>Update Password</span>';
            })
            .catch(() => {
                form.submit();
            });
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
