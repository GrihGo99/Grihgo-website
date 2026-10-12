<?php
/**
 * GrihGO Digital & Tech — Manager Lead Details & Progression
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

require_once __DIR__ . '/includes/auth.php';

$leadId = (int)($_GET['id'] ?? 0);
if ($leadId <= 0) {
    header('Location: leads.php');
    exit;
}

$managerId = (int)$currentManager['id'];

// Check permission: manager can access assigned or unassigned leads
$stmt = $pdo->prepare("
    SELECT l.*, a.full_name AS assigned_admin_name 
    FROM leads l 
    LEFT JOIN admins a ON l.assigned_to = a.id 
    WHERE l.id = ? AND (l.assigned_to = ? OR l.assigned_to IS NULL OR ? IN ('admin', 'super_admin'))
    LIMIT 1
");
$stmt->execute([$leadId, $managerId, $currentManager['role']]);
$lead = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lead) {
    header('Location: leads.php?error=not_found');
    exit;
}

$pageTitle = 'Lead #' . $lead['id'] . ' — ' . htmlspecialchars($lead['name']);
$pageSection = 'leads';

$error = '';
$success = '';
$validStatuses = ['New', 'Contacted', 'Interested', 'Proposal Sent', 'Negotiation', 'Converted', 'Lost'];

// Handle Lead Update (Status, Notes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_lead') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $status = in_array($_POST['status'] ?? '', $validStatuses, true) ? $_POST['status'] : $lead['status'];
        $notes  = sanitizeInput($_POST['notes'] ?? '');

        try {
            $upd = $pdo->prepare("UPDATE leads SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $upd->execute([$status, $notes, $leadId]);

            // Notify Admin
            addSystemNotification(
                null,
                'Lead Progression Updated',
                "Manager {$currentManager['full_name']} updated Lead #{$leadId} ({$lead['name']}) to '{$status}'.",
                'admin',
                'admin/lead-details.php?id=' . $leadId
            );

            // Re-fetch lead
            $stmt->execute([$leadId, $managerId, $currentManager['role']]);
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);
            $success = 'Lead details updated successfully.';
        } catch (Exception $e) {
            $error = 'Failed to update lead information.';
        }
    }
}

// Handle Convert to Client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'convert_lead') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $companyName   = sanitizeInput($_POST['company_name'] ?? ($lead['company_name'] ?: $lead['name'] . ' Company'));
        $contactPerson = sanitizeInput($_POST['contact_person'] ?? $lead['name']);
        $email         = sanitizeEmail($_POST['email'] ?? $lead['email']);
        $phone         = sanitizePhone($_POST['phone'] ?? $lead['phone']);
        $password      = bin2hex(random_bytes(18));

        try {
            $pdo->beginTransaction();

            $checkClient = $pdo->prepare("SELECT id FROM clients WHERE email = ? LIMIT 1");
            $checkClient->execute([$email]);
            $existingClientId = $checkClient->fetchColumn();

            if ($existingClientId) {
                $newClientId = (int)$existingClientId;
            } else {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $userStmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, 'client', 'active')");
                $userStmt->execute([$contactPerson, $email, $phone, $passwordHash]);
                $userId = (int)$pdo->lastInsertId();

                $clientStmt = $pdo->prepare("INSERT INTO clients (user_id, company_name, contact_person, email, phone, status) VALUES (?, ?, ?, ?, ?, 'active')");
                $clientStmt->execute([$userId, $companyName, $contactPerson, $email, $phone]);
                $newClientId = (int)$pdo->lastInsertId();

                addSystemNotification(
                    $newClientId,
                    'Welcome to GrihGO Client Portal',
                    'Your client workspace has been initialized. Welcome aboard!',
                    'client',
                    'client/dashboard.php'
                );
            }

            // Mark lead as converted
            $pdo->prepare("UPDATE leads SET status = 'Converted', notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                ->execute([($lead['notes'] ? $lead['notes'] . "\n" : '') . "[Converted to Client #{$newClientId} by {$currentManager['full_name']} on " . date('Y-m-d H:i') . "]", $leadId]);

            // Notify Admin
            addSystemNotification(
                null,
                'Lead Converted to Client',
                "Manager {$currentManager['full_name']} successfully converted Lead #{$leadId} ({$lead['name']}) to Client #{$newClientId}.",
                'admin',
                'admin/client-details.php?id=' . $newClientId
            );

            $pdo->commit();

            // Re-fetch lead
            $stmt->execute([$leadId, $managerId, $currentManager['role']]);
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);
            $success = "Lead successfully converted to Client #{$newClientId}!";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Failed to convert lead: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-content">
    <div class="admin-header-bar">
        <div>
            <div style="font-size: 0.85rem; color: #6b7280; margin-bottom: 4px;">
                <a href="leads.php" style="color: #059669; text-decoration: none;">&larr; Back to Assigned Leads</a>
            </div>
            <h1 class="page-title">Lead Dossier: <?= htmlspecialchars($lead['name']) ?></h1>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="btn-primary" style="background: #0256c4; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                Call Lead
            </a>
            <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $lead['phone']) ?>?text=<?= urlencode('Hello ' . $lead['name'] . ', this is ' . $currentManager['full_name'] . ' from GrihGO Digital & Tech regarding your enquiry.') ?>" target="_blank" class="btn-primary" style="background: #059669; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                </svg>
                WhatsApp
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div style="background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Left: Details & Notes -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="admin-card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 class="card-title">Lead Information</h2>
                    <span class="badge" style="
                        <?php
                        switch ($lead['status']) {
                            case 'New': echo 'background: #dbeafe; color: #1e40af;'; break;
                            case 'Contacted': echo 'background: #fef3c7; color: #92400e;'; break;
                            case 'Interested': echo 'background: #e0e7ff; color: #3730a3;'; break;
                            case 'Proposal Sent': echo 'background: #f3e8ff; color: #6b21a8;'; break;
                            case 'Negotiation': echo 'background: #fed7aa; color: #9a3412;'; break;
                            case 'Converted': echo 'background: #d1fae5; color: #065f46;'; break;
                            case 'Lost': echo 'background: #fee2e2; color: #991b1b;'; break;
                            default: echo 'background: #f3f4f6; color: #374151;';
                        }
                        ?>
                        font-size: 0.85rem; padding: 4px 10px; border-radius: 9999px; font-weight: 600;
                    ">
                        Status: <?= htmlspecialchars($lead['status']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; color: #6b7280; font-weight: 600;">Full Name</div>
                            <div style="font-size: 1.05rem; font-weight: 600; color: #111827;"><?= htmlspecialchars($lead['name']) ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; color: #6b7280; font-weight: 600;">Business / Org</div>
                            <div style="font-size: 1.05rem; font-weight: 600; color: #111827;"><?= htmlspecialchars($lead['company_name'] ?: 'Not specified') ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; color: #6b7280; font-weight: 600;">Email Address</div>
                            <div style="font-size: 0.95rem; color: #111827;">
                                <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" style="color: #0256c4;"><?= htmlspecialchars($lead['email'] ?: 'None') ?></a>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; color: #6b7280; font-weight: 600;">Phone Number</div>
                            <div style="font-size: 0.95rem; color: #111827;">
                                <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" style="color: #059669; font-weight: 600;"><?= htmlspecialchars($lead['phone']) ?></a>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; color: #6b7280; font-weight: 600;">Service Required</div>
                            <div style="font-size: 0.95rem; color: #111827; font-weight: 500;"><?= htmlspecialchars($lead['service_interest'] ?? ($lead['service'] ?? 'General Enquiry')) ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; color: #6b7280; font-weight: 600;">Budget Tier</div>
                            <div style="font-size: 0.95rem; color: #111827;"><?= htmlspecialchars($lead['budget_range'] ?? ($lead['budget'] ?? 'Not specified')) ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; color: #6b7280; font-weight: 600;">Lead Source</div>
                            <div style="font-size: 0.95rem; color: #111827;"><?= htmlspecialchars($lead['source'] ?? 'Website Form') ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; color: #6b7280; font-weight: 600;">Submitted On</div>
                            <div style="font-size: 0.95rem; color: #111827;"><?= date('d M Y, h:i A', strtotime($lead['created_at'])) ?></div>
                        </div>
                    </div>

                    <?php if (!empty($lead['message'])): ?>
                        <div style="margin-top: 16px; padding: 14px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                            <div style="font-size: 0.8rem; font-weight: 600; color: #4b5563; margin-bottom: 6px;">Original Message / Requirements:</div>
                            <div style="color: #1f2937; line-height: 1.5; font-size: 0.95rem; white-space: pre-wrap;"><?= htmlspecialchars($lead['message']) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Follow-up Notes & Status Form -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="card-title">Update Lead Progression</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="update_lead">

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 6px;">Lead Status</label>
                            <select name="status" class="form-control" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                                <?php foreach ($validStatuses as $st): ?>
                                    <option value="<?= $st ?>" <?= $lead['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 6px;">Manager Notes &amp; Follow-up History</label>
                            <textarea name="notes" rows="6" class="form-control" placeholder="Record calls, customer requirements, agreed proposals, next steps..." style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; font-size: 0.9rem;"><?= htmlspecialchars($lead['notes'] ?? '') ?></textarea>
                            <small style="color: #6b7280; font-size: 0.8rem;">Every note update is logged and automatically synced with Super Admin oversight.</small>
                        </div>

                        <button type="submit" class="btn-primary" style="background: #059669; border: none; padding: 10px 20px; font-weight: 600; cursor: pointer; border-radius: 6px; color: #fff;">
                            Save Lead Updates
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Actions & Conversion -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Convert to Client Card -->
            <div class="admin-card" style="border: 2px solid #a7f3d0; background: #f0fdf4;">
                <div class="card-header" style="background: transparent; border-bottom: 1px solid #d1fae5;">
                    <h3 class="card-title" style="color: #065f46; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <polyline points="17 11 19 13 23 9"></polyline>
                        </svg>
                        Convert to Client
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($lead['status'] === 'Converted'): ?>
                        <div style="background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; font-size: 0.9rem; font-weight: 500;">
                            ✓ This lead has already been marked as Converted into a registered GrihGO Client.
                        </div>
                    <?php else: ?>
                        <p style="font-size: 0.875rem; color: #374151; margin-bottom: 16px; line-height: 1.4;">
                            When this prospect agrees to proceed, convert them into an active client record to unlock project provisioning and client portal access.
                        </p>
                        <form method="POST" action="" onsubmit="return confirm('Convert this lead to an active client?');">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="action" value="convert_lead">
                            
                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Company Name</label>
                                <input type="text" name="company_name" value="<?= htmlspecialchars($lead['company_name'] ?: $lead['name'] . ' Enterprises') ?>" class="form-control" required style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                            </div>

                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Contact Person</label>
                                <input type="text" name="contact_person" value="<?= htmlspecialchars($lead['name']) ?>" class="form-control" required style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                            </div>

                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($lead['email']) ?>" class="form-control" required style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                            </div>

                            <div style="margin-bottom: 16px;">
                                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Phone</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($lead['phone']) ?>" class="form-control" required style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                            </div>

                            <button type="submit" class="btn-primary" style="width: 100%; background: #059669; border: none; padding: 10px; font-weight: 600; cursor: pointer; border-radius: 6px; color: #fff;">
                                Convert &amp; Initialize Client Account
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ownership & Context Card -->
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 1rem;">Assignment Context</h3>
                </div>
                <div class="card-body" style="font-size: 0.9rem; color: #4b5563;">
                    <div style="margin-bottom: 10px;">
                        <strong>Current Handler:</strong><br>
                        <?= htmlspecialchars($lead['assigned_admin_name'] ?? $currentManager['full_name']) ?>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Lead Reference:</strong><br>
                        GRIHGO-LEAD-#<?= str_pad($lead['id'], 5, '0', STR_PAD_LEFT) ?>
                    </div>
                    <div>
                        <strong>Last Updated:</strong><br>
                        <?= !empty($lead['updated_at']) ? date('d M Y, h:i A', strtotime($lead['updated_at'])) : 'Not modified' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
