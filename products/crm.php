<?php
$page_title = 'Enterprise CRM Platform | GrihGO Digital & Tech';
$meta_desc = 'End-to-end customer relationship management, visual sales pipelines, lead scoring, and automated deal tracking for high-performance sales teams.';
$current_page = 'product_crm';
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
            <span class="breadcrumb-current">Enterprise CRM</span>
        </nav>
        <div class="section-tag" style="background: #e0f2fe; color: #0369a1; border-color: rgba(3, 105, 161, 0.3);">
            Sales & Pipelines
        </div>
        <h1 style="margin-bottom: 16px;">Enterprise <span class="gradient-text">CRM Platform</span></h1>
        <p class="section-subtitle" style="max-width: 760px; margin-left: 0;">
            Unify your leads, accounts, sales pipelines, and customer communications in a clean, visual interface engineered to shorten sales cycles.
        </p>
        <div class="btn-group" style="margin-top: 24px;">
            <a href="../appointment.php?service=crm" class="btn btn-primary btn-lg">Request CRM Demo</a>
            <a href="../contact.php" class="btn btn-outline-primary btn-lg">Talk to Sales Specialist</a>
        </div>
    </div>
</section>

<!-- Overview & UI Preview -->
<section class="section" id="product-overview">
    <div class="container">
        <div class="benefits-split-grid">
            <div>
                <span class="section-tag">Sales Acceleration</span>
                <h2 class="section-title">Complete Visibility Across <span class="gradient-text">Every Deal</span></h2>
                <p>
                    When sales representatives track leads across disparate spreadsheets and personal note apps, opportunities fall through the cracks, forecasts become guesswork, and customer handoffs suffer.
                </p>
                <p>
                    GrihGO Enterprise CRM provides a centralized sales cockpit where reps manage visual deal stages, schedule follow-up reminders, record call summaries, and track client documents in one synchronized hub.
                </p>
                <div class="benefits-list" style="margin-top: 24px;">
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Kanban Deal Stages</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Drag-and-drop deals across New, Qualified, Proposal Sent, Negotiation, and Won stages.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Automated Task & Call Prompts</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Receive daily smart notifications for pending client follow-ups and quote expirations.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Accurate Revenue Forecasting</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Predict month-end closings based on weighted historical conversion probabilities.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UI Preview Box -->
            <div class="form-card" style="padding: 28px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <strong>Active Deals Pipeline</strong>
                    <span class="badge-dot green"></span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Apex Industrial (ERP Suite)</span>
                            <span style="color: var(--primary-blue);">₹ 4,50,000</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Stage: Proposal Review • Owner: Vikram S.</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Metropolis Clinic (Hospital EMR)</span>
                            <span style="color: var(--secondary-green);">₹ 2,80,000</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Stage: Contract Negotiation • Owner: Ananya M.</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Greenfield International School</span>
                            <span style="color: var(--text-primary);">₹ 3,20,000</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Stage: Technical Walkthrough • Owner: Priya S.</span>
                    </div>
                </div>
                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light);">
                    <a href="../appointment.php?service=crm" class="btn btn-primary w-100">Schedule CRM Walkthrough &rarr;</a>
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
            <h2 class="section-title">Built For Modern <span class="gradient-text">Sales Organizations</span></h2>
            <p class="section-subtitle">Scalable tools designed to turn prospects into loyal commercial accounts.</p>
        </div>

        <div class="services-grid-3">
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(2, 86, 196, 0.3);">🎯</div>
                <h3 style="color: #fff;">Lead Capture & Scoring</h3>
                <p style="color: var(--text-light-muted);">Auto-ingest leads from websites, ads, and incoming calls with rule-based qualification scoring.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(16, 185, 129, 0.25);">📞</div>
                <h3 style="color: #fff;">Activity & Call Logs</h3>
                <p style="color: var(--text-light-muted);">Log meeting summaries, WhatsApp interactions, and email conversations under a single customer record.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(0, 153, 255, 0.25);">📊</div>
                <h3 style="color: #fff;">Rep Performance Reports</h3>
                <p style="color: var(--text-light-muted);">Measure team closing rates, average deal cycle length, and pipeline velocity with executive dashboards.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section class="section">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Transform Your Sales Velocity with GrihGO CRM</h2>
            <p>Schedule a live walkthrough configured with your organization's sales stages and customer types.</p>
            <div class="btn-group">
                <a href="../appointment.php?service=crm" class="btn btn-primary btn-lg">Request Live Demo</a>
                <a href="../contact.php" class="btn btn-outline-white btn-lg">Talk to Product Specialist</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
