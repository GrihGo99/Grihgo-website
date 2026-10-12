<?php
$page_title = 'School Management System | GrihGO Digital & Tech';
$meta_desc = 'Comprehensive school and institute management software covering student admissions, fee billing, exams, and parent communication.';
$current_page = 'product_school';
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
            <span class="breadcrumb-current">School Management System</span>
        </nav>
        <div class="section-tag" style="background: #e0e7ff; color: #3730a3; border-color: rgba(55, 48, 163, 0.3);">
            Education Technology
        </div>
        <h1 style="margin-bottom: 16px;">School & Institute <span class="gradient-text">Management System</span></h1>
        <p class="section-subtitle" style="max-width: 760px; margin-left: 0;">
            A unified digital campus platform integrating student admissions, digital fee collection, exam report cards, timetable scheduling, and instant parent SMS alerts.
        </p>
        <div class="btn-group" style="margin-top: 24px;">
            <a href="../appointment.php?service=school-management" class="btn btn-primary btn-lg">Request School Demo</a>
            <a href="../contact.php" class="btn btn-outline-primary btn-lg">Consult Education Team</a>
        </div>
    </div>
</section>

<!-- Overview & UI Preview -->
<section class="section" id="product-overview">
    <div class="container">
        <div class="benefits-split-grid">
            <div>
                <span class="section-tag">Campus Operations</span>
                <h2 class="section-title">Automate School Administration In <span class="gradient-text">One Portal</span></h2>
                <p>
                    Educational institutions juggle thousands of fee receipts, student academic records, teacher assignments, and urgent parental notifications every single day.
                </p>
                <p>
                    GrihGO School Management centralizes your entire administrative workflow into a clean web application with role-based access for principals, teachers, accountants, students, and parents.
                </p>
                <div class="benefits-list" style="margin-top: 24px;">
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Online & Counter Fee Invoicing</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Issue instant printed receipts, track installment arrears, and send WhatsApp payment links.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Instant Parent Communication</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Automate daily attendance SMS, circular broadcasts, and exam date sheets directly to parents.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Digital Report Card Generation</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Calculate grades, marks percentages, and attendance percentages into downloadable PDF report cards.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UI Preview Box -->
            <div class="form-card" style="padding: 28px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <strong>Academic Overview</strong>
                    <span class="badge-dot blue"></span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Total Enrolled Students</span>
                            <span style="color: var(--primary-blue);">1,840 Students</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Grades Nursery to 12th • 54 Sections</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Quarter 3 Fee Collection</span>
                            <span style="color: var(--secondary-green);">94.2% Collected</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Automated WhatsApp reminders sent for 106 pending balances</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Daily Campus Attendance</span>
                            <span style="color: var(--text-primary);">97.1% Present</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Absence alerts dispatched to parent mobiles at 10:00 AM</span>
                    </div>
                </div>
                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light);">
                    <a href="../appointment.php?service=school-management" class="btn btn-primary w-100">Schedule School ERP Demo &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="section section-dark" id="features">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Campus Modules</span>
            <h2 class="section-title">Built For Academic <span class="gradient-text">Excellence</span></h2>
            <p class="section-subtitle">A modular platform engineered for K-12 schools, colleges, and coaching academies.</p>
        </div>

        <div class="services-grid-3">
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(2, 86, 196, 0.3);">🎓</div>
                <h3 style="color: #fff;">Student Information System</h3>
                <p style="color: var(--text-light-muted);">Manage admissions, blood group records, parent contacts, previous school transfers, and document attachments.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(16, 185, 129, 0.25);">🧾</div>
                <h3 style="color: #fff;">Fee Management & Receipts</h3>
                <p style="color: var(--text-light-muted);">Custom fee structures by class, bus route charges, discount concessions, and automatic late fee calculations.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(0, 153, 255, 0.25);">📅</div>
                <h3 style="color: #fff;">Timetables & Teacher Allocation</h3>
                <p style="color: var(--text-light-muted);">Conflict-free automated period scheduling, subject teacher assignments, and substitute teacher management.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section class="section">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Ready to Digitize Your Educational Campus?</h2>
            <p>Schedule a live demonstration to see how fast you can enroll students and manage school operations.</p>
            <div class="btn-group">
                <a href="../appointment.php?service=school-management" class="btn btn-primary btn-lg">Request Live Demo</a>
                <a href="../contact.php" class="btn btn-outline-white btn-lg">Talk to Product Specialist</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
