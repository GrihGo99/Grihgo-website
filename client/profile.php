<?php
/**
 * GrihGO Digital & Tech — Client Profile Management
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/includes/auth.php';

$portalPageTitle = 'My Profile';
$portalHeaderTitle = 'Account & Business Profile';
$current_portal_page = 'profile';

$pdo = Database::getConnection();

// Handle Standard Non-AJAX POST Submission Fallback
$profileSuccess = '';
$profileError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($token)) {
        $profileError = 'Security validation failed. Please refresh and try again.';
    } else {
        $fullName    = sanitizeInput($_POST['contact_person'] ?? '');
        $companyName = sanitizeInput($_POST['company_name'] ?? '');
        $phone       = sanitizePhone($_POST['phone'] ?? '');
        $address     = sanitizeInput($_POST['address'] ?? '');

        if (!validateName($fullName, 2, 100)) {
            $profileError = 'Full name must be between 2 and 100 characters.';
        } elseif (!validateName($companyName, 2, 150)) {
            $profileError = 'Company name must be between 2 and 150 characters.';
        } elseif (!validatePhone($phone)) {
            $profileError = 'Please provide a valid contact phone number.';
        } else {
            try {
                $upd = $pdo->prepare("
                    UPDATE clients 
                    SET contact_person = :person, company_name = :company, phone = :phone, address = :address, updated_at = CURRENT_TIMESTAMP
                    WHERE id = :cid AND user_id = :uid
                ");
                $upd->execute([
                    ':person'  => $fullName,
                    ':company' => $companyName,
                    ':phone'   => $phone,
                    ':address' => $address,
                    ':cid'     => $clientId,
                    ':uid'     => $clientUserId
                ]);

                $pdo->prepare("UPDATE users SET name = ?, phone = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                    ->execute([$fullName, $phone, $clientUserId]);

                $_SESSION['user_name'] = $fullName;
                $profileSuccess = 'Profile updated successfully.';

                // Refresh authClient data
                $authStmt->execute([':client_id' => $clientId, ':user_id' => $clientUserId]);
                $authClient = $authStmt->fetch(PDO::FETCH_ASSOC);
                $clientName = htmlspecialchars($authClient['contact_person'] ?: $authClient['user_name'], ENT_QUOTES, 'UTF-8');
                $clientCompany = htmlspecialchars($authClient['company_name'], ENT_QUOTES, 'UTF-8');
                $clientPhone = htmlspecialchars($authClient['phone'], ENT_QUOTES, 'UTF-8');
                $clientAddress = htmlspecialchars($authClient['address'] ?? '', ENT_QUOTES, 'UTF-8');

            } catch (Exception $e) {
                $profileError = 'An error occurred while saving profile changes.';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div style="max-width: 840px; margin: 0 auto;">
    <?php if (!empty($profileSuccess)): ?>
        <div class="alert alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <span style="font-weight: 600;"><?= htmlspecialchars($profileSuccess, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($profileError)): ?>
        <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span style="font-weight: 600;"><?= htmlspecialchars($profileError, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <div class="portal-card">
        <div class="portal-card-header" style="background: #fafafa;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div class="portal-user-avatar" style="width: 54px; height: 54px; font-size: 1.3rem;">
                    <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; margin: 0; color: var(--portal-text-main);">
                        <?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') ?>
                    </h2>
                    <div style="font-size: 0.82rem; color: var(--portal-text-muted); margin-top: 2px;">
                        Enterprise Client ID: <strong>#CL-<?= str_pad((string)$clientId, 4, '0', STR_PAD_LEFT) ?></strong> &bull; Member since <?= date('M Y', strtotime($authClient['created_at'])) ?>
                    </div>
                </div>
            </div>
            <span class="badge-status badge-completed">Account Active</span>
        </div>

        <div class="portal-card-body">
            <form id="profileForm" method="POST" action="profile.php">
                <?= getCsrfField() ?>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <!-- Full Name -->
                    <div class="portal-form-group">
                        <label for="contact_person" class="portal-form-label">
                            Full Contact Name <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" class="portal-form-input" id="contact_person" name="contact_person" 
                               value="<?= htmlspecialchars($authClient['contact_person'] ?: $authClient['user_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <!-- Company / Business Name -->
                    <div class="portal-form-group">
                        <label for="company_name" class="portal-form-label">
                            Company / Business Name <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" class="portal-form-input" id="company_name" name="company_name" 
                               value="<?= htmlspecialchars($authClient['company_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <!-- Email Address (Read-Only) -->
                    <div class="portal-form-group">
                        <label class="portal-form-label">
                            Registered Email Address <span style="font-size: 0.75rem; color: var(--portal-text-muted); font-weight: normal;">(Admin Managed)</span>
                        </label>
                        <input type="email" class="portal-form-input" value="<?= htmlspecialchars($authClient['email'], ENT_QUOTES, 'UTF-8') ?>" readonly disabled 
                               style="background: #f1f5f9; color: #64748b; cursor: not-allowed;">
                    </div>

                    <!-- Phone Number -->
                    <div class="portal-form-group">
                        <label for="phone" class="portal-form-label">
                            Phone / WhatsApp Number <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" class="portal-form-input" id="phone" name="phone" 
                               value="<?= htmlspecialchars($authClient['phone'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <!-- GST Number if available (Read-Only for Client) -->
                    <div class="portal-form-group">
                        <label class="portal-form-label">
                            GSTIN / Tax ID <span style="font-size: 0.75rem; color: var(--portal-text-muted); font-weight: normal;">(Billing Identity)</span>
                        </label>
                        <input type="text" class="portal-form-input" value="<?= htmlspecialchars($authClient['gst_number'] ?? 'Not Specified', ENT_QUOTES, 'UTF-8') ?>" readonly disabled
                               style="background: #f1f5f9; color: #64748b; cursor: not-allowed;">
                    </div>

                    <!-- Account Role (Read-Only) -->
                    <div class="portal-form-group">
                        <label class="portal-form-label">System Role</label>
                        <input type="text" class="portal-form-input" value="Verified Client" readonly disabled
                               style="background: #f1f5f9; color: #64748b; cursor: not-allowed;">
                    </div>
                </div>

                <!-- Office / Business Address -->
                <div class="portal-form-group">
                    <label for="address" class="portal-form-label">Official Business / Billing Address</label>
                    <textarea class="portal-form-textarea" id="address" name="address" rows="3"><?= htmlspecialchars($authClient['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--portal-border);">
                    <div style="font-size: 0.8rem; color: var(--portal-text-muted);">
                        To update billing GST or registered email, please raise a ticket.
                    </div>
                    <button type="submit" id="btnSaveProfile" class="btn-portal-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        <span>Save Profile Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileForm');
    const btn = document.getElementById('btnSaveProfile');

    if (form && btn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            btn.disabled = true;
            btn.innerHTML = '<span>Saving Changes...</span>';

            const formData = new FormData(form);

            fetch('../api/client-profile-update.php', {
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
                    window.showPortalToast(data.message || 'Profile updated successfully.', 'success');
                    btn.disabled = false;
                    btn.innerHTML = '<span>Save Profile Changes</span>';
                } else {
                    window.showPortalToast(data.message || 'Failed to update profile.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<span>Save Profile Changes</span>';
                }
            })
            .catch(() => {
                // If fetch fails, submit normally
                form.submit();
            });
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
