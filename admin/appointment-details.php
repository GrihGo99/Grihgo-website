<?php
/**
 * GrihGO Digital & Tech — Appointment Details & Action Console
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$apptId = (int)($_GET['id'] ?? 0);
if ($apptId <= 0) {
    header('Location: appointments.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT a.*, c.company_name AS client_company_name, c.contact_person AS client_contact_person 
    FROM appointments a 
    LEFT JOIN clients c ON a.client_id = c.id 
    WHERE a.id = ? LIMIT 1
");
$stmt->execute([$apptId]);
$appt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appt) {
    header('Location: appointments.php?error=not_found');
    exit;
}

$pageTitle = 'Appointment #' . $appt['id'] . ' — Details';
$pageSection = 'appointments';

$error = '';
$success = '';

$validStatuses = ['Pending', 'Confirmed', 'Rescheduled', 'Completed', 'Cancelled'];

// Handle Status & Reschedule Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_appointment') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $status = in_array($_POST['status'] ?? '', $validStatuses) ? $_POST['status'] : $appt['status'];
        $prefDate = sanitizeInput($_POST['preferred_date'] ?? $appt['preferred_date']);
        $prefTime = sanitizeInput($_POST['preferred_time'] ?? $appt['preferred_time']);
        $adminNotes = sanitizeInput($_POST['admin_notes'] ?? '');

        try {
            $upd = $pdo->prepare("
                UPDATE appointments 
                SET status = ?, preferred_date = ?, preferred_time = ?, message = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            // If notes provided, append or preserve
            $mergedMessage = $appt['message'];
            if (!empty($adminNotes)) {
                $mergedMessage .= "\n[Admin Note - " . date('d M Y') . "]: " . $adminNotes;
            }
            $upd->execute([$status, $prefDate, $prefTime, $mergedMessage, $apptId]);

            // Notify client if linked
            if (!empty($appt['client_id'])) {
                $pdo->prepare("INSERT INTO notifications (client_id, type, title, message, reference_id, is_read) VALUES (?, 'Appointment', ?, ?, ?, 0)")
                    ->execute([
                        $appt['client_id'],
                        "Appointment {$status}",
                        "Your appointment for {$prefDate} ({$prefTime}) is now marked as {$status}.",
                        $apptId
                    ]);
            }

            logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Appointment Updated', 'appointments', $apptId, "Updated appointment #{$apptId} to {$status}");

            // Refresh appointment record
            $stmt->execute([$apptId]);
            $appt = $stmt->fetch(PDO::FETCH_ASSOC);
            $success = "Appointment #{$apptId} updated successfully!";
        } catch (Exception $e) {
            $error = 'Failed to update appointment.';
        }
    }
}

// Handle Linking to Client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'link_client') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $targetClientId = (int)($_POST['target_client_id'] ?? 0);
        if ($targetClientId > 0) {
            try {
                $pdo->prepare("UPDATE appointments SET client_id = ? WHERE id = ?")->execute([$targetClientId, $apptId]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Appointment Linked', 'appointments', $apptId, "Linked appointment #{$apptId} to client #{$targetClientId}");
                $stmt->execute([$apptId]);
                $appt = $stmt->fetch(PDO::FETCH_ASSOC);
                $success = "Appointment linked to client successfully!";
            } catch (Exception $e) {
                $error = 'Failed to link client.';
            }
        }
    }
}

// Fetch all clients for potential linking
$allClients = $pdo->query("SELECT id, company_name, contact_person FROM clients WHERE status = 'active' ORDER BY company_name ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <div style="font-size: 13px; color: var(--admin-text-muted); margin-bottom: 4px;">
            <a href="appointments.php" style="color: var(--admin-primary); text-decoration: none;">&larr; Back to Appointments</a>
        </div>
        <h1>Consultation Review: <?= htmlspecialchars($appt['full_name']) ?></h1>
        <p class="page-subtitle">Schedule details, service scope, and client notification status.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

    <!-- Left: Appointment Data -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Meeting & Requirement Details</h3>
            <span class="badge badge-primary"><?= htmlspecialchars($appt['status']) ?></span>
        </div>
        <div class="admin-card-body" style="font-size: 13.5px; line-height: 1.8;">
            <div><strong style="color: var(--admin-text-muted);">Prospect Name:</strong><br><?= htmlspecialchars($appt['full_name']) ?></div>
            <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Business Entity:</strong><br><?= htmlspecialchars($appt['business_name'] ?: 'Not Specified') ?></div>
            <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Email Address:</strong><br><a href="mailto:<?= htmlspecialchars($appt['email']) ?>" style="color: var(--admin-primary);"><?= htmlspecialchars($appt['email']) ?></a></div>
            <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Phone Number:</strong><br><?= htmlspecialchars($appt['phone']) ?></div>
            <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Service Requested:</strong><br><?= htmlspecialchars($appt['service']) ?></div>
            <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Estimated Budget:</strong><br><strong><?= htmlspecialchars($appt['budget_range'] ?: 'Flexible') ?></strong></div>
            <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Requested Meeting Time:</strong><br><?= htmlspecialchars($appt['preferred_date'] ?? 'N/A') ?> (<?= htmlspecialchars($appt['preferred_time'] ?? '') ?>)</div>
            <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Message / Requirement Summary:</strong><br><div style="background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid var(--admin-border); margin-top: 4px; white-space: pre-line;"><?= htmlspecialchars($appt['message'] ?: 'No additional notes provided.') ?></div></div>
            <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Booking Created:</strong><br><?= date('d M Y, h:i A', strtotime($appt['created_at'])) ?></div>
        </div>
    </div>

    <!-- Right: Status Update, Rescheduling, and Client Linking -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Update Status & Schedule</h3>
            </div>
            <form method="POST" action="appointment-details.php?id=<?= $apptId ?>" class="admin-card-body">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="update_appointment">

                <div class="form-group">
                    <label class="form-label">Appointment Status</label>
                    <select name="status" class="form-control">
                        <?php foreach ($validStatuses as $st): ?>
                            <option value="<?= $st ?>" <?= $appt['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Meeting Date</label>
                        <input type="date" name="preferred_date" class="form-control" value="<?= htmlspecialchars($appt['preferred_date'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meeting Time Slot</label>
                        <input type="text" name="preferred_time" class="form-control" value="<?= htmlspecialchars($appt['preferred_time'] ?? '') ?>" placeholder="e.g. 03:00 PM - 04:00 PM">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Add Admin Meeting Notes</label>
                    <textarea name="admin_notes" class="form-control" rows="3" placeholder="Meeting discussion points, Google Meet / Zoom link, follow-up actions..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Save Appointment Changes</button>
            </form>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Client Account Association</h3>
            </div>
            <div class="admin-card-body">
                <?php if (!empty($appt['client_id'])): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong>Linked to Client:</strong>
                            <div style="color: var(--admin-primary); font-weight: 600;"><?= htmlspecialchars($appt['client_company_name']) ?> (<?= htmlspecialchars($appt['client_contact_person']) ?>)</div>
                        </div>
                        <a href="client-details.php?id=<?= $appt['client_id'] ?>" class="btn btn-sm btn-outline">View Client</a>
                    </div>
                <?php else: ?>
                    <p style="font-size: 13px; color: var(--admin-text-muted); margin-bottom: 14px;">
                        This appointment was submitted by a public prospect and is not currently linked to an existing client account.
                    </p>
                    <form method="POST" action="appointment-details.php?id=<?= $apptId ?>">
                        <?= getCsrfField() ?>
                        <input type="hidden" name="action" value="link_client">
                        <div class="form-group">
                            <label class="form-label">Link to Existing Client</label>
                            <select name="target_client_id" class="form-control">
                                <option value="">Select Client Account...</option>
                                <?php foreach ($allClients as $cl): ?>
                                    <option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['company_name']) ?> (<?= htmlspecialchars($cl['contact_person']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-outline btn-block">Link to Selected Client</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
