<?php
/**
 * GrihGO Digital & Tech — Manager Consultation & Appointment Schedule
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Appointments & Consultations';
$pageSection = 'appointments';
$managerId = (int)$currentManager['id'];

$validStatuses = ['Pending', 'Confirmed', 'Rescheduled', 'Completed', 'Cancelled'];
$error = '';
$success = '';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_appt') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $apptId    = (int)($_POST['appointment_id'] ?? 0);
        $newStatus = in_array($_POST['status'] ?? '', $validStatuses, true) ? $_POST['status'] : '';
        $notes     = sanitizeInput($_POST['notes'] ?? '');

        if ($apptId > 0 && $newStatus) {
            try {
                $apptStmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND (assigned_to = ? OR assigned_to IS NULL OR ? IN ('admin', 'super_admin')) LIMIT 1");
                $apptStmt->execute([$apptId, $managerId, $currentManager['role']]);
                $appt = $apptStmt->fetch(PDO::FETCH_ASSOC);

                if ($appt) {
                    $upd = $pdo->prepare("UPDATE appointments SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $upd->execute([$newStatus, $notes, $apptId]);

                    // Client notification
                    if (!empty($appt['client_id'])) {
                        addSystemNotification(
                            (int)$appt['client_id'],
                            "Appointment {$newStatus}",
                            "Your consultation on " . htmlspecialchars($appt['preferred_date'] ?? 'the selected date') . " has been marked as {$newStatus}.",
                            'client',
                            'client/appointments.php'
                        );
                    }

                    // Admin notification
                    $attendeeName = $appt['full_name'] ?? ($appt['name'] ?? 'Attendee');
                    addSystemNotification(
                        null,
                        "Appointment Status Updated",
                        "Manager {$currentManager['full_name']} updated Appointment #{$apptId} ({$attendeeName}) to '{$newStatus}'.",
                        'admin',
                        'admin/appointments.php'
                    );

                    $success = "Appointment #{$apptId} successfully updated to {$newStatus}.";
                } else {
                    $error = 'Appointment not found or not assigned to you.';
                }
            } catch (Exception $e) {
                $error = 'Failed to update appointment: ' . $e->getMessage();
            }
        }
    }
}

// Search and Filter
$search = trim($_GET['search'] ?? '');
$statusFilter = in_array($_GET['status'] ?? '', $validStatuses, true) ? $_GET['status'] : '';

$params = [$managerId];
$sql = "SELECT * FROM appointments WHERE (assigned_to = ? OR assigned_to IS NULL)";

if ($statusFilter) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

if ($search) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR service LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-content">
    <div class="admin-header-bar">
        <div>
            <h1 class="page-title">Appointments &amp; Consultations</h1>
            <p class="page-subtitle">Schedule, verify, confirm, and conduct client discovery calls and technical consultations.</p>
        </div>
        <div style="font-size: 0.9rem; color: #4b5563; background: #fff; padding: 8px 14px; border-radius: 8px; border: 1px solid #e5e7eb;">
            Total Appointments: <strong style="color: #0256c4;"><?= count($appointments) ?></strong>
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

    <!-- Filter & Search Card -->
    <div class="admin-card" style="margin-bottom: 24px;">
        <div class="card-body" style="padding: 16px 20px;">
            <form method="GET" action="" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 240px;">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search attendee, phone, email, service..." class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
                <div style="min-width: 180px;">
                    <select name="status" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <?php foreach ($validStatuses as $st): ?>
                            <option value="<?= $st ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="background: #059669; border: none; padding: 8px 16px; border-radius: 6px; color: #fff; cursor: pointer; font-weight: 500;">
                    Filter
                </button>
                <?php if ($search || $statusFilter): ?>
                    <a href="appointments.php" style="color: #6b7280; text-decoration: none; font-size: 0.9rem; padding: 8px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="admin-card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Attendee &amp; Contact</th>
                            <th>Service Requested</th>
                            <th>Preferred Slot</th>
                            <th>Status</th>
                            <th>Quick Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">
                                    No appointments scheduled or assigned.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $a): ?>
                                <tr>
                                    <td style="font-weight: 600; color: #6b7280;">#<?= $a['id'] ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: #111827;"><?= htmlspecialchars($a['full_name'] ?? ($a['name'] ?? 'Attendee')) ?></div>
                                        <div style="font-size: 0.85rem; color: #059669; font-weight: 500;"><?= htmlspecialchars($a['phone'] ?? '') ?></div>
                                        <div style="font-size: 0.8rem; color: #6b7280;"><?= htmlspecialchars($a['email'] ?? '') ?></div>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; background: #eff6ff; color: #1e40af; padding: 3px 8px; border-radius: 4px; font-weight: 500;">
                                            <?= htmlspecialchars($a['service']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #374151;">
                                        <div style="font-weight: 600;"><?= !empty($a['preferred_date']) ? date('d M Y', strtotime($a['preferred_date'])) : 'Any Date' ?></div>
                                        <div style="color: #6b7280;"><?= htmlspecialchars($a['preferred_time'] ?? 'Flexible') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge" style="
                                            <?php
                                            switch ($a['status']) {
                                                case 'Confirmed': echo 'background: #d1fae5; color: #065f46;'; break;
                                                case 'Pending': echo 'background: #fef3c7; color: #92400e;'; break;
                                                case 'Completed': echo 'background: #dbeafe; color: #1e40af;'; break;
                                                case 'Rescheduled': echo 'background: #fed7aa; color: #9a3412;'; break;
                                                case 'Cancelled': echo 'background: #fee2e2; color: #991b1b;'; break;
                                                default: echo 'background: #f3f4f6; color: #374151;';
                                            }
                                            ?>
                                            font-size: 0.8rem; padding: 4px 10px; border-radius: 9999px; font-weight: 600;
                                        ">
                                            <?= htmlspecialchars($a['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Update Form Inline -->
                                        <form method="POST" action="" style="display: flex; gap: 6px; align-items: center;">
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <input type="hidden" name="action" value="update_appt">
                                            <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                                            
                                            <select name="status" style="font-size: 0.8rem; padding: 4px 6px; border: 1px solid #d1d5db; border-radius: 4px;">
                                                <?php foreach ($validStatuses as $st): ?>
                                                    <option value="<?= $st ?>" <?= $a['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                                <?php endforeach; ?>
                                            </select>

                                            <button type="submit" title="Save Status" style="background: #059669; color: white; border: none; padding: 5px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem; font-weight: 600;">
                                                Update
                                            </button>

                                            <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $a['phone'] ?? '') ?>?text=<?= urlencode('Hello ' . ($a['full_name'] ?? ($a['name'] ?? 'Attendee')) . ', this is ' . $currentManager['full_name'] . ' from GrihGO Digital & Tech regarding your scheduled consultation.') ?>" target="_blank" title="WhatsApp Attendee" style="background: #25D366; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; font-size: 0.75rem; display: inline-flex; align-items: center;">
                                                WA
                                            </a>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
