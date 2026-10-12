<?php
/**
 * GrihGO Digital & Tech — Admin Layout Sidebar
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

$currentPage = basename($_SERVER['PHP_SELF']);
$isSuperAdmin = ($currentAdmin['role'] ?? '') === 'super_admin';
$isAdminOrAbove = in_array($currentAdmin['role'] ?? '', ['super_admin', 'admin'], true);

// Fetch pending counts for sidebar badges
$pendingApptsCount = 0;
$newLeadsCount = 0;
$openTicketsCount = 0;
try {
    $pendingApptsCount = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetchColumn();
    $newLeadsCount = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'New'")->fetchColumn();
    $openTicketsCount = (int)$pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'Open'")->fetchColumn();
} catch (Exception $e) {}
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
        <a href="dashboard.php" class="admin-brand">
            <span class="admin-brand-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </span>
            <span class="admin-brand-text">GrihGO</span>
            <span class="admin-brand-badge">Admin</span>
        </a>
    </div>

    <nav class="admin-sidebar-nav">
        <div class="nav-section-title">Core</div>
        <a href="dashboard.php" class="nav-item <?= in_array($currentPage, ['dashboard.php', 'index.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Dashboard</span>
        </a>

        <div class="nav-section-title">CRM & Customers</div>
        <a href="leads.php" class="nav-item <?= in_array($currentPage, ['leads.php', 'lead-details.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
            </svg>
            <span>Lead CRM</span>
            <?php if ($newLeadsCount > 0): ?>
                <span class="nav-badge"><?= $newLeadsCount ?></span>
            <?php endif; ?>
        </a>

        <a href="clients.php" class="nav-item <?= in_array($currentPage, ['clients.php', 'client-details.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>Clients</span>
        </a>

        <a href="appointments.php" class="nav-item <?= in_array($currentPage, ['appointments.php', 'appointment-details.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>Appointments</span>
            <?php if ($pendingApptsCount > 0): ?>
                <span class="nav-badge"><?= $pendingApptsCount ?></span>
            <?php endif; ?>
        </a>

        <a href="contact-enquiries.php" class="nav-item <?= $currentPage === 'contact-enquiries.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>Contact Inquiries</span>
        </a>

        <div class="nav-section-title">Delivery & Operations</div>
        <a href="projects.php" class="nav-item <?= in_array($currentPage, ['projects.php', 'project-details.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                <polyline points="2 17 12 22 22 17"></polyline>
                <polyline points="2 12 12 17 22 12"></polyline>
            </svg>
            <span>Projects</span>
        </a>

        <a href="project-tasks.php" class="nav-item <?= $currentPage === 'project-tasks.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            <span>Project Tasks</span>
        </a>

        <a href="support-tickets.php" class="nav-item <?= in_array($currentPage, ['support-tickets.php', 'ticket-details.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <span>Support Desk</span>
            <?php if ($openTicketsCount > 0): ?>
                <span class="nav-badge"><?= $openTicketsCount ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-section-title">Billing & Accounts</div>
        <a href="invoices.php" class="nav-item <?= in_array($currentPage, ['invoices.php', 'invoice-details.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            <span>Invoices</span>
        </a>

        <a href="payments.php" class="nav-item <?= $currentPage === 'payments.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                <line x1="2" y1="10" x2="22" y2="10"></line>
            </svg>
            <span>Payments</span>
        </a>

        <div class="nav-section-title">Catalog & Marketing</div>
        <a href="services.php" class="nav-item <?= in_array($currentPage, ['services.php', 'service-edit.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
            </svg>
            <span>Services</span>
        </a>

        <a href="products.php" class="nav-item <?= in_array($currentPage, ['products.php', 'product-edit.php']) ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
            <span>Products</span>
        </a>

        <a href="portfolio.php" class="nav-item <?= $currentPage === 'portfolio.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
            <span>Portfolio</span>
        </a>

        <a href="testimonials.php" class="nav-item <?= $currentPage === 'testimonials.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            <span>Testimonials</span>
        </a>

        <a href="blog.php" class="nav-item <?= $currentPage === 'blog.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            <span>Blog</span>
        </a>

        <div class="nav-section-title">Administration</div>
        <a href="notifications.php" class="nav-item <?= $currentPage === 'notifications.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span>Notifications</span>
        </a>

        <a href="settings.php" class="nav-item <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            </svg>
            <span>Website Settings</span>
        </a>

        <?php if ($isAdminOrAbove): ?>
            <a href="admin-users.php" class="nav-item <?= $currentPage === 'admin-users.php' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                    <line x1="12" y1="19" x2="12" y2="23"></line>
                    <line x1="8" y1="23" x2="16" y2="23"></line>
                </svg>
                <span>Admin Users</span>
            </a>
        <?php endif; ?>

        <a href="logout.php" class="nav-item" data-confirm="Are you sure you want to log out of the Admin Console?">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Log Out</span>
        </a>
    </nav>

    <div class="admin-sidebar-footer">
        <div style="font-size: 11px; color: var(--admin-text-subtle);">
            GrihGO Console v4.0.0
        </div>
    </div>
</aside>
