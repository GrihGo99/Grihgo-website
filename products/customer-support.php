<?php
$page_title = 'Customer Support & Helpdesk System | GrihGO Digital & Tech';
$meta_desc = 'Multi-channel customer ticketing, SLA management, canned macro replies, and customer satisfaction surveys in one collaborative helpdesk.';
$current_page = 'product_support';
$base_url = '../';
include '../includes/header.php';
?>

<!-- Product Hero -->
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="../index.php">Home</a>
            <span class="breadcrumb-separator">/</span>
            <a href="../products.php">Products</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Customer Support System</span>
        </nav>
        <div class="section-tag" style="background: #fae8ff; color: #701a75; border-color: rgba(112, 26, 117, 0.3);">
            Helpdesk & Service
        </div>
        <h1 style="margin-bottom: 16px;">Customer Support & <span class="gradient-text">Helpdesk Suite</span></h1>
        <p class="section-subtitle" style="max-width: 760px; margin-left: 0;">
            Deliver world-class post-sale support with a unified ticketing platform featuring automated priority classification, SLA timers, and team performance metrics.
        </p>
        <div class="btn-group" style="margin-top: 24px;">
            <a href="../appointment.php?service=customer-support" class="btn btn-primary btn-lg">Request Helpdesk Demo</a>
            <a href="../contact.php" class="btn btn-outline-primary btn-lg">Talk to Support Specialist</a>
        </div>
    </div>
</section>

<!-- Overview & UI Preview -->
<section class="section" id="product-overview">
    <div class="container">
        <div class="benefits-split-grid">
            <div>
                <span class="section-tag">Rapid Issue Resolution</span>
                <h2 class="section-title">Zero Unresolved Tickets. <span class="gradient-text">Happy Long-Term Clients.</span></h2>
                <p>
                    When client technical issues are handled via disjointed email chains and personal phone calls, unresolved bugs linger for days, SLAs are breached, and customer retention plummets.
                </p>
                <p>
                    GrihGO Customer Support Helpdesk converts every inbound email, WhatsApp message, and portal submission into a tracked, prioritized ticket assigned to the right technician with automated escalation alerts.
                </p>
                <div class="benefits-list" style="margin-top: 24px;">
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Automated SLA Timers</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Ensure first-response within 15 minutes and escalation alerts if resolution delays occur.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Canned Quick Macros</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Empower support agents with pre-written, verified answers to common technical queries.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Customer Satisfaction Ratings (CSAT)</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Automatically request 1-click star feedback when a ticket status moves to Resolved.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UI Preview Box -->
            <div class="form-card" style="padding: 28px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <strong>Support Ticket Queue</strong>
                    <span class="badge-dot green"></span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>#TK-4091 • Thermal Printer Driver Setup</span>
                            <span style="color: #ef4444; font-weight: 800;">High Priority • Assigned</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Client: Sharma Retail • SLA: 24m Remaining</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>#TK-4089 • WhatsApp Webhook Sync Check</span>
                            <span style="color: var(--secondary-green); font-weight: 800;">Resolved</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Client: Metropolis Health • CSAT: 5 Stars Received</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>#TK-4084 • GSTR-3B Tax Formula Query</span>
                            <span style="color: var(--primary-blue); font-weight: 800;">Waiting for Client</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Client: Apex Industrial • Solution note dispatched</span>
                    </div>
                </div>
                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light);">
                    <a href="../appointment.php?service=customer-support" class="btn btn-primary w-100">Schedule Helpdesk Demo &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="section section-dark" id="features">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Key Features</span>
            <h2 class="section-title">Engineered For Responsive <span class="gradient-text">Customer Care</span></h2>
            <p class="section-subtitle">Scalable tools for dedicated support and client operations teams.</p>
        </div>

        <div class="services-grid-3">
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(2, 86, 196, 0.3);">🎟️</div>
                <h3 style="color: #fff;">Omnichannel Ticketing</h3>
                <p style="color: var(--text-light-muted);">Convert emails, web forms, and WhatsApp inquiries into organized, trackable ticket records automatically.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(16, 185, 129, 0.25);">⏱️</div>
                <h3 style="color: #fff;">SLA Policies & Escalation</h3>
                <p style="color: var(--text-light-muted);">Define target response and resolution times based on client tiers and ticket severity levels.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(0, 153, 255, 0.25);">📚</div>
                <h3 style="color: #fff;">Knowledge Base Portal</h3>
                <p style="color: var(--text-light-muted);">Publish customer-facing help articles, software manuals, and video walkthroughs to encourage self-resolution.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section class="section">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Elevate Your Customer Experience with GrihGO Support</h2>
            <p>Schedule a live demonstration to see how our helpdesk streamlines ticket assignment and SLA tracking.</p>
            <div class="btn-group">
                <a href="../appointment.php?service=customer-support" class="btn btn-primary btn-lg">Request Live Demo</a>
                <a href="../contact.php" class="btn btn-outline-white btn-lg">Talk to Product Specialist</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
