<?php
/**
 * GrihGO Digital & Tech — Master Admin Dashboard
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Executive Dashboard';
$pageSection = 'dashboard';

// 1. Fetch Real Database Metrics
$totalClients = (int)$pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$activeProjects = (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status IN ('In Progress', 'Testing', 'Review')")->fetchColumn();
$totalAppointments = (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$pendingAppointments = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetchColumn();
$pendingTickets = (int)$pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status IN ('Open', 'In Progress', 'Waiting for Client')")->fetchColumn();

// Actual Revenue from Successful Payments
$revenueTotal = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status IN ('Success', 'Completed')")->fetchColumn();

// Pending Payment Balances from Invoices
$pendingRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM invoices WHERE status NOT IN ('Paid', 'Cancelled')")->fetchColumn();

// Lead Metrics
$totalLeads = (int)$pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$newLeads = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'New'")->fetchColumn();
$convertedLeads = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'Converted'")->fetchColumn();

// 2. Fetch Recent Activities & Records
// Recent Appointments
$recentAppointments = $pdo->query("
    SELECT a.*, c.company_name, c.contact_person 
    FROM appointments a 
    LEFT JOIN clients c ON a.client_id = c.id 
    ORDER BY a.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Recent Leads
$recentLeads = $pdo->query("
    SELECT * FROM leads 
    ORDER BY id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Active Projects
$recentProjects = $pdo->query("
    SELECT p.*, c.company_name 
    FROM projects p 
    LEFT JOIN clients c ON p.client_id = c.id 
    ORDER BY p.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Pending Tickets
$recentTickets = $pdo->query("
    SELECT t.*, c.company_name, c.contact_person 
    FROM support_tickets t 
    LEFT JOIN clients c ON t.client_id = c.id 
    ORDER BY t.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Recent Payments
$recentPayments = $pdo->query("
    SELECT p.*, c.company_name, i.invoice_number 
    FROM payments p 
    LEFT JOIN clients c ON p.client_id = c.id 
    LEFT JOIN invoices i ON p.invoice_id = i.id 
    ORDER BY p.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Recent Contact Messages
$recentInquiries = $pdo->query("
    SELECT * FROM contact_messages 
    ORDER BY id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Welcome Back, <?= htmlspecialchars($currentAdmin['full_name']) ?> 👋</h1>
        <p class="page-subtitle">Here is the real-time operational status across clients, delivery pipelines, and commercial accounts.</p>
    </div>
    <div class="page-actions">
        <a href="appointments.php" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line></svg>
            <span>Appointments</span>
        </a>
        <a href="leads.php" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle></svg>
            <span>Manage Leads</span>
        </a>
        <a href="invoices.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Create Invoice</span>
        </a>
    </div>
</div>

<!-- Key Performance Indicators (Section 8 & 51) -->
<div class="kpi-grid">
    <div class="kpi-card" style="--kpi-accent: #1e5ba8; --kpi-bg: rgba(30,91,168,0.1);">
        <div class="kpi-card-content">
            <span class="kpi-title">Total Clients</span>
            <span class="kpi-value"><?= number_format($totalClients) ?></span>
            <span class="kpi-subtext">Registered corporate accounts</span>
        </div>
        <div class="kpi-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
    </div>

    <div class="kpi-card" style="--kpi-accent: #0ea5e9; --kpi-bg: rgba(14,165,233,0.1);">
        <div class="kpi-card-content">
            <span class="kpi-title">Active Projects</span>
            <span class="kpi-value"><?= number_format($activeProjects) ?></span>
            <span class="kpi-subtext">In delivery pipeline</span>
        </div>
        <div class="kpi-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
        </div>
    </div>

    <div class="kpi-card" style="--kpi-accent: #f59e0b; --kpi-bg: rgba(245,158,11,0.1);">
        <div class="kpi-card-content">
            <span class="kpi-title">Appointments</span>
            <span class="kpi-value"><?= number_format($totalAppointments) ?></span>
            <span class="kpi-subtext"><?= $pendingAppointments ?> pending confirmation</span>
        </div>
        <div class="kpi-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        </div>
    </div>

    <div class="kpi-card" style="--kpi-accent: #ef4444; --kpi-bg: rgba(239,68,68,0.1);">
        <div class="kpi-card-content">
            <span class="kpi-title">Pending Tickets</span>
            <span class="kpi-value"><?= number_format($pendingTickets) ?></span>
            <span class="kpi-subtext">Awaiting client / team resolution</span>
        </div>
        <div class="kpi-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </div>
    </div>

    <div class="kpi-card" style="--kpi-accent: #10b981; --kpi-bg: rgba(16,185,129,0.1);">
        <div class="kpi-card-content">
            <span class="kpi-title">Total Revenue</span>
            <span class="kpi-value">₹<?= number_format($revenueTotal, 0) ?></span>
            <span class="kpi-subtext">Settled via successful payments</span>
        </div>
        <div class="kpi-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
        </div>
    </div>

    <div class="kpi-card" style="--kpi-accent: #8b5cf6; --kpi-bg: rgba(139,92,246,0.1);">
        <div class="kpi-card-content">
            <span class="kpi-title">Pending Balances</span>
            <span class="kpi-value">₹<?= number_format($pendingRevenue, 0) ?></span>
            <span class="kpi-subtext">Unsettled invoice receivables</span>
        </div>
        <div class="kpi-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line></svg>
        </div>
    </div>
</div>

<!-- Dashboard Grid (Section 9) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(480px, 1fr)); gap: 24px;">
    
    <!-- 1. Recent Appointments -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line></svg>
                <span>Recent Appointments</span>
            </h3>
            <a href="appointments.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Client / Prospect</th>
                        <th>Service</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentAppointments)): ?>
                        <tr><td colspan="5" class="empty-state" style="padding: 24px;">No appointments found.</td></tr>
                    <?php else: foreach ($recentAppointments as $app): 
                        $statusClass = match($app['status']) {
                            'Confirmed' => 'badge-success',
                            'Completed' => 'badge-primary',
                            'Cancelled' => 'badge-danger',
                            'Rescheduled' => 'badge-warning',
                            default => 'badge-neutral'
                        };
                    ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($app['full_name']) ?></strong>
                                <?php if (!empty($app['business_name'])): ?>
                                    <div style="font-size: 11.5px; color: var(--admin-text-muted);"><?= htmlspecialchars($app['business_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($app['service']) ?></td>
                            <td>
                                <div><?= htmlspecialchars($app['preferred_date'] ?? 'N/A') ?></div>
                                <div style="font-size: 11px; color: var(--admin-text-muted);"><?= htmlspecialchars($app['preferred_time'] ?? '') ?></div>
                            </td>
                            <td><span class="badge <?= $statusClass ?>"><span class="badge-dot"></span><?= htmlspecialchars($app['status']) ?></span></td>
                            <td><a href="appointment-details.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-outline">Review</a></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Recent Leads -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle></svg>
                <span>Recent Leads & Inquiries</span>
            </h3>
            <a href="leads.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Lead Name</th>
                        <th>Source</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentLeads)): ?>
                        <tr><td colspan="5" class="empty-state" style="padding: 24px;">No leads recorded yet.</td></tr>
                    <?php else: foreach ($recentLeads as $lead): 
                        $leadStatusClass = match($lead['status']) {
                            'Converted' => 'badge-success',
                            'Proposal Sent', 'Negotiation' => 'badge-purple',
                            'Contacted', 'Interested' => 'badge-primary',
                            'Lost' => 'badge-danger',
                            default => 'badge-warning'
                        };
                    ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($lead['name']) ?></strong>
                                <?php if (!empty($lead['business_name'])): ?>
                                    <div style="font-size: 11.5px; color: var(--admin-text-muted);"><?= htmlspecialchars($lead['business_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-neutral"><?= htmlspecialchars($lead['source'] ?? 'Website') ?></span></td>
                            <td><?= htmlspecialchars($lead['service'] ?? 'General') ?></td>
                            <td><span class="badge <?= $leadStatusClass ?>"><span class="badge-dot"></span><?= htmlspecialchars($lead['status']) ?></span></td>
                            <td><a href="lead-details.php?id=<?= $lead['id'] ?>" class="btn btn-sm btn-outline">Pipeline</a></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Active Delivery Projects -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline></svg>
                <span>Active Delivery Projects</span>
            </h3>
            <a href="projects.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Project & Client</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentProjects)): ?>
                        <tr><td colspan="4" class="empty-state" style="padding: 24px;">No projects currently created.</td></tr>
                    <?php else: foreach ($recentProjects as $p): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($p['name']) ?></strong>
                                <div style="font-size: 11.5px; color: var(--admin-text-muted);"><?= htmlspecialchars($p['company_name'] ?? 'Client ID #' . $p['client_id']) ?></div>
                            </td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($p['status']) ?></span></td>
                            <td style="min-width: 140px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                        <div style="width: <?= min(100, max(0, (int)$p['progress'])) ?>%; height: 100%; background: var(--admin-primary); border-radius: 3px;"></div>
                                    </div>
                                    <span style="font-size: 11.5px; font-weight: 600;"><?= (int)$p['progress'] ?>%</span>
                                </div>
                            </td>
                            <td><a href="project-details.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Track</a></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. Support Desk Requests -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path></svg>
                <span>Pending Support Tickets</span>
            </h3>
            <a href="support-tickets.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Client</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTickets)): ?>
                        <tr><td colspan="5" class="empty-state" style="padding: 24px;">No support tickets awaiting response.</td></tr>
                    <?php else: foreach ($recentTickets as $t): 
                        $ticketStatusClass = match($t['status']) {
                            'Resolved', 'Closed' => 'badge-success',
                            'Waiting for Client' => 'badge-warning',
                            default => 'badge-danger'
                        };
                    ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($t['ticket_number']) ?></strong>
                                <div style="font-size: 11.5px; color: var(--admin-text-muted); max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars($t['subject']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($t['company_name'] ?? 'Client #' . $t['client_id']) ?></td>
                            <td>
                                <span class="badge <?= $t['priority'] === 'High' ? 'badge-danger' : 'badge-neutral' ?>">
                                    <?= htmlspecialchars($t['priority']) ?>
                                </span>
                            </td>
                            <td><span class="badge <?= $ticketStatusClass ?>"><span class="badge-dot"></span><?= htmlspecialchars($t['status']) ?></span></td>
                            <td><a href="ticket-details.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline">Reply</a></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. Recent Settlement Payments -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                <span>Recent Payments</span>
            </h3>
            <a href="payments.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Client</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentPayments)): ?>
                        <tr><td colspan="5" class="empty-state" style="padding: 24px;">No payments recorded yet.</td></tr>
                    <?php else: foreach ($recentPayments as $pay): ?>
                        <tr>
                            <td>
                                <code><?= htmlspecialchars($pay['transaction_id']) ?></code>
                                <div style="font-size: 11px; color: var(--admin-text-muted);"><?= date('d M Y, h:i A', strtotime($pay['payment_date'] ?? $pay['created_at'])) ?></div>
                            </td>
                            <td><?= htmlspecialchars($pay['company_name'] ?? 'Client #' . $pay['client_id']) ?></td>
                            <td><strong>₹<?= number_format((float)$pay['amount'], 2) ?></strong></td>
                            <td style="font-size: 12px;"><?= htmlspecialchars($pay['payment_method']) ?></td>
                            <td><span class="badge badge-success"><span class="badge-dot"></span><?= htmlspecialchars($pay['status']) ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 6. Recent Contact Messages -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <span>Recent Website Inquiries</span>
            </h3>
            <a href="contact-enquiries.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentInquiries)): ?>
                        <tr><td colspan="4" class="empty-state" style="padding: 24px;">No contact inquiries recorded.</td></tr>
                    <?php else: foreach ($recentInquiries as $msg): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($msg['name']) ?></strong>
                                <div style="font-size: 11px; color: var(--admin-text-muted);"><?= htmlspecialchars($msg['email']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($msg['service'] ?? 'General Inquiry') ?></td>
                            <td>
                                <span class="badge <?= $msg['status'] === 'New' ? 'badge-warning' : 'badge-neutral' ?>">
                                    <?= htmlspecialchars($msg['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($msg['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
