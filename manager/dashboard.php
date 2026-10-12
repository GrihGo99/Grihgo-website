<?php
/**
 * GrihGO Digital & Tech — Dedicated Manager Dashboard
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Manager Dashboard';
$pageSection = 'dashboard';
$managerId = (int)$currentManager['id'];

// Metrics scoped to this manager
$stats = [
    'assigned_leads'    => 0,
    'converted_leads'   => 0,
    'active_projects'   => 0,
    'upcoming_appts'    => 0,
    'open_tickets'      => 0
];

try {
    // Leads assigned
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE assigned_to = ?");
    $stmt->execute([$managerId]);
    $stats['assigned_leads'] = (int)$stmt->fetchColumn();

    // Converted leads
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE assigned_to = ? AND status = 'Converted'");
    $stmt->execute([$managerId]);
    $stats['converted_leads'] = (int)$stmt->fetchColumn();

    // Active assigned projects
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE (assigned_to = ? OR manager_id = ?) AND status != 'completed'");
    $stmt->execute([$managerId, $managerId]);
    $stats['active_projects'] = (int)$stmt->fetchColumn();

    // Upcoming appointments
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE (assigned_to = ? OR assigned_to IS NULL) AND status IN ('Pending', 'Confirmed')");
    $stmt->execute([$managerId]);
    $stats['upcoming_appts'] = (int)$stmt->fetchColumn();

    // Open assigned tickets
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE (assigned_to = ? OR assigned_to IS NULL) AND status IN ('Open', 'In Progress')");
    $stmt->execute([$managerId]);
    $stats['open_tickets'] = (int)$stmt->fetchColumn();

    // Recent assigned leads
    $leadsStmt = $pdo->prepare("
        SELECT id, name, phone, email, service_interested, status, created_at 
        FROM leads 
        WHERE assigned_to = ? OR assigned_to IS NULL
        ORDER BY id DESC LIMIT 5
    ");
    $leadsStmt->execute([$managerId]);
    $recentLeads = $leadsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent assigned projects
    $projStmt = $pdo->prepare("
        SELECT p.*, c.company_name, c.contact_person 
        FROM projects p
        LEFT JOIN clients c ON p.client_id = c.id
        WHERE p.assigned_to = ? OR p.manager_id = ?
        ORDER BY p.id DESC LIMIT 5
    ");
    $projStmt->execute([$managerId, $managerId]);
    $recentProjects = $projStmt->fetchAll(PDO::FETCH_ASSOC);

    // Upcoming appointments list
    $apptStmt = $pdo->prepare("
        SELECT * FROM appointments 
        WHERE (assigned_to = ? OR assigned_to IS NULL) AND status IN ('Pending', 'Confirmed')
        ORDER BY preferred_date ASC, id ASC LIMIT 5
    ");
    $apptStmt->execute([$managerId]);
    $upcomingAppts = $apptStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $recentLeads = [];
    $recentProjects = [];
    $upcomingAppts = [];
}

include __DIR__ . '/includes/header.php';
?>

<!-- KPI Cards -->
<div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 28px;">
    <div class="kpi-card" style="background: #ffffff; padding: 22px; border-radius: 12px; border: 1px solid var(--admin-border); box-shadow: var(--admin-shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Assigned Leads</span>
            <span style="background: #ecfdf5; color: #059669; padding: 6px 10px; border-radius: 8px; font-size: 1.1rem;">🎯</span>
        </div>
        <div style="font-size: 1.85rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;"><?= $stats['assigned_leads'] ?></div>
        <div style="font-size: 0.8rem; color: #059669; font-weight: 600;"><?= $stats['converted_leads'] ?> Converted to Clients</div>
    </div>

    <div class="kpi-card" style="background: #ffffff; padding: 22px; border-radius: 12px; border: 1px solid var(--admin-border); box-shadow: var(--admin-shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Active Projects</span>
            <span style="background: #eff6ff; color: #0256c4; padding: 6px 10px; border-radius: 8px; font-size: 1.1rem;">📦</span>
        </div>
        <div style="font-size: 1.85rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;"><?= $stats['active_projects'] ?></div>
        <div style="font-size: 0.8rem; color: #64748b;">Under active management</div>
    </div>

    <div class="kpi-card" style="background: #ffffff; padding: 22px; border-radius: 12px; border: 1px solid var(--admin-border); box-shadow: var(--admin-shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Upcoming Appointments</span>
            <span style="background: #fef3c7; color: #d97706; padding: 6px 10px; border-radius: 8px; font-size: 1.1rem;">📅</span>
        </div>
        <div style="font-size: 1.85rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;"><?= $stats['upcoming_appts'] ?></div>
        <div style="font-size: 0.8rem; color: #d97706; font-weight: 600;">Pending or Confirmed</div>
    </div>

    <div class="kpi-card" style="background: #ffffff; padding: 22px; border-radius: 12px; border: 1px solid var(--admin-border); box-shadow: var(--admin-shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">Support Inquiries</span>
            <span style="background: #fee2e2; color: #dc2626; padding: 6px 10px; border-radius: 8px; font-size: 1.1rem;">💬</span>
        </div>
        <div style="font-size: 1.85rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;"><?= $stats['open_tickets'] ?></div>
        <div style="font-size: 0.8rem; color: #dc2626; font-weight: 600;">Awaiting resolution</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; margin-bottom: 28px;">
    <!-- Assigned Leads Table -->
    <div class="admin-card" style="background: #ffffff; border-radius: 12px; border: 1px solid var(--admin-border); padding: 24px; box-shadow: var(--admin-shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 1.1rem; color: #0f172a;">Assigned Leads</h3>
                <span style="font-size: 0.8rem; color: #64748b;">Direct lead pipeline assigned to your desk</span>
            </div>
            <a href="leads.php" class="btn btn-sm btn-outline-primary">View All &rarr;</a>
        </div>

        <?php if (empty($recentLeads)): ?>
            <p style="color: #64748b; font-size: 0.9rem; text-align: center; padding: 24px;">No assigned leads at this moment.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--admin-border); text-align: left; color: #64748b;">
                            <th style="padding: 10px 8px;">Lead</th>
                            <th style="padding: 10px 8px;">Service</th>
                            <th style="padding: 10px 8px;">Status</th>
                            <th style="padding: 10px 8px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLeads as $lead): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 8px;">
                                    <strong style="color: #0f172a; display: block;"><?= htmlspecialchars($lead['name']) ?></strong>
                                    <span style="font-size: 0.775rem; color: #64748b;"><?= htmlspecialchars($lead['phone']) ?></span>
                                </td>
                                <td style="padding: 12px 8px; color: #475569;">
                                    <?= htmlspecialchars($lead['service_interested'] ?: 'General Inquiry') ?>
                                </td>
                                <td style="padding: 12px 8px;">
                                    <span class="status-badge" style="padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; background: #f1f5f9; color: #334155;">
                                        <?= htmlspecialchars($lead['status'] ?? 'New') ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 8px; text-align: right;">
                                    <a href="lead-details.php?id=<?= $lead['id'] ?>" class="btn btn-sm btn-outline-primary" style="padding: 4px 10px; font-size: 0.75rem;">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Upcoming Appointments -->
    <div class="admin-card" style="background: #ffffff; border-radius: 12px; border: 1px solid var(--admin-border); padding: 24px; box-shadow: var(--admin-shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 1.1rem; color: #0f172a;">Upcoming Appointments</h3>
                <span style="font-size: 0.8rem; color: #64748b;">Consultations and software walkthroughs</span>
            </div>
            <a href="appointments.php" class="btn btn-sm btn-outline-primary">View All &rarr;</a>
        </div>

        <?php if (empty($upcomingAppts)): ?>
            <p style="color: #64748b; font-size: 0.9rem; text-align: center; padding: 24px;">No upcoming appointments.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($upcomingAppts as $appt): ?>
                    <div style="padding: 12px 14px; border: 1px solid #f1f5f9; border-radius: 8px; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: #0f172a; font-size: 0.9rem; display: block;"><?= htmlspecialchars($appt['full_name'] ?? $appt['name'] ?? 'Client') ?></strong>
                            <span style="font-size: 0.775rem; color: #64748b;">
                                📅 <?= htmlspecialchars($appt['preferred_date'] ?? 'TBD') ?> &bull; <?= htmlspecialchars($appt['preferred_time'] ?? '') ?>
                            </span>
                        </div>
                        <a href="appointments.php" class="btn btn-sm btn-ghost" style="font-size: 0.75rem; color: #0256c4;">Update</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Active Projects Under Management -->
<div class="admin-card" style="background: #ffffff; border-radius: 12px; border: 1px solid var(--admin-border); padding: 24px; box-shadow: var(--admin-shadow-sm);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
        <div>
            <h3 style="margin: 0 0 4px 0; font-size: 1.1rem; color: #0f172a;">Assigned Projects &amp; Execution</h3>
            <span style="font-size: 0.8rem; color: #64748b;">Project progress, task milestones and client deliverables</span>
        </div>
        <a href="projects.php" class="btn btn-sm btn-outline-primary">View All Projects &rarr;</a>
    </div>

    <?php if (empty($recentProjects)): ?>
        <p style="color: #64748b; font-size: 0.9rem; text-align: center; padding: 24px;">No active projects currently assigned to you.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--admin-border); text-align: left; color: #64748b;">
                        <th style="padding: 10px 8px;">Project Name</th>
                        <th style="padding: 10px 8px;">Client</th>
                        <th style="padding: 10px 8px;">Progress</th>
                        <th style="padding: 10px 8px;">Status</th>
                        <th style="padding: 10px 8px; text-align: right;">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentProjects as $proj): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px 8px;">
                                <strong style="color: #0f172a;"><?= htmlspecialchars($proj['name'] ?? $proj['title'] ?? 'Project') ?></strong>
                            </td>
                            <td style="padding: 12px 8px; color: #475569;">
                                <?= htmlspecialchars($proj['company_name'] ?: ($proj['contact_person'] ?: 'Client')) ?>
                            </td>
                            <td style="padding: 12px 8px; min-width: 140px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                                        <div style="width: <?= (int)($proj['progress'] ?? 0) ?>%; height: 100%; background: #0256c4;"></div>
                                    </div>
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #475569;"><?= (int)($proj['progress'] ?? 0) ?>%</span>
                                </div>
                            </td>
                            <td style="padding: 12px 8px;">
                                <span class="status-badge" style="padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; background: #ecfdf5; color: #059669;">
                                    <?= htmlspecialchars($proj['status'] ?? 'in_progress') ?>
                                </span>
                            </td>
                            <td style="padding: 12px 8px; text-align: right;">
                                <a href="project-details.php?id=<?= $proj['id'] ?>" class="btn btn-sm btn-outline-primary" style="padding: 4px 10px; font-size: 0.75rem;">
                                    Update Tasks
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
