    </main>
    <!-- End Main Content -->

    <!-- Site Footer -->
    <footer class="site-footer" id="site-footer">
        <!-- Footer Wave / Top Glow -->
        <div class="footer-top-accent"></div>

        <div class="container footer-container">
            <div class="footer-grid">
                <!-- Col 1: Brand Info -->
                <div class="footer-brand-col">
                    <a href="<?php echo $base_url; ?>index.php" class="footer-brand-logo" aria-label="GrihGO Digital & Tech">
                        <img src="<?php echo $base_url; ?>assets/images/logo/logo.png" alt="GrihGO Digital & Tech" class="footer-logo-img" width="180" height="50">
                    </a>
                    <p class="footer-tagline"><?php echo htmlspecialchars(getSetting('tagline', 'Technology • Innovation • Growth')); ?></p>
                    <p class="footer-desc">
                        GrihGO Digital & Tech helps businesses build, automate and scale through modern websites, applications, software solutions, AI automation and performance-driven digital marketing.
                    </p>
                    <div class="footer-social-links" aria-label="Social Media Links">
                        <a href="<?php echo htmlspecialchars(getSetting('social_instagram', 'https://instagram.com/grihgodigital')); ?>" target="_blank" rel="noopener noreferrer" class="social-icon-btn" aria-label="Instagram">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                        </a>
                        <a href="<?php echo htmlspecialchars(getSetting('social_facebook', 'https://facebook.com/grihgodigital')); ?>" target="_blank" rel="noopener noreferrer" class="social-icon-btn" aria-label="Facebook">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                        </a>
                        <a href="<?php echo htmlspecialchars(getSetting('social_linkedin', 'https://linkedin.com/company/grihgodigital')); ?>" target="_blank" rel="noopener noreferrer" class="social-icon-btn" aria-label="LinkedIn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                        </a>
                        <a href="<?php echo htmlspecialchars(getSetting('social_youtube', 'https://youtube.com/@grihgodigital')); ?>" target="_blank" rel="noopener noreferrer" class="social-icon-btn" aria-label="YouTube">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>
                        </a>
                        <a href="<?php echo htmlspecialchars(getSetting('social_x', 'https://x.com/grihgodigital')); ?>" target="_blank" rel="noopener noreferrer" class="social-icon-btn" aria-label="X">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Company -->
                <div class="footer-links-col">
                    <h3 class="footer-col-title">Company</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo $base_url; ?>about.php">About Us</a></li>
                        <li><a href="<?php echo $base_url; ?>services.php">Services</a></li>
                        <li><a href="<?php echo $base_url; ?>products.php">Products</a></li>
                        <li><a href="<?php echo $base_url; ?>index.php#case-studies">Portfolio</a></li>
                        <li><a href="<?php echo $base_url; ?>contact.php">Contact Us</a></li>
                        <li><a href="<?php echo $base_url; ?>appointment.php">Book Appointment</a></li>
                    </ul>
                </div>

                <!-- Col 3: Solutions -->
                <div class="footer-links-col">
                    <h3 class="footer-col-title">Solutions</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo $base_url; ?>services/website-development.php">Website Development</a></li>
                        <li><a href="<?php echo $base_url; ?>services/application-development.php">Application Development</a></li>
                        <li><a href="<?php echo $base_url; ?>services/software-development.php">Software Development</a></li>
                        <li><a href="<?php echo $base_url; ?>services/ai-automation.php">AI Automation</a></li>
                        <li><a href="<?php echo $base_url; ?>services/digital-marketing.php">Digital Marketing</a></li>
                        <li><a href="<?php echo $base_url; ?>services/seo.php">SEO</a></li>
                        <li><a href="<?php echo $base_url; ?>services/social-media-marketing.php">Social Media Marketing</a></li>
                        <li><a href="<?php echo $base_url; ?>services/performance-marketing.php">Performance Marketing</a></li>
                        <li><a href="<?php echo $base_url; ?>services/saas-services.php">SaaS Solutions</a></li>
                    </ul>
                </div>

                <!-- Col 4: Client & Support -->
                <div class="footer-links-col">
                    <h3 class="footer-col-title">Client & Support</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo $base_url; ?>login.php" class="portal-link-highlight"><span class="badge-dot green"></span> Client Login</a></li>
                        <li><a href="<?php echo $base_url; ?>manager/login.php" class="portal-link-highlight"><span class="badge-dot blue"></span> Manager Login</a></li>
                        <li><a href="<?php echo $base_url; ?>admin/login.php"><span class="badge-dot blue"></span> Admin Console</a></li>
                        <li><a href="<?php echo $base_url; ?>contact.php#support">Support</a></li>
                        <li><a href="<?php echo $base_url; ?>appointment.php">Book Appointment</a></li>
                        <li><a href="<?php echo $base_url; ?>terms.php">Terms &amp; Conditions</a></li>
                        <li><a href="<?php echo $base_url; ?>privacy-policy.php">Privacy Policy</a></li>
                    </ul>
                    <div class="footer-contact-snippet" style="margin-top: 16px; padding-top: 14px; border-top: 1px solid rgba(255, 255, 255, 0.08);">
                        <div class="snippet-item" style="margin-bottom: 8px;">
                            <span class="snippet-label" style="display: block; font-size: 11px; text-transform: uppercase; color: var(--text-light-muted); letter-spacing: 0.5px;">HQ Office:</span>
                            <span class="snippet-val" style="color: #cbd5e1; font-size: 13px; line-height: 1.5; display: block;">
                                AIIMS Patna, Phulwari Sharif<br>Patna, Bihar, India
                            </span>
                        </div>
                        <div class="snippet-item" style="margin-bottom: 6px;">
                            <span class="snippet-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-light-muted); margin-right: 6px;">Mobile:</span>
                            <a href="tel:<?php echo htmlspecialchars(getSetting('phone', '8092932060')); ?>" class="snippet-val" style="color: #38bdf8; text-decoration: none; font-size: 13px; font-weight: 600;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 3px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                <?php echo htmlspecialchars(getSetting('phone', '8092932060')); ?>
                            </a>
                        </div>
                        <div class="snippet-item">
                            <span class="snippet-label" style="font-size: 11px; text-transform: uppercase; color: var(--text-light-muted); margin-right: 6px;">WhatsApp:</span>
                            <a href="https://wa.me/<?php echo htmlspecialchars(getWhatsAppNumber(), ENT_QUOTES, 'UTF-8'); ?>?text=Hello%20GrihGO%20Digital%20%26%20Tech%2C%20I%20would%20like%20to%20know%20more%20about%20your%20technology%20and%20digital%20solutions.%20Please%20help%20me%20with%20the%20right%20solution%20for%20my%20business." target="_blank" rel="noopener noreferrer" class="snippet-val" style="color: #34d399; text-decoration: none; font-size: 13px; font-weight: 600;">
                                +<?php echo htmlspecialchars(getWhatsAppNumber(), ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom / Copyright -->
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p><?php echo htmlspecialchars(getSetting('footer_copyright', '© 2026 GrihGO Digital & Tech. All Rights Reserved.')); ?></p>
                </div>
                <div class="footer-legal-links" style="display: flex; gap: 16px; font-size: 13px;">
                    <a href="<?php echo $base_url; ?>terms.php" style="color: var(--text-light-muted); text-decoration: none;">Terms &amp; Conditions</a>
                    <span style="color: rgba(255, 255, 255, 0.2);">•</span>
                    <a href="<?php echo $base_url; ?>privacy-policy.php" style="color: var(--text-light-muted); text-decoration: none;">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to top button -->
    <button type="button" class="back-to-top" id="backToTop" aria-label="Back to top of page">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
    </button>

    <!-- Global Floating WhatsApp Quick Action with Pre-filled Business Inquiry -->
    <a href="https://wa.me/<?php echo htmlspecialchars(getWhatsAppNumber(), ENT_QUOTES, 'UTF-8'); ?>?text=Hello%20GrihGO%20Digital%20%26%20Tech%2C%20I%20would%20like%20to%20know%20more%20about%20your%20technology%20and%20digital%20solutions.%20Please%20help%20me%20with%20the%20right%20solution%20for%20my%20business." target="_blank" rel="noopener noreferrer" class="floating-wa-btn" aria-label="Chat with GrihGO Digital & Tech on WhatsApp">
        <span class="wa-tooltip">Chat on WhatsApp</span>
        <svg width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.299.144.347.491 1.2.534 1.288.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.353.101.173.45 0.742.966 1.201.664.591 1.224.774 1.397.86.173.086.274.072.376-.043.101-.116.433-.506.549-.679.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
    </a>

    <!-- ======================================================================
         PART 2 MODAL: REQUEST DEMO SYSTEM (SECTION 11)
         ====================================================================== -->
    <div class="grihgo-modal-backdrop" id="demoModal" role="dialog" aria-modal="true" aria-labelledby="demoModalTitle">
        <div class="grihgo-modal">
            <div class="grihgo-modal-header">
                <div class="grihgo-modal-header-text">
                    <span class="grihgo-modal-badge">Interactive Demo</span>
                    <h3 class="grihgo-modal-title" id="demoModalTitle">Request <span class="gradient-text" id="demoModalProductDisplay">Software Demo</span></h3>
                    <p class="grihgo-modal-subtitle">Connect with a product specialist for a personalized 30-minute demonstration.</p>
                </div>
                <button type="button" class="grihgo-modal-close" id="closeDemoModal" aria-label="Close demo modal">&times;</button>
            </div>
            <div class="grihgo-modal-body">
                <div class="target-highlight-pill" id="demoProductPill">
                    <span class="pill-icon">💻</span>
                    <span>Selected Platform: <strong id="demoProductLabel">GrihGO Software</strong></span>
                </div>
                <div class="form-alert" id="demoAlert"></div>
                <form id="demoForm" method="POST" action="<?php echo $base_url; ?>api/demo-submit.php" novalidate>
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" id="demoProductInput" name="product" value="">
                    
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="demoFullName" class="form-label">Full Name <span class="req">*</span></label>
                            <input type="text" class="form-control" id="demoFullName" name="full_name" placeholder="e.g. Rahul Sharma" required>
                            <div class="invalid-feedback">Please enter your full name.</div>
                        </div>
                        <div class="form-group">
                            <label for="demoBusinessName" class="form-label">Business / Organization</label>
                            <input type="text" class="form-control" id="demoBusinessName" name="business_name" placeholder="e.g. Apex Enterprises">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="demoEmail" class="form-label">Work Email <span class="req">*</span></label>
                            <input type="email" class="form-control" id="demoEmail" name="email" placeholder="e.g. rahul@apex.com" required>
                            <div class="invalid-feedback">Please provide a valid work email.</div>
                        </div>
                        <div class="form-group">
                            <label for="demoPhone" class="form-label">Phone / WhatsApp <span class="req">*</span></label>
                            <input type="tel" class="form-control" id="demoPhone" name="phone" placeholder="e.g. +91 98765 43210" required>
                            <div class="invalid-feedback">Please provide a valid phone number.</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="demoMessage" class="form-label">Specific Requirements / Team Size</label>
                        <textarea class="form-control" id="demoMessage" name="message" rows="3" placeholder="Tell us about your team size, current challenges, or must-have features..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-secondary btn-lg w-100" id="demoSubmitBtn">
                        <span class="btn-text">Confirm Demo Booking</span>
                    </button>
                </form>

                <div id="demoSuccessCard" class="form-success-card" style="display: none;">
                    <div class="success-check-circle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <h3 class="success-title">Demo Request Received</h3>
                    <p class="success-desc" id="demoSuccessMessage">Thank you for your interest in GrihGO. Our team will contact you shortly.</p>
                    <div class="success-meta-pill">Priority Scheduling • Tech Consultant Assigned</div>
                    <div class="success-actions">
                        <button type="button" class="btn btn-outline-primary" id="demoDoneBtn">Done</button>
                        <a href="<?php echo $base_url; ?>index.php" class="btn btn-primary">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================
         PART 2 MODAL: SERVICE ENQUIRY & GET A QUOTE (SECTION 13)
         ====================================================================== -->
    <div class="grihgo-modal-backdrop" id="quoteModal" role="dialog" aria-modal="true" aria-labelledby="quoteModalTitle">
        <div class="grihgo-modal">
            <div class="grihgo-modal-header">
                <div class="grihgo-modal-header-text">
                    <span class="grihgo-modal-badge">Service Consultation</span>
                    <h3 class="grihgo-modal-title" id="quoteModalTitle">Discuss Your <span class="gradient-text" id="quoteModalServiceDisplay">Project Scope</span></h3>
                    <p class="grihgo-modal-subtitle">Share your specifications to receive an engineering estimate and timeline.</p>
                </div>
                <button type="button" class="grihgo-modal-close" id="closeQuoteModal" aria-label="Close quote modal">&times;</button>
            </div>
            <div class="grihgo-modal-body">
                <div class="target-highlight-pill" id="quoteServicePill">
                    <span class="pill-icon">⚡</span>
                    <span>Service Requirement: <strong id="quoteServiceLabel">Website Development</strong></span>
                </div>
                <div class="form-alert" id="quoteAlert"></div>
                <form id="quoteForm" method="POST" action="<?php echo $base_url; ?>api/service-enquiry.php" novalidate>
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" id="quoteServiceInput" name="service" value="">
                    
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="quoteFullName" class="form-label">Full Name <span class="req">*</span></label>
                            <input type="text" class="form-control" id="quoteFullName" name="full_name" placeholder="e.g. Priya Sharma" required>
                            <div class="invalid-feedback">Please enter your full name.</div>
                        </div>
                        <div class="form-group">
                            <label for="quoteBusinessName" class="form-label">Business / Company</label>
                            <input type="text" class="form-control" id="quoteBusinessName" name="business_name" placeholder="e.g. Sharma Logistics LLP">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="quoteEmail" class="form-label">Work Email <span class="req">*</span></label>
                            <input type="email" class="form-control" id="quoteEmail" name="email" placeholder="e.g. priya@sharma.com" required>
                            <div class="invalid-feedback">Please provide a valid work email.</div>
                        </div>
                        <div class="form-group">
                            <label for="quotePhone" class="form-label">Phone Number <span class="req">*</span></label>
                            <input type="tel" class="form-control" id="quotePhone" name="phone" placeholder="e.g. +91 99999 88888" required>
                            <div class="invalid-feedback">Please provide a valid phone number.</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="quoteBudget" class="form-label">Expected Investment Range</label>
                        <select class="form-control" id="quoteBudget" name="budget">
                            <option value="₹ 25,000 – ₹ 75,000">Standard Tier (₹ 25,000 – ₹ 75,000)</option>
                            <option value="₹ 75,000 – ₹ 2,00,000" selected>Growth Tier (₹ 75,000 – ₹ 2,00,000)</option>
                            <option value="₹ 2,00,000+">Enterprise Tier (₹ 2,00,000+)</option>
                            <option value="Monthly Retainer">Monthly Marketing Retainer</option>
                            <option value="To Discuss">To Be Determined During Call</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quoteMessage" class="form-label">Project Scope & Timeline</label>
                        <textarea class="form-control" id="quoteMessage" name="message" rows="3" placeholder="Briefly outline your objectives, target deliverables, or project launch deadline..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="quoteSubmitBtn">
                        <span class="btn-text">Submit Project Requirement</span>
                    </button>
                </form>

                <div id="quoteSuccessCard" class="form-success-card" style="display: none;">
                    <div class="success-check-circle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <h3 class="success-title">Requirement Received</h3>
                    <p class="success-desc" id="quoteSuccessMessage">Thank you for contacting GrihGO Digital & Tech. Your requirement has been received. Our solutions team will get in touch shortly.</p>
                    <div class="success-meta-pill">Engineering Review In Progress</div>
                    <div class="success-actions">
                        <button type="button" class="btn btn-outline-primary" id="quoteDoneBtn">Done</button>
                        <a href="<?php echo $base_url; ?>index.php" class="btn btn-primary">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vanilla JavaScript Files (Strictly zero external dependencies) -->
    <script src="<?php echo $base_url; ?>assets/js/animations.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/main.js"></script>
</body>
</html>
