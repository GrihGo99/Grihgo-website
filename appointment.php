<?php
$page_title = 'Book an Appointment | GrihGO Digital & Tech';
$meta_desc = 'Schedule an exploratory meeting or software demonstration with the senior technology team at GrihGO Digital & Tech.';
$current_page = 'appointment';
$base_url = './';

// Pre-fill service from URL if provided (e.g. ?service=whatsapp-crm)
$selected_service = isset($_GET['service']) ? htmlspecialchars($_GET['service']) : '';

include 'includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Book Appointment</span>
        </nav>
        <div class="section-tag">Direct Scheduling</div>
        <h1 style="margin-bottom: 16px;">Schedule Your <span class="gradient-text">Discovery Session</span></h1>
        <p class="section-subtitle" style="max-width: 720px; margin-left: 0;">
            Connect with our technical consultants to evaluate your software architecture, explore customized platform demos, or plan your digital growth roadmap.
        </p>
    </div>
</section>

<!-- Appointment Form Section -->
<section class="section" id="appointment-section">
    <div class="container">
        <div style="max-width: 860px; margin: 0 auto;">
            <div class="form-card">
                <!-- Alert Box for feedback -->
                <div class="form-alert" id="appointmentAlert"></div>

                <form id="appointmentForm" method="POST" action="api/appointment-submit.php" novalidate>
                    <?php echo getCsrfField(); ?>
                    <div class="form-grid-2">
                        <!-- Full Name -->
                        <div class="form-group">
                            <label for="fullName" class="form-label">Full Name <span class="req">*</span></label>
                            <input type="text" class="form-control" id="fullName" name="full_name" placeholder="e.g. Rahul Sharma" required>
                            <div class="invalid-feedback">Please enter your full name (minimum 2 characters).</div>
                        </div>

                        <!-- Business Name -->
                        <div class="form-group">
                            <label for="businessName" class="form-label">Business / Organization Name</label>
                            <input type="text" class="form-control" id="businessName" name="business_name" placeholder="e.g. Apex Enterprises Pvt Ltd">
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">Work Email <span class="req">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="e.g. rahul@apex.com" required>
                            <div class="invalid-feedback">Please provide a valid email address.</div>
                        </div>

                        <!-- Phone Number -->
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone / WhatsApp Number <span class="req">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="e.g. +91 98765 43210" required>
                            <div class="invalid-feedback">Please enter a valid phone number.</div>
                        </div>

                        <!-- Service Option -->
                        <div class="form-group">
                            <label for="service" class="form-label">Service or Product of Interest <span class="req">*</span></label>
                            <select class="form-control" id="service" name="service" required>
                                <option value="" disabled <?php echo empty($selected_service) ? 'selected' : ''; ?>>Select a solution...</option>
                                <option value="website-development" <?php echo ($selected_service === 'website-development') ? 'selected' : ''; ?>>Website Development</option>
                                <option value="application-development" <?php echo ($selected_service === 'application-development' || $selected_service === 'mobile-app') ? 'selected' : ''; ?>>Application Development (Android / iOS)</option>
                                <option value="software-development" <?php echo ($selected_service === 'software-development') ? 'selected' : ''; ?>>Custom Software Development</option>
                                <option value="saas-services" <?php echo ($selected_service === 'saas-services') ? 'selected' : ''; ?>>SaaS Product Development</option>
                                <option value="ai-automation" <?php echo ($selected_service === 'ai-automation' || $selected_service === 'ai-solutions') ? 'selected' : ''; ?>>AI Automation &amp; Chatbots</option>
                                <option value="digital-marketing" <?php echo ($selected_service === 'digital-marketing') ? 'selected' : ''; ?>>Digital Marketing Services</option>
                                <option value="seo" <?php echo ($selected_service === 'seo') ? 'selected' : ''; ?>>Search Engine Optimization (SEO)</option>
                                <option value="social-media-marketing" <?php echo ($selected_service === 'social-media-marketing' || $selected_service === 'smm') ? 'selected' : ''; ?>>Social Media Marketing (SMM)</option>
                                <option value="meta-ads" <?php echo ($selected_service === 'meta-ads') ? 'selected' : ''; ?>>Meta Ads (Facebook &amp; Instagram)</option>
                                <option value="google-ads" <?php echo ($selected_service === 'google-ads') ? 'selected' : ''; ?>>Google Ads (Search &amp; Display)</option>
                                <option value="performance-marketing" <?php echo ($selected_service === 'performance-marketing') ? 'selected' : ''; ?>>Performance Marketing</option>
                                <option value="whatsapp-crm" <?php echo ($selected_service === 'whatsapp-crm') ? 'selected' : ''; ?>>GrihGO WhatsApp CRM</option>
                                <option value="billing-software" <?php echo ($selected_service === 'billing-software') ? 'selected' : ''; ?>>GST Billing Software</option>
                                <option value="crm" <?php echo ($selected_service === 'crm') ? 'selected' : ''; ?>>Enterprise CRM</option>
                                <option value="erp" <?php echo ($selected_service === 'erp') ? 'selected' : ''; ?>>Enterprise ERP</option>
                                <option value="inventory" <?php echo ($selected_service === 'inventory') ? 'selected' : ''; ?>>Inventory Management</option>
                                <option value="other">Other Business Inquiries</option>
                            </select>
                            <div class="invalid-feedback">Please choose a service.</div>
                        </div>

                        <!-- Budget Range -->
                        <div class="form-group">
                            <label for="budgetRange" class="form-label">Expected Investment Range</label>
                            <select class="form-control" id="budgetRange" name="budget_range">
                                <option value="" selected>Select approximate budget...</option>
                                <option value="standard">Standard Tier (₹ 25,000 – ₹ 75,000)</option>
                                <option value="growth">Growth Tier (₹ 75,000 – ₹ 2,00,000)</option>
                                <option value="enterprise">Enterprise Tier (₹ 2,00,000+)</option>
                                <option value="retainer">Monthly Marketing Retainer</option>
                                <option value="custom">To Be Determined During Call</option>
                            </select>
                        </div>

                        <!-- Preferred Date -->
                        <div class="form-group">
                            <label for="preferredDate" class="form-label">Preferred Date</label>
                            <input type="date" class="form-control" id="preferredDate" name="preferred_date" min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <!-- Preferred Time -->
                        <div class="form-group">
                            <label for="preferredTime" class="form-label">Preferred Time Slot</label>
                            <select class="form-control" id="preferredTime" name="preferred_time">
                                <option value="morning">Morning (10:00 AM – 01:00 PM)</option>
                                <option value="afternoon" selected>Afternoon (02:00 PM – 05:00 PM)</option>
                                <option value="evening">Evening (05:00 PM – 07:00 PM)</option>
                            </select>
                        </div>

                        <!-- Message -->
                        <div class="form-group col-full">
                            <label for="message" class="form-label">Project Overview or Specific Questions</label>
                            <textarea class="form-control" id="message" name="message" rows="4" placeholder="Briefly describe your objectives, current challenges, or any specific software features needed..."></textarea>
                            <span class="form-helper">Your details are kept strictly confidential.</span>
                        </div>
                    </div>

                    <div style="margin-top: 10px;">
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="appointmentSubmitBtn">
                            <span class="btn-text">Confirm & Schedule Appointment</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </button>
                    </div>
                </form>

                <!-- Success UI State (Section 27) -->
                <div id="appointmentSuccessCard" class="form-success-card" style="display: none;">
                    <div class="success-check-circle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <h2 class="success-title">Appointment Request Received</h2>
                    <p class="success-desc" id="appointmentSuccessMessage">
                        Thank you for contacting GrihGO Digital & Tech. Your appointment request has been received. Our team will contact you shortly.
                    </p>
                    <div class="success-meta-pill" id="appointmentReferenceBadge">
                        Senior Technology Team Assigned • Discovery Call Scheduled
                    </div>
                    <div class="success-actions">
                        <a href="index.php" class="btn btn-primary btn-lg">Back to Home</a>
                        <button type="button" class="btn btn-outline-primary" id="bookAnotherBtn">Book Another Session</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
