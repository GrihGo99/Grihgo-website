<?php
$page_title = 'Lead Management System | GrihGO Digital & Tech';
$meta_desc = 'Multi-channel lead ingestion, duplicate filtering, automated round-robin assignment, and conversation tracking in one unified pipeline.';
$current_page = 'product_lead';
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
            <span class="breadcrumb-current">Lead Management</span>
        </nav>
        <div class="section-tag" style="background: #fef9c3; color: #854d0e; border-color: rgba(133, 77, 14, 0.3);">
            Acquisition & Pipeline
        </div>
        <h1 style="margin-bottom: 16px;">Automated Lead <span class="gradient-text">Management System</span></h1>
        <p class="section-subtitle" style="max-width: 760px; margin-left: 0;">
            Capture, clean, and route every customer inquiry from Meta Ads, Google Ads, website forms, and WhatsApp into a single deduplicated conversion pipeline.
        </p>
        <div class="btn-group" style="margin-top: 24px;">
            <a href="../appointment.php?service=lead-management" class="btn btn-primary btn-lg">Request Lead System Demo</a>
            <a href="../contact.php" class="btn btn-outline-primary btn-lg">Talk to Lead Strategist</a>
        </div>
    </div>
</section>

<!-- Overview & UI Preview -->
<section class="section" id="product-overview">
    <div class="container">
        <div class="benefits-split-grid">
            <div>
                <span class="section-tag">Zero Leaked Inquiries</span>
                <h2 class="section-title">Speed-To-Lead Is The <span class="gradient-text">Single Biggest Factor</span></h2>
                <p>
                    Research consistently demonstrates that contacting a digital lead within the first five minutes yields a 9x higher conversion rate than waiting an hour.
                </p>
                <p>
                    GrihGO Lead Management eliminates manual export-import delays by ingesting leads instantaneously via direct webhooks, eliminating duplicates, and pushing prompt notifications to your sales reps' mobile phones immediately.
                </p>
                <div class="benefits-list" style="margin-top: 24px;">
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Instant Omnichannel Ingestion</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Direct webhook integrations for Meta Lead Ads, Google Ads, IndiaMART, Justdial, and web forms.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Intelligent Deduplication</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Prevent multiple reps from calling the same prospect by merging matching phone numbers and emails.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Round-Robin Auto Assignment</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Distribute leads equitably among active reps based on shifts, language, or territory.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UI Preview Box -->
            <div class="form-card" style="padding: 28px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <strong>Real-Time Ingestion Feed</strong>
                    <span class="pulse-indicator"></span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Source: Meta Ad (Instagram Reel)</span>
                            <span style="color: var(--secondary-green);">Assigned in 4s</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Prospect: Alok Kumar • Product: GST Billing Software</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Source: Google Search Ads (Noida)</span>
                            <span style="color: var(--primary-blue);">Auto-WhatsApp Sent</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Prospect: Ritu Singhania • Product: Website & App Scope</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>Source: Website Form (Direct)</span>
                            <span style="color: var(--text-primary);">Call Logged</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Prospect: Deepak Jain • Stage: Interested (Demo Set)</span>
                    </div>
                </div>
                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light);">
                    <a href="../appointment.php?service=lead-management" class="btn btn-primary w-100">Schedule Lead Engine Walkthrough &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="section section-dark" id="features">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Key Capabilities</span>
            <h2 class="section-title">Built For High-Volume <span class="gradient-text">Lead Routing</span></h2>
            <p class="section-subtitle">Scalable tools from initial registration to customer conversion.</p>
        </div>

        <div class="services-grid-3">
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(2, 86, 196, 0.3);">🎯</div>
                <h3 style="color: #fff;">Multi-Channel Ingestion</h3>
                <p style="color: var(--text-light-muted);">Native API connections for social platforms, paid ads, trade portals, and landing page forms.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(16, 185, 129, 0.25);">⚡</div>
                <h3 style="color: #fff;">Automated Auto-Responders</h3>
                <p style="color: var(--text-light-muted);">Trigger personalized SMS and WhatsApp greeting messages the second a lead enters the database.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(0, 153, 255, 0.25);">📊</div>
                <h3 style="color: #fff;">Channel Cost & ROI Analytics</h3>
                <p style="color: var(--text-light-muted);">Calculate real acquisition costs per channel so marketing budgets are allocated to winning campaigns.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section class="section">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Accelerate Your Lead Response Speeds with GrihGO</h2>
            <p>Schedule a live demonstration to see how our lead engine eliminates lost inquiries.</p>
            <div class="btn-group">
                <a href="../appointment.php?service=lead-management" class="btn btn-primary btn-lg">Request Live Demo</a>
                <a href="../contact.php" class="btn btn-outline-white btn-lg">Talk to Product Specialist</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
