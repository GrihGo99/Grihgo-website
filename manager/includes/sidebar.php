<?php
/**
 * GrihGO Digital & Tech — Dedicated Manager Portal Sidebar
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

$currentPage = basename($_SERVER['PHP_SELF']);
$managerId = (int)($_SESSION['manager_id'] ?? 0);

// Fetch counts scoped to this manager
$assignedLeadsCount = 0;
$upcomingApptsCount = 0;
$assignedTicketsCount = 0;
$unreadManagerNotifs = 0;

try {
    if (!isset($pdo)) $pdo = Database::getConnection();
    // Leads assigned or new
    $assignedLeadsCount = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE assigned_to = {$managerId} AND status NOT IN ('Converted', 'Lost')")->fetchColumn();
    // Appointments assigned or pending
    $upcomingApptsCount = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE (assigned_to = {$managerId} OR assigned_to IS NULL) AND status IN ('Pending', 'Confirmed')")->fetchColumn();
    // Tickets assigned or open
    $assignedTicketsCount = (int)$pdo->query("SELECT COUNT(*) FROM support_tickets WHERE (assigned_to = {$managerId} OR assigned_to IS NULL) AND status IN ('Open', 'In Progress')")->fetchColumn();
    // Notifications
    $unreadManagerNotifs = (int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND (user_role = 'manager' OR (client_id IS NULL AND user_role IS NULL))")->fetchColumn();
} catch (Exception $e) {}
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
        <a href="dashboard.php" class="admin-brand">
            <span class="admin-brand-icon" style="background: linear-gradient(135deg, #059669, #10b981);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <polyline points="17 11 19 13 23 9"></polyline>
                </svg>
            </span>
            <span class="admin-brand-text">GrihGO</span>
            <span class="admin-brand-badge" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">Manager</span>
        </a>
    </div>

    <nav class="admin-sidebar-nav">
        <div class="nav-section-title">Overview</div>
        <a href="dashboard.php" class="nav-item <?= in_array($currentPage, ['dashboard.php', 'index.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Dashboard</span>
        </a>

        <div class="nav-section-title">Execution &amp; Workflows</div>
        <a href="leads.php" class="nav-item <?= in_array($currentPage, ['leads.php', 'lead-details.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
            </svg>
            <span>Assigned Leads</span>
            <?php if ($assignedLeadsCount > 0): ?>
                <span class="nav-badge" style="background: #059669;"><?= $assignedLeadsCount ?></span>
            <?php endif; ?>
        </a>

        <a href="projects.php" class="nav-item <?= in_array($currentPage, ['projects.php', 'project-details.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                <polyline points="2 17 12 22 22 17"></polyline>
                <polyline points="2 12 12 17 22 12"></polyline>
            </svg>
            <span>Assigned Projects</span>
        </a>

        <a href="appointments.php" class="nav-item <?= in_array($currentPage, ['appointments.php', 'appointment-details.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>Appointments</span>
            <?php if ($upcomingApptsCount > 0): ?>
                <span class="nav-badge" style="background: #0256c4;"><?= $upcomingApptsCount ?></span>
            <?php endif; ?>
        </a>

        <a href="support-tickets.php" class="nav-item <?= in_array($currentPage, ['support-tickets.php', 'ticket-details.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>Support Tickets</span>
            <?php if ($assignedTicketsCount > 0): ?>
                <span class="nav-badge" style="background: #f59e0b;"><?= $assignedTicketsCount ?></span>
            <?php endif; ?>
        </a>

        <a href="clients.php" class="nav-item <?= in_array($currentPage, ['clients.php', 'client-details.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>Assigned Clients</span>
        </a>

        <div class="nav-section-title">Communications</div>
        <a href="notifications.php" class="nav-item <?= $currentPage === 'notifications.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span>Notifications</span>
            <?php if ($unreadManagerNotifs > 0): ?>
                <span class="nav-badge" style="background: #ef4444;"><?= $unreadManagerNotifs ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-section-title">Session</div>
        <a href="logout.php" class="nav-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Sign Out</span>
        </a>
    </nav>
</aside>
