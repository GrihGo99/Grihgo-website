/**
 * GrihGO Digital & Tech — Main Interactions & Logic
 * Pure Vanilla JavaScript (Zero external libraries)
 */

(function () {
    'use strict';

    /* --------------------------------------------------------------------------
       1. Sticky Header & Scroll Effects
       -------------------------------------------------------------------------- */
    function initStickyHeader() {
        const header = document.getElementById('site-header');
        const backToTop = document.getElementById('backToTop');
        if (!header) return;

        function handleScroll() {
            const scrollY = window.scrollY || window.pageYOffset;

            if (scrollY > 30) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            if (backToTop) {
                if (scrollY > 400) {
                    backToTop.classList.add('show');
                } else {
                    backToTop.classList.remove('show');
                }
            }
        }

        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();

        if (backToTop) {
            backToTop.addEventListener('click', function () {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    }

    /* --------------------------------------------------------------------------
       2. Mobile Navigation Drawer
       -------------------------------------------------------------------------- */
    function initMobileNav() {
        const toggleBtn = document.getElementById('mobileMenuToggle');
        const closeBtn = document.getElementById('mobileDrawerClose');
        const drawer = document.getElementById('mobileMenuDrawer');
        const overlay = document.getElementById('mobileMenuOverlay');

        if (!toggleBtn || !drawer || !overlay) return;

        function openDrawer() {
            drawer.classList.add('active');
            overlay.classList.add('active');
            drawer.setAttribute('aria-hidden', 'false');
            toggleBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function closeDrawer() {
            drawer.classList.remove('active');
            overlay.classList.remove('active');
            drawer.setAttribute('aria-hidden', 'true');
            toggleBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        toggleBtn.addEventListener('click', openDrawer);
        if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
        overlay.addEventListener('click', closeDrawer);

        // Close on ESC key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && drawer.classList.contains('active')) {
                closeDrawer();
            }
        });

        // Mobile drawer accordion items
        const accordionBtns = drawer.querySelectorAll('.mobile-accordion-btn');
        accordionBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const sublist = this.nextElementSibling;
                if (sublist) {
                    const isOpen = sublist.classList.contains('open');
                    sublist.classList.toggle('open');
                    const arrow = this.querySelector('svg');
                    if (arrow) {
                        arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
                    }
                }
            });
        });
    }

    /* --------------------------------------------------------------------------
       3. FAQ Accordion (Single-expanded, Keyboard accessible)
       -------------------------------------------------------------------------- */
    function initFaqAccordion() {
        const faqItems = document.querySelectorAll('.faq-item');
        if (!faqItems.length) return;

        faqItems.forEach(item => {
            const btn = item.querySelector('.faq-question-btn');
            const panel = item.querySelector('.faq-answer-panel');

            if (!btn || !panel) return;

            btn.addEventListener('click', function () {
                const isCurrentlyActive = item.classList.contains('active');

                // Close all other items (One open item at a time requirement)
                faqItems.forEach(otherItem => {
                    otherItem.classList.remove('active');
                    const otherBtn = otherItem.querySelector('.faq-question-btn');
                    if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
                });

                if (!isCurrentlyActive) {
                    item.classList.add('active');
                    btn.setAttribute('aria-expanded', 'true');
                } else {
                    item.classList.remove('active');
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    /* --------------------------------------------------------------------------
       4. WhatsApp CRM Live Mockup Interactive Switcher
       -------------------------------------------------------------------------- */
    function initWhatsAppMockup() {
        const chips = document.querySelectorAll('.wa-stat-chip');
        const chatName = document.getElementById('waMockupName');
        const chatBadge = document.getElementById('waMockupBadge');
        const msgContainer = document.getElementById('waMockupMessages');

        if (!chips.length || !msgContainer) return;

        const presets = {
            leads: {
                name: "Rahul Verma (New Lead)",
                badge: "Incoming Lead",
                messages: [
                    { type: 'in', text: 'Hi GrihGO Tech team! We need an automated GST billing software and ERP for our 3 warehouses.', time: '11:42 AM' },
                    { type: 'out', text: 'Hello Rahul! Absolutely. We can deploy our ready GST Billing & Multi-Warehouse ERP. Would you like a live demo today?', time: '11:43 AM' },
                    { type: 'in', text: 'Yes, please schedule a 30-min walkthrough with your technical specialist.', time: '11:44 AM' }
                ]
            },
            chats: {
                name: "Ananya Mehta (Active Project)",
                badge: "Enterprise CRM",
                messages: [
                    { type: 'in', text: 'Quick question: can our sales reps track WhatsApp conversations directly inside our lead dashboard?', time: '02:15 PM' },
                    { type: 'out', text: 'Yes Ananya! GrihGO WhatsApp CRM provides multi-agent team inbox, automated message tagging and direct chat sync.', time: '02:16 PM' },
                    { type: 'in', text: 'Brilliant! The team loves the speed of the interface.', time: '02:17 PM' }
                ]
            },
            followups: {
                name: "Vikram Singhania (Follow-up)",
                badge: "Scheduled Call",
                messages: [
                    { type: 'out', text: 'Hello Vikram, automated reminder: your technology architecture review is scheduled for today at 4:00 PM.', time: '10:00 AM' },
                    { type: 'in', text: 'Thanks for the timely ping! Looking forward to reviewing the website & mobile app scope.', time: '10:05 AM' }
                ]
            },
            conversions: {
                name: "Dr. Alok Sen (Hospital ERP)",
                badge: "Converted Client",
                messages: [
                    { type: 'in', text: 'Contract signed and initial milestone cleared. Let us initiate the hospital management module.', time: '04:30 PM' },
                    { type: 'out', text: 'Welcome onboard Dr. Sen! Your dedicated technical project manager has created your Client Portal workspace.', time: '04:32 PM' }
                ]
            }
        };

        chips.forEach(chip => {
            chip.addEventListener('click', function () {
                chips.forEach(c => c.classList.remove('active'));
                this.classList.add('active');

                const key = this.getAttribute('data-mockup');
                const data = presets[key];
                if (!data) return;

                if (chatName) chatName.textContent = data.name;
                if (chatBadge) chatBadge.textContent = data.badge;

                // Render messages with smooth fade
                msgContainer.style.opacity = '0';
                setTimeout(() => {
                    msgContainer.innerHTML = '';
                    data.messages.forEach(m => {
                        const div = document.createElement('div');
                        div.className = `chat-bubble ${m.type}`;
                        div.innerHTML = `${m.text}<span class="chat-time">${m.time}</span>`;
                        msgContainer.appendChild(div);
                    });
                    msgContainer.style.opacity = '1';
                }, 150);
            });
        });
    }

    /* --------------------------------------------------------------------------
       5. Product Catalog Filtering Tabs
       -------------------------------------------------------------------------- */
    function initProductFilters() {
        const filterBtns = document.querySelectorAll('.filter-btn');
        const productCards = document.querySelectorAll('.product-card');

        if (!filterBtns.length || !productCards.length) return;

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.getAttribute('data-filter');

                productCards.forEach(card => {
                    const category = card.getAttribute('data-category');
                    if (filter === 'all' || category === filter) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }

    /* --------------------------------------------------------------------------
       6. Part 2 — Form Validation, CSRF & Fetch API Submissions
       -------------------------------------------------------------------------- */
    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function validatePhone(phone) {
        const clean = phone.replace(/[\s\-\(\)\+]/g, '');
        return clean.length >= 7 && clean.length <= 15;
    }

    function getActiveCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag && metaTag.getAttribute('content')) {
            return metaTag.getAttribute('content');
        }
        if (window.GRIHGO_CONFIG && window.GRIHGO_CONFIG.csrfToken) {
            return window.GRIHGO_CONFIG.csrfToken;
        }
        return '';
    }

    function getBaseUrl() {
        if (window.GRIHGO_CONFIG && window.GRIHGO_CONFIG.baseUrl) {
            return window.GRIHGO_CONFIG.baseUrl;
        }
        return './';
    }

    /**
     * Submit form via Fetch API with unified validation, loading states, and error display.
     */
    async function submitAjaxForm(form, submitBtn, alertBox, successCard, successMsgBox, onComplete) {
        const csrf = getActiveCsrfToken();
        const formData = new FormData(form);

        if (!formData.get('csrf_token') && csrf) {
            formData.append('csrf_token', csrf);
        }

        // Set loading state on button
        if (submitBtn) {
            submitBtn.classList.add('is-loading');
            submitBtn.disabled = true;
        }

        if (alertBox) {
            alertBox.style.display = 'none';
            alertBox.className = 'form-alert';
            alertBox.innerHTML = '';
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            const result = await response.json().catch(() => null);

            if (response.ok && result && result.success) {
                // Success State Transition
                if (alertBox) alertBox.style.display = 'none';
                form.style.display = 'none';
                
                if (successCard) {
                    successCard.style.display = 'block';
                    if (successMsgBox && result.message) {
                        successMsgBox.textContent = result.message;
                    }
                }
                
                form.reset();

                if (typeof onComplete === 'function') {
                    onComplete(result);
                }
            } else {
                // Error Handling
                const errorMsg = (result && result.message) ? result.message : 'An unexpected error occurred. Please try again.';
                if (alertBox) {
                    alertBox.style.display = 'block';
                    alertBox.className = 'form-alert error';
                    alertBox.innerHTML = `<strong>Submission Notice:</strong> ${errorMsg}`;
                }

                if (result && result.errors) {
                    Object.keys(result.errors).forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.nextElementSibling;
                            if (feedback && feedback.classList.contains('invalid-feedback')) {
                                feedback.textContent = result.errors[field];
                            }
                        }
                    });
                }
            }
        } catch (err) {
            if (alertBox) {
                alertBox.style.display = 'block';
                alertBox.className = 'form-alert error';
                alertBox.innerHTML = '<strong>Connection Error:</strong> Unable to connect to server. Please check your network or try again.';
            }
        } finally {
            if (submitBtn) {
                submitBtn.classList.remove('is-loading');
                submitBtn.disabled = false;
            }
        }
    }

    function initForms() {
        // 1. Appointment Booking Form (Section 6 & 27)
        const appointmentForm = document.getElementById('appointmentForm');
        const appointmentSubmitBtn = document.getElementById('appointmentSubmitBtn') || (appointmentForm ? appointmentForm.querySelector('button[type="submit"]') : null);
        const appointmentAlert = document.getElementById('appointmentAlert');
        const appointmentSuccessCard = document.getElementById('appointmentSuccessCard');
        const appointmentSuccessMessage = document.getElementById('appointmentSuccessMessage');
        const bookAnotherBtn = document.getElementById('bookAnotherBtn');

        if (appointmentForm) {
            appointmentForm.addEventListener('submit', function (e) {
                e.preventDefault();
                let isValid = true;

                const name = document.getElementById('fullName');
                const email = document.getElementById('email');
                const phone = document.getElementById('phone');
                const service = document.getElementById('service');
                const preferredDate = document.getElementById('preferredDate');

                // Name validation
                if (!name.value.trim() || name.value.trim().length < 2) {
                    name.classList.add('is-invalid');
                    isValid = false;
                } else {
                    name.classList.remove('is-invalid');
                }

                // Email validation
                if (!validateEmail(email.value.trim())) {
                    email.classList.add('is-invalid');
                    isValid = false;
                } else {
                    email.classList.remove('is-invalid');
                }

                // Phone validation
                if (!validatePhone(phone.value.trim())) {
                    phone.classList.add('is-invalid');
                    isValid = false;
                } else {
                    phone.classList.remove('is-invalid');
                }

                // Service validation
                if (!service.value) {
                    service.classList.add('is-invalid');
                    isValid = false;
                } else {
                    service.classList.remove('is-invalid');
                }

                // Date validation (not in the past)
                if (preferredDate && preferredDate.value) {
                    const selected = new Date(preferredDate.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (selected < today) {
                        preferredDate.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        preferredDate.classList.remove('is-invalid');
                    }
                }

                if (isValid) {
                    submitAjaxForm(
                        appointmentForm,
                        appointmentSubmitBtn,
                        appointmentAlert,
                        appointmentSuccessCard,
                        appointmentSuccessMessage,
                        function () {
                            if (appointmentSuccessCard) {
                                window.scrollTo({ top: appointmentSuccessCard.offsetTop - 120, behavior: 'smooth' });
                            }
                        }
                    );
                }
            });

            if (bookAnotherBtn) {
                bookAnotherBtn.addEventListener('click', function () {
                    appointmentSuccessCard.style.display = 'none';
                    appointmentForm.style.display = 'block';
                    appointmentForm.reset();
                    if (appointmentAlert) appointmentAlert.style.display = 'none';
                });
            }
        }

        // 2. Contact Inquiries Form (Section 9 & 28)
        const contactForm = document.getElementById('contactForm');
        const contactSubmitBtn = document.getElementById('contactSubmitBtn') || (contactForm ? contactForm.querySelector('button[type="submit"]') : null);
        const contactAlert = document.getElementById('contactAlert');
        const contactSuccessCard = document.getElementById('contactSuccessCard');
        const contactSuccessMessage = document.getElementById('contactSuccessMessage');
        const sendAnotherContactBtn = document.getElementById('sendAnotherContactBtn');

        if (contactForm) {
            contactForm.addEventListener('submit', function (e) {
                e.preventDefault();
                let isValid = true;

                const name = document.getElementById('contactName');
                const email = document.getElementById('contactEmail');
                const phone = document.getElementById('contactPhone');
                const message = document.getElementById('contactMessage');

                if (name && (!name.value.trim() || name.value.trim().length < 2)) {
                    name.classList.add('is-invalid');
                    isValid = false;
                } else if (name) {
                    name.classList.remove('is-invalid');
                }

                if (email && !validateEmail(email.value.trim())) {
                    email.classList.add('is-invalid');
                    isValid = false;
                } else if (email) {
                    email.classList.remove('is-invalid');
                }

                if (phone && !validatePhone(phone.value.trim())) {
                    phone.classList.add('is-invalid');
                    isValid = false;
                } else if (phone) {
                    phone.classList.remove('is-invalid');
                }

                if (message && (!message.value.trim() || message.value.trim().length < 5)) {
                    message.classList.add('is-invalid');
                    isValid = false;
                } else if (message) {
                    message.classList.remove('is-invalid');
                }

                if (isValid) {
                    submitAjaxForm(
                        contactForm,
                        contactSubmitBtn,
                        contactAlert,
                        contactSuccessCard,
                        contactSuccessMessage,
                        function () {
                            if (contactSuccessCard) {
                                window.scrollTo({ top: contactSuccessCard.offsetTop - 120, behavior: 'smooth' });
                            }
                        }
                    );
                }
            });

            if (sendAnotherContactBtn) {
                sendAnotherContactBtn.addEventListener('click', function () {
                    contactSuccessCard.style.display = 'none';
                    contactForm.style.display = 'block';
                    contactForm.reset();
                    if (contactAlert) contactAlert.style.display = 'none';
                });
            }
        }

    }

    /* --------------------------------------------------------------------------
       7. Part 2 — Interactive Modals (Request Demo & Service Quote)
       -------------------------------------------------------------------------- */
    function initModals() {
        const demoModal = document.getElementById('demoModal');
        const quoteModal = document.getElementById('quoteModal');

        // Helper to open modal
        function openModal(modalEl) {
            if (!modalEl) return;
            modalEl.classList.add('is-active');
            modalEl.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        // Helper to close modal
        function closeModal(modalEl) {
            if (!modalEl) return;
            modalEl.classList.remove('is-active');
            modalEl.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        // Close on ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (demoModal && demoModal.classList.contains('is-active')) closeModal(demoModal);
                if (quoteModal && quoteModal.classList.contains('is-active')) closeModal(quoteModal);
            }
        });

        // Close on backdrop click
        [demoModal, quoteModal].forEach(modal => {
            if (!modal) return;
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal(modal);
                }
            });
        });

        // A. Request Demo Modal Logic (Section 11)
        if (demoModal) {
            const closeBtn = document.getElementById('closeDemoModal');
            const doneBtn = document.getElementById('demoDoneBtn');
            const demoForm = document.getElementById('demoForm');
            const demoSubmitBtn = document.getElementById('demoSubmitBtn');
            const demoAlert = document.getElementById('demoAlert');
            const demoSuccessCard = document.getElementById('demoSuccessCard');
            const demoSuccessMessage = document.getElementById('demoSuccessMessage');
            const productLabel = document.getElementById('demoProductLabel');
            const productInput = document.getElementById('demoProductInput');
            const productDisplay = document.getElementById('demoModalProductDisplay');

            if (closeBtn) closeBtn.addEventListener('click', () => closeModal(demoModal));
            if (doneBtn) doneBtn.addEventListener('click', () => closeModal(demoModal));

            // Function to configure and open Demo Modal
            window.openDemoModalForProduct = function (productName) {
                const name = productName || 'GrihGO Business Suite';
                if (productLabel) productLabel.textContent = name;
                if (productInput) productInput.value = name;
                if (productDisplay) productDisplay.textContent = name;

                // Reset state
                if (demoForm) {
                    demoForm.style.display = 'block';
                    demoForm.reset();
                    if (productInput) productInput.value = name;
                }
                if (demoSuccessCard) demoSuccessCard.style.display = 'none';
                if (demoAlert) demoAlert.style.display = 'none';

                openModal(demoModal);
            };

            // Global interception for "Request Demo" buttons/links
            document.querySelectorAll('a, button').forEach(el => {
                const text = el.textContent.trim().toLowerCase();
                const isDemoTrigger = el.hasAttribute('data-open-demo') || 
                                     text === 'request demo' || 
                                     text === 'request live demo';

                if (isDemoTrigger) {
                    el.addEventListener('click', function (e) {
                        e.preventDefault();
                        
                        let targetProduct = el.getAttribute('data-product') || el.getAttribute('data-open-demo');
                        
                        // 1. Check parent card title (e.g. on products.php catalog)
                        if (!targetProduct) {
                            const cardTitle = el.closest('.product-card')?.querySelector('.product-card-title');
                            if (cardTitle) {
                                targetProduct = cardTitle.textContent.trim();
                            }
                        }

                        // 2. Check href query param (?service=...)
                        if (!targetProduct && el.hasAttribute('href')) {
                            const href = el.getAttribute('href');
                            const match = href.match(/[?&]service=([^&#]+)/);
                            if (match && match[1]) {
                                const slug = decodeURIComponent(match[1]);
                                const slugMap = {
                                    'whatsapp-crm': 'GrihGO WhatsApp CRM',
                                    'billing-software': 'GST Billing Software',
                                    'crm': 'Enterprise CRM',
                                    'inventory': 'Inventory Management',
                                    'erp': 'Enterprise ERP Platform',
                                    'hr-payroll': 'HR Management & Payroll',
                                    'school-management': 'School Management System',
                                    'hospital-management': 'Hospital Management Suite',
                                    'restaurant-management': 'Restaurant POS & Management',
                                    'real-estate-crm': 'Real Estate CRM',
                                    'lead-management': 'Lead Management Platform',
                                    'project-management': 'Project Management Suite',
                                    'customer-support': 'Customer Support System',
                                    'appointment-management': 'Appointment Management'
                                };
                                targetProduct = slugMap[slug] || slug.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            }
                        }

                        // 3. Fallback to page context (e.g. on dedicated product page)
                        if (!targetProduct) {
                            const breadcrumb = document.querySelector('.breadcrumb-current');
                            const h1 = document.querySelector('h1');
                            if (breadcrumb && breadcrumb.textContent.trim()) {
                                targetProduct = breadcrumb.textContent.trim();
                            } else if (h1) {
                                targetProduct = h1.textContent.trim().replace(/^GrihGO\s+/i, '');
                            } else {
                                targetProduct = 'GrihGO WhatsApp CRM';
                            }
                        }

                        window.openDemoModalForProduct(targetProduct);
                    });
                }
            });

            // Demo form submit
            if (demoForm) {
                demoForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    let isValid = true;

                    const fullName = document.getElementById('demoFullName');
                    const email = document.getElementById('demoEmail');
                    const phone = document.getElementById('demoPhone');

                    if (!fullName.value.trim() || fullName.value.trim().length < 2) {
                        fullName.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        fullName.classList.remove('is-invalid');
                    }

                    if (!validateEmail(email.value.trim())) {
                        email.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        email.classList.remove('is-invalid');
                    }

                    if (!validatePhone(phone.value.trim())) {
                        phone.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        phone.classList.remove('is-invalid');
                    }

                    if (isValid) {
                        submitAjaxForm(
                            demoForm,
                            demoSubmitBtn,
                            demoAlert,
                            demoSuccessCard,
                            demoSuccessMessage
                        );
                    }
                });
            }
        }

        // B. Service Quote Modal Logic (Section 13)
        if (quoteModal) {
            const closeBtn = document.getElementById('closeQuoteModal');
            const doneBtn = document.getElementById('quoteDoneBtn');
            const quoteForm = document.getElementById('quoteForm');
            const quoteSubmitBtn = document.getElementById('quoteSubmitBtn');
            const quoteAlert = document.getElementById('quoteAlert');
            const quoteSuccessCard = document.getElementById('quoteSuccessCard');
            const quoteSuccessMessage = document.getElementById('quoteSuccessMessage');
            const serviceLabel = document.getElementById('quoteServiceLabel');
            const serviceInput = document.getElementById('quoteServiceInput');
            const serviceDisplay = document.getElementById('quoteModalServiceDisplay');

            if (closeBtn) closeBtn.addEventListener('click', () => closeModal(quoteModal));
            if (doneBtn) doneBtn.addEventListener('click', () => closeModal(quoteModal));

            window.openQuoteModalForService = function (serviceName) {
                const name = serviceName || 'Technology Solution';
                if (serviceLabel) serviceLabel.textContent = name;
                if (serviceInput) serviceInput.value = name;
                if (serviceDisplay) serviceDisplay.textContent = name;

                if (quoteForm) {
                    quoteForm.style.display = 'block';
                    quoteForm.reset();
                    if (serviceInput) serviceInput.value = name;
                }
                if (quoteSuccessCard) quoteSuccessCard.style.display = 'none';
                if (quoteAlert) quoteAlert.style.display = 'none';

                openModal(quoteModal);
            };

            // Global interception for Service Quote CTAs
            document.querySelectorAll('a, button').forEach(el => {
                const text = el.textContent.trim().toLowerCase();
                const isQuoteTrigger = el.hasAttribute('data-open-quote') ||
                                      text === 'get a quote' ||
                                      text === 'discuss your requirement' ||
                                      text === 'book web consultation' ||
                                      text === 'book discovery appointment' ||
                                      text === 'talk to engineers';

                if (isQuoteTrigger) {
                    el.addEventListener('click', function (e) {
                        e.preventDefault();
                        let targetService = el.getAttribute('data-service') || el.getAttribute('data-open-quote');
                        
                        // 1. Check parent card
                        if (!targetService) {
                            const cardTitle = el.closest('.service-card')?.querySelector('h3');
                            if (cardTitle) {
                                targetService = cardTitle.textContent.trim();
                            }
                        }

                        // 2. Check href query param
                        if (!targetService && el.hasAttribute('href')) {
                            const href = el.getAttribute('href');
                            const match = href.match(/[?&]service=([^&#]+)/);
                            if (match && match[1]) {
                                const slug = decodeURIComponent(match[1]);
                                const serviceMap = {
                                    'website-development': 'Website Development',
                                    'application-development': 'Application Development',
                                    'software-development': 'Custom Software Development',
                                    'ai-solutions': 'AI Solutions & Chatbots',
                                    'digital-marketing': 'Digital Marketing',
                                    'seo': 'Search Engine Optimization (SEO)'
                                };
                                targetService = serviceMap[slug] || slug.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            }
                        }

                        // 3. Fallback to page context
                        if (!targetService) {
                            const breadcrumb = document.querySelector('.breadcrumb-current');
                            const h1 = document.querySelector('h1');
                            if (breadcrumb && breadcrumb.textContent.trim()) {
                                targetService = breadcrumb.textContent.trim();
                            } else if (h1) {
                                targetService = h1.textContent.trim().replace(/^Enterprise\s+/i, '');
                            } else {
                                targetService = 'Website Development';
                            }
                        }

                        window.openQuoteModalForService(targetService);
                    });
                }
            });

            // Quote form submit
            if (quoteForm) {
                quoteForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    let isValid = true;

                    const fullName = document.getElementById('quoteFullName');
                    const email = document.getElementById('quoteEmail');
                    const phone = document.getElementById('quotePhone');

                    if (!fullName.value.trim() || fullName.value.trim().length < 2) {
                        fullName.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        fullName.classList.remove('is-invalid');
                    }

                    if (!validateEmail(email.value.trim())) {
                        email.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        email.classList.remove('is-invalid');
                    }

                    if (!validatePhone(phone.value.trim())) {
                        phone.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        phone.classList.remove('is-invalid');
                    }

                    if (isValid) {
                        submitAjaxForm(
                            quoteForm,
                            quoteSubmitBtn,
                            quoteAlert,
                            quoteSuccessCard,
                            quoteSuccessMessage
                        );
                    }
                });
            }
        }
    }

    /* --------------------------------------------------------------------------
       8. Initialize All Modules
       -------------------------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {
        initStickyHeader();
        initMobileNav();
        initFaqAccordion();
        initWhatsAppMockup();
        initProductFilters();
        initForms();
        initModals();
    });
})();
