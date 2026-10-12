<?php
/**
 * GrihGO Digital & Tech — Client Portal Sidebar Component
 * Version: 3.0 (Part 3 - Client Portal)
 */

$currentPage = $current_portal_page ?? 'dashboard';
?>
<aside class="portal-sidebar" id="portalSidebar">
    <!-- Brand / Header -->
    <div class="portal-sidebar-brand">
        <a href="dashboard.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
            <img src="../assets/images/logo/logo.png" alt="GrihGO Digital & Tech">
        </a>
        <span class="portal-sidebar-badge">Client Portal</span>
    </div>

    <!-- Client Account Chip -->
    <div class="portal-sidebar-user">
        <div class="portal-user-avatar">
            <?= htmlspecialchars($initials ?? 'CL', ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="portal-user-meta">
            <div class="portal-user-name" title="<?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="portal-user-company" title="<?= htmlspecialchars($clientCompany, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($clientCompany, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div style="font-size: 0.72rem; color: #94a3b8; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($clientEmail, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($clientEmail, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="portal-sidebar-nav" aria-label="Client Portal Navigation">
        <div class="portal-nav-group-title">Main Menu</div>

        <a href="dashboard.php" class="portal-nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="profile.php" class="portal-nav-link <?= $currentPage === 'profile' ? 'active' : '' ?>">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>My Profile</span>
        </a>

        <a href="projects.php" class="portal-nav-link <?= in_array($currentPage, ['projects', 'project-details']) ? 'active' : '' ?>">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>My Projects</span>
        </a>

        <a href="services.php" class="portal-nav-link <?= $currentPage === 'services' ? 'active' : '' ?>">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
            </svg>
            <span>Services</span>
        </a>

        <div class="portal-nav-group-title">Operations & Support</div>

        <a href="support.php" class="portal-nav-link <?= in_array($currentPage, ['support', 'support-create']) ? 'active' : '' ?>">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>Support Tickets</span>
        </a>

        <a href="appointments.php" class="portal-nav-link <?= $currentPage === 'appointments' ? 'active' : '' ?>">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>Appointments</span>
        </a>

        <a href="invoices.php" class="portal-nav-link <?= $currentPage === 'invoices' ? 'active' : '' ?>">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            <span>Invoices</span>
        </a>

        <a href="payments.php" class="portal-nav-link <?= $currentPage === 'payments' ? 'active' : '' ?>">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                <line x1="1" y1="10" x2="23" y2="10"></line>
            </svg>
            <span>Payments</span>
        </a>

        <a href="documents.php" class="portal-nav-link <?= $currentPage === 'documents' ? 'active' : '' ?>">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span>Documents</span>
        </a>

        <a href="messages.php" class="portal-nav-link <?= $currentPage === 'messages' ? 'active' : '' ?>">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <span>Messages</span>
        </a>

        <a href="notifications.php" class="portal-nav-link <?= $currentPage === 'notifications' ? 'active' : '' ?>">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span>Notifications</span>
            <?php if (!empty($unreadNotificationsCount) && $unreadNotificationsCount > 0): ?>
                <span class="portal-nav-badge danger"><?= $unreadNotificationsCount ?></span>
            <?php endif; ?>
        </a>

        <div class="portal-nav-group-title">Account Security</div>

        <a href="change-password.php" class="portal-nav-link <?= $currentPage === 'change-password' ? 'active' : '' ?>">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <span>Change Password</span>
        </a>

        <a href="logout.php" class="portal-nav-link" style="color: #ef4444;" onclick="return confirm('Confirm sign out?');">
            <svg class="portal-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Logout</span>
        </a>
    </nav>
</aside>
