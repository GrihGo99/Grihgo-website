<?php
/**
 * GrihGO Digital & Tech — 360 Degree Client Dossier
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$clientId = (int)($_GET['id'] ?? 0);
if ($clientId <= 0) {
    header('Location: clients.php');
    exit;
}

$stmt = $pdo->prepare("SELECT c.*, u.role, u.created_at AS user_created_at FROM clients c LEFT JOIN users u ON c.user_id = u.id WHERE c.id = ? LIMIT 1");
$stmt->execute([$clientId]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    header('Location: clients.php?error=not_found');
    exit;
}

$pageTitle = htmlspecialchars($client['company_name']) . ' — Client Profile';
$pageSection = 'clients';

$error = '';
$success = '';

// Handle Client Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $contactPerson = sanitizeInput($_POST['contact_person'] ?? '');
        $companyName   = sanitizeInput($_POST['company_name'] ?? '');
        $phone         = sanitizePhone($_POST['phone'] ?? '');
        $gstNumber     = sanitizeInput($_POST['gst_number'] ?? '');
        $address       = sanitizeInput($_POST['address'] ?? '');
        $status        = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if (empty($contactPerson) || empty($companyName) || empty($phone)) {
            $error = 'Please fill all required profile fields.';
        } else {
            try {
                $pdo->beginTransaction();
                $upd = $pdo->prepare("UPDATE clients SET contact_person = ?, company_name = ?, phone = ?, gst_number = ?, address = ?, status = ? WHERE id = ?");
                $upd->execute([$contactPerson, $companyName, $phone, $gstNumber, $address, $status, $clientId]);

                if (!empty($client['user_id'])) {
                    $pdo->prepare("UPDATE users SET name = ?, phone = ?, status = ? WHERE id = ?")
                        ->execute([$contactPerson, $phone, $status, $client['user_id']]);
                }

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Client Updated', 'clients', $clientId, "Updated profile for {$companyName}");
                $pdo->commit();

                // Refresh client data
                $stmt->execute([$clientId]);
                $client = $stmt->fetch(PDO::FETCH_ASSOC);
                $success = 'Client profile updated successfully!';
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Failed to update profile details.';
            }
        }
    }
}

// Handle Password Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $newPass = $_POST['new_password'] ?? '';
        if (strlen($newPass) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif (empty($client['user_id'])) {
            $error = 'No login account linked to this client.';
        } else {
            try {
                $hash = password_hash($newPass, PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $client['user_id']]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Client Password Reset', 'clients', $clientId, "Reset password for client {$client['company_name']}");
                $success = 'Client portal password has been reset successfully!';
            } catch (Exception $e) {
                $error = 'Unable to reset password.';
            }
        }
    }
}

// Fetch 360 Data
// 1. Projects
$projects = $pdo->prepare("SELECT * FROM projects WHERE client_id = ? ORDER BY id DESC");
$projects->execute([$clientId]);
$clientProjects = $projects->fetchAll(PDO::FETCH_ASSOC);

// 2. Appointments
$appts = $pdo->prepare("SELECT * FROM appointments WHERE client_id = ? OR email = ? ORDER BY id DESC");
$appts->execute([$clientId, $client['email']]);
$clientAppointments = $appts->fetchAll(PDO::FETCH_ASSOC);

// 3. Invoices & Payments
$invs = $pdo->prepare("SELECT * FROM invoices WHERE client_id = ? ORDER BY id DESC");
$invs->execute([$clientId]);
$clientInvoices = $invs->fetchAll(PDO::FETCH_ASSOC);

$pays = $pdo->prepare("SELECT p.*, i.invoice_number FROM payments p LEFT JOIN invoices i ON p.invoice_id = i.id WHERE p.client_id = ? ORDER BY p.id DESC");
$pays->execute([$clientId]);
$clientPayments = $pays->fetchAll(PDO::FETCH_ASSOC);

// 4. Support Tickets
$ticks = $pdo->prepare("SELECT * FROM support_tickets WHERE client_id = ? ORDER BY id DESC");
$ticks->execute([$clientId]);
$clientTickets = $ticks->fetchAll(PDO::FETCH_ASSOC);

// 5. Documents
$docs = $pdo->prepare("SELECT * FROM documents WHERE client_id = ? ORDER BY id DESC");
$docs->execute([$clientId]);
$clientDocs = $docs->fetchAll(PDO::FETCH_ASSOC);

// 6. Notifications
$notifs = $pdo->prepare("SELECT * FROM notifications WHERE client_id = ? ORDER BY id DESC LIMIT 10");
$notifs->execute([$clientId]);
$clientNotifications = $notifs->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <div style="font-size: 13px; color: var(--admin-text-muted); margin-bottom: 4px;">
            <a href="clients.php" style="color: var(--admin-primary); text-decoration: none;">&larr; Back to Clients</a>
        </div>
        <h1><?= htmlspecialchars($client['company_name']) ?></h1>
        <p class="page-subtitle">360° Client Dossier &bull; Account #CL-<?= str_pad($client['id'], 3, '0', STR_PAD_LEFT) ?> &bull; Contact: <?= htmlspecialchars($client['contact_person']) ?></p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-outline" onclick="openAdminModal('editProfileModal')">Edit Profile</button>
        <button type="button" class="btn btn-outline" onclick="openAdminModal('resetPasswordModal')">Reset Password</button>
        <a href="invoices.php?client_id=<?= $client['id'] ?>" class="btn btn-primary">+ Create Invoice</a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Summary Cards -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="kpi-card" style="--kpi-accent: var(--admin-primary);">
        <div class="kpi-card-content">
            <span class="kpi-title">Account Status</span>
            <span class="kpi-value" style="font-size: 20px; color: <?= $client['status'] === 'active' ? 'var(--admin-accent)' : 'var(--admin-danger)' ?>;">
                <?= ucfirst($client['status']) ?>
            </span>
            <span class="kpi-subtext">Member since <?= date('M Y', strtotime($client['created_at'])) ?></span>
        </div>
    </div>
    <div class="kpi-card" style="--kpi-accent: #0ea5e9;">
        <div class="kpi-card-content">
            <span class="kpi-title">Projects</span>
            <span class="kpi-value"><?= count($clientProjects) ?></span>
            <span class="kpi-subtext">Active & delivered</span>
        </div>
    </div>
    <div class="kpi-card" style="--kpi-accent: var(--admin-accent);">
        <div class="kpi-card-content">
            <span class="kpi-title">Invoiced Revenue</span>
            <?php 
                $clientRev = array_sum(array_column($clientPayments, 'amount'));
            ?>
            <span class="kpi-value">₹<?= number_format($clientRev, 0) ?></span>
            <span class="kpi-subtext">Total received payments</span>
        </div>
    </div>
    <div class="kpi-card" style="--kpi-accent: var(--admin-warning);">
        <div class="kpi-card-content">
            <span class="kpi-title">Support Tickets</span>
            <span class="kpi-value"><?= count($clientTickets) ?></span>
            <span class="kpi-subtext">Queries logged</span>
        </div>
    </div>
</div>

<!-- Dossier Grid -->
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    
    <!-- Left Column: Company & Account Information -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Corporate Information</h3>
            </div>
            <div class="admin-card-body" style="font-size: 13.5px; line-height: 1.8;">
                <div><strong style="color: var(--admin-text-muted);">Company Name:</strong><br><?= htmlspecialchars($client['company_name']) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Authorized Contact:</strong><br><?= htmlspecialchars($client['contact_person']) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Official Email:</strong><br><a href="mailto:<?= htmlspecialchars($client['email']) ?>" style="color: var(--admin-primary);"><?= htmlspecialchars($client['email']) ?></a></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Phone Number:</strong><br><?= htmlspecialchars($client['phone']) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">GST Number:</strong><br><?= htmlspecialchars($client['gst_number'] ?: 'Not Provided') ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Registered Address:</strong><br><?= nl2br(htmlspecialchars($client['address'] ?: 'Not Provided')) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Onboarded Date:</strong><br><?= date('d M Y, h:i A', strtotime($client['created_at'])) ?></div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Recent Client Notifications</h3>
            </div>
            <div class="admin-card-body" style="padding: 16px;">
                <?php if (empty($clientNotifications)): ?>
                    <div style="font-size: 13px; color: var(--admin-text-muted);">No notifications dispatched yet.</div>
                <?php else: foreach ($clientNotifications as $cn): ?>
                    <div style="padding: 10px 0; border-bottom: 1px solid var(--admin-border-subtle); font-size: 12.5px;">
                        <div style="font-weight: 600; color: var(--admin-text-main);"><?= htmlspecialchars($cn['title']) ?></div>
                        <div style="color: #64748b;"><?= htmlspecialchars($cn['message']) ?></div>
                        <div style="font-size: 11px; color: #94a3b8; margin-top: 3px;"><?= date('d M Y, h:i A', strtotime($cn['created_at'])) ?></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Projects, Invoices, Tickets Tabs -->
    <div>
        <!-- Projects Section -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Contracted Projects</h3>
                <a href="projects.php?client_id=<?= $client['id'] ?>" class="btn btn-sm btn-outline">+ Add Project</a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Service</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clientProjects)): ?>
                            <tr><td colspan="5" class="empty-state">No projects associated with this client.</td></tr>
                        <?php else: foreach ($clientProjects as $p): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                                <td><?= htmlspecialchars($p['service']) ?></td>
                                <td style="width: 140px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                            <div style="width: <?= min(100, max(0, (int)$p['progress'])) ?>%; height: 100%; background: var(--admin-primary);"></div>
                                        </div>
                                        <span style="font-size: 11px; font-weight: 600;"><?= (int)$p['progress'] ?>%</span>
                                    </div>
                                </td>
                                <td><span class="badge badge-primary"><?= htmlspecialchars($p['status']) ?></span></td>
                                <td><a href="project-details.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Details</a></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Invoices & Payments Section -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Invoices & Balances</h3>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clientInvoices)): ?>
                            <tr><td colspan="6" class="empty-state">No invoices generated for this client.</td></tr>
                        <?php else: foreach ($clientInvoices as $inv): 
                            $invBadge = match($inv['status']) {
                                'Paid' => 'badge-success',
                                'Cancelled' => 'badge-danger',
                                default => 'badge-warning'
                            };
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($inv['invoice_number']) ?></strong></td>
                                <td><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                                <td>₹<?= number_format((float)$inv['total_amount'], 2) ?></td>
                                <td>₹<?= number_format((float)$inv['paid_amount'], 2) ?></td>
                                <td><span class="badge <?= $invBadge ?>"><span class="badge-dot"></span><?= htmlspecialchars($inv['status']) ?></span></td>
                                <td><a href="invoice-details.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Support Tickets Section -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Support Tickets</h3>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Subject</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clientTickets)): ?>
                            <tr><td colspan="5" class="empty-state">No support tickets submitted.</td></tr>
                        <?php else: foreach ($clientTickets as $t): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['ticket_number']) ?></strong></td>
                                <td><?= htmlspecialchars($t['subject']) ?></td>
                                <td><span class="badge <?= $t['priority'] === 'High' ? 'badge-danger' : 'badge-neutral' ?>"><?= htmlspecialchars($t['priority']) ?></span></td>
                                <td><span class="badge badge-primary"><?= htmlspecialchars($t['status']) ?></span></td>
                                <td><a href="ticket-details.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline">Thread</a></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal: Edit Profile -->
<div id="editProfileModal" class="admin-modal-backdrop">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Edit Client Profile</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('editProfileModal')">&times;</button>
        </div>
        <form method="POST" action="client-details.php?id=<?= $clientId ?>">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="update_profile">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Contact Person Name</label>
                    <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($client['contact_person']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($client['company_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($client['phone']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">GST Number</label>
                    <input type="text" name="gst_number" class="form-control" value="<?= htmlspecialchars($client['gst_number'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($client['address'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="active" <?= $client['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $client['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('editProfileModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reset Password -->
<div id="resetPasswordModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 440px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Reset Client Portal Password</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('resetPasswordModal')">&times;</button>
        </div>
        <form method="POST" action="client-details.php?id=<?= $clientId ?>">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="reset_password">
            <div class="admin-modal-body">
                <p style="font-size: 13.5px; color: var(--admin-text-muted); margin-bottom: 16px;">
                    Set a new secure password for <strong><?= htmlspecialchars($client['email']) ?></strong>. The client will be able to log in to the Client Portal using this password.
                </p>
                <div class="form-group">
                    <label class="form-label">New Password (Min. 6 chars)</label>
                    <input type="password" name="new_password" class="form-control" placeholder="••••••••••••" required>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('resetPasswordModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
