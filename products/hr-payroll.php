<?php
$page_title = 'HR Management & Payroll System | GrihGO Digital & Tech';
$meta_desc = 'Automate biometric attendance, leave approvals, compliant salary slip calculation, and employee records with GrihGO HR & Payroll.';
$current_page = 'product_hr';
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
            <span class="breadcrumb-current">HR Management & Payroll</span>
        </nav>
        <div class="section-tag" style="background: #ffe4e6; color: #be123c; border-color: rgba(190, 18, 60, 0.3);">
            Human Resources
        </div>
        <h1 style="margin-bottom: 16px;">HR Management & <span class="gradient-text">Automated Payroll</span></h1>
        <p class="section-subtitle" style="max-width: 760px; margin-left: 0;">
            Simplify workforce management, attendance tracking, statutory deductions, and automated monthly salary disbursement with a clean self-service portal.
        </p>
        <div class="btn-group" style="margin-top: 24px;">
            <a href="../appointment.php?service=hr-payroll" class="btn btn-primary btn-lg">Request HR & Payroll Demo</a>
            <a href="../contact.php" class="btn btn-outline-primary btn-lg">Talk to HR Tech Specialist</a>
        </div>
    </div>
</section>

<!-- Overview & UI Preview -->
<section class="section" id="product-overview">
    <div class="container">
        <div class="benefits-split-grid">
            <div>
                <span class="section-tag">Frictionless People Ops</span>
                <h2 class="section-title">Automate Payroll In <span class="gradient-text">Minutes, Not Days</span></h2>
                <p>
                    Manual salary calculations, tracking leave via email chains, and reconciling biometric attendance machines take valuable HR hours away from strategic talent development.
                </p>
                <p>
                    GrihGO HR & Payroll automates monthly pay runs, calculates statutory PF, ESI, TDS, and Professional Tax deductions automatically, and delivers encrypted PDF salary slips to employees via email and WhatsApp.
                </p>
                <div class="benefits-list" style="margin-top: 24px;">
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Biometric & Mobile Clock-In</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Sync biometric fingerprint/face scanners and geofenced mobile check-ins seamlessly.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Statutory Tax & PF Compliance</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Pre-configured Indian tax slabs, PF, ESI, gratuity, and bonus calculations.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Employee Self-Service Portal</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Employees view leave balances, apply for vacations, and download Form 16 & salary slips.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UI Preview Box -->
            <div class="form-card" style="padding: 28px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <strong>Payroll Execution Preview</strong>
                    <span class="badge-dot green"></span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Active Workforce Headcount</span>
                            <span style="color: var(--primary-blue);">142 Employees</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">All biometric logs reconciled • Zero discrepancies</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Gross Monthly Payroll</span>
                            <span style="color: var(--text-primary);">₹ 64,80,000</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Net Payable: ₹ 57,20,000 • PF/TDS Deducted: ₹ 7,60,000</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Bank Disbursement Batch</span>
                            <span style="color: var(--secondary-green);">Processed (NEFT File Ready)</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Salary slips dispatched to employee WhatsApp</span>
                    </div>
                </div>
                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light);">
                    <a href="../appointment.php?service=hr-payroll" class="btn btn-primary w-100">Schedule HR Demo Session &rarr;</a>
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
            <h2 class="section-title">Complete People Management <span class="gradient-text">Engine</span></h2>
            <p class="section-subtitle">Scalable tools from onboarding documentation to exit settlements.</p>
        </div>

        <div class="services-grid-3">
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(2, 86, 196, 0.3);">👥</div>
                <h3 style="color: #fff;">Employee Database & KYC</h3>
                <p style="color: var(--text-light-muted);">Centralized digital records for PAN, Aadhaar, bank details, emergency contacts, and signed agreements.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(16, 185, 129, 0.25);">📅</div>
                <h3 style="color: #fff;">Leave & Shift Policies</h3>
                <p style="color: var(--text-light-muted);">Multi-shift scheduling, overtime tracking, casual/sick leave accrual, and manager approval chains.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(0, 153, 255, 0.25);">📑</div>
                <h3 style="color: #fff;">One-Click Pay Slips</h3>
                <p style="color: var(--text-light-muted);">Generate clean PDF salary slips with your company header, earnings, deductions, and tax withholdings.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section class="section">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Ready to Modernize Your HR & Payroll?</h2>
            <p>Schedule a live demonstration to see how fast you can execute monthly payroll with GrihGO HR.</p>
            <div class="btn-group">
                <a href="../appointment.php?service=hr-payroll" class="btn btn-primary btn-lg">Request Live Demo</a>
                <a href="../contact.php" class="btn btn-outline-white btn-lg">Talk to Product Specialist</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
