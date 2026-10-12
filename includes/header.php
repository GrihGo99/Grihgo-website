<?php
// Ensure $base_url is defined
if (!isset($base_url)) {
    $base_url = './';
}
if (!isset($page_title)) {
    $page_title = 'GrihGO Digital & Tech | Technology • Innovation • Growth';
}
if (!isset($meta_desc)) {
    $meta_desc = 'GrihGO Digital & Tech delivers IT services, AI solutions, business software, digital marketing and customized technology solutions designed to help businesses grow.';
}
if (!isset($current_page)) {
    $current_page = 'home';
}

// Load Core Backend & Security Utilities
require_once __DIR__ . '/../config/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(getCsrfToken()); ?>">
    <script>
        window.GRIHGO_CONFIG = {
            baseUrl: '<?php echo $base_url; ?>',
            csrfToken: '<?php echo htmlspecialchars(getCsrfToken()); ?>'
        };
    </script>
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <meta name="keywords" content="GrihGO Digital & Tech, IT services, AI solutions, business software, WhatsApp CRM, GST billing software, website development, application development, digital marketing, SEO, Patna, Bihar, India">
    <meta name="author" content="GrihGO Digital & Tech">
    <?php if (!empty($meta_robots)): ?>
        <meta name="robots" content="<?php echo htmlspecialchars($meta_robots); ?>">
    <?php endif; ?>
    
    <!-- Open Graph / Meta -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <meta property="og:image" content="<?php echo $base_url; ?>assets/images/logo/logo.png">
    
    <?php
    $schemaWebsite = rtrim(getSetting('website', 'https://grihgo.com'), '/');
    $schemaLogo = $schemaWebsite . '/assets/images/logo/logo.png';
    $schemaPhone = getSetting('phone', '8092932060');
    $schemaEmail = getSetting('email', 'contact@grihgo.com');
    $schemaCompany = getSetting('company_name', 'GrihGO Digital & Tech');
    $schemaSocialProfiles = array_values(array_filter([
        getSetting('social_instagram', 'https://instagram.com/grihgodigital'),
        getSetting('social_facebook', 'https://facebook.com/grihgodigital'),
        getSetting('social_linkedin', 'https://linkedin.com/company/grihgodigital'),
        getSetting('social_youtube', 'https://youtube.com/@grihgodigital'),
        getSetting('social_x', 'https://x.com/grihgodigital'),
    ]));
    $schemaData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => $schemaWebsite . '/#organization',
                'name' => $schemaCompany,
                'url' => $schemaWebsite,
                'logo' => $schemaLogo,
                'description' => 'GrihGO Digital & Tech delivers IT services, AI solutions, business software, digital marketing and customized technology solutions designed to help businesses grow.',
                'telephone' => $schemaPhone,
                'email' => $schemaEmail,
                'sameAs' => $schemaSocialProfiles,
            ],
            [
                '@type' => 'LocalBusiness',
                '@id' => $schemaWebsite . '/#localbusiness',
                'name' => $schemaCompany,
                'url' => $schemaWebsite,
                'image' => $schemaLogo,
                'telephone' => $schemaPhone,
                'email' => $schemaEmail,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => getSetting('office_address', ''),
                    'addressCountry' => 'IN',
                ],
                'priceRange' => '₹₹',
            ],
        ],
    ];
    ?>
    <script type="application/ld+json"><?php echo json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo $base_url; ?>assets/images/logo/favicon.png">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/images/logo/favicon.ico">
    
    <!-- Google Fonts: Outfit (Display & Headings) + Inter (Clean UI & Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Main & Responsive CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/responsive.css">
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Top Announcement / Contact Bar (Subtle & Professional) -->
    <div class="top-bar">
        <div class="container top-bar-container">
            <div class="top-bar-left">
                <span class="pulse-indicator"></span>
                <span class="top-bar-text">Empowering Modern Enterprises with AI & Smart Technology</span>
            </div>
            <div class="top-bar-right">
                <a href="mailto:<?php echo htmlspecialchars(getSetting('email', 'contact@grihgo.com')); ?>" class="top-bar-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <span><?php echo htmlspecialchars(getSetting('email', 'contact@grihgo.com')); ?></span>
                </a>
                <span class="top-bar-divider">|</span>
                <a href="tel:<?php echo htmlspecialchars(getSetting('phone', '8092932060')); ?>" class="top-bar-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <span>Call: <?php echo htmlspecialchars(getSetting('phone', '8092932060')); ?></span>
                </a>
                <span class="top-bar-divider">|</span>
                <a href="https://wa.me/<?php echo htmlspecialchars(getWhatsAppNumber(), ENT_QUOTES, 'UTF-8'); ?>?text=Hello%20GrihGO%20Digital%20%26%20Tech%2C%20I%20would%20like%20to%20know%20more%20about%20your%20technology%20and%20digital%20solutions.%20Please%20help%20me%20with%20the%20right%20solution%20for%20my%20business." target="_blank" rel="noopener noreferrer" class="top-bar-link top-bar-wa">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.299.144.347.491 1.2.534 1.288.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.353.101.173.45 0.742.966 1.201.664.591 1.224.774 1.397.86.173.086.274.072.376-.043.101-.116.433-.506.549-.679.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
                    <span>Quick WhatsApp</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Header & Navigation -->
    <header class="site-header" id="site-header">
        <div class="container header-container">
            <!-- Brand Logo -->
            <a href="<?php echo $base_url; ?>index.php" class="brand-logo" aria-label="GrihGO Digital & Tech Homepage">
                <img src="<?php echo $base_url; ?>assets/images/logo/logo.png" alt="GrihGO Digital & Tech Logo" class="logo-image" width="170" height="48">
            </a>

            <!-- Desktop Navigation -->
            <nav class="main-nav" id="main-nav" aria-label="Main Navigation">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>index.php" class="nav-link <?php echo ($current_page === 'home') ? 'active' : ''; ?>">
                            <span>Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>about.php" class="nav-link <?php echo ($current_page === 'about') ? 'active' : ''; ?>">
                            <span>About Us</span>
                        </a>
                    </li>
                    <li class="nav-item has-dropdown">
                        <a href="<?php echo $base_url; ?>services.php" class="nav-link <?php echo ($current_page === 'services' || strpos($current_page, 'service_') === 0) ? 'active' : ''; ?>" aria-haspopup="true" aria-expanded="false">
                            <span>Services</span>
                            <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                        </a>
                        <div class="dropdown-menu mega-dropdown" aria-label="Services Submenu">
                            <div class="dropdown-grid">
                                <div class="dropdown-col">
                                    <div class="dropdown-col-title">
                                        <span class="badge-dot blue"></span>
                                        IT Services
                                    </div>
                                    <ul class="dropdown-sublist">
                                        <li>
                                            <a href="<?php echo $base_url; ?>services/website-development.php" class="dropdown-link">
                                                <div class="drop-icon">💻</div>
                                                <div class="drop-text">
                                                    <strong>Website Development</strong>
                                                    <span>Corporate, E-Commerce, Web Apps</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo $base_url; ?>services/application-development.php" class="dropdown-link">
                                                <div class="drop-icon">📱</div>
                                                <div class="drop-text">
                                                    <strong>Application Development</strong>
                                                    <span>Android, iOS & Enterprise Mobile Apps</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo $base_url; ?>services/software-development.php" class="dropdown-link">
                                                <div class="drop-icon">⚙️</div>
                                                <div class="drop-text">
                                                    <strong>Software Development</strong>
                                                    <span>Custom ERP, CRM & Business Tools</span>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="dropdown-col">
                                    <div class="dropdown-col-title">
                                        <span class="badge-dot green"></span>
                                        AI & Marketing
                                    </div>
                                    <ul class="dropdown-sublist">
                                        <li>
                                            <a href="<?php echo $base_url; ?>services/ai-solutions.php" class="dropdown-link">
                                                <div class="drop-icon">🤖</div>
                                                <div class="drop-text">
                                                    <strong>AI Solutions</strong>
                                                    <span>Chatbots, Workflows & Automation</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo $base_url; ?>services/digital-marketing.php" class="dropdown-link">
                                                <div class="drop-icon">📈</div>
                                                <div class="drop-text">
                                                    <strong>Digital Marketing</strong>
                                                    <span>Performance Ads, Social & Leads</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo $base_url; ?>services/seo.php" class="dropdown-link">
                                                <div class="drop-icon">🎯</div>
                                                <div class="drop-text">
                                                    <strong>SEO Services</strong>
                                                    <span>Technical, On-Page & Local Growth</span>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="dropdown-featured-card">
                                    <div class="featured-badge">Featured</div>
                                    <h4>Custom Tech Consultation</h4>
                                    <p>Find the exact digital architecture tailored to your business scale.</p>
                                    <a href="<?php echo $base_url; ?>appointment.php" class="btn btn-sm btn-outline-primary">Book Session</a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item has-dropdown">
                        <a href="<?php echo $base_url; ?>products.php" class="nav-link <?php echo ($current_page === 'products' || strpos($current_page, 'product_') === 0) ? 'active' : ''; ?>" aria-haspopup="true" aria-expanded="false">
                            <span>Products</span>
                            <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                        </a>
                        <div class="dropdown-menu mega-dropdown products-mega" aria-label="Products Submenu">
                            <div class="dropdown-grid product-grid-menu">
                                <div class="dropdown-col">
                                    <div class="dropdown-col-title">Flagship SaaS</div>
                                    <ul class="dropdown-sublist">
                                        <li>
                                            <a href="<?php echo $base_url; ?>products/whatsapp-crm.php" class="dropdown-link">
                                                <div class="drop-icon wa">💬</div>
                                                <div class="drop-text">
                                                    <strong>GrihGO WhatsApp CRM</strong>
                                                    <span>Team Inbox, Auto-Replies & Broadcasts</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo $base_url; ?>products/billing-software.php" class="dropdown-link">
                                                <div class="drop-icon bill">🧾</div>
                                                <div class="drop-text">
                                                    <strong>GST Billing Software</strong>
                                                    <span>Invoicing, Inventory & GST Reports</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo $base_url; ?>products/crm.php" class="dropdown-link">
                                                <div class="drop-icon crm">📊</div>
                                                <div class="drop-text">
                                                    <strong>Enterprise CRM</strong>
                                                    <span>Lead Lifecycle & Client Pipeline</span>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="dropdown-col">
                                    <div class="dropdown-col-title">Industry Solutions</div>
                                    <ul class="dropdown-sublist">
                                        <li>
                                            <a href="<?php echo $base_url; ?>products/erp.php" class="dropdown-link">
                                                <div class="drop-icon erp">🏢</div>
                                                <div class="drop-text">
                                                    <strong>Integrated ERP</strong>
                                                    <span>Complete Enterprise Operations</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo $base_url; ?>products/school-management.php" class="dropdown-link">
                                                <div class="drop-icon">🎓</div>
                                                <div class="drop-text">
                                                    <strong>School Management</strong>
                                                    <span>Admissions, Fees & Student Portal</span>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo $base_url; ?>products/hospital-management.php" class="dropdown-link">
                                                <div class="drop-icon">🏥</div>
                                                <div class="drop-text">
                                                    <strong>Hospital Management</strong>
                                                    <span>EMR, Appointments & Pharmacy</span>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="dropdown-col view-all-col">
                                    <div class="dropdown-col-title">Explore All 14 Systems</div>
                                    <p class="view-all-desc">Discover our comprehensive suite of ready-to-deploy business software.</p>
                                    <a href="<?php echo $base_url; ?>products.php" class="btn btn-sm btn-primary w-100">Browse All Products &rarr;</a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>contact.php" class="nav-link <?php echo ($current_page === 'contact') ? 'active' : ''; ?>">
                            <span>Contact Us</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Header Action CTAs -->
            <div class="header-actions">
                <?php if (!empty($_SESSION['admin_id'])): ?>
                    <?php if (($_SESSION['admin_role'] ?? '') === 'manager'): ?>
                        <a href="<?php echo $base_url; ?>manager/dashboard.php" class="btn btn-ghost login-btn" aria-label="Manager Console">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                            <span>Manager Console</span>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>admin/dashboard.php" class="btn btn-ghost login-btn" aria-label="Admin Console">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                            <span>Admin Console</span>
                        </a>
                    <?php endif; ?>
                <?php elseif (!empty($_SESSION['client_id'])): ?>
                    <a href="<?php echo $base_url; ?>client/dashboard.php" class="btn btn-ghost login-btn" aria-label="Client Portal">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <span>Client Portal</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $base_url; ?>login.php" class="btn btn-ghost login-btn <?php echo ($current_page === 'login') ? 'active' : ''; ?>" aria-label="Client Login Portal">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <span>Client Login</span>
                    </a>
                <?php endif; ?>
                <a href="<?php echo $base_url; ?>appointment.php" class="btn btn-primary cta-btn">
                    <span>Book Appointment</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>

                <!-- Mobile Menu Hamburger Button -->
                <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Navigation Menu" aria-controls="mobileMenuDrawer" aria-expanded="false">
                    <span class="hamburger-bar"></span>
                    <span class="hamburger-bar"></span>
                    <span class="hamburger-bar"></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Drawer Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
    <div class="mobile-menu-drawer" id="mobileMenuDrawer" aria-hidden="true">
        <div class="mobile-drawer-header">
            <img src="<?php echo $base_url; ?>assets/images/logo/logo.png" alt="GrihGO Digital & Tech" class="mobile-drawer-logo">
            <button type="button" class="mobile-drawer-close" id="mobileDrawerClose" aria-label="Close Navigation Menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="mobile-drawer-body">
            <ul class="mobile-nav-list">
                <li><a href="<?php echo $base_url; ?>index.php" class="mobile-nav-link <?php echo ($current_page === 'home') ? 'active' : ''; ?>">Home</a></li>
                <li><a href="<?php echo $base_url; ?>about.php" class="mobile-nav-link <?php echo ($current_page === 'about') ? 'active' : ''; ?>">About Us</a></li>
                <li class="mobile-accordion-item">
                    <button type="button" class="mobile-accordion-btn">
                        <span>Services</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <ul class="mobile-sublist">
                        <li><a href="<?php echo $base_url; ?>services.php">All Services Overview</a></li>
                        <li><a href="<?php echo $base_url; ?>services/website-development.php">Website Development</a></li>
                        <li><a href="<?php echo $base_url; ?>services/application-development.php">Application Development</a></li>
                        <li><a href="<?php echo $base_url; ?>services/software-development.php">Software Development</a></li>
                        <li><a href="<?php echo $base_url; ?>services/ai-solutions.php">AI Solutions</a></li>
                        <li><a href="<?php echo $base_url; ?>services/digital-marketing.php">Digital Marketing</a></li>
                        <li><a href="<?php echo $base_url; ?>services/seo.php">SEO Services</a></li>
                    </ul>
                </li>
                <li class="mobile-accordion-item">
                    <button type="button" class="mobile-accordion-btn">
                        <span>Products</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <ul class="mobile-sublist">
                        <li><a href="<?php echo $base_url; ?>products.php">All Products Hub (14 Systems)</a></li>
                        <li><a href="<?php echo $base_url; ?>products/whatsapp-crm.php">GrihGO WhatsApp CRM</a></li>
                        <li><a href="<?php echo $base_url; ?>products/billing-software.php">Billing Software (GST)</a></li>
                        <li><a href="<?php echo $base_url; ?>products/crm.php">Enterprise CRM</a></li>
                        <li><a href="<?php echo $base_url; ?>products/inventory.php">Inventory Management</a></li>
                        <li><a href="<?php echo $base_url; ?>products/erp.php">Enterprise ERP</a></li>
                        <li><a href="<?php echo $base_url; ?>products/hr-payroll.php">HR & Payroll</a></li>
                        <li><a href="<?php echo $base_url; ?>products/school-management.php">School Management</a></li>
                        <li><a href="<?php echo $base_url; ?>products/hospital-management.php">Hospital Management</a></li>
                        <li><a href="<?php echo $base_url; ?>products/restaurant-management.php">Restaurant Management</a></li>
                        <li><a href="<?php echo $base_url; ?>products/real-estate-crm.php">Real Estate CRM</a></li>
                    </ul>
                </li>
                <li><a href="<?php echo $base_url; ?>contact.php" class="mobile-nav-link <?php echo ($current_page === 'contact') ? 'active' : ''; ?>">Contact Us</a></li>
            </ul>
            <div class="mobile-drawer-ctas">
                <a href="<?php echo $base_url; ?>appointment.php" class="btn btn-primary w-100">Book Appointment</a>
                <?php if (!empty($_SESSION['admin_id'])): ?>
                    <a href="<?php echo $base_url; ?>admin/dashboard.php" class="btn btn-outline-primary w-100">Admin Console</a>
                <?php elseif (!empty($_SESSION['client_id'])): ?>
                    <a href="<?php echo $base_url; ?>client/dashboard.php" class="btn btn-outline-primary w-100">Client Portal</a>
                <?php else: ?>
                    <a href="<?php echo $base_url; ?>login.php" class="btn btn-outline-primary w-100">Client Portal Login</a>
                <?php endif; ?>
            </div>
            <div class="mobile-drawer-contact">
                <p>Email: <?php echo htmlspecialchars(getSetting('email', 'contact@grihgo.com')); ?></p>
                <p>Phone: <?php echo htmlspecialchars(getSetting('phone', '8092932060')); ?></p>
                <p>WhatsApp: +<?php echo htmlspecialchars(getWhatsAppNumber(), ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="tagline-sm">Technology • Innovation • Growth</p>
            </div>
        </div>
    </div>

    <!-- Main Content Container Wrapper -->
    <main id="main-content">
