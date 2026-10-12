<?php
/**
 * GrihGO Digital & Tech — Lead Dossier & Conversion Engine
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$leadId = (int)($_GET['id'] ?? 0);
if ($leadId <= 0) {
    header('Location: leads.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT l.*, a.full_name AS assigned_admin_name 
    FROM leads l 
    LEFT JOIN admins a ON l.assigned_to = a.id 
    WHERE l.id = ? LIMIT 1
");
$stmt->execute([$leadId]);
$lead = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lead) {
    header('Location: leads.php?error=not_found');
    exit;
}

$pageTitle = 'Lead: ' . htmlspecialchars($lead['name']) . ' — Dossier';
$pageSection = 'leads';

$error = '';
$success = '';

$validStatuses = ['New', 'Contacted', 'Interested', 'Proposal Sent', 'Negotiation', 'Converted', 'Lost'];

// Handle Lead Update (Status, Notes, Assignee)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_lead') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $status     = in_array($_POST['status'] ?? '', $validStatuses) ? $_POST['status'] : $lead['status'];
        $notes      = sanitizeInput($_POST['notes'] ?? '');
        $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;

        try {
            $upd = $pdo->prepare("UPDATE leads SET status = ?, notes = ?, assigned_to = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $upd->execute([$status, $notes, $assignedTo, $leadId]);

            logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Lead Updated', 'leads', $leadId, "Updated lead status to {$status}");
            
            // Refresh lead
            $stmt->execute([$leadId]);
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);
            $success = 'Lead information updated successfully!';
        } catch (Exception $e) {
            $error = 'Failed to update lead.';
        }
    }
}

// Handle Lead -> Client Conversion Flow (Section 44)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'convert_to_client') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $companyName   = sanitizeInput($_POST['company_name'] ?? ($lead['business_name'] ?: $lead['name'] . ' Enterprises'));
        $contactPerson = sanitizeInput($_POST['contact_person'] ?? $lead['name']);
        $email         = sanitizeEmail($_POST['email'] ?? $lead['email']);
        $phone         = sanitizePhone($_POST['phone'] ?? $lead['phone']);
        $password      = $_POST['password'] ?? '';
        $address       = sanitizeInput($_POST['address'] ?? '');

        if (strlen($password) < 12) {
            $error = 'Set an initial password with at least 12 characters.';
        }

        try {
            if ($error !== '') {
                throw new RuntimeException($error);
            }
            $pdo->beginTransaction();

            // Check if client with this email already exists
            $checkClient = $pdo->prepare("SELECT id FROM clients WHERE email = ? LIMIT 1");
            $checkClient->execute([$email]);
            $existingClientId = $checkClient->fetchColumn();

            if ($existingClientId) {
                // Link directly
                $newClientId = (int)$existingClientId;
            } else {
                // Create user account
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $userStmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, 'client', 'active')");
                $userStmt->execute([$contactPerson, $email, $phone, $passwordHash]);
                $userId = (int)$pdo->lastInsertId();

                // Create client record
                $clientStmt = $pdo->prepare("INSERT INTO clients (user_id, company_name, contact_person, email, phone, address, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                $clientStmt->execute([$userId, $companyName, $contactPerson, $email, $phone, $address]);
                $newClientId = (int)$pdo->lastInsertId();

                // Notification
                $pdo->prepare("INSERT INTO notifications (client_id, type, title, message, is_read) VALUES (?, 'System', 'Welcome to GrihGO Client Portal', 'Your account has been created via our sales desk. Please login using your registered email.', 0)")
                    ->execute([$newClientId]);
            }

            // Update lead status to Converted
            $pdo->prepare("UPDATE leads SET status = 'Converted', updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$leadId]);

            logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Lead Converted', 'leads', $leadId, "Converted lead {$lead['name']} to client #CL-{$newClientId}");
            $pdo->commit();

            header("Location: client-details.php?id={$newClientId}&converted=1");
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Failed to convert lead into a client: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Fetch staff for assignment
$adminStaff = $pdo->query("SELECT id, full_name, role FROM admins WHERE status = 'active' ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch logs related to this lead
$logsStmt = $pdo->prepare("SELECT * FROM admin_activity_logs WHERE entity_type = 'leads' AND entity_id = ? ORDER BY id DESC");
$logsStmt->execute([$leadId]);
$leadLogs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <div style="font-size: 13px; color: var(--admin-text-muted); margin-bottom: 4px;">
            <a href="leads.php" style="color: var(--admin-primary); text-decoration: none;">&larr; Back to Lead Pipeline</a>
        </div>
        <h1><?= htmlspecialchars($lead['name']) ?></h1>
        <p class="page-subtitle">Lead Dossier &bull; #LD-<?= str_pad($lead['id'], 3, '0', STR_PAD_LEFT) ?> &bull; <?= htmlspecialchars($lead['business_name'] ?: 'Individual Prospect') ?></p>
    </div>
    <div class="page-actions">
        <?php if ($lead['status'] !== 'Converted'): ?>
            <button type="button" class="btn btn-success" onclick="openAdminModal('convertClientModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                <span>Convert to Client Account</span>
            </button>
        <?php else: ?>
            <span class="badge badge-success" style="padding: 8px 14px; font-size: 13px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>Converted to Client</span>
            </span>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

    <!-- Left: Lead Info & Pipeline Updater -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Prospect Details</h3>
                <span class="badge badge-primary"><?= htmlspecialchars($lead['status']) ?></span>
            </div>
            <div class="admin-card-body" style="font-size: 13.5px; line-height: 1.8;">
                <div><strong style="color: var(--admin-text-muted);">Lead Name:</strong><br><?= htmlspecialchars($lead['name']) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Business Entity:</strong><br><?= htmlspecialchars($lead['business_name'] ?: 'Not Specified') ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Email Address:</strong><br><a href="mailto:<?= htmlspecialchars($lead['email']) ?>" style="color: var(--admin-primary);"><?= htmlspecialchars($lead['email']) ?></a></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Phone Number:</strong><br><?= htmlspecialchars($lead['phone']) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Service of Interest:</strong><br><?= htmlspecialchars($lead['service'] ?? 'General') ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Acquisition Source:</strong><br><span class="badge badge-neutral"><?= htmlspecialchars($lead['source'] ?? 'Website') ?></span></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Captured On:</strong><br><?= date('d M Y, h:i A', strtotime($lead['created_at'])) ?></div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Update Pipeline Stage & Notes</h3>
            </div>
            <form method="POST" action="lead-details.php?id=<?= $leadId ?>" class="admin-card-body">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="update_lead">

                <div class="form-group">
                    <label class="form-label">Pipeline Stage</label>
                    <select name="status" class="form-control">
                        <?php foreach ($validStatuses as $st): ?>
                            <option value="<?= $st ?>" <?= $lead['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Assigned Account Manager</label>
                    <select name="assigned_to" class="form-control">
                        <option value="">Unassigned</option>
                        <?php foreach ($adminStaff as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $lead['assigned_to'] == $s['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['full_name']) ?> (<?= ucfirst($s['role']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Internal Sales Notes</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Client pain points, follow up commitments..."><?= htmlspecialchars($lead['notes'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Save Lead Updates</button>
            </form>
        </div>
    </div>

    <!-- Right: Activity History & Progression -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Pipeline Activity History</h3>
            </div>
            <div class="admin-card-body">
                <div class="admin-timeline">
                    <!-- Initial Entry -->
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background: var(--admin-accent);"></div>
                        <div class="timeline-title">Lead Generated</div>
                        <div class="timeline-meta"><?= date('d M Y, h:i A', strtotime($lead['created_at'])) ?> via <?= htmlspecialchars($lead['source'] ?? 'Website') ?></div>
                        <div class="timeline-body">Prospect submitted inquiry for <strong><?= htmlspecialchars($lead['service'] ?? 'GrihGO Solutions') ?></strong>.</div>
                    </div>

                    <?php foreach ($leadLogs as $log): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-title"><?= htmlspecialchars($log['action']) ?></div>
                            <div class="timeline-meta"><?= date('d M Y, h:i A', strtotime($log['created_at'])) ?> by <?= htmlspecialchars($log['admin_name'] ?? 'Admin') ?></div>
                            <div class="timeline-body"><?= htmlspecialchars($log['description'] ?? '') ?></div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($lead['status'] === 'Converted'): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot" style="background: var(--admin-accent);"></div>
                            <div class="timeline-title" style="color: var(--admin-accent);">Successfully Converted</div>
                            <div class="timeline-meta"><?= date('d M Y, h:i A', strtotime($lead['updated_at'] ?? $lead['created_at'])) ?></div>
                            <div class="timeline-body">Commercial agreement confirmed and transitioned into active client contract.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Convert Lead to Client -->
<div id="convertClientModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 580px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Convert Lead to Corporate Client</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('convertClientModal')">&times;</button>
        </div>
        <form method="POST" action="lead-details.php?id=<?= $leadId ?>">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="convert_to_client">
            <div class="admin-modal-body">
                <p style="font-size: 13px; color: var(--admin-text-muted); margin-bottom: 16px;">
                    This will provision a client account in the GrihGO database and grant access to the Client Portal.
                </p>
                <div class="form-group">
                    <label class="form-label">Authorized Contact Person *</label>
                    <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($lead['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Company / Business Name *</label>
                    <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($lead['business_name'] ?: $lead['name'] . ' Tech') ?>" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Official Email (Username) *</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($lead['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($lead['phone']) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Initial Portal Password</label>
                    <input type="password" name="password" class="form-control" minlength="12" autocomplete="new-password" required>
                    <small style="color: var(--admin-text-muted); font-size: 11.5px;">Use a unique password with at least 12 characters.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Registered Office Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Full address"></textarea>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('convertClientModal')">Cancel</button>
                <button type="submit" class="btn btn-success">Confirm & Create Client</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
