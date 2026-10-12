<?php
$page_title = 'Business Software & SaaS Platforms | GrihGO Digital & Tech';
$meta_desc = 'Explore our 14 ready-to-deploy enterprise software products including GrihGO WhatsApp CRM, GST Billing, Multi-branch ERP, Hospital, School and Real Estate management suites.';
$current_page = 'products';
$base_url = './';
include 'includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Products</span>
        </nav>
        <div class="section-tag">Business Software & SaaS</div>
        <h1 style="margin-bottom: 16px;">Enterprise Ready <span class="gradient-text">Software Platforms</span></h1>
        <p class="section-subtitle" style="max-width: 760px; margin-left: 0;">
            Accelerate operational efficiency with 14 modular, customizable software platforms engineered for Indian and global enterprise requirements.
        </p>
    </div>
</section>

<!-- Product Catalog -->
<section class="section" id="products-catalog">
    <div class="container">
        <!-- Interactive Category Filters -->
        <div class="products-filter-bar" role="tablist" aria-label="Product Categories">
            <button type="button" class="filter-btn active" data-filter="all">All 14 Systems</button>
            <button type="button" class="filter-btn" data-filter="crm">CRM & Communication</button>
            <button type="button" class="filter-btn" data-filter="finance">Finance & ERP</button>
            <button type="button" class="filter-btn" data-filter="sector">Industry Specific</button>
            <button type="button" class="filter-btn" data-filter="ops">Operations & Team</button>
        </div>

        <div class="products-grid">
            <!-- 1. GrihGO WhatsApp CRM -->
            <div class="product-card" data-category="crm">
                <div class="product-card-top">
                    <div class="product-icon" style="background: var(--secondary-green-light); color: var(--secondary-green);">💬</div>
                    <span class="product-pill" style="background: rgba(16, 185, 129, 0.15); color: var(--secondary-green); font-weight: 700;">Flagship SaaS</span>
                </div>
                <h3 class="product-card-title">GrihGO WhatsApp CRM</h3>
                <p class="product-card-desc">
                    Centralized multi-agent WhatsApp team inbox, automated lead routing, quick reply templates, and campaign broadcasting on your official business number.
                </p>
                <div class="product-card-actions">
                    <a href="products/whatsapp-crm.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=whatsapp-crm" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 2. Billing Software -->
            <div class="product-card" data-category="finance">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #fef3c7; color: #d97706;">🧾</div>
                    <span class="product-pill">GST Ready</span>
                </div>
                <h3 class="product-card-title">GST Billing Software</h3>
                <p class="product-card-desc">
                    Instant 10-second tax invoicing, HSN/SAC code tracking, automated inventory deduction, expense logging, and one-click GSTR reporting.
                </p>
                <div class="product-card-actions">
                    <a href="products/billing-software.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=billing-software" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 3. Enterprise CRM -->
            <div class="product-card" data-category="crm">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #e0f2fe; color: #0284c7;">📊</div>
                    <span class="product-pill">Sales Pipeline</span>
                </div>
                <h3 class="product-card-title">Enterprise CRM</h3>
                <p class="product-card-desc">
                    Comprehensive lead lifecycle management, visual sales deal pipelines, automated task assignments, call logs, and customer interaction histories.
                </p>
                <div class="product-card-actions">
                    <a href="products/crm.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=crm" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 4. Inventory Management -->
            <div class="product-card" data-category="finance">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #f1f5f9; color: #475569;">📦</div>
                    <span class="product-pill">Supply Chain</span>
                </div>
                <h3 class="product-card-title">Inventory Management</h3>
                <p class="product-card-desc">
                    Multi-warehouse stock tracking, barcode and QR scanning, automated low-stock reorder thresholds, supplier purchase orders, and stock audit trails.
                </p>
                <div class="product-card-actions">
                    <a href="products/inventory.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=inventory" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 5. Integrated ERP -->
            <div class="product-card" data-category="finance">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #ede9fe; color: #7c3aed;">🏢</div>
                    <span class="product-pill">Core Enterprise</span>
                </div>
                <h3 class="product-card-title">Enterprise ERP</h3>
                <p class="product-card-desc">
                    Unified enterprise resource planning connecting sales, inventory, accounting, purchasing, and manufacturing into one synchronized dashboard.
                </p>
                <div class="product-card-actions">
                    <a href="products/erp.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=erp" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 6. HR & Payroll -->
            <div class="product-card" data-category="ops">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #ffe4e6; color: #e11d48;">👥</div>
                    <span class="product-pill">Human Capital</span>
                </div>
                <h3 class="product-card-title">HR & Payroll Management</h3>
                <p class="product-card-desc">
                    Biometric attendance sync, leave application approvals, automated salary slip generation, PF/ESI compliance, and employee document archives.
                </p>
                <div class="product-card-actions">
                    <a href="products/hr-payroll.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=hr-payroll" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 7. School Management -->
            <div class="product-card" data-category="sector">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #e0e7ff; color: #4338ca;">🎓</div>
                    <span class="product-pill">Education</span>
                </div>
                <h3 class="product-card-title">School Management System</h3>
                <p class="product-card-desc">
                    Student admission workflows, fee collection & receipts, timetable scheduling, exam grade reporting, and parent communication SMS portals.
                </p>
                <div class="product-card-actions">
                    <a href="products/school-management.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=school-management" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 8. Hospital Management -->
            <div class="product-card" data-category="sector">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #dcfce7; color: #15803d;">🏥</div>
                    <span class="product-pill">Healthcare</span>
                </div>
                <h3 class="product-card-title">Hospital Management (HIS)</h3>
                <p class="product-card-desc">
                    OPD/IPD patient registration, digital electronic medical records (EMR), doctor queue management, bed allocation, and in-house pharmacy billing.
                </p>
                <div class="product-card-actions">
                    <a href="products/hospital-management.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=hospital-management" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 9. Restaurant Management -->
            <div class="product-card" data-category="sector">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #ffedd5; color: #c2410c;">🍽️</div>
                    <span class="product-pill">Hospitality</span>
                </div>
                <h3 class="product-card-title">Restaurant POS & Management</h3>
                <p class="product-card-desc">
                    Touchscreen billing POS, digital kitchen order tickets (KOT), table layout management, recipe ingredient inventory, and online delivery sync.
                </p>
                <div class="product-card-actions">
                    <a href="products/restaurant-management.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=restaurant-management" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 10. Real Estate CRM -->
            <div class="product-card" data-category="sector">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #ccfbf1; color: #0f766e;">🏡</div>
                    <span class="product-pill">Real Estate</span>
                </div>
                <h3 class="product-card-title">Real Estate CRM</h3>
                <p class="product-card-desc">
                    Property inventory management, site visit scheduling, channel partner commission tracking, lead auto-assignment, and booking confirmation workflows.
                </p>
                <div class="product-card-actions">
                    <a href="products/real-estate-crm.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=real-estate-crm" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 11. Lead Management -->
            <div class="product-card" data-category="crm">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #fef9c3; color: #a16207;">🎯</div>
                    <span class="product-pill">Acquisition</span>
                </div>
                <h3 class="product-card-title">Lead Management System</h3>
                <p class="product-card-desc">
                    Multi-channel lead ingestion from website forms, Google Ads, Meta Ads, and phone calls into a single deduplicated qualification pipeline.
                </p>
                <div class="product-card-actions">
                    <a href="products/lead-management.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=lead-management" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 12. Project Management -->
            <div class="product-card" data-category="ops">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #e0f2fe; color: #0369a1;">📋</div>
                    <span class="product-pill">Delivery</span>
                </div>
                <h3 class="product-card-title">Project Management System</h3>
                <p class="product-card-desc">
                    Milestone tracking, team task boards, client review stages, time logging, document attachments, and project budget monitoring.
                </p>
                <div class="product-card-actions">
                    <a href="products/project-management.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=project-management" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 13. Customer Support System -->
            <div class="product-card" data-category="ops">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #fae8ff; color: #86198f;">🎧</div>
                    <span class="product-pill">Helpdesk</span>
                </div>
                <h3 class="product-card-title">Customer Support Helpdesk</h3>
                <p class="product-card-desc">
                    Ticketing portal with SLA tracking, priority classification, automated response emails, canned macros, and customer satisfaction surveys.
                </p>
                <div class="product-card-actions">
                    <a href="products/customer-support.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=customer-support" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>

            <!-- 14. Appointment Management -->
            <div class="product-card" data-category="ops">
                <div class="product-card-top">
                    <div class="product-icon" style="background: #ecfdf5; color: #047857;">📅</div>
                    <span class="product-pill">Scheduling</span>
                </div>
                <h3 class="product-card-title">Appointment Management</h3>
                <p class="product-card-desc">
                    Self-service online booking calendar, staff availability slots, automated SMS and WhatsApp reminders, and calendar synchronization.
                </p>
                <div class="product-card-actions">
                    <a href="products/appointment-management.php" class="btn btn-sm btn-outline-primary">View Product</a>
                    <a href="appointment.php?service=appointment-management" class="btn btn-sm btn-primary">Request Demo</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section class="section" style="padding-top: 0;">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Ready to Deploy an Enterprise Platform?</h2>
            <p>
                Request a personalized live walkthrough of any GrihGO software system configured with your sample data.
            </p>
            <div class="btn-group">
                <a href="appointment.php" class="btn btn-primary btn-lg">Request Live Demo</a>
                <a href="contact.php" class="btn btn-outline-white btn-lg">Talk to Product Specialist</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
