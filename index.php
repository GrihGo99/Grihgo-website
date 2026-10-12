<?php
$page_title = 'GrihGO Digital & Tech | Website, Software, AI & Digital Marketing Solutions';
$meta_desc = 'GrihGO Digital & Tech provides website development, software solutions, application development, AI automation, SEO, social media marketing and performance marketing services for businesses in Patna, Bihar and across India.';
$current_page = 'home';
$base_url = './';
include 'includes/header.php';
?>

<!-- ==========================================================================
     1. HERO SECTION & PSEUDO-3D TECHNOLOGY ECOSYSTEM
     ========================================================================== -->
<section class="hero-section" id="hero">
    <div class="container hero-container">
        <div class="hero-grid">
            <!-- Hero Copy -->
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="pulse-indicator"></span>
                    <span>Next-Generation Digital Transformation</span>
                </div>
                <h1 class="hero-headline">
                    <span class="headline-line">BUILD DIGITAL.</span>
                    <span class="headline-line gradient-text">BUILD SMART.</span>
                    <span class="headline-line">BUILD FOR THE FUTURE.</span>
                </h1>
                <p class="hero-subheading">
                    GrihGO Digital & Tech delivers enterprise-grade IT services, AI solutions, business software, digital marketing, and customized technology architectures designed to accelerate sustainable business growth.
                </p>
                <div class="hero-ctas">
                    <a href="services.php" class="btn btn-primary btn-lg">
                        <span>Explore Services</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                    <a href="appointment.php" class="btn btn-secondary btn-lg">
                        <span>Book an Appointment</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </a>
                    <a href="contact.php" class="btn btn-ghost btn-lg">
                        <span>Talk to Our Team</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    </a>
                </div>
                <!-- Trust & Metric Counters -->
                <div class="hero-metrics">
                    <div class="hero-metric-item">
                        <strong data-counter="14" data-suffix="+">14+</strong>
                        <span>Enterprise Software Suites</span>
                    </div>
                    <div class="hero-metric-item">
                        <strong data-counter="99" data-suffix=".9%">99.9%</strong>
                        <span>Engineered Uptime</span>
                    </div>
                    <div class="hero-metric-item">
                        <strong data-counter="100" data-suffix="%">100%</strong>
                        <span>Dedicated Tech Support</span>
                    </div>
                </div>
            </div>

            <!-- Pseudo-3D Interactive Ecosystem Visual -->
            <div class="hero-visual-wrapper" id="heroVisualWrap" aria-hidden="true">
                <canvas class="hero-particle-canvas" id="heroParticleCanvas"></canvas>
                
                <div class="hero-3d-scene">
                    <!-- Central Pulsing Digital Sphere -->
                    <div class="digital-sphere"></div>
                    
                    <!-- 3D Orbit Rings -->
                    <div class="orbit-ring orbit-ring-1"></div>
                    <div class="orbit-ring orbit-ring-2"></div>
                    <div class="orbit-ring orbit-ring-3"></div>

                    <!-- Floating Glass Technology Nodes & Analytics Cards -->
                    <div class="hero-card-float card-float-top">
                        <div class="float-icon ai">🤖</div>
                        <div class="float-info">
                            <strong>AI Business Engine</strong>
                            <span>Real-Time Automation</span>
                        </div>
                    </div>

                    <div class="hero-card-float card-float-right">
                        <div class="float-icon growth">📈</div>
                        <div class="float-info">
                            <strong>Connected Workflows</strong>
                            <span>+140% Team Velocity</span>
                        </div>
                    </div>

                    <div class="hero-card-float card-float-bottom">
                        <div class="float-icon stack">💬</div>
                        <div class="float-info">
                            <strong>WhatsApp CRM Core</strong>
                            <span>Instant Lead Routing</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     2. BUSINESS INTRODUCTION (4 FLAGSHIP PILLARS)
     ========================================================================== -->
<section class="section" id="business-intro">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Integrated Ecosystem</span>
            <h2 class="section-title">One Technology Partner. <span class="gradient-text">Multiple Business Solutions.</span></h2>
            <p class="section-subtitle">
                Eliminate fragmented vendors. GrihGO Digital & Tech serves as your end-to-end technical backbone, bridging engineering, intelligent software, and high-performance acquisition.
            </p>
        </div>

        <div class="biz-intro-grid">
            <!-- Card 1: IT Services -->
            <div class="biz-card">
                <div class="biz-card-icon">💻</div>
                <h3 class="biz-card-title">IT Services</h3>
                <p class="biz-card-desc">
                    High-performance business websites, iOS/Android mobile apps, and robust backend engineering built for speed, uptime, and long-term security.
                </p>
                <a href="services.php#it-services" class="biz-card-link">
                    <span>Learn More</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>

            <!-- Card 2: AI Solutions -->
            <div class="biz-card">
                <div class="biz-card-icon">🤖</div>
                <h3 class="biz-card-title">AI Solutions</h3>
                <p class="biz-card-desc">
                    Intelligent chatbots, WhatsApp auto-responders, lead scoring models, and workflow automations that free your team to focus on strategic growth.
                </p>
                <a href="services/ai-solutions.php" class="biz-card-link">
                    <span>Learn More</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>

            <!-- Card 3: Business Software -->
            <div class="biz-card">
                <div class="biz-card-icon">📊</div>
                <h3 class="biz-card-title">Business Software</h3>
                <p class="biz-card-desc">
                    14 ready-to-deploy, customizable SaaS platforms including GrihGO WhatsApp CRM, GST Billing, Multi-branch ERP, and Hospital & School systems.
                </p>
                <a href="products.php" class="biz-card-link">
                    <span>Learn More</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>

            <!-- Card 4: Digital Marketing -->
            <div class="biz-card">
                <div class="biz-card-icon">🎯</div>
                <h3 class="biz-card-title">Digital Marketing</h3>
                <p class="biz-card-desc">
                    Data-grounded performance marketing across Meta Ads and Google Ads, paired with strategic SEO to consistently generate high-intent customer leads.
                </p>
                <a href="services/digital-marketing.php" class="biz-card-link">
                    <span>Learn More</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     3. TECHNOLOGY • INNOVATION • GROWTH (SECTION REDESIGN)
     ========================================================================== -->
<section class="section section-dark" id="about">
    <div class="container">
        <div class="section-header" style="max-width: 900px; margin: 0 auto 48px auto; text-align: center;">
            <span class="section-tag">Core Philosophy</span>
            <h2 class="section-title" style="color: #ffffff;">Technology. Innovation. Growth.</h2>
            <p class="section-subtitle" style="color: #cbd5e1; font-size: 1.1rem; line-height: 1.7; margin: 0 auto;">
                At GrihGO Digital &amp; Tech, we combine technology, innovation and business strategy to create scalable digital solutions. From high-performance websites and custom software to AI automation, SaaS platforms and digital marketing, we help businesses build stronger digital foundations and accelerate sustainable growth.
            </p>
        </div>

        <!-- Three Dedicated Content Blocks -->
        <div class="tig-pillars-grid">
            <!-- Block 1: TECHNOLOGY -->
            <div class="tig-pillar-card">
                <div class="tig-pillar-icon">💻</div>
                <h3 class="tig-pillar-title">TECHNOLOGY</h3>
                <p class="tig-pillar-desc">
                    Build on reliable technologies, modern development practices and scalable architecture designed for performance, security and long-term business growth.
                </p>
            </div>

            <!-- Block 2: INNOVATION -->
            <div class="tig-pillar-card">
                <div class="tig-pillar-icon">⚡</div>
                <h3 class="tig-pillar-title">INNOVATION</h3>
                <p class="tig-pillar-desc">
                    Transform repetitive processes and business challenges with AI-powered automation, intelligent applications and customized digital solutions.
                </p>
            </div>

            <!-- Block 3: GROWTH -->
            <div class="tig-pillar-card">
                <div class="tig-pillar-icon">📈</div>
                <h3 class="tig-pillar-title">GROWTH</h3>
                <p class="tig-pillar-desc">
                    Turn technology into measurable business growth through high-performing websites, software, SEO, social media and performance marketing.
                </p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="about.php" class="btn btn-outline-white btn-lg">
                <span>Learn More About Our Team &amp; Process</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        </div>
    </div>
</section>

<!-- ==========================================================================
     4. IT SERVICES SHOWCASE
     ========================================================================== -->
<section class="section" id="it-services">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Core Engineering</span>
            <h2 class="section-title">Engineered For Reliability & <span class="gradient-text">Scale</span></h2>
            <p class="section-subtitle">
                Robust, bespoke IT solutions designed with modern standards to power high-performing organizations.
            </p>
        </div>

        <div class="services-grid-3">
            <!-- Service 1: Website Development -->
            <div class="service-card">
                <div class="service-card-icon">🌐</div>
                <h3 class="service-card-title">Website Development</h3>
                <p class="service-card-desc">
                    Clean, responsive, high-converting digital portals tailored for corporate brands, e-commerce, and SaaS platforms.
                </p>
                <ul class="service-features-list">
                    <li><span class="feature-bullet"></span> Corporate & Brand Websites</li>
                    <li><span class="feature-bullet"></span> Custom E-Commerce Portals</li>
                    <li><span class="feature-bullet"></span> PHP & Vanilla Web Apps</li>
                    <li><span class="feature-bullet"></span> High-Speed Landing Pages</li>
                </ul>
                <div class="service-card-footer">
                    <a href="services/website-development.php" class="btn btn-sm btn-outline-primary">Learn More &rarr;</a>
                </div>
            </div>

            <!-- Service 2: Application Development -->
            <div class="service-card">
                <div class="service-card-icon">📱</div>
                <h3 class="service-card-title">Application Development</h3>
                <p class="service-card-desc">
                    Fluid, native-grade mobile applications built for Android and iOS that deliver seamless customer and enterprise utility.
                </p>
                <ul class="service-features-list">
                    <li><span class="feature-bullet"></span> Native Android & iOS</li>
                    <li><span class="feature-bullet"></span> Enterprise Business Apps</li>
                    <li><span class="feature-bullet"></span> Customer Facing Apps</li>
                    <li><span class="feature-bullet"></span> Admin & Field Force Systems</li>
                </ul>
                <div class="service-card-footer">
                    <a href="services/application-development.php" class="btn btn-sm btn-outline-primary">Learn More &rarr;</a>
                </div>
            </div>

            <!-- Service 3: Software Development -->
            <div class="service-card">
                <div class="service-card-icon">⚙️</div>
                <h3 class="service-card-title">Software Development</h3>
                <p class="service-card-desc">
                    Custom web-based business management software engineered around your specific operational workflows.
                </p>
                <ul class="service-features-list">
                    <li><span class="feature-bullet"></span> Tailored ERP & CRM Systems</li>
                    <li><span class="feature-bullet"></span> Multi-Store Inventory & Billing</li>
                    <li><span class="feature-bullet"></span> HR & Payroll Management</li>
                    <li><span class="feature-bullet"></span> Specialized Sector Software</li>
                </ul>
                <div class="service-card-footer">
                    <a href="services/software-development.php" class="btn btn-sm btn-outline-primary">Learn More &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     5. AUTOMATE ROUTINE & ACCELERATE INNOVATION (AI SOLUTIONS SECTION)
     ========================================================================== -->
<section class="section automate-routine-section" id="ai-solutions">
    <div class="container">
        <!-- Part A: Automate Routine Work (Strict White Text on Dark Background) -->
        <div class="section-header" style="max-width: 900px; margin: 0 auto 40px auto; text-align: center;">
            <span class="section-tag">Intelligent Operations</span>
            <h2 class="section-title" style="color: #ffffff !important;">Automate Routine Work. Focus on What Matters.</h2>
            <p class="section-subtitle" style="color: #ffffff !important; opacity: 0.95;">
                Reduce repetitive manual tasks with intelligent automation designed around your business workflows. GrihGO Digital &amp; Tech helps automate lead management, customer communication, follow-ups, reporting, internal processes and routine operations so your team can spend more time on strategic growth.
            </p>
        </div>

        <div class="automate-points-grid">
            <div class="automate-point-card">
                <div class="automate-point-icon">🤖</div>
                <h4 class="automate-point-title">AI-Powered Automation</h4>
                <p class="automate-point-desc">Deploy contextual bots and intelligent models to handle routine queries and actions 24/7.</p>
            </div>
            <div class="automate-point-card">
                <div class="automate-point-icon">🔄</div>
                <h4 class="automate-point-title">Workflow Automation</h4>
                <p class="automate-point-desc">Connect disjointed platforms to trigger approvals, data updates, and syncs automatically.</p>
            </div>
            <div class="automate-point-card">
                <div class="automate-point-icon">🎯</div>
                <h4 class="automate-point-title">Lead Management</h4>
                <p class="automate-point-desc">Instant lead capture, scoring, verification, and automated routing to designated managers.</p>
            </div>
            <div class="automate-point-card">
                <div class="automate-point-icon">💬</div>
                <h4 class="automate-point-title">Customer Communication</h4>
                <p class="automate-point-desc">Automated WhatsApp, SMS, and email alerts for appointment booking, status, and invoices.</p>
            </div>
            <div class="automate-point-card">
                <div class="automate-point-icon">⚙️</div>
                <h4 class="automate-point-title">Business Process Automation</h4>
                <p class="automate-point-desc">Streamline reporting, inventory checks, billing calculations, and administrative paperwork.</p>
            </div>
        </div>

        <!-- Part B: Accelerate Innovation With Smarter Technology (Requirement 17) -->
        <div class="accelerate-innovation-block">
            <div class="section-header" style="max-width: 900px; margin: 0 auto 36px auto; text-align: center;">
                <span class="section-tag" style="background: rgba(96, 165, 250, 0.2); color: #60a5fa; border-color: rgba(96, 165, 250, 0.35);">Digital Velocity</span>
                <h2 class="section-title" style="color: #ffffff !important;">Accelerate Innovation With Smarter Technology</h2>
                <p class="section-subtitle" style="color: #cbd5e1 !important;">
                    Move from ideas to scalable digital solutions with technology built around your business goals. We combine modern development, AI capabilities, automation and digital strategy to help businesses launch faster, operate smarter and adapt to changing customer expectations.
                </p>
            </div>

            <div class="accelerate-points-grid">
                <div class="accelerate-point-card">
                    <div class="accelerate-point-icon">🚀</div>
                    <h4 class="accelerate-point-title">Modern Technology</h4>
                    <p class="accelerate-point-desc">Clean, standardized web and mobile architectures built for longevity and performance.</p>
                </div>
                <div class="accelerate-point-card">
                    <div class="accelerate-point-icon">🧠</div>
                    <h4 class="accelerate-point-title">AI-Driven Solutions</h4>
                    <p class="accelerate-point-desc">Pragmatic AI implementations that solve actual friction points rather than empty buzzwords.</p>
                </div>
                <div class="accelerate-point-card">
                    <div class="accelerate-point-icon">🏗️</div>
                    <h4 class="accelerate-point-title">Scalable Architecture</h4>
                    <p class="accelerate-point-desc">Modular systems designed to expand smoothly as your transaction and customer numbers double.</p>
                </div>
                <div class="accelerate-point-card">
                    <div class="accelerate-point-icon">📊</div>
                    <h4 class="accelerate-point-title">Data-Informed Decisions</h4>
                    <p class="accelerate-point-desc">Clear dashboards and conversation analytics that inform your product and marketing choices.</p>
                </div>
                <div class="accelerate-point-card">
                    <div class="accelerate-point-icon">📈</div>
                    <h4 class="accelerate-point-title">Continuous Improvement</h4>
                    <p class="accelerate-point-desc">Agile release cycles, proactive security updates, and performance monitoring round the clock.</p>
                </div>
            </div>
        </div>

        <!-- Part C: AI Capabilities Showcase Grid -->
        <div style="margin-top: 60px;">
            <h3 style="color: #ffffff; text-align: center; font-size: 1.3rem; margin-bottom: 28px; font-weight: 600;">Explore Specialized AI &amp; Automation Modules</h3>
            <div class="ai-grid">
                <div class="ai-card">
                    <span class="ai-badge">Automated 24/7</span>
                    <div class="ai-icon-wrap">💬</div>
                    <h3>AI Chatbot</h3>
                    <p>Context-aware virtual assistants that handle customer inquiries, qualify leads, and schedule appointments instantly.</p>
                    <a href="services/ai-automation.php" class="btn btn-sm btn-outline-white">Explore AI Chatbot</a>
                </div>

                <div class="ai-card">
                    <span class="ai-badge">High Conversion</span>
                    <div class="ai-icon-wrap">⚡</div>
                    <h3>AI WhatsApp Automation</h3>
                    <p>Transform your WhatsApp into an intelligent sales machine with smart trigger workflows and automated catalogs.</p>
                    <a href="services/ai-automation.php" class="btn btn-sm btn-outline-white">Explore WhatsApp AI</a>
                </div>

                <div class="ai-card">
                    <span class="ai-badge">Predictive</span>
                    <div class="ai-icon-wrap">🎯</div>
                    <h3>AI Lead Management</h3>
                    <p>Automatically score, categorize, and route incoming leads to the appropriate sales specialist without human delay.</p>
                    <a href="services/ai-automation.php" class="btn btn-sm btn-outline-white">Explore Lead AI</a>
                </div>

                <div class="ai-card">
                    <span class="ai-badge">Generative</span>
                    <div class="ai-icon-wrap">✍️</div>
                    <h3>AI Content Solutions</h3>
                    <p>Automate marketing copy, personalized customer emails, and multi-channel localized updates at enterprise scale.</p>
                    <a href="services/ai-automation.php" class="btn btn-sm btn-outline-white">Explore Content AI</a>
                </div>

                <div class="ai-card">
                    <span class="ai-badge">Co-Pilot</span>
                    <div class="ai-icon-wrap">🧠</div>
                    <h3>AI Business Assistant</h3>
                    <p>Internal intelligent knowledge bases that assist your employees in answering policy, technical, and billing queries.</p>
                    <a href="services/ai-automation.php" class="btn btn-sm btn-outline-white">Explore Assistant AI</a>
                </div>

                <div class="ai-card">
                    <span class="ai-badge">Workflows</span>
                    <div class="ai-icon-wrap">🔄</div>
                    <h3>AI Automation Pipelines</h3>
                    <p>End-to-end task automation bridging your CRM, invoices, email notifications, and reporting databases effortlessly.</p>
                    <a href="services/ai-automation.php" class="btn btn-sm btn-outline-white">Explore Workflows</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     6. WHATSAPP CRM DEDICATED SHOWCASE (WITH LIVE INTERACTIVE MOCKUP)
     ========================================================================== -->
<section class="section wa-showcase-section" id="whatsapp-crm">
    <div class="container">
        <div class="wa-grid">
            <!-- Left Info -->
            <div class="wa-content">
                <span class="section-tag" style="background: var(--secondary-green-light); color: var(--secondary-green); border-color: rgba(16, 185, 129, 0.3);">
                    Flagship SaaS Platform
                </span>
                <h2 class="section-title">
                    TURN WHATSAPP INTO YOUR <span class="green-text">BUSINESS WORKSPACE.</span>
                </h2>
                <p class="section-subtitle" style="margin-left: 0;">
                    Stop losing prospective leads across personal phones. GrihGO WhatsApp CRM equips your sales and support teams with a centralized multi-agent dashboard, automated replies, and deep analytics.
                </p>

                <div class="wa-features-pill-grid">
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> WhatsApp Lead Management</div>
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> Multiple Agents, One Number</div>
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> Team Shared Inbox</div>
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> Automated Welcome & Replies</div>
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> Verified Template Campaigns</div>
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> Smart Lead Tagging</div>
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> Automated Follow-up Pings</div>
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> Real-time Performance Metrics</div>
                </div>

                <div class="btn-group">
                    <a href="products/whatsapp-crm.php" class="btn btn-secondary btn-lg">
                        <span>Request WhatsApp CRM Demo</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                    <a href="appointment.php" class="btn btn-outline-primary btn-lg">
                        <span>Consult Our Team</span>
                    </a>
                </div>
            </div>

            <!-- Right Interactive Live Dashboard Mockup -->
            <div class="wa-mockup-wrapper">
                <div class="wa-mockup-header">
                    <div class="mockup-window-controls">
                        <span class="win-dot red"></span>
                        <span class="win-dot yellow"></span>
                        <span class="win-dot green"></span>
                    </div>
                    <div class="mockup-title-bar">GrihGO WhatsApp CRM — Unified Team Inbox</div>
                    <div class="mockup-live-status">
                        <span class="pulse-indicator"></span>
                        <span>API Connected</span>
                    </div>
                </div>

                <div class="wa-mockup-body">
                    <!-- Stat Tabs / Quick Sidebar -->
                    <div class="wa-mockup-sidebar" role="tablist">
                        <div class="wa-stat-chip active" data-mockup="leads" role="tab" tabindex="0">
                            <span class="wa-stat-label">New Leads</span>
                            <span class="wa-stat-val">+38 Today</span>
                        </div>
                        <div class="wa-stat-chip" data-mockup="chats" role="tab" tabindex="0">
                            <span class="wa-stat-label">Active Chats</span>
                            <span class="wa-stat-val">12 Agents</span>
                        </div>
                        <div class="wa-stat-chip" data-mockup="followups" role="tab" tabindex="0">
                            <span class="wa-stat-label">Follow-ups</span>
                            <span class="wa-stat-val">24 Due</span>
                        </div>
                        <div class="wa-stat-chip" data-mockup="conversions" role="tab" tabindex="0">
                            <span class="wa-stat-label">Conversions</span>
                            <span class="wa-stat-val">₹ 4.8L Today</span>
                        </div>
                    </div>

                    <!-- Chat Mockup Window -->
                    <div class="wa-mockup-chat">
                        <div class="wa-chat-header">
                            <div class="wa-chat-avatar">RV</div>
                            <div>
                                <div class="wa-chat-name" id="waMockupName">Rahul Verma (New Lead)</div>
                                <span class="wa-chat-badge" id="waMockupBadge">Incoming Lead</span>
                            </div>
                        </div>

                        <div class="wa-chat-messages" id="waMockupMessages">
                            <div class="chat-bubble in">
                                Hi GrihGO Tech team! We need an automated GST billing software and ERP for our 3 warehouses.
                                <span class="chat-time">11:42 AM</span>
                            </div>
                            <div class="chat-bubble out">
                                Hello Rahul! Absolutely. We can deploy our ready GST Billing & Multi-Warehouse ERP. Would you like a live demo today?
                                <span class="chat-time">11:43 AM</span>
                            </div>
                            <div class="chat-bubble in">
                                Yes, please schedule a 30-min walkthrough with your technical specialist.
                                <span class="chat-time">11:44 AM</span>
                            </div>
                        </div>

                        <div class="wa-chat-input-bar">
                            <span>Type an automated reply or select quick template...</span>
                            <span style="color: var(--secondary-green); font-weight: 700;">SEND</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     7. BILLING SOFTWARE SHOWCASE
     ========================================================================== -->
<section class="section" id="billing-software">
    <div class="container">
        <div class="billing-showcase-grid">
            <!-- Left: Invoice Preview Card -->
            <div class="billing-preview-card">
                <div class="invoice-preview-head">
                    <div>
                        <h4 style="margin: 0; font-size: 1.15rem;">GST TAX INVOICE</h4>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">INV-2026-0842 • Original for Recipient</span>
                    </div>
                    <span class="invoice-badge">PAID • GST COMPLIANT</span>
                </div>

                <table class="invoice-table-preview">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th>HSN/SAC</th>
                            <th>Qty</th>
                            <th>GST%</th>
                            <th style="text-align: right;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>GrihGO Enterprise Software License</td>
                            <td>998313</td>
                            <td>1</td>
                            <td>18%</td>
                            <td style="text-align: right;">45,000.00</td>
                        </tr>
                        <tr>
                            <td>Multi-Branch Inventory Module</td>
                            <td>998314</td>
                            <td>1</td>
                            <td>18%</td>
                            <td style="text-align: right;">15,000.00</td>
                        </tr>
                        <tr>
                            <td>Cloud Setup & WhatsApp Sync</td>
                            <td>998315</td>
                            <td>1</td>
                            <td>18%</td>
                            <td style="text-align: right;">8,000.00</td>
                        </tr>
                        <tr class="invoice-total-row">
                            <td colspan="4" style="text-align: right; padding-top: 14px;">Total Taxable + IGST (18%):</td>
                            <td style="text-align: right; padding-top: 14px; color: var(--primary-blue);">₹ 80,240.00</td>
                        </tr>
                    </tbody>
                </table>

                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px dashed var(--border-light); font-size: 0.8rem; color: var(--text-muted);">
                    <span>Generated instantly with GrihGO Billing Engine</span>
                    <span style="font-weight: 600; color: var(--secondary-green);">PDF / Print Ready</span>
                </div>
            </div>

            <!-- Right Info -->
            <div class="billing-content">
                <span class="section-tag">Finance & Operations</span>
                <h2 class="section-title">Speed. Precision. <span class="gradient-text">Complete GST Compliance.</span></h2>
                <p class="section-subtitle" style="margin-left: 0;">
                    Modernize your invoicing, inventory replenishment, and accounting with our lightning-fast billing software built for retailers, distributors, and service providers.
                </p>

                <div class="wa-features-pill-grid">
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> 10-Second GST Invoicing</div>
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> Multi-Store Inventory Tracking</div>
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> Barcode & Thermal Printing</div>
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> Purchase & Expense Records</div>
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> Payment Reminders via WhatsApp</div>
                    <div class="wa-pill-item"><span class="wa-pill-dot"></span> GSTR-1 & GSTR-3B Reports</div>
                </div>

                <div class="btn-group">
                    <a href="products/billing-software.php" class="btn btn-primary btn-lg">
                        <span>Request Billing Software Demo</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                    <a href="appointment.php" class="btn btn-ghost btn-lg">
                        <span>Schedule Technical Call</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     8. DIGITAL MARKETING
     ========================================================================== -->
<section class="section section-dark" id="digital-marketing">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Growth Marketing</span>
            <h2 class="section-title">Fuel Business Demand with <span class="gradient-text">Precision Marketing</span></h2>
            <p class="section-subtitle">
                Software is only half the battle. We generate predictable customer pipelines using high-return performance advertising and organic search domination.
            </p>
        </div>

        <div class="services-grid-3">
            <!-- Pillar 1: Social Media Marketing -->
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(2, 86, 196, 0.3);">📸</div>
                <h3 class="service-card-title" style="color: #fff;">Social Media Marketing</h3>
                <p class="service-card-desc">
                    Comprehensive brand positioning across Instagram and Facebook with high-impact visual creatives, reels, and community engagement.
                </p>
                <ul class="service-features-list">
                    <li style="color: var(--text-light-muted);"><span class="feature-bullet" style="background: var(--secondary-green);"></span> Instagram Strategy & Curation</li>
                    <li style="color: var(--text-light-muted);"><span class="feature-bullet" style="background: var(--secondary-green);"></span> High-Converting Video Reels</li>
                    <li style="color: var(--text-light-muted);"><span class="feature-bullet" style="background: var(--secondary-green);"></span> Graphic Design & Creatives</li>
                    <li style="color: var(--text-light-muted);"><span class="feature-bullet" style="background: var(--secondary-green);"></span> Brand Voice & Community Building</li>
                </ul>
                <div class="service-card-footer" style="border-top-color: rgba(255, 255, 255, 0.1);">
                    <a href="services/digital-marketing.php" class="btn btn-sm btn-outline-white">Explore SMM &rarr;</a>
                </div>
            </div>

            <!-- Pillar 2: Performance Marketing -->
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(16, 185, 129, 0.25);">🎯</div>
                <h3 class="service-card-title" style="color: #fff;">Performance Marketing</h3>
                <p class="service-card-desc">
                    ROI-obsessed paid customer acquisition targeting verified audiences across Meta Ads (Instagram/FB) and Google Search & Display Ads.
                </p>
                <ul class="service-features-list">
                    <li style="color: var(--text-light-muted);"><span class="feature-bullet" style="background: var(--secondary-green);"></span> Meta Ads (High-Intent Leads)</li>
                    <li style="color: var(--text-light-muted);"><span class="feature-bullet" style="background: var(--secondary-green);"></span> Google Search & Shopping Ads</li>
                    <li style="color: var(--text-light-muted);"><span class="feature-bullet" style="background: var(--secondary-green);"></span> Smart Retargeting Funnels</li>
                    <li style="color: var(--text-light-muted);"><span class="feature-bullet" style="background: var(--secondary-green);"></span> Conversion Rate Optimization (CRO)</li>
                </ul>
                <div class="service-card-footer" style="border-top-color: rgba(255, 255, 255, 0.1);">
                    <a href="services/digital-marketing.php" class="btn btn-sm btn-outline-white">Explore Performance &rarr;</a>
                </div>
            </div>

            <!-- Pillar 3: Search Engine Optimization -->
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(0, 153, 255, 0.25);">🔍</div>
                <h3 class="service-card-title" style="color: #fff;">Strategic SEO</h3>
                <p class="service-card-desc">
                    Long-term ranking dominance on Google through technical architecture optimization, keyword clustering, and localized presence.
                </p>
                <ul class="service-features-list">
                    <li style="color: var(--text-light-muted);"><span class="feature-bullet" style="background: var(--secondary-green);"></span> Technical Audits & Web Vitals</li>
                    <li style="color: var(--text-light-muted);"><span class="feature-bullet" style="background: var(--secondary-green);"></span> On-Page Content Optimization</li>
                    <li style="color: var(--text-light-muted);"><span class="feature-bullet" style="background: var(--secondary-green);"></span> Google Business Profile (Local SEO)</li>
                    <li style="color: var(--text-light-muted);"><span class="feature-bullet" style="background: var(--secondary-green);"></span> High-Authority Backlink Profile</li>
                </ul>
                <div class="service-card-footer" style="border-top-color: rgba(255, 255, 255, 0.1);">
                    <a href="services/seo.php" class="btn btn-sm btn-outline-white">Explore SEO &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     9. TECHNOLOGY STACK (CATEGORIZED PROVEN TECHNOLOGIES)
     ========================================================================== -->
<section class="section" id="tech-stack" style="background-color: #f8fafc;">
    <div class="container">
        <div class="section-header" style="max-width: 900px; margin: 0 auto 40px auto; text-align: center;">
            <span class="section-tag">Engineering Stack</span>
            <h2 class="section-title">Built on Proven, Scalable Technologies</h2>
            <p class="section-subtitle" style="margin: 0 auto;">
                We use reliable programming languages, frameworks, databases and cloud-ready technologies to build secure, scalable and maintainable digital products.
            </p>
        </div>

        <div class="tech-categories-grid">
            <!-- Category 1: Frontend -->
            <div class="tech-cat-box">
                <div class="tech-cat-header">
                    <div class="tech-cat-badge-icon">🎨</div>
                    <h3 class="tech-cat-title">Frontend Engineering</h3>
                </div>
                <div class="tech-items-stack">
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🌐</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">HTML5</span>
                            <p class="tech-item-desc">Semantic, accessible and SEO-friendly structures for high-performance web pages.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🎨</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">CSS3</span>
                            <p class="tech-item-desc">Modern responsive design systems, fluid typography, and smooth hardware-accelerated animations.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">⚡</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">JavaScript</span>
                            <p class="tech-item-desc">Fast, dependency-free interactive UI logic and real-time client capabilities.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">⚛️</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">React.js</span>
                            <p class="tech-item-desc">Component-driven architecture for complex, state-heavy interactive web applications.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 2: Backend -->
            <div class="tech-cat-box">
                <div class="tech-cat-header">
                    <div class="tech-cat-badge-icon">⚙️</div>
                    <h3 class="tech-cat-title">Backend Architecture</h3>
                </div>
                <div class="tech-items-stack">
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🐘</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">PHP</span>
                            <p class="tech-item-desc">Reliable server-side technology for secure, dynamic and scalable web applications.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🟢</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">Node.js</span>
                            <p class="tech-item-desc">Event-driven asynchronous backend runtime for real-time services and scalable APIs.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🐍</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">Python</span>
                            <p class="tech-item-desc">Flexible programming technology for automation, data processing and AI-driven solutions.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">☕</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">Java</span>
                            <p class="tech-item-desc">Enterprise-ready technology for robust, multi-threaded and scalable business applications.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 3: Mobile -->
            <div class="tech-cat-box">
                <div class="tech-cat-header">
                    <div class="tech-cat-badge-icon">📱</div>
                    <h3 class="tech-cat-title">Mobile Development</h3>
                </div>
                <div class="tech-items-stack">
                    <div class="tech-item-row">
                        <span class="tech-item-icon">📲</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">React Native</span>
                            <p class="tech-item-desc">High-performance cross-platform applications delivering native iOS and Android experiences.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🤖</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">Android Native</span>
                            <p class="tech-item-desc">Optimized business and customer applications tailored for Google Android ecosystem.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🍎</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">iOS Solutions</span>
                            <p class="tech-item-desc">Smooth, secure mobile applications aligned with Apple design and security standards.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🔄</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">Offline-First Sync</span>
                            <p class="tech-item-desc">Resilient mobile local storage engines with automatic background cloud synchronization.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 4: Database -->
            <div class="tech-cat-box">
                <div class="tech-cat-header">
                    <div class="tech-cat-badge-icon">🗄️</div>
                    <h3 class="tech-cat-title">Databases & Storage</h3>
                </div>
                <div class="tech-items-stack">
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🐬</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">MySQL</span>
                            <p class="tech-item-desc">Reliable relational database technology for structured and high-performance business data.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🐘</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">PostgreSQL</span>
                            <p class="tech-item-desc">Advanced open-source relational engine built for complex analytical queries and strict ACID guarantees.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🍃</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">MongoDB</span>
                            <p class="tech-item-desc">Flexible document-based NoSQL database for unstructured and rapidly evolving data schemas.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🛡️</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">Encrypted Backups</span>
                            <p class="tech-item-desc">Automated scheduled snapshots with point-in-time recovery for zero operational data loss.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 5: APIs & Integrations -->
            <div class="tech-cat-box">
                <div class="tech-cat-header">
                    <div class="tech-cat-badge-icon">🔌</div>
                    <h3 class="tech-cat-title">APIs & Integrations</h3>
                </div>
                <div class="tech-items-stack">
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🌐</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">REST APIs & JSON</span>
                            <p class="tech-item-desc">Standardized interfaces and lightweight data serialization for secure cross-platform data flow.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">💳</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">Payment Gateway Integration</span>
                            <p class="tech-item-desc">PCI-compliant integration with Razorpay, Cashfree, UPI, Net Banking, and recurring subscriptions.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">💬</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">WhatsApp Cloud API</span>
                            <p class="tech-item-desc">Official Meta Cloud API hooks for automated messaging, catalog triggers, and multi-agent inboxes.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🔄</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">Third-Party API Integration</span>
                            <p class="tech-item-desc">Clean integration with external CRMs, SMS gateways, ERP systems, and cloud storage.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 6: Cloud & DevOps -->
            <div class="tech-cat-box">
                <div class="tech-cat-header">
                    <div class="tech-cat-badge-icon">☁️</div>
                    <h3 class="tech-cat-title">Cloud & DevOps</h3>
                </div>
                <div class="tech-items-stack">
                    <div class="tech-item-row">
                        <span class="tech-item-icon">☁️</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">Cloud Infrastructure</span>
                            <p class="tech-item-desc">High-availability cloud hosting, auto-scaling, server firewalls, and resilient configurations.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🐙</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">Git & GitHub</span>
                            <p class="tech-item-desc">Distributed version control, branch-based deployments, and collaborative engineering safety.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">🔒</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">SSL & Enterprise Security</span>
                            <p class="tech-item-desc">Enforced TLS/SSL encryption, CSRF protection, SQL injection prevention, and session hardening.</p>
                        </div>
                    </div>
                    <div class="tech-item-row">
                        <span class="tech-item-icon">⚡</span>
                        <div class="tech-item-meta">
                            <span class="tech-item-name">Performance Optimization</span>
                            <p class="tech-item-desc">Gzip/Brotli compression, static asset caching, database indexing, and sub-second load times.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     10. WHY GRIHGO (6 PILLARS)
     ========================================================================== -->
<section class="section" style="background-color: var(--bg-white);" id="why-grihgo">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">The GrihGO Advantage</span>
            <h2 class="section-title">Why GrihGO?</h2>
            <p class="section-subtitle">
                Six foundational principles that make GrihGO Digital & Tech the preferred digital partner for ambitious companies.
            </p>
        </div>

        <div class="why-grid">
            <div class="why-card">
                <div class="why-icon">🎯</div>
                <h3>Customized Solutions</h3>
                <p>No cookie-cutter templates. We configure software, databases, and digital strategies tailored specifically to your unique business operations.</p>
            </div>
            <div class="why-card">
                <div class="why-icon">📈</div>
                <h3>Scalable Technology</h3>
                <p>Architectures engineered from day one to handle high concurrent users, growing transaction volumes, and multi-location expansion without lag.</p>
            </div>
            <div class="why-card">
                <div class="why-icon">🤖</div>
                <h3>AI-Powered Workflows</h3>
                <p>We infuse modern intelligence into daily operations, from automated lead routing to automated customer servicing via WhatsApp.</p>
            </div>
            <div class="why-card">
                <div class="why-icon">💼</div>
                <h3>Business Focused</h3>
                <p>We judge technical success by commercial impact — reduced overhead hours, faster inventory turnarounds, and elevated sales conversion rates.</p>
            </div>
            <div class="why-card">
                <div class="why-icon">🛠️</div>
                <h3>Dedicated Technical Support</h3>
                <p>Real human engineers standing by. Quick ticket response times, continuous server monitoring, and scheduled security updates.</p>
            </div>
            <div class="why-card">
                <div class="why-icon">🌐</div>
                <h3>One Technology Partner</h3>
                <p>From your corporate domain to custom ERP, payment gateways, and Meta advertising campaigns, get everything coordinated by one accountable team.</p>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     11. WORK PROCESS (01 TO 07 ANIMATED TIMELINE)
     ========================================================================== -->
<section class="section" id="work-process">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Methodology</span>
            <h2 class="section-title">A Structured Path From <span class="gradient-text">Concept to Scale</span></h2>
            <p class="section-subtitle">
                Our proven seven-step delivery framework ensures transparent milestones, zero scope surprises, and on-time launches.
            </p>
        </div>

        <div class="process-timeline">
            <!-- 01 DISCOVER -->
            <div class="process-step-item">
                <div class="step-number-circle">01</div>
                <h4 class="process-step-title">DISCOVER</h4>
                <p class="process-step-desc">Audit current workflows, pain-points, and technical requirements.</p>
            </div>

            <!-- 02 PLAN -->
            <div class="process-step-item">
                <div class="step-number-circle">02</div>
                <h4 class="process-step-title">PLAN</h4>
                <p class="process-step-desc">Architect the data models, system boundaries, and project roadmap.</p>
            </div>

            <!-- 03 DESIGN -->
            <div class="process-step-item">
                <div class="step-number-circle">03</div>
                <h4 class="process-step-title">DESIGN</h4>
                <p class="process-step-desc">Craft clean, intuitive, and conversion-optimized UI/UX flows.</p>
            </div>

            <!-- 04 DEVELOP -->
            <div class="process-step-item">
                <div class="step-number-circle">04</div>
                <h4 class="process-step-title">DEVELOP</h4>
                <p class="process-step-desc">Engineer resilient, clean code using secure PHP and vanilla frontend standards.</p>
            </div>

            <!-- 05 TEST -->
            <div class="process-step-item">
                <div class="step-number-circle">05</div>
                <h4 class="process-step-title">TEST</h4>
                <p class="process-step-desc">Rigorous QA for responsiveness, security vulnerabilities, and edge cases.</p>
            </div>

            <!-- 06 LAUNCH -->
            <div class="process-step-item">
                <div class="step-number-circle">06</div>
                <h4 class="process-step-title">LAUNCH</h4>
                <p class="process-step-desc">Production server deployment, DNS configuration, and live validation.</p>
            </div>

            <!-- 07 GROW -->
            <div class="process-step-item">
                <div class="step-number-circle">07</div>
                <h4 class="process-step-title">GROW</h4>
                <p class="process-step-desc">Continuous monitoring, marketing acquisition, and feature iterations.</p>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     12. PROVEN ENGINEERING IN ACTION (IMAGE + CONTENT SHOWCASE & DEPLOYMENTS)
     ========================================================================== -->
<section class="section proven-eng-section" id="case-studies">
    <div class="container">
        <!-- Flagship Image + Content Layout -->
        <div class="proven-eng-grid" style="margin-bottom: 64px;">
            <!-- Left: Professional technology/engineering image -->
            <div class="proven-eng-visual-wrap">
                <img src="assets/images/portfolio/proven-engineering.jpg" 
                     alt="Software engineering and digital technology solutions by GrihGO Digital & Tech" 
                     class="proven-eng-img"
                     loading="lazy"
                     width="640"
                     height="420">
                <div class="proven-eng-overlay-badge">
                    <span class="pulse-indicator"></span>
                    <span style="font-size: 0.825rem; font-weight: 600; color: #ffffff;">Modern Cloud &amp; AI Engineering</span>
                </div>
            </div>

            <!-- Right: Heading, Content, Technology Tags, CTA -->
            <div class="proven-eng-content">
                <span class="section-tag">Practical Innovation</span>
                <h2 class="section-title">Proven Engineering in Action</h2>
                <p class="proven-eng-text">
                    From business websites and custom software to CRM platforms, AI automation and digital marketing systems, our solutions are designed around real business requirements. We combine structured development, scalable architecture and practical technology to create digital systems that are reliable, maintainable and built for growth.
                </p>

                <div class="proven-eng-tags-title">Technology &amp; Architectural Stack</div>
                <div class="proven-eng-tags">
                    <span class="proven-tag-pill">HTML5</span>
                    <span class="proven-tag-pill">CSS3</span>
                    <span class="proven-tag-pill">JavaScript</span>
                    <span class="proven-tag-pill">PHP</span>
                    <span class="proven-tag-pill">React.js</span>
                    <span class="proven-tag-pill">Node.js</span>
                    <span class="proven-tag-pill">Python</span>
                    <span class="proven-tag-pill">Java</span>
                    <span class="proven-tag-pill">React Native</span>
                    <span class="proven-tag-pill">MySQL</span>
                    <span class="proven-tag-pill">PostgreSQL</span>
                    <span class="proven-tag-pill">MongoDB</span>
                    <span class="proven-tag-pill">REST APIs</span>
                    <span class="proven-tag-pill">Cloud</span>
                    <span class="proven-tag-pill">AI &amp; Automation</span>
                </div>

                <div class="proven-eng-actions">
                    <a href="services.php" class="btn btn-primary btn-lg">
                        <span>View Services</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                    <a href="appointment.php" class="btn btn-outline-white btn-lg">
                        <span>Explore Our Services &amp; Consult</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="section-header" style="text-align: center; margin-bottom: 40px;">
            <span class="section-tag">Case Studies</span>
            <h3 style="color: #ffffff; font-size: 1.5rem; margin-bottom: 8px;">Featured Client Implementations</h3>
            <p style="color: #94a3b8; font-size: 0.95rem; margin: 0 auto; max-width: 600px;">
                Representative deployments demonstrating measurable operational gains and reliability.
            </p>
        </div>

        <div class="portfolio-grid">
            <?php
            $livePortfolio = [];
            try {
                $dbConn = Database::getConnection();
                $livePortfolio = $dbConn->query("SELECT * FROM portfolio WHERE status = 'published' ORDER BY sort_order ASC, id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {}
            if (!empty($livePortfolio)):
                foreach ($livePortfolio as $portItem):
            ?>
                <div class="portfolio-card">
                    <div class="portfolio-img-box">
                        <span class="portfolio-badge-industry"><?php echo htmlspecialchars($portItem['category'] ?? 'Enterprise'); ?></span>
                        <div class="portfolio-screen-mockup">
                            <div class="mockup-bar-line" style="width: 55%; background: var(--primary-blue);"></div>
                            <div class="mockup-grid-lines">
                                <div class="mockup-bar-line"></div>
                                <div class="mockup-bar-line" style="background: var(--secondary-green);"></div>
                            </div>
                            <div class="mockup-bar-line" style="width: 80%;"></div>
                        </div>
                    </div>
                    <div class="portfolio-body">
                        <h3 class="portfolio-title"><?php echo htmlspecialchars($portItem['title']); ?></h3>
                        <p class="portfolio-solution">
                            <?php echo htmlspecialchars($portItem['description']); ?>
                        </p>
                        <?php if (!empty($portItem['impact_metrics'])): ?>
                            <div class="portfolio-impact-box">
                                ✓ <?php echo htmlspecialchars($portItem['impact_metrics']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="portfolio-card-footer">
                            <span class="portfolio-tech-tag"><?php echo htmlspecialchars($portItem['technologies'] ?? 'PHP • MySQL • Vanilla JS'); ?></span>
                            <a href="<?php echo !empty($portItem['live_url']) ? htmlspecialchars($portItem['live_url']) : 'appointment.php'; ?>" class="biz-card-link">Discuss Similar &rarr;</a>
                        </div>
                    </div>
                </div>
            <?php
                endforeach;
            else:
            ?>
                <!-- Card 1 -->
                <div class="portfolio-card">
                    <div class="portfolio-img-box">
                        <span class="portfolio-badge-industry">Retail & Logistics</span>
                        <div class="portfolio-screen-mockup">
                            <div class="mockup-bar-line" style="width: 50%;"></div>
                            <div class="mockup-grid-lines">
                                <div class="mockup-bar-line"></div>
                                <div class="mockup-bar-line"></div>
                            </div>
                            <div class="mockup-bar-line" style="width: 75%; background: var(--secondary-green);"></div>
                        </div>
                    </div>
                    <div class="portfolio-body">
                        <h3 class="portfolio-title">Multi-Warehouse Inventory & GST Engine</h3>
                        <p class="portfolio-solution">
                            Centralized inventory control sync across 5 branches with instant GST barcode invoicing and automated low-stock reorder triggers.
                        </p>
                        <div class="portfolio-impact-box">
                            ✓ Eliminated manual reconciliation delays; automated daily dispatch.
                        </div>
                        <div class="portfolio-card-footer">
                            <span class="portfolio-tech-tag">PHP • MySQL • Vanilla JS</span>
                            <a href="appointment.php" class="biz-card-link">Discuss Similar &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="portfolio-card">
                    <div class="portfolio-img-box">
                        <span class="portfolio-badge-industry">Real Estate</span>
                        <div class="portfolio-screen-mockup">
                            <div class="mockup-bar-line" style="width: 60%; background: #0099ff;"></div>
                            <div class="mockup-grid-lines">
                                <div class="mockup-bar-line"></div>
                                <div class="mockup-bar-line"></div>
                            </div>
                            <div class="mockup-bar-line" style="width: 90%;"></div>
                        </div>
                    </div>
                    <div class="portfolio-body">
                        <h3 class="portfolio-title">WhatsApp Automated Lead Pipeline</h3>
                        <p class="portfolio-solution">
                            Automated lead capture system from Meta Ads directly into WhatsApp team inbox with instant brochure delivery and agent call routing.
                        </p>
                        <div class="portfolio-impact-box">
                            ✓ Reduced initial response time from 3 hours to 15 seconds.
                        </div>
                        <div class="portfolio-card-footer">
                            <span class="portfolio-tech-tag">WhatsApp API • Meta Ads</span>
                            <a href="appointment.php" class="biz-card-link">Discuss Similar &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="portfolio-card">
                    <div class="portfolio-img-box">
                        <span class="portfolio-badge-industry">Healthcare</span>
                        <div class="portfolio-screen-mockup">
                            <div class="mockup-bar-line" style="width: 45%;"></div>
                            <div class="mockup-grid-lines">
                                <div class="mockup-bar-line"></div>
                                <div class="mockup-bar-line" style="background: var(--secondary-green);"></div>
                            </div>
                            <div class="mockup-bar-line" style="width: 80%;"></div>
                        </div>
                    </div>
                    <div class="portfolio-body">
                        <h3 class="portfolio-title">Multi-Doctor Appointment & EMR Portal</h3>
                        <p class="portfolio-solution">
                            Digital outpatient queue management with doctor scheduling, digital prescription PDF dispatch, and patient SMS notifications.
                        </p>
                        <div class="portfolio-impact-box">
                            ✓ Streamlined OPD waiting hall times and enabled zero-paper records.
                        </div>
                        <div class="portfolio-card-footer">
                            <span class="portfolio-tech-tag">PHP • AJAX • REST API</span>
                            <a href="appointment.php" class="biz-card-link">Discuss Similar &rarr;</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
$liveTestimonials = [];
try {
    if (!isset($dbConn)) $dbConn = Database::getConnection();
    $testimonialQuery = $dbConn->prepare("SELECT * FROM testimonials WHERE status = 'published' AND content NOT IN (?, ?, ?) ORDER BY sort_order ASC, id DESC LIMIT 6");
    $testimonialQuery->execute([
        'GrihGO Digital & Tech transformed our commercial digital workflow. The custom web architecture and WhatsApp CRM have supercharged our lead response times by 300%. Outstanding technical delivery!',
        'The fleet enterprise ERP developed by GrihGO handles over 2,000 dispatches daily without a glitch. The system stability and intuitive interface are best-in-class.',
        'Exceptional development capabilities. Their team delivered our multi-branch inventory engine ahead of schedule with flawless data migration.',
    ]);
    $liveTestimonials = $testimonialQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $liveTestimonials = [];
}
?>
<?php if (!empty($liveTestimonials)): ?>
<!-- ==========================================================================
     13. TESTIMONIALS
     ========================================================================== -->
<section class="section" id="testimonials">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Client Feedback</span>
            <h2 class="section-title">Trusted By Forward-Thinking <span class="gradient-text">Businesses</span></h2>
            <p class="section-subtitle">
                Here is what business leaders say about partner execution, reliability, and technical transparency.
            </p>
        </div>

        <div class="testimonials-grid">
            <?php
                foreach ($liveTestimonials as $tstItem):
                    $stars = str_repeat('★', max(1, min(5, (int)$tstItem['rating'])));
                    $initials = '';
                    $words = explode(' ', trim($tstItem['client_name']));
                    foreach ($words as $w) {
                        if (!empty($w)) $initials .= strtoupper($w[0]);
                    }
                    $initials = mb_substr($initials, 0, 2);
            ?>
                <div class="testimonial-card">
                    <div class="testimonial-stars"><?php echo $stars; ?></div>
                    <p class="testimonial-quote">
                        "<?php echo htmlspecialchars($tstItem['content']); ?>"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar"><?php echo htmlspecialchars($initials ?: 'CL'); ?></div>
                        <div class="author-info">
                            <strong><?php echo htmlspecialchars($tstItem['client_name']); ?></strong>
                            <span><?php echo htmlspecialchars($tstItem['designation'] ?? ''); ?><?php echo !empty($tstItem['company_name']) ? ', ' . htmlspecialchars($tstItem['company_name']) : ''; ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==========================================================================
     14. FAQ ACCORDION
     ========================================================================== -->
<section class="section" style="background-color: var(--bg-white);" id="faq">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Clear Answers</span>
            <h2 class="section-title">Frequently Asked <span class="gradient-text">Questions</span></h2>
            <p class="section-subtitle">
                Everything you need to know about our services, software deployment, and partnership models.
            </p>
        </div>

        <div class="faq-wrapper" role="region" aria-label="FAQ Accordion">
            <!-- Item 1 -->
            <div class="faq-item active">
                <button type="button" class="faq-question-btn" aria-expanded="true">
                    <span>What types of website development does GrihGO offer?</span>
                    <svg class="faq-icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="faq-answer-panel">
                    <div class="faq-answer-content">
                        We build high-performance corporate brand portals, custom e-commerce systems, landing pages, and interactive PHP web applications. Every website is built with clean semantic code, strict responsive design, and built-in search engine optimization.
                    </div>
                </div>
            </div>

            <!-- Item 2 -->
            <div class="faq-item">
                <button type="button" class="faq-question-btn" aria-expanded="false">
                    <span>How does GrihGO WhatsApp CRM help my business team?</span>
                    <svg class="faq-icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="faq-answer-panel">
                    <div class="faq-answer-content">
                        GrihGO WhatsApp CRM connects your official business WhatsApp number to a centralized multi-agent team inbox. Your sales and support reps can chat simultaneously, tag leads, send automated replies, trigger follow-ups, and review conversation reports without needing separate phones.
                    </div>
                </div>
            </div>

            <!-- Item 3 -->
            <div class="faq-item">
                <button type="button" class="faq-question-btn" aria-expanded="false">
                    <span>Is the Billing Software fully GST compliant?</span>
                    <svg class="faq-icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="faq-answer-panel">
                    <div class="faq-answer-content">
                        Yes, our Billing Software generates official GST tax invoices with HSN/SAC codes, CGST, SGST, and IGST breakdowns. It includes instant PDF creation, thermal receipt printing, inventory deduction, and automated GSTR-ready sales reports.
                    </div>
                </div>
            </div>

            <!-- Item 4 -->
            <div class="faq-item">
                <button type="button" class="faq-question-btn" aria-expanded="false">
                    <span>Can we customize the business software to match our exact workflow?</span>
                    <svg class="faq-icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="faq-answer-panel">
                    <div class="faq-answer-content">
                        Absolutely. Unlike rigid off-the-shelf software where you must adapt to the tool, GrihGO customizes user permissions, input fields, invoice templates, and approval hierarchies to mirror your exact operations.
                    </div>
                </div>
            </div>

            <!-- Item 5 -->
            <div class="faq-item">
                <button type="button" class="faq-question-btn" aria-expanded="false">
                    <span>What AI solutions can you integrate into existing operations?</span>
                    <svg class="faq-icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="faq-answer-panel">
                    <div class="faq-answer-content">
                        We deploy customer-facing AI Chatbots, automated WhatsApp lead qualification flows, predictive lead scoring, automated email/content generators, and custom AI business assistants trained on your internal documentation.
                    </div>
                </div>
            </div>

            <!-- Item 6 -->
            <div class="faq-item">
                <button type="button" class="faq-question-btn" aria-expanded="false">
                    <span>How do you handle Digital Marketing and SEO?</span>
                    <svg class="faq-icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="faq-answer-panel">
                    <div class="faq-answer-content">
                        Our marketing team focuses strictly on conversion. We manage Meta Ads (Facebook & Instagram) and Google Search Ads to capture immediate buyer intent, while executing technical on-page SEO and Google Business Profile optimization to ensure long-term organic rankings.
                    </div>
                </div>
            </div>

            <!-- Item 7 -->
            <div class="faq-item">
                <button type="button" class="faq-question-btn" aria-expanded="false">
                    <span>How can I schedule an architectural consultation or software demo?</span>
                    <svg class="faq-icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="faq-answer-panel">
                    <div class="faq-answer-content">
                        Simply visit our <a href="appointment.php" style="font-weight: 600;">Book Appointment</a> page, pick your preferred service and date, and our technical director will confirm a live walkthrough via email and WhatsApp.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     15. BOOK APPOINTMENT CTA
     ========================================================================== -->
<section class="section" id="cta">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Ready to Build Your <span class="gradient-text">Digital Future?</span></h2>
            <p>
                Connect directly with our senior technology team to explore custom software, AI automations, and growth marketing engineered for measurable ROI.
            </p>
            <div class="btn-group">
                <a href="appointment.php" class="btn btn-primary btn-lg">
                    <span>Book an Appointment</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </a>
                <a href="contact.php" class="btn btn-outline-white btn-lg">
                    <span>Talk to Our Team</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
