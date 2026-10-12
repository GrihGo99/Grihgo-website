<?php
/**
 * GrihGO Digital & Tech — Administrative User Management Console
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

// Super Admin / Admin role check
requireAdminRole(['super_admin', 'admin']);

$pageTitle = 'Administrator Users & RBAC';
$pageSection = 'admin-users';

$error = '';
$success = '';

$validRoles = ['super_admin', 'admin', 'manager', 'staff'];
$validStatuses = ['active', 'inactive', 'suspended'];

// Handle Create Admin User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_admin') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $email    = sanitizeEmail($_POST['email'] ?? '');
        $phone    = sanitizePhone($_POST['phone'] ?? '');
        $role     = in_array($_POST['role'] ?? '', $validRoles) ? $_POST['role'] : 'staff';
        $status   = in_array($_POST['status'] ?? '', $validStatuses) ? $_POST['status'] : 'active';
        $password = $_POST['password'] ?? '';

        // Only super_admin can create another super_admin
        if ($role === 'super_admin' && ($currentAdmin['role'] ?? '') !== 'super_admin') {
            $error = 'Only Super Administrators can provision another Super Admin account.';
        } elseif (empty($fullName) || empty($email) || empty($password)) {
            $error = 'Full Name, Email, and Initial Password are required.';
        } elseif (!validateEmail($email)) {
            $error = 'Please provide a valid administrative email.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            // Check email uniqueness in admins table
            $check = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetchColumn() > 0) {
                $error = 'An administrator account with this email address already exists.';
            } else {
                try {
                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO admins (full_name, email, password_hash, role, status, phone) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$fullName, $email, $passwordHash, $role, $status, $phone]);
                    $newAdminId = (int)$pdo->lastInsertId();

                    logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Admin User Created', 'admins', $newAdminId, "Created administrative user {$fullName} ({$email}) with role {$role}");
                    $success = "Administrator '{$fullName}' created successfully!";
                } catch (Exception $e) {
                    $error = 'Failed to create administrator user.';
                }
            }
        }
    }
}

// Handle Reset Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_admin_password') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $targetId = (int)($_POST['target_id'] ?? 0);
        $newPass = $_POST['new_password'] ?? '';

        if ($targetId <= 0 || strlen($newPass) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            try {
                $hash = password_hash($newPass, PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE admins SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$hash, $targetId]);

                $targetName = $pdo->query("SELECT full_name FROM admins WHERE id = {$targetId}")->fetchColumn() ?: 'Admin';
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Admin Password Reset', 'admins', $targetId, "Reset password for {$targetName}");
                $success = "Password for {$targetName} updated successfully!";
            } catch (Exception $e) {
                $error = 'Failed to reset password.';
            }
        }
    }
}

// Handle Status Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $targetId = (int)($_POST['target_id'] ?? 0);
        $newStatus = in_array($_POST['new_status'] ?? '', ['active', 'inactive']) ? $_POST['new_status'] : 'active';

        // Cannot deactivate yourself
        if ($targetId === (int)$currentAdmin['id']) {
            $error = 'You cannot deactivate your own active session.';
        } else {
            try {
                $pdo->prepare("UPDATE admins SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$newStatus, $targetId]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Admin Status Toggled', 'admins', $targetId, "Set admin #{$targetId} status to {$newStatus}");
                $success = "Admin account status updated.";
            } catch (Exception $e) {
                $error = 'Failed to update admin status.';
            }
        }
    }
}

// Fetch Admins
$adminsList = $pdo->query("SELECT * FROM admins ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Administrative Users & RBAC</h1>
        <p class="page-subtitle">Manage internal access credentials, security roles, and console operators.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" onclick="openAdminModal('newAdminModal')">+ Add Admin User</button>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Admin Name</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($adminsList)): ?>
                    <tr><td colspan="7" class="empty-state">No administrative users found.</td></tr>
                <?php else: foreach ($adminsList as $adm): 
                    $roleClass = match($adm['role']) {
                        'super_admin' => 'badge-purple',
                        'admin' => 'badge-primary',
                        'manager' => 'badge-success',
                        default => 'badge-neutral'
                    };
                ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="admin-avatar" style="width: 32px; height: 32px; font-size: 12px;">
                                    <?= strtoupper(substr($adm['full_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($adm['full_name']) ?></strong>
                                    <?php if ($adm['id'] == $currentAdmin['id']): ?>
                                        <span style="font-size: 10.5px; background: #e0f2fe; color: #0369a1; padding: 1px 6px; border-radius: 4px; font-weight: 700; margin-left: 4px;">You</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><code><?= htmlspecialchars($adm['email']) ?></code></td>
                        <td><?= htmlspecialchars($adm['phone'] ?: '-') ?></td>
                        <td>
                            <span class="badge <?= $roleClass ?>">
                                <?= ucwords(str_replace('_', ' ', htmlspecialchars($adm['role']))) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $adm['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                <span class="badge-dot"></span><?= ucfirst(htmlspecialchars($adm['status'])) ?>
                            </span>
                        </td>
                        <td style="font-size: 12px; color: var(--admin-text-muted);">
                            <?= !empty($adm['last_login_at']) ? date('d M Y, h:i A', strtotime($adm['last_login_at'])) : 'Never logged in' ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <button type="button" class="btn btn-sm btn-outline" onclick="openResetModal(<?= $adm['id'] ?>, '<?= htmlspecialchars(addslashes($adm['full_name'])) ?>')">Reset Pass</button>
                                <?php if ($adm['id'] != $currentAdmin['id']): ?>
                                    <form method="POST" action="admin-users.php" style="margin: 0;">
                                        <?= getCsrfField() ?>
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="target_id" value="<?= $adm['id'] ?>">
                                        <input type="hidden" name="new_status" value="<?= $adm['status'] === 'active' ? 'inactive' : 'active' ?>">
                                        <button type="submit" class="btn btn-sm <?= $adm['status'] === 'active' ? 'btn-outline' : 'btn-success' ?>">
                                            <?= $adm['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Admin -->
<div id="newAdminModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 520px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Provision Administrative Account</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('newAdminModal')">&times;</button>
        </div>
        <form method="POST" action="admin-users.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="create_admin">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" placeholder="e.g. Sanya Iyer" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Official Email (Username) *</label>
                        <input type="email" name="email" class="form-control" placeholder="sanya@grihgo.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 98765 00000">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Access Role *</label>
                        <select name="role" class="form-control">
                            <?php if (($currentAdmin['role'] ?? '') === 'super_admin'): ?>
                                <option value="super_admin">Super Admin (Universal Access)</option>
                            <?php endif; ?>
                            <option value="admin">Administrator</option>
                            <option value="manager" selected>Operations Manager</option>
                            <option value="staff">Engineering / Support Staff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Initial Password * (Min. 6 chars)</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••••••" required>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('newAdminModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Admin User</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reset Password -->
<div id="resetAdminPasswordModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 440px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Reset Admin Password</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('resetAdminPasswordModal')">&times;</button>
        </div>
        <form method="POST" action="admin-users.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="reset_admin_password">
            <input type="hidden" name="target_id" id="resetTargetId" value="0">
            <div class="admin-modal-body">
                <p style="font-size: 13.5px; color: var(--admin-text-muted); margin-bottom: 16px;">
                    Set a new password for <strong id="resetTargetName">User</strong>.
                </p>
                <div class="form-group">
                    <label class="form-label">New Password (Min. 6 characters)</label>
                    <input type="password" name="new_password" class="form-control" placeholder="••••••••••••" required>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('resetAdminPasswordModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save New Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function openResetModal(adminId, adminName) {
    document.getElementById('resetTargetId').value = adminId;
    document.getElementById('resetTargetName').innerText = adminName;
    openAdminModal('resetAdminPasswordModal');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
