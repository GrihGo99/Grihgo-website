<?php
/**
 * GrihGO Digital & Tech — Client Portal Dashboard
 * Version: 3.0 (Part 3 - Client Dashboard)
 * Technology: PHP 8+, PDO, Real Client Data Scoping & Responsive SaaS UI
 */

require_once __DIR__ . '/includes/auth.php';

$portalPageTitle = 'Dashboard';
$portalHeaderTitle = 'Client Dashboard';
$current_portal_page = 'dashboard';

$pdo = Database::getConnection();

// 1. Fetch Real Summary Metrics scoped to logged-in client
// Active Projects
$stmtActiveProjects = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE client_id = :cid AND status != 'Completed'");
$stmtActiveProjects->execute([':cid' => $clientId]);
$countActiveProjects = (int)$stmtActiveProjects->fetchColumn();

// Pending Tasks
$stmtPendingTasks = $pdo->prepare("
    SELECT COUNT(pt.id) 
    FROM project_tasks pt
    JOIN projects p ON pt.project_id = p.id
    WHERE p.client_id = :cid AND pt.status IN ('Pending', 'In Progress')
");
$stmtPendingTasks->execute([':cid' => $clientId]);
$countPendingTasks = (int)$stmtPendingTasks->fetchColumn();

// Unpaid/Pending Invoices
$stmtInvoices = $pdo->prepare("SELECT COUNT(*), COALESCE(SUM(total_amount - paid_amount), 0) FROM invoices WHERE client_id = :cid AND status != 'Paid'");
$stmtInvoices->execute([':cid' => $clientId]);
$invoiceStats = $stmtInvoices->fetch(PDO::FETCH_NUM);
$countPendingInvoices = (int)$invoiceStats[0];
$sumPendingAmount = (float)$invoiceStats[1];

// Upcoming Appointments
$stmtAppointments = $pdo->prepare("
    SELECT COUNT(*) FROM appointments 
    WHERE (client_id = :cid OR (client_id IS NULL AND LOWER(email) = LOWER(:email)))
    AND status IN ('Confirmed', 'Pending', 'Rescheduled')
");
$stmtAppointments->execute([':cid' => $clientId, ':email' => $authClient['email']]);
$countUpcomingAppointments = (int)$stmtAppointments->fetchColumn();

// 2. Fetch Active Projects List
$stmtProjects = $pdo->prepare("
    SELECT * FROM projects 
    WHERE client_id = :cid 
    ORDER BY CASE WHEN status = 'In Progress' THEN 1 WHEN status = 'Planning' THEN 2 ELSE 3 END, updated_at DESC 
    LIMIT 4
");
$stmtProjects->execute([':cid' => $clientId]);
$activeProjects = $stmtProjects->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch Upcoming Appointments
$stmtApptList = $pdo->prepare("
    SELECT * FROM appointments 
    WHERE (client_id = :cid OR (client_id IS NULL AND LOWER(email) = LOWER(:email)))
    ORDER BY preferred_date ASC, created_at DESC 
    LIMIT 3
");
$stmtApptList->execute([':cid' => $clientId, ':email' => $authClient['email']]);
$upcomingAppointments = $stmtApptList->fetchAll(PDO::FETCH_ASSOC);

// 4. Fetch Pending Tasks
$stmtTaskList = $pdo->prepare("
    SELECT pt.*, p.name AS project_name 
    FROM project_tasks pt
    JOIN projects p ON pt.project_id = p.id
    WHERE p.client_id = :cid AND pt.status IN ('Pending', 'In Progress')
    ORDER BY CASE WHEN pt.priority = 'Urgent' THEN 1 WHEN pt.priority = 'High' THEN 2 ELSE 3 END, pt.due_date ASC
    LIMIT 5
");
$stmtTaskList->execute([':cid' => $clientId]);
$pendingTasksList = $stmtTaskList->fetchAll(PDO::FETCH_ASSOC);

// 5. Fetch Recent Invoices
$stmtInvList = $pdo->prepare("
    SELECT * FROM invoices 
    WHERE client_id = :cid 
    ORDER BY invoice_date DESC, id DESC 
    LIMIT 4
");
$stmtInvList->execute([':cid' => $clientId]);
$recentInvoices = $stmtInvList->fetchAll(PDO::FETCH_ASSOC);

// 6. Fetch Recent Notifications
$stmtRecentNotif = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE client_id = :cid
    ORDER BY created_at DESC 
    LIMIT 4
");
$stmtRecentNotif->execute([':cid' => $clientId]);
$recentNotifications = $stmtRecentNotif->fetchAll(PDO::FETCH_ASSOC);

// 7. Fetch Support Tickets
$stmtTickets = $pdo->prepare("
    SELECT * FROM support_tickets 
    WHERE client_id = :cid 
    ORDER BY updated_at DESC, id DESC 
    LIMIT 3
");
$stmtTickets->execute([':cid' => $clientId]);
$recentTickets = $stmtTickets->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner -->
<div class="portal-welcome-banner">
    <div>
        <h2 class="portal-welcome-title">Welcome Back, <?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') ?>!</h2>
        <p class="portal-welcome-sub">
            Connected to <strong><?= htmlspecialchars($clientCompany, ENT_QUOTES, 'UTF-8') ?></strong> &bull; All systems operational. Here is your enterprise delivery overview.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="projects.php" class="btn btn-outline" style="background: rgba(255, 255, 255, 0.15); color: #ffffff; border-color: rgba(255, 255, 255, 0.3); font-size: 0.85rem; padding: 8px 16px;">
            <span>View All Projects</span>
        </a>
        <a href="support-create.php" class="btn btn-primary" style="background: var(--portal-accent, #10b981); border: none; font-size: 0.85rem; padding: 8px 16px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>New Support Ticket</span>
        </a>
    </div>
</div>

<!-- 4 Summary Stat Cards -->
<div class="portal-stats-grid">
    <div class="portal-stat-card">
        <div class="portal-stat-header">
            <span class="portal-stat-label">Active Projects</span>
            <div class="portal-stat-icon-wrapper blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
            </div>
        </div>
        <div class="portal-stat-value"><?= $countActiveProjects ?></div>
        <div class="portal-stat-footer">
            <span style="color: var(--portal-accent); font-weight: 600;">Live Milestones</span> &bull; Scoped to account
        </div>
    </div>

    <div class="portal-stat-card">
        <div class="portal-stat-header">
            <span class="portal-stat-label">Pending Tasks</span>
            <div class="portal-stat-icon-wrapper amber">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
        </div>
        <div class="portal-stat-value"><?= $countPendingTasks ?></div>
        <div class="portal-stat-footer">
            <span style="color: #d97706; font-weight: 600;">In Progress</span> &bull; Assigned to team
        </div>
    </div>

    <div class="portal-stat-card">
        <div class="portal-stat-header">
            <span class="portal-stat-label">Pending Invoices</span>
            <div class="portal-stat-icon-wrapper red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
        </div>
        <div class="portal-stat-value"><?= $countPendingInvoices ?></div>
        <div class="portal-stat-footer">
            <?php if ($sumPendingAmount > 0): ?>
                <span style="color: #b91c1c; font-weight: 600;">₹<?= number_format($sumPendingAmount, 2) ?></span> due
            <?php else: ?>
                <span style="color: var(--portal-accent); font-weight: 600;">All Clear</span> &bull; Zero balance
            <?php endif; ?>
        </div>
    </div>

    <div class="portal-stat-card">
        <div class="portal-stat-header">
            <span class="portal-stat-label">Appointments</span>
            <div class="portal-stat-icon-wrapper green">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line></svg>
            </div>
        </div>
        <div class="portal-stat-value"><?= $countUpcomingAppointments ?></div>
        <div class="portal-stat-footer">
            <span style="color: var(--portal-accent); font-weight: 600;">Scheduled</span> &bull; Tech reviews
        </div>
    </div>
</div>

<!-- Main Dashboard Grid: Projects & Upcoming Appointments -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 24px; margin-bottom: 24px;">
    <!-- Active Projects Section -->
    <div class="portal-card" style="margin-bottom: 0;">
        <div class="portal-card-header">
            <h3 class="portal-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                <span>Active Projects</span>
            </h3>
            <a href="projects.php" class="btn-portal-outline btn-portal-sm">View All &rarr;</a>
        </div>
        <div class="portal-card-body" style="padding: 16px 20px;">
            <?php if (empty($activeProjects)): ?>
                <div class="portal-empty-state">
                    <div class="portal-empty-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <div class="portal-empty-title">No Projects Yet</div>
                    <p class="portal-empty-desc">No active projects are currently assigned to your account.</p>
                    <a href="services.php" class="btn-portal-primary btn-portal-sm">Explore GrihGO Services</a>
                </div>
            <?php else: ?>
                <?php foreach ($activeProjects as $p): ?>
                    <div style="padding: 14px 0; border-bottom: 1px solid var(--portal-border);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <div>
                                <a href="project-details.php?id=<?= (int)$p['id'] ?>" style="font-weight: 700; color: var(--portal-text-main); text-decoration: none; font-size: 0.95rem;">
                                    <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                                <div style="font-size: 0.78rem; color: var(--portal-text-muted);">
                                    <?= htmlspecialchars($p['service'], ENT_QUOTES, 'UTF-8') ?> &bull; Team: <?= htmlspecialchars($p['assigned_team'] ?? 'GrihGO Core', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            <span class="badge-status <?= $p['status'] === 'Completed' ? 'badge-completed' : 'badge-in-progress' ?>">
                                <?= htmlspecialchars($p['status'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="portal-progress-bar-bg" style="flex: 1;">
                                <div class="portal-progress-bar-fill" style="width: <?= (int)$p['progress'] ?>%;"></div>
                            </div>
                            <span style="font-size: 0.8rem; font-weight: 700; color: var(--portal-primary); min-width: 36px; text-align: right;">
                                <?= (int)$p['progress'] ?>%
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Appointments Section -->
    <div class="portal-card" style="margin-bottom: 0;">
        <div class="portal-card-header">
            <h3 class="portal-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line></svg>
                <span>Upcoming Appointments</span>
            </h3>
            <a href="appointments.php" class="btn-portal-outline btn-portal-sm">All Bookings &rarr;</a>
        </div>
        <div class="portal-card-body" style="padding: 16px 20px;">
            <?php if (empty($upcomingAppointments)): ?>
                <div class="portal-empty-state">
                    <div class="portal-empty-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect></svg>
                    </div>
                    <div class="portal-empty-title">No Appointments</div>
                    <p class="portal-empty-desc">You don't have any upcoming appointments scheduled right now.</p>
                    <a href="../appointment.php" target="_blank" class="btn-portal-primary btn-portal-sm">Schedule Meeting</a>
                </div>
            <?php else: ?>
                <?php foreach ($upcomingAppointments as $appt): ?>
                    <div style="padding: 12px 14px; background: #f8fafc; border: 1px solid var(--portal-border); border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--portal-text-main);">
                                <?= htmlspecialchars($appt['service'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--portal-text-muted); margin-top: 2px;">
                                📅 <?= htmlspecialchars($appt['preferred_date'] ?? 'TBD', ENT_QUOTES, 'UTF-8') ?> &bull; ⏰ <?= htmlspecialchars($appt['preferred_time'] ?? 'Pending Schedule', ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </div>
                        <span class="badge-status <?= $appt['status'] === 'Confirmed' ? 'badge-completed' : ($appt['status'] === 'Rescheduled' ? 'badge-pending' : 'badge-in-progress') ?>">
                            <?= htmlspecialchars($appt['status'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Pending Tasks & Recent Invoices Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 24px; margin-bottom: 24px;">
    <!-- Pending Tasks Section -->
    <div class="portal-card" style="margin-bottom: 0;">
        <div class="portal-card-header">
            <h3 class="portal-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>Pending Project Tasks</span>
            </h3>
        </div>
        <div class="portal-card-body" style="padding: 0;">
            <?php if (empty($pendingTasksList)): ?>
                <div class="portal-empty-state">
                    <div class="portal-empty-title">All Caught Up!</div>
                    <p class="portal-empty-desc">No pending tasks for your projects right now.</p>
                </div>
            <?php else: ?>
                <div class="portal-table-container">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Task Name</th>
                                <th>Project</th>
                                <th>Priority</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingTasksList as $task): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600;"><?= htmlspecialchars($task['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div style="font-size: 0.75rem; color: var(--portal-text-muted);">Due: <?= htmlspecialchars($task['due_date'] ?? 'Flexible', ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td style="font-size: 0.82rem; color: #475569;">
                                        <?= htmlspecialchars($task['project_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-<?= strtolower($task['priority']) ?>">
                                            <?= htmlspecialchars($task['priority'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status <?= $task['status'] === 'Completed' ? 'badge-completed' : 'badge-in-progress' ?>">
                                            <?= htmlspecialchars($task['status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Invoices Section -->
    <div class="portal-card" style="margin-bottom: 0;">
        <div class="portal-card-header">
            <h3 class="portal-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                <span>Recent Invoices</span>
            </h3>
            <a href="invoices.php" class="btn-portal-outline btn-portal-sm">All Invoices &rarr;</a>
        </div>
        <div class="portal-card-body" style="padding: 0;">
            <?php if (empty($recentInvoices)): ?>
                <div class="portal-empty-state">
                    <div class="portal-empty-title">No Invoices Available</div>
                    <p class="portal-empty-desc">No billing invoices have been generated for your account yet.</p>
                </div>
            <?php else: ?>
                <div class="portal-table-container">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentInvoices as $inv): ?>
                                <tr>
                                    <td>
                                        <strong style="color: var(--portal-primary);"><?= htmlspecialchars($inv['invoice_number'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    </td>
                                    <td style="font-size: 0.82rem; color: #475569;">
                                        <?= htmlspecialchars($inv['invoice_date'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="font-weight: 700;">
                                        ₹<?= number_format((float)$inv['total_amount'], 2) ?>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-<?= strtolower(str_replace(' ', '-', $inv['status'])) ?>">
                                            <?= htmlspecialchars($inv['status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Support Tickets & Recent Notifications Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 24px;">
    <!-- Support Tickets Section -->
    <div class="portal-card" style="margin-bottom: 0;">
        <div class="portal-card-header">
            <h3 class="portal-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <span>Support Tickets</span>
            </h3>
            <a href="support.php" class="btn-portal-outline btn-portal-sm">View All &rarr;</a>
        </div>
        <div class="portal-card-body" style="padding: 16px 20px;">
            <?php if (empty($recentTickets)): ?>
                <div class="portal-empty-state">
                    <div class="portal-empty-title">No Tickets Yet</div>
                    <p class="portal-empty-desc">You haven't created any support tickets yet. Need help? Open a ticket anytime.</p>
                    <a href="support-create.php" class="btn-portal-primary btn-portal-sm">Open Support Ticket</a>
                </div>
            <?php else: ?>
                <?php foreach ($recentTickets as $tck): ?>
                    <div style="padding: 12px 14px; background: #ffffff; border: 1px solid var(--portal-border); border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--portal-primary); font-weight: 700;">
                                <?= htmlspecialchars($tck['ticket_number'], ENT_QUOTES, 'UTF-8') ?> &bull; <?= htmlspecialchars($tck['category'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <a href="support.php?ticket_id=<?= (int)$tck['id'] ?>" style="font-weight: 600; font-size: 0.9rem; color: var(--portal-text-main); text-decoration: none;">
                                <?= htmlspecialchars($tck['subject'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </div>
                        <span class="badge-status badge-<?= strtolower(str_replace(' ', '-', $tck['status'])) ?>">
                            <?= htmlspecialchars($tck['status'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Notifications Section -->
    <div class="portal-card" style="margin-bottom: 0;">
        <div class="portal-card-header">
            <h3 class="portal-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path></svg>
                <span>Recent Notifications</span>
            </h3>
            <a href="notifications.php" class="btn-portal-outline btn-portal-sm">Notification Feed &rarr;</a>
        </div>
        <div class="portal-card-body" style="padding: 16px 20px;">
            <?php if (empty($recentNotifications)): ?>
                <div class="portal-empty-state">
                    <div class="portal-empty-title">You're All Caught Up</div>
                    <p class="portal-empty-desc">No new notifications for your account.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recentNotifications as $notif): ?>
                    <div style="padding: 12px 0; border-bottom: 1px solid var(--portal-border); display: flex; gap: 12px; align-items: flex-start;">
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: <?= empty($notif['is_read']) ? 'var(--portal-primary)' : '#cbd5e1' ?>; margin-top: 6px; flex-shrink: 0;"></div>
                        <div style="flex: 1;">
                            <div style="font-size: 0.88rem; font-weight: <?= empty($notif['is_read']) ? '700' : '500' ?>; color: var(--portal-text-main);">
                                <?= htmlspecialchars($notif['title'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--portal-text-muted); margin-top: 2px;">
                                <?= htmlspecialchars($notif['message'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 4px;">
                                <?= htmlspecialchars($notif['created_at'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
