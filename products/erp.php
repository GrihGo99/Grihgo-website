<?php
$page_title = 'Integrated Enterprise ERP | GrihGO Digital & Tech';
$meta_desc = 'Comprehensive ERP software uniting inventory, procurement, sales orders, accounts, and operations into one scalable database.';
$current_page = 'product_erp';
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
            <span class="breadcrumb-current">Enterprise ERP</span>
        </nav>
        <div class="section-tag" style="background: #ede9fe; color: #6d28d9; border-color: rgba(109, 40, 217, 0.3);">
            Enterprise Core
        </div>
        <h1 style="margin-bottom: 16px;">Integrated Enterprise <span class="gradient-text">ERP Platform</span></h1>
        <p class="section-subtitle" style="max-width: 760px; margin-left: 0;">
            Unify procurement, manufacturing, multi-warehouse stock, sales orders, and financial ledgers into a single, high-speed enterprise database.
        </p>
        <div class="btn-group" style="margin-top: 24px;">
            <a href="../appointment.php?service=erp" class="btn btn-primary btn-lg">Request ERP Walkthrough</a>
            <a href="../contact.php" class="btn btn-outline-primary btn-lg">Consult ERP Architects</a>
        </div>
    </div>
</section>

<!-- Overview & UI Preview -->
<section class="section" id="product-overview">
    <div class="container">
        <div class="benefits-split-grid">
            <div>
                <span class="section-tag">Total Operational Control</span>
                <h2 class="section-title">Break Down Departmental <span class="gradient-text">Data Silos</span></h2>
                <p>
                    When procurement, sales, inventory, and finance use separate spreadsheets, management suffers from delayed decision-making, accounting reconciliations take days, and customer order statuses are obscured.
                </p>
                <p>
                    GrihGO ERP provides a synchronized operational engine where a sales order automatically reserves warehouse stock, triggers procurement if below threshold, updates financial ledgers, and generates customer dispatch challans.
                </p>
                <div class="benefits-list" style="margin-top: 24px;">
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Single Source of Truth</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Eliminate manual copy-pasting between separate billing and warehouse files.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Customizable Approval Hierarchies</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Route purchase orders and discount overrides to designated managers automatically.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Multi-Company & Branch Support</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Manage multiple legal entities or geographic branches with consolidated reporting.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UI Preview Box -->
            <div class="form-card" style="padding: 28px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <strong>Enterprise KPI Overview</strong>
                    <span class="badge-dot blue"></span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 12px;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">Monthly Revenue</span>
                        <strong style="font-size: 1.2rem; color: var(--primary-blue);">₹ 48.6 Lakhs</strong>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 12px;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">Inventory Valuation</span>
                        <strong style="font-size: 1.2rem; color: var(--secondary-green);">₹ 1.24 Crore</strong>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 12px;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">Pending Dispatches</span>
                        <strong style="font-size: 1.2rem; color: var(--text-primary);">38 Orders</strong>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 12px;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">Receivables (Due)</span>
                        <strong style="font-size: 1.2rem; color: #f59e0b);">₹ 9.15 Lakhs</strong>
                    </div>
                </div>
                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-light);">
                    <a href="../appointment.php?service=erp" class="btn btn-primary w-100">Schedule ERP Technical Scope &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="section section-dark" id="features">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Modular ERP Architecture</span>
            <h2 class="section-title">Built For Heavy <span class="gradient-text">Commercial Transactions</span></h2>
            <p class="section-subtitle">Scalable modules tailored to manufacturers, wholesalers, and services.</p>
        </div>

        <div class="services-grid-3">
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(2, 86, 196, 0.3);">💼</div>
                <h3 style="color: #fff;">Sales & Invoicing Core</h3>
                <p style="color: var(--text-light-muted);">Manage quotation generation, sales orders, proformas, automated GST tax billing, and credit limits.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(16, 185, 129, 0.25);">🏭</div>
                <h3 style="color: #fff;">Procurement & Production</h3>
                <p style="color: var(--text-light-muted);">Track purchase requisitions, supplier POs, raw material Bill of Materials (BOM), and finished goods costing.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(0, 153, 255, 0.25);">📊</div>
                <h3 style="color: #fff;">Financial Ledgers & Audit</h3>
                <p style="color: var(--text-light-muted);">Double-entry general ledgers, trial balance, automated bank reconciliations, and immutable audit logs.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section class="section">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Ready to Upgrade to GrihGO Enterprise ERP?</h2>
            <p>Schedule a discovery session with our senior enterprise architects to review migration timelines.</p>
            <div class="btn-group">
                <a href="../appointment.php?service=erp" class="btn btn-primary btn-lg">Request Live Demo</a>
                <a href="../contact.php" class="btn btn-outline-white btn-lg">Talk to Product Specialist</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
