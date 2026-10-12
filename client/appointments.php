<?php
/**
 * GrihGO Digital & Tech — Client Appointments Directory
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/includes/auth.php';

$portalPageTitle = 'Appointments';
$portalHeaderTitle = 'Consultation & Review Appointments';
$current_portal_page = 'appointments';

$pdo = Database::getConnection();

// Scoped strictly to authenticated client (matching client_id OR verified registered email)
$stmt = $pdo->prepare("
    SELECT * FROM appointments 
    WHERE client_id = :cid OR (client_id IS NULL AND LOWER(email) = LOWER(:email))
    ORDER BY CASE 
        WHEN status = 'Confirmed' THEN 1 
        WHEN status = 'Pending' THEN 2 
        WHEN status = 'Rescheduled' THEN 3 
        ELSE 4 
    END, preferred_date DESC, created_at DESC
");
$stmt->execute([':cid' => $clientId, ':email' => $authClient['email']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--portal-text-main); margin: 0 0 4px 0;">Consultation Appointments</h2>
        <p style="font-size: 0.85rem; color: var(--portal-text-muted); margin: 0;">Track meeting schedules, engineering sprint reviews, and technical advisory sessions</p>
    </div>
    <a href="../appointment.php" target="_blank" class="btn-portal-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        <span>Schedule New Appointment</span>
    </a>
</div>

<div class="portal-card">
    <div class="portal-card-header">
        <h3 class="portal-card-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line></svg>
            <span>My Bookings (<?= count($appointments) ?>)</span>
        </h3>
    </div>

    <div class="portal-card-body" style="padding: 0;">
        <?php if (empty($appointments)): ?>
            <div class="portal-empty-state">
                <div class="portal-empty-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect></svg>
                </div>
                <div class="portal-empty-title">No Appointments Found</div>
                <p class="portal-empty-desc">You don't have any appointments on record. Would you like to schedule a strategy session?</p>
                <a href="../appointment.php" target="_blank" class="btn-portal-primary">Schedule Consultation &rarr;</a>
            </div>
        <?php else: ?>
            <div class="portal-table-container">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service Requested</th>
                            <th>Scheduled Date</th>
                            <th>Time Window</th>
                            <th>Status</th>
                            <th>Meeting Notes</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appt): ?>
                            <?php
                            $status = $appt['status'];
                            $badgeClass = 'badge-pending';
                            if ($status === 'Confirmed') $badgeClass = 'badge-completed';
                            elseif ($status === 'Rescheduled') $badgeClass = 'badge-medium';
                            elseif ($status === 'Completed') $badgeClass = 'badge-low';
                            elseif ($status === 'Cancelled') $badgeClass = 'badge-overdue';
                            ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--portal-primary);">#APT-<?= str_pad((string)$appt['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--portal-text-main);">
                                        <?= htmlspecialchars($appt['service'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <?php if (!empty($appt['budget_range'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--portal-text-muted);">
                                            Budget: <?= htmlspecialchars($appt['budget_range'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 0.88rem;">
                                        <?= htmlspecialchars($appt['preferred_date'] ?? 'TBD', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td style="font-size: 0.82rem; color: #475569;">
                                    <?= htmlspecialchars($appt['preferred_time'] ?? 'Pending', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <span class="badge-status <?= $badgeClass ?>">
                                        <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="max-width: 260px; font-size: 0.8rem; color: var(--portal-text-muted);">
                                    <?= htmlspecialchars(mb_substr($appt['message'] ?? 'Consultation meeting', 0, 80), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="font-size: 0.78rem; color: #94a3b8;">
                                    <?= date('d M Y', strtotime($appt['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
