<?php
$page_title = 'Contact Us | GrihGO Digital & Tech';
$meta_desc = 'Get in touch with GrihGO Digital & Tech. Connect via WhatsApp, phone, email or visit our technology office.';
$current_page = 'contact';
$base_url = './';
include 'includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Contact Us</span>
        </nav>
        <div class="section-tag">Direct Communication</div>
        <h1 style="margin-bottom: 16px;">Let's Build Something <span class="gradient-text">Exceptional</span></h1>
        <p class="section-subtitle" style="max-width: 720px; margin-left: 0;">
            Have questions about our enterprise platforms or custom software engineering? Reach out to our specialized consultants today.
        </p>
    </div>
</section>

<!-- Contact Layout -->
<section class="section" id="contact-details">
    <div class="container">
        <div class="contact-layout-grid">
            <!-- Left: Info Cards & Map -->
            <div class="contact-info-cards">
                <div class="contact-card-item">
                    <div class="contact-icon-box green">💬</div>
                    <div class="contact-details">
                        <strong>Instant WhatsApp</strong>
                        <p>Direct chat with customer success and technical advisors.</p>
                        <a href="https://wa.me/918092932060?text=Hello%20GrihGO%20Digital%20%26%20Tech%2C%20I%20would%20like%20to%20know%20more%20about%20your%20technology%20and%20digital%20solutions." target="_blank" rel="noopener noreferrer" style="font-weight: 600; color: var(--secondary-green);">+91 8092932060 &rarr;</a>
                    </div>
                </div>

                <div class="contact-card-item">
                    <div class="contact-icon-box">📞</div>
                    <div class="contact-details">
                        <strong>Telephone Support</strong>
                        <p>Monday to Saturday, 9:30 AM – 7:00 PM IST.</p>
                        <a href="tel:8092932060" style="font-weight: 600;">8092932060</a>
                    </div>
                </div>

                <div class="contact-card-item">
                    <div class="contact-icon-box">✉️</div>
                    <div class="contact-details">
                        <strong>Email Enquiries</strong>
                        <p>For RFPs, commercial contracts, and partnerships.</p>
                        <a href="mailto:contact@grihgo.com" style="font-weight: 600;">contact@grihgo.com</a>
                    </div>
                </div>

                <div class="contact-card-item">
                    <div class="contact-icon-box">📍</div>
                    <div class="contact-details">
                        <strong>HQ Office</strong>
                        <p>AIIMS Patna, Phulwari Sharif<br>Patna, Bihar, India</p>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">Website: www.grihgo.com</span>
                    </div>
                </div>

                <!-- Google Map Box -->
                <div class="map-placeholder-box">
                    <div class="map-placeholder-overlay">
                        <div style="font-size: 2rem; margin-bottom: 8px;">🗺️</div>
                        <strong style="font-size: 1.05rem; margin-bottom: 4px;">GrihGO Digital &amp; Tech HQ</strong>
                        <p style="font-size: 0.85rem; max-width: 320px; margin-bottom: 12px;">AIIMS Patna, Phulwari Sharif • Patna, Bihar, India</p>
                        <a href="https://maps.google.com/?q=AIIMS+Patna+Phulwari+Sharif+Patna+Bihar" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">Open in Google Maps</a>
                    </div>
                </div>
            </div>

            <!-- Right: Contact Form -->
            <div class="form-card">
                <h3 style="font-size: 1.6rem; margin-bottom: 8px;">Send an Inquiry</h3>
                <p style="color: var(--text-muted); font-size: 0.925rem; margin-bottom: 28px;">
                    Fill out the form below and an engineering lead will review your message within 24 hours.
                </p>

                <div class="form-alert" id="contactAlert"></div>

                <form id="contactForm" method="POST" action="api/contact-submit.php" novalidate>
                    <?php echo getCsrfField(); ?>
                    <div class="form-group">
                        <label for="contactName" class="form-label">Your Name <span class="req">*</span></label>
                        <input type="text" class="form-control" id="contactName" name="name" placeholder="e.g. Priya Sharma" required>
                        <div class="invalid-feedback">Please enter your name.</div>
                    </div>

                    <div class="form-group">
                        <label for="contactBusiness" class="form-label">Business / Company Name</label>
                        <input type="text" class="form-control" id="contactBusiness" name="business_name" placeholder="e.g. Sharma Logistics LLP">
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="contactEmail" class="form-label">Email Address <span class="req">*</span></label>
                            <input type="email" class="form-control" id="contactEmail" name="email" placeholder="priya@sharma.com" required>
                            <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>

                        <div class="form-group">
                            <label for="contactPhone" class="form-label">Phone Number <span class="req">*</span></label>
                            <input type="tel" class="form-control" id="contactPhone" name="phone" placeholder="+91 99999 88888" required>
                            <div class="invalid-feedback">Please enter a valid phone number.</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contactService" class="form-label">Primary Interest</label>
                        <select class="form-control" id="contactService" name="service">
                            <option value="General Inquiry" selected>General Inquiry</option>
                            <option value="Website Development">Website Development</option>
                            <option value="Application Development">Application Development</option>
                            <option value="Software Development">Custom Software / ERP</option>
                            <option value="WhatsApp CRM">GrihGO WhatsApp CRM</option>
                            <option value="Billing Software">GST Billing Software</option>
                            <option value="AI Solutions">AI Solutions</option>
                            <option value="Digital Marketing">Digital Marketing & SEO</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contactMessage" class="form-label">Your Message <span class="req">*</span></label>
                        <textarea class="form-control" id="contactMessage" name="message" rows="5" placeholder="Tell us about your requirements, project timeline, or questions..." required></textarea>
                        <div class="invalid-feedback">Please enter a message (minimum 5 characters).</div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="contactSubmitBtn">
                        <span class="btn-text">Send Message</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </form>

                <!-- Contact Success UI State (Section 28) -->
                <div id="contactSuccessCard" class="form-success-card" style="display: none;">
                    <div class="success-check-circle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <h2 class="success-title">Thank You!</h2>
                    <p class="success-desc" id="contactSuccessMessage">
                        Your enquiry has been successfully submitted. Our team will get back to you shortly.
                    </p>
                    <div class="success-meta-pill">Engineering Review In Progress • Response within 24 Hours</div>
                    <div class="success-actions">
                        <a href="index.php" class="btn btn-primary btn-lg">Back to Home</a>
                        <button type="button" class="btn btn-outline-primary" id="sendAnotherContactBtn">Send Another Message</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
