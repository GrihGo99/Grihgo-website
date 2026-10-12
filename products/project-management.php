<?php
$page_title = 'Project Management System | GrihGO Digital & Tech';
$meta_desc = 'Agile task boards, milestone tracking, client review stages, and time logging engineered for service agencies and technical teams.';
$current_page = 'product_pm';
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
            <span class="breadcrumb-current">Project Management</span>
        </nav>
        <div class="section-tag" style="background: #e0f2fe; color: #075985; border-color: rgba(7, 89, 133, 0.3);">
            Delivery & Collaboration
        </div>
        <h1 style="margin-bottom: 16px;">Collaborative <span class="gradient-text">Project Management</span></h1>
        <p class="section-subtitle" style="max-width: 760px; margin-left: 0;">
            Keep cross-functional technical teams and client stakeholders aligned with milestone roadmaps, visual Kanban boards, and transparent task tracking.
        </p>
        <div class="btn-group" style="margin-top: 24px;">
            <a href="../appointment.php?service=project-management" class="btn btn-primary btn-lg">Request Project Suite Demo</a>
            <a href="../contact.php" class="btn btn-outline-primary btn-lg">Talk to Delivery Specialist</a>
        </div>
    </div>
</section>

<!-- Overview & UI Preview -->
<section class="section" id="product-overview">
    <div class="container">
        <div class="benefits-split-grid">
            <div>
                <span class="section-tag">Flawless Execution</span>
                <h2 class="section-title">Zero Scope Creep. <span class="gradient-text">On-Time Milestones.</span></h2>
                <p>
                    Technical and creative projects derail when client feedback is scattered across chat apps, task deadlines are ambiguous, and developers work without documented priorities.
                </p>
                <p>
                    GrihGO Project Management unites internal developer task boards with external client progress portals, ensuring both parties have a single transparent view of delivery milestones.
                </p>
                <div class="benefits-list" style="margin-top: 24px;">
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Visual Kanban Task Boards</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Organize tasks by Backlog, In Progress, Code Review, Testing, and Deployed stages.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Milestone Billing Linkage</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Automatically generate invoice approvals when designated delivery stages are signed off.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Asset & Document Repository</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Store wireframes, technical specs, and contract files directly under relevant project cards.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UI Preview Box -->
            <div class="form-card" style="padding: 28px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <strong>Active Project Sprint</strong>
                    <span class="badge-dot blue"></span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Sprint 4 • Healthcare Portal EMR</span>
                            <span style="color: var(--secondary-green); font-weight: 800;">85% Completed</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Testing Stage: Prescription PDF generation verified</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Sprint 2 • Logistics Billing Engine</span>
                            <span style="color: var(--primary-blue); font-weight: 800;">60% Completed</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Development Stage: GST calculation and barcode scanner sync</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Sprint 1 • Realty CRM Lead Webhooks</span>
                            <span style="color: #b45309; font-weight: 800;">Planning Stage</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Architecture Review: Meta Conversions API mapping</span>
                    </div>
                </div>
                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light);">
                    <a href="../appointment.php?service=project-management" class="btn btn-primary w-100">Schedule PM Demo Session &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="section section-dark" id="features">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Core Features</span>
            <h2 class="section-title">Engineered For Clear <span class="gradient-text">Delivery Governance</span></h2>
            <p class="section-subtitle">Scalable tools for technical agencies, software houses, and enterprises.</p>
        </div>

        <div class="services-grid-3">
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(2, 86, 196, 0.3);">📋</div>
                <h3 style="color: #fff;">Milestone Roadmaps</h3>
                <p style="color: var(--text-light-muted);">Track project phases from initial discovery through design, code development, and deployment launch.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(16, 185, 129, 0.25);">⏱️</div>
                <h3 style="color: #fff;">Time Logging & Budgets</h3>
                <p style="color: var(--text-light-muted);">Record billable and non-billable developer hours to ensure commercial contracts stay profitable.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(0, 153, 255, 0.25);">💬</div>
                <h3 style="color: #fff;">Client Review Approvals</h3>
                <p style="color: var(--text-light-muted);">Give clients restricted guest access to review deliverables, leave comments, and grant formal milestone approvals.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section class="section">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Streamline Your Project Delivery with GrihGO</h2>
            <p>Schedule a live demonstration to see how our project suite organizes engineering tasks and milestone sign-offs.</p>
            <div class="btn-group">
                <a href="../appointment.php?service=project-management" class="btn btn-primary btn-lg">Request Live Demo</a>
                <a href="../contact.php" class="btn btn-outline-white btn-lg">Talk to Product Specialist</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
