<?php
/**
 * GrihGO Digital & Tech — Database Connection Manager
 * Version: 2.0 (Part 2 - Backend, Database & Lead Architecture)
 * Technology: PHP 8+, PDO (MySQL / MariaDB with Secure Fallback)
 */

// cPanel deployments can keep credentials outside the document root in
// /home/<account>/grihgo-config.php. Environment variables take precedence.
$privateConfigPath = getenv('GRIHGO_CONFIG_PATH') ?: dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'grihgo-config.php';
$privateConfig = is_file($privateConfigPath) ? require $privateConfigPath : [];
if (!is_array($privateConfig)) {
    $privateConfig = [];
}

foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'INITIAL_ADMIN_EMAIL', 'INITIAL_ADMIN_PASSWORD'] as $configKey) {
    $environmentValue = getenv($configKey);
    if ($environmentValue !== false && $environmentValue !== '') {
        $privateConfig[$configKey] = $environmentValue;
    }
}

if (!defined('APP_ENV')) {
    define('APP_ENV', $privateConfig['APP_ENV'] ?? (getenv('APP_ENV') ?: 'development'));
}
if (!defined('DB_HOST')) {
    define('DB_HOST', $privateConfig['DB_HOST'] ?? '127.0.0.1');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', $privateConfig['DB_PORT'] ?? '3306');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $privateConfig['DB_NAME'] ?? 'grihgo_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', $privateConfig['DB_USER'] ?? 'root');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', $privateConfig['DB_PASSWORD'] ?? '');
}
if (!defined('INITIAL_ADMIN_EMAIL')) {
    define('INITIAL_ADMIN_EMAIL', $privateConfig['INITIAL_ADMIN_EMAIL'] ?? '');
}
if (!defined('INITIAL_ADMIN_PASSWORD')) {
    define('INITIAL_ADMIN_PASSWORD', $privateConfig['INITIAL_ADMIN_PASSWORD'] ?? '');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

class Database {
    private static ?PDO $instance = null;
    private static string $activeDriver = 'mysql';

    /**
     * Get active database connection using PDO.
     *
     * @return PDO
     * @throws RuntimeException If both MySQL and fallback drivers fail.
     */
    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 1,
        ];

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        if (APP_ENV === 'production' && (DB_NAME === 'grihgo_db' || DB_USER === 'root' || DB_PASSWORD === '')) {
            throw new RuntimeException('Production database configuration is incomplete.');
        }

        // 1. Attempt Primary MySQL Connection
        try {
            self::$instance = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
            self::$activeDriver = 'mysql';
            self::seedInitialAdmin(self::$instance);
            return self::$instance;

        } catch (PDOException $e) {
            // Log MySQL connection issue securely (never reveal credentials to client)
            self::logError("MySQL Connection Notice: " . $e->getMessage());

            // Check if server is running but database 'grihgo_db' doesn't exist yet:
            // Attempt to create database automatically on localhost
            if (str_contains($e->getMessage(), 'Unknown database')) {
                try {
                    $rootDsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
                    $rootPdo = new PDO($rootDsn, DB_USER, DB_PASSWORD, $options);
                    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    
                    // Reconnect to newly created database
                    self::$instance = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
                    self::initializeSchema(self::$instance, 'mysql');
                    self::$activeDriver = 'mysql';
                    return self::$instance;
                } catch (PDOException $createEx) {
                    self::logError("MySQL Auto-Create failed: " . $createEx->getMessage());
                }
            }

            // Keep SQLite as a local-development fallback only. Production must
            // fail loudly rather than silently storing submissions in a file.
            if (APP_ENV === 'production') {
                throw new RuntimeException('Production database is unavailable. Check the database setup.');
            }

            try {
                $dbDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database';
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0755, true);
                }
                $sqlitePath = $dbDir . DIRECTORY_SEPARATOR . 'grihgo_local.sqlite';
                self::$instance = new PDO('sqlite:' . $sqlitePath, null, null, $options);
                self::$activeDriver = 'sqlite';
                self::$instance->exec('PRAGMA journal_mode = WAL;');
                self::$instance->exec('PRAGMA busy_timeout = 10000;');
                self::initializeSchema(self::$instance, 'sqlite');
                return self::$instance;

            } catch (Exception $fallbackEx) {
                self::logError("Fatal Database Error: " . $fallbackEx->getMessage());
                throw new RuntimeException("Database service is currently unavailable. Please try again shortly.");
            }
        }
    }

    /** Create the first administrator after the production schema is imported. */
    private static function seedInitialAdmin(PDO $pdo): void {
        $email = trim(INITIAL_ADMIN_EMAIL);
        $password = INITIAL_ADMIN_PASSWORD;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 16) {
            return;
        }

        try {
            if ((int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() !== 0) {
                return;
            }
            $stmt = $pdo->prepare("INSERT INTO admins (full_name, email, password_hash, role, status) VALUES (?, ?, ?, 'super_admin', 'active')");
            $stmt->execute(['Super Administrator', $email, password_hash($password, PASSWORD_DEFAULT)]);
        } catch (PDOException $e) {
            // During initial setup the schema may not have been imported yet.
            self::logError('Initial administrator setup notice: ' . $e->getMessage());
        }
    }

    /**
     * Returns currently active driver ('mysql' or 'sqlite').
     */
    public static function getActiveDriver(): string {
        return self::$activeDriver;
    }

    /**
     * Initializes base tables if they don't exist yet.
     */
    private static function initializeSchema(PDO $pdo, string $driver): void {
        if ($driver === 'sqlite') {
            $schema = "
                CREATE TABLE IF NOT EXISTS appointments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    client_id INTEGER,
                    full_name TEXT NOT NULL,
                    business_name TEXT,
                    email TEXT NOT NULL,
                    phone TEXT NOT NULL,
                    service TEXT NOT NULL,
                    budget_range TEXT,
                    preferred_date TEXT,
                    preferred_time TEXT,
                    message TEXT,
                    status TEXT NOT NULL DEFAULT 'Pending',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS leads (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    business_name TEXT,
                    phone TEXT NOT NULL,
                    email TEXT NOT NULL,
                    service TEXT,
                    source TEXT NOT NULL DEFAULT 'Website',
                    appointment_id INTEGER,
                    status TEXT NOT NULL DEFAULT 'New',
                    assigned_to INTEGER,
                    notes TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS contact_messages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    business_name TEXT,
                    email TEXT NOT NULL,
                    phone TEXT NOT NULL,
                    service TEXT,
                    message TEXT NOT NULL,
                    status TEXT NOT NULL DEFAULT 'New',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS notifications (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    client_id INTEGER,
                    type TEXT NOT NULL,
                    title TEXT NOT NULL,
                    message TEXT NOT NULL,
                    reference_id INTEGER,
                    is_read INTEGER NOT NULL DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS services (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    slug TEXT UNIQUE NOT NULL,
                    name TEXT NOT NULL,
                    short_desc TEXT NOT NULL,
                    icon TEXT DEFAULT '⚡',
                    is_active INTEGER DEFAULT 1,
                    sort_order INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    slug TEXT UNIQUE NOT NULL,
                    name TEXT NOT NULL,
                    category TEXT NOT NULL,
                    short_desc TEXT NOT NULL,
                    icon TEXT DEFAULT '💻',
                    badge TEXT,
                    is_active INTEGER DEFAULT 1,
                    sort_order INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_key TEXT UNIQUE NOT NULL,
                    setting_value TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                -- Part 3 Client Portal Tables
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    phone TEXT,
                    password_hash TEXT NOT NULL,
                    role TEXT NOT NULL DEFAULT 'client',
                    status TEXT NOT NULL DEFAULT 'active',
                    remember_token TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS clients (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER,
                    company_name TEXT NOT NULL,
                    contact_person TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    phone TEXT NOT NULL,
                    address TEXT,
                    profile_image TEXT,
                    gst_number TEXT,
                    status TEXT NOT NULL DEFAULT 'active',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS projects (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    client_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    service TEXT NOT NULL,
                    description TEXT,
                    start_date TEXT NOT NULL,
                    expected_completion TEXT,
                    status TEXT NOT NULL DEFAULT 'In Progress',
                    progress INTEGER NOT NULL DEFAULT 0,
                    assigned_team TEXT DEFAULT 'GrihGO Engineering Team',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS project_tasks (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    project_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    description TEXT,
                    assigned_team TEXT DEFAULT 'GrihGO Tech Core',
                    due_date TEXT,
                    completed_date TEXT,
                    priority TEXT NOT NULL DEFAULT 'Medium',
                    status TEXT NOT NULL DEFAULT 'Pending',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS invoices (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    invoice_number TEXT UNIQUE NOT NULL,
                    client_id INTEGER NOT NULL,
                    project_id INTEGER,
                    invoice_date TEXT NOT NULL,
                    due_date TEXT NOT NULL,
                    total_amount REAL NOT NULL DEFAULT 0.0,
                    paid_amount REAL NOT NULL DEFAULT 0.0,
                    status TEXT NOT NULL DEFAULT 'Pending',
                    notes TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS invoice_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    invoice_id INTEGER NOT NULL,
                    description TEXT NOT NULL,
                    quantity INTEGER NOT NULL DEFAULT 1,
                    unit_price REAL NOT NULL DEFAULT 0.0,
                    total REAL NOT NULL DEFAULT 0.0
                );

                CREATE TABLE IF NOT EXISTS payments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    invoice_id INTEGER NOT NULL,
                    client_id INTEGER NOT NULL,
                    amount REAL NOT NULL DEFAULT 0.0,
                    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    payment_method TEXT NOT NULL DEFAULT 'Bank Transfer (NEFT/RTGS)',
                    transaction_id TEXT NOT NULL,
                    status TEXT NOT NULL DEFAULT 'Success',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS documents (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    client_id INTEGER NOT NULL,
                    project_id INTEGER,
                    name TEXT NOT NULL,
                    type TEXT NOT NULL DEFAULT 'Contract',
                    file_path TEXT NOT NULL,
                    file_size TEXT DEFAULT '1.2 MB',
                    uploaded_by TEXT DEFAULT 'GrihGO Operations',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS support_tickets (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ticket_number TEXT UNIQUE NOT NULL,
                    client_id INTEGER NOT NULL,
                    subject TEXT NOT NULL,
                    category TEXT NOT NULL DEFAULT 'General',
                    priority TEXT NOT NULL DEFAULT 'Medium',
                    status TEXT NOT NULL DEFAULT 'Open',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS ticket_messages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ticket_id INTEGER NOT NULL,
                    sender_type TEXT NOT NULL DEFAULT 'client',
                    sender_id INTEGER NOT NULL,
                    sender_name TEXT NOT NULL,
                    message TEXT NOT NULL,
                    attachment_path TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS messages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    client_id INTEGER NOT NULL,
                    project_id INTEGER,
                    ticket_id INTEGER,
                    sender_type TEXT NOT NULL DEFAULT 'client',
                    sender_name TEXT NOT NULL,
                    message TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                -- Part 4 Admin Panel & CRM Tables
                CREATE TABLE IF NOT EXISTS admins (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER,
                    full_name TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    password_hash TEXT NOT NULL,
                    role TEXT NOT NULL DEFAULT 'admin',
                    status TEXT NOT NULL DEFAULT 'active',
                    phone TEXT,
                    last_login_at DATETIME,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS admin_activity_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    admin_id INTEGER,
                    admin_name TEXT,
                    action TEXT NOT NULL,
                    entity_type TEXT,
                    entity_id INTEGER,
                    description TEXT,
                    ip_address TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS testimonials (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    client_name TEXT NOT NULL,
                    company_name TEXT,
                    designation TEXT,
                    rating INTEGER NOT NULL DEFAULT 5,
                    content TEXT NOT NULL,
                    image_url TEXT,
                    status TEXT NOT NULL DEFAULT 'published',
                    sort_order INTEGER NOT NULL DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS portfolio (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    category TEXT NOT NULL,
                    client_name TEXT,
                    industry TEXT,
                    description TEXT,
                    solution TEXT,
                    technology TEXT,
                    impact TEXT,
                    image_url TEXT,
                    project_url TEXT,
                    status TEXT NOT NULL DEFAULT 'published',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS blog_posts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    slug TEXT NOT NULL UNIQUE,
                    excerpt TEXT,
                    content TEXT NOT NULL,
                    author_name TEXT NOT NULL DEFAULT 'GrihGO Editorial Team',
                    featured_image TEXT,
                    category TEXT DEFAULT 'Technology',
                    tags TEXT,
                    meta_title TEXT,
                    meta_desc TEXT,
                    status TEXT NOT NULL DEFAULT 'published',
                    published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ";
            $pdo->exec($schema);

            // Ensure column additions if existing tables were created earlier
            try { $pdo->exec("ALTER TABLE appointments ADD COLUMN client_id INTEGER;"); } catch (Exception $e) {}
            try { $pdo->exec("ALTER TABLE notifications ADD COLUMN client_id INTEGER;"); } catch (Exception $e) {}
            try { $pdo->exec("ALTER TABLE users ADD COLUMN phone TEXT;"); } catch (Exception $e) {}
            try { $pdo->exec("ALTER TABLE clients ADD COLUMN profile_image TEXT;"); } catch (Exception $e) {}
            try { $pdo->exec("ALTER TABLE invoices ADD COLUMN subtotal REAL DEFAULT 0.0;"); } catch (Exception $e) {}
            try { $pdo->exec("ALTER TABLE invoices ADD COLUMN tax_amount REAL DEFAULT 0.0;"); } catch (Exception $e) {}
            try { $pdo->exec("ALTER TABLE invoices ADD COLUMN discount_amount REAL DEFAULT 0.0;"); } catch (Exception $e) {}

            // Seed Client A and Client B if not already seeded
            self::seedPart4AdminData($pdo);
        }
    }

    /**
     * Seeds initial Part 4 Admin & CRM data.
     */
    private static function seedPart4AdminData(PDO $pdo): void {
        // 1. Create the first admin only when the installer supplies credentials.
        try {
            $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
            $initialAdminEmail = INITIAL_ADMIN_EMAIL;
            $initialAdminPassword = INITIAL_ADMIN_PASSWORD;
            if ($adminCount === 0 && filter_var($initialAdminEmail, FILTER_VALIDATE_EMAIL) && strlen($initialAdminPassword) >= 16) {
                $hash = password_hash($initialAdminPassword, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO admins (full_name, email, password_hash, role, status) VALUES (?, ?, ?, 'super_admin', 'active')")
                    ->execute(['Super Administrator', $initialAdminEmail, $hash]);
            }
        } catch (Exception $e) {
            self::logError("Admin seed notice: " . $e->getMessage());
        }

        // 3. Seed Portfolio if empty
        try {
            $cntPort = $pdo->query("SELECT COUNT(*) FROM portfolio")->fetchColumn();
            if ($cntPort == 0) {
                $portfolios = [
                    ['Apex Enterprise Digital Portal & CRM', 'Enterprise Software', 'Apex Enterprises', 'Manufacturing & Retail', 'Fragmented legacy communication and delayed client inquiry resolution.', 'Full-stack cloud portal with real-time WhatsApp Business API integration and quotation engine.', 'PHP 8, MySQL, Vanilla JS, WhatsApp Cloud API', '320% increase in lead velocity and complete elimination of manual quotation delays.', 'https://grihgo.com/demo/apex', 'draft'],
                    ['Zenith Fleet Telematics & ERP System', 'Cloud ERP', 'Zenith Logistics Ltd.', 'Logistics & Supply Chain', 'High dispatch latency and lack of centralized vehicle maintenance tracking.', 'High-capacity ERP synchronizing multi-hub fleet movements, consignment tracking, and automated driver settlements.', 'PHP 8, PDO, MariaDB, REST Telematics Sync', 'Reduced fleet turnaround cycle by 42% and achieved 99.98% inventory accuracy.', 'https://grihgo.com/demo/zenith', 'draft'],
                    ['SmartBilling Cloud Point-of-Sale', 'FinTech & Billing', 'RetailChain India', 'FMCG Retail', 'Slow checkout speeds and multi-store tax calculation mismatch.', 'Ultra-fast offline-first desktop-ready cloud billing engine with instant GST invoice generation.', 'PHP 8, Modern CSS, SQLite/MySQL Sync', 'Processed over 150,000 transactions during peak festive rush with sub-second receipt printing.', 'https://grihgo.com/demo/smartbilling', 'draft']
                ];
                $stmtP = $pdo->prepare("INSERT INTO portfolio (title, category, client_name, industry, description, solution, technology, impact, project_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($portfolios as $p) {
                    $stmtP->execute($p);
                }
            }
        } catch (Exception $e) {}

        // 4. Seed Blog Posts if empty
        try {
            $cntBlog = $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
            if ($cntBlog == 0) {
                $blogs = [
                    ['Why Modern Businesses are Migrating to Custom WhatsApp CRM in 2026', 'custom-whatsapp-crm-migration-2026', 'Discover how official WhatsApp Business Cloud API integration is replacing traditional email inquiries for high-conversion enterprises.', 'WhatsApp has become the undisputed primary communication bridge across modern commerce. In this comprehensive guide, we unpack how custom API integrations empower businesses to automate responses, route leads dynamically, and generate instant revenue.', 'GrihGO Tech Core', 'Technology', 'CRM, WhatsApp, Business Automation', 'Custom WhatsApp CRM Migration 2026 | GrihGO Digital', 'Complete guide on how to integrate WhatsApp Cloud API for business growth.', 'published'],
                    ['Building Resilient Cloud ERP Systems Without Framework Bloat', 'resilient-cloud-erp-without-framework-bloat', 'A deep dive into building lightning-fast enterprise resource planning software using pure native web standards and tuned SQL.', 'Framework churn is one of the highest hidden costs in long-term enterprise software maintenance. By relying on robust native PHP 8+, optimized PDO architectures, and vanilla frontends, organizations unlock sub-100ms response latencies and seamless decades-long reliability.', 'GrihGO Architecture Team', 'Software Architecture', 'ERP, Architecture, Performance', 'Building Resilient Cloud ERP Systems | GrihGO Digital', 'Discover how lean architecture drives superior scalability and reduced maintenance costs.', 'published']
                ];
                $stmtB = $pdo->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, author_name, category, tags, meta_title, meta_desc, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($blogs as $b) {
                    $stmtB->execute($b);
                }
            }
        } catch (Exception $e) {}

        // 5. Seed Settings if empty
        try {
            $defaultSettings = [
                'company_name' => 'GrihGO Digital & Tech',
                'tagline' => 'Engineering High-Velocity Digital Architecture',
                'phone' => '+91 98765 43210',
                'email' => 'contact@grihgo.com',
                'whatsapp' => '+91 98765 43210',
                'website' => 'https://grihgo.com',
                'office_address' => 'Plot 42, Cyber City, Phase II, Gurugram, Haryana - 122002',
                'social_linkedin' => 'https://linkedin.com/company/grihgo-digital',
                'social_twitter' => 'https://twitter.com/grihgodigital',
                'footer_copyright' => '© 2026 GrihGO Digital & Tech. All rights reserved.',
                'default_seo_title' => 'GrihGO Digital & Tech — Enterprise Software, AI & Web Solutions',
                'default_seo_desc' => 'Award-winning software development, WhatsApp CRM, and enterprise digital solutions.'
            ];
            $stmtSet = $pdo->prepare("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
            foreach ($defaultSettings as $k => $v) {
                try {
                    $stmtSet->execute([$k, $v]);
                } catch (Exception $ex) {
                    // Fallback for MySQL which uses INSERT ... ON DUPLICATE KEY UPDATE
                    $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")
                        ->execute([$k, $v]);
                }
            }
        } catch (Exception $e) {}

        // 6. Ensure initial leads if empty
        try {
            $cntLeads = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
            if ($cntLeads == 0) {
                $leads = [
                    ['Vikram Malhotra', 'Malhotra Auto Components', '+91 98112 33445', 'vikram@malhotraauto.in', 'Software Development', 'Website', 'New', 'Interested in custom inventory tracking for 3 regional warehouses.'],
                    ['Sunita Rao', 'Kaveri Hospital Diagnostics', '+91 98450 12345', 's.rao@kaverihospital.org', 'AI Solutions', 'Service Enquiry', 'Contacted', 'Discussed appointment scheduling AI bot; follow-up scheduled for Monday.'],
                    ['Amit Singhania', 'Singhania Real Estate', '+91 99887 76655', 'amit@singhaniarealty.com', 'WhatsApp CRM', 'Request Demo', 'Proposal Sent', 'Sent commercial proposal for Real Estate WhatsApp lead capture module.']
                ];
                $stmtL = $pdo->prepare("INSERT INTO leads (name, business_name, phone, email, service, source, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($leads as $l) {
                    $stmtL->execute($l);
                }
            }
        } catch (Exception $e) {}
    }

    /**
     * Log an admin action to the activity log.
     */
    public static function logAdminActivity(PDO $pdo, ?int $adminId, ?string $adminName, string $action, ?string $entityType = null, ?int $entityId = null, ?string $description = null): void {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt = $pdo->prepare("INSERT INTO admin_activity_logs (admin_id, admin_name, action, entity_type, entity_id, description, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$adminId, $adminName, $action, $entityType, $entityId, $description, $ip]);
        } catch (Exception $e) {
            self::logError("Admin activity log error: " . $e->getMessage());
        }
    }

    /**
     * Internal secure error logger.
     */
    private static function logError(string $msg): void {
        $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'database.log';
        $timestamp = date('Y-m-d H:i:s');
        @file_put_contents($logFile, "[{$timestamp}] {$msg}\n", FILE_APPEND);
    }
}

/**
 * Global helper function to log admin activity
 */
if (!function_exists('logAdminActivity')) {
    function logAdminActivity(PDO $pdo, ?int $adminId, ?string $adminName, string $action, ?string $entityType = null, ?int $entityId = null, ?string $description = null): void {
        Database::logAdminActivity($pdo, $adminId, $adminName, $action, $entityType, $entityId, $description);
    }
}
