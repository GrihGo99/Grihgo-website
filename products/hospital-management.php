<?php
$page_title = 'Hospital Management System (HIS/EMR) | GrihGO Digital & Tech';
$meta_desc = 'Comprehensive hospital information system covering OPD registration, electronic medical records, doctor scheduling, and pharmacy billing.';
$current_page = 'product_hospital';
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
            <span class="breadcrumb-current">Hospital Management</span>
        </nav>
        <div class="section-tag" style="background: #dcfce7; color: #166534; border-color: rgba(22, 101, 52, 0.3);">
            Healthcare Technology
        </div>
        <h1 style="margin-bottom: 16px;">Hospital Information & <span class="gradient-text">EMR System</span></h1>
        <p class="section-subtitle" style="max-width: 760px; margin-left: 0;">
            A unified clinical operating system streamlining OPD/IPD patient registration, digital prescriptions, bed management, laboratory testing, and pharmacy billing.
        </p>
        <div class="btn-group" style="margin-top: 24px;">
            <a href="../appointment.php?service=hospital-management" class="btn btn-primary btn-lg">Request Hospital Demo</a>
            <a href="../contact.php" class="btn btn-outline-primary btn-lg">Consult Healthcare Team</a>
        </div>
    </div>
</section>

<!-- Overview & UI Preview -->
<section class="section" id="product-overview">
    <div class="container">
        <div class="benefits-split-grid">
            <div>
                <span class="section-tag">Clinical Efficiency</span>
                <h2 class="section-title">Better Patient Care Through <span class="gradient-text">Digitized Operations</span></h2>
                <p>
                    Hospitals and multi-specialty clinics face crowded waiting rooms, misplaced patient paper records, and slow pharmacy billing reconciliations.
                </p>
                <p>
                    GrihGO Hospital Management connects the reception desk, consultation chambers, nursing stations, pathology labs, and pharmacy counters into one confidential and secure clinical platform.
                </p>
                <div class="benefits-list" style="margin-top: 24px;">
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Streamlined OPD Queue System</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Issue patient tokens, display chamber waiting screens, and track doctor consultation times.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Electronic Medical Records (EMR)</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Secure digital history of diagnosis, vitals, past allergies, and printable Rx prescriptions.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Integrated Pharmacy & Lab Billing</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Prescription items route directly to the in-house pharmacy counter for immediate dispatch.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UI Preview Box -->
            <div class="form-card" style="padding: 28px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <strong>Clinical Census & Queue</strong>
                    <span class="badge-dot green"></span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>OPD Registrations Today</span>
                            <span style="color: var(--primary-blue);">312 Patients</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Average consultation queue duration: 11 minutes</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>IPD Bed Occupancy</span>
                            <span style="color: var(--secondary-green);">42 / 50 Beds (84%)</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">ICU: 6 Occupied • General: 36 Occupied</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Pharmacy Dispensing</span>
                            <span style="color: var(--text-primary);">₹ 1,84,000 Today</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">100% digital prescription fulfillment with barcode batch checks</span>
                    </div>
                </div>
                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light);">
                    <a href="../appointment.php?service=hospital-management" class="btn btn-primary w-100">Schedule Hospital Software Walkthrough &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="section section-dark" id="features">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Clinical Capabilities</span>
            <h2 class="section-title">Built For Patient Safety & <span class="gradient-text">Compliance</span></h2>
            <p class="section-subtitle">Scalable modules supporting small polyclinics to multi-floor hospitals.</p>
        </div>

        <div class="services-grid-3">
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(2, 86, 196, 0.3);">🏥</div>
                <h3 style="color: #fff;">OPD & IPD Registration</h3>
                <p style="color: var(--text-light-muted);">Issue unique UHID numbers, manage admission deposits, bed allocations, and discharge summary generation.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(16, 185, 129, 0.25);">💊</div>
                <h3 style="color: #fff;">Pharmacy Stock & POS</h3>
                <p style="color: var(--text-light-muted);">Drug batch tracking, expiry date warnings, fast barcode billing, and scheduled supplier reordering.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(0, 153, 255, 0.25);">🧪</div>
                <h3 style="color: #fff;">Laboratory & Pathology</h3>
                <p style="color: var(--text-light-muted);">Sample barcode tracking, test parameter normal ranges, and instant WhatsApp delivery of verified test PDF reports.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section class="section">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Transform Your Healthcare Operations with GrihGO HIS</h2>
            <p>Schedule a live demonstration to see how our hospital platform elevates patient satisfaction and operational control.</p>
            <div class="btn-group">
                <a href="../appointment.php?service=hospital-management" class="btn btn-primary btn-lg">Request Live Demo</a>
                <a href="../contact.php" class="btn btn-outline-white btn-lg">Talk to Product Specialist</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
