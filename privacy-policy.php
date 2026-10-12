<?php
/**
 * GrihGO Digital & Tech — Privacy Policy
 * Version: 5.0 (Final Enterprise Architecture & Legal Documentation)
 */

$page_title = 'Privacy Policy | GrihGO Digital & Tech';
$meta_desc = 'Learn how GrihGO Digital & Tech collects, uses, protects and manages customer, client and website information.';
$current_page = 'privacy';
$meta_robots = 'noindex, follow';

require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
    <!-- Breadcrumb & Hero -->
    <section class="section page-header-section" style="background: linear-gradient(135deg, #070d1e 0%, #0b1536 100%); padding: 60px 0 40px; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
        <div class="container text-center">
            <span class="section-tag" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 4px 14px; border-radius: 999px; font-size: 13px; font-weight: 600; text-transform: uppercase;">Data Protection</span>
            <h1 style="color: #ffffff; font-size: clamp(32px, 4vw, 44px); font-weight: 800; margin: 16px 0 10px; font-family: 'Outfit', sans-serif;">Privacy Policy</h1>
            <p style="color: #94a3b8; font-size: 16px; max-width: 650px; margin: 0 auto;">
                Effective Date: January 1, 2026 • Last Updated: October 8, 2026
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="section" style="padding: 60px 0; background-color: #f8fafc;">
        <div class="container" style="max-width: 960px;">
            <div style="background: #ffffff; border-radius: 16px; padding: clamp(24px, 5vw, 48px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; line-height: 1.8; color: #334155;">

                <!-- 1. Introduction -->
                <div style="margin-bottom: 36px;">
                    <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin-bottom: 12px; font-family: 'Outfit', sans-serif; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">1. Introduction</h2>
                    <p>
                        At <strong>GrihGO Digital & Tech</strong> ("Company", "we", "us", or "our"), safeguarding your personal and business data is a core commitment. This Privacy Policy details how we collect, process, store, and protect information when you visit our website (<a href="index.php" style="color: #0099ff; text-decoration: underline;">grihgodigital.com</a>), book consultations, interact with our AI and WhatsApp workflows, or access our secure Client Portal.
                    </p>
                </div>

                <!-- 2. Information We Collect -->
                <div style="margin-bottom: 36px;">
                    <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin-bottom: 12px; font-family: 'Outfit', sans-serif; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">2. Information We Collect</h2>
                    <p>We collect information in the following contexts:</p>
                    <ul style="padding-left: 20px;">
                        <li style="margin-bottom: 8px;">
                            <strong>Contact Information:</strong> When you submit inquiry forms, we collect your name, business name, work email address, telephone number, and message notes.
                        </li>
                        <li style="margin-bottom: 8px;">
                            <strong>Appointment Information:</strong> Preferred consultation dates, time slots, project scope, estimated budget range, and meeting objectives.
                        </li>
                        <li style="margin-bottom: 8px;">
                            <strong>Lead Information:</strong> Inquiries originating from Meta Ads, Google Ads, or WhatsApp chat channels regarding software demos or custom technology requirements.
                        </li>
                        <li style="margin-bottom: 8px;">
                            <strong>Client Account Information:</strong> Company name, registered corporate address, GSTIN, primary contact credentials (email, phone, securely salted password hashes), and billing details.
                        </li>
                        <li style="margin-bottom: 8px;">
                            <strong>Website Usage Data:</strong> Anonymized server telemetry including IP addresses, browser user-agent strings, referral pages, and session timestamps.
                        </li>
                    </ul>
                </div>

                <!-- 3. How Information Is Used -->
                <div style="margin-bottom: 36px;">
                    <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin-bottom: 12px; font-family: 'Outfit', sans-serif; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">3. How Information Is Used</h2>
                    <p>We process collected data strictly for legitimate operational purposes:</p>
                    <ul style="padding-left: 20px;">
                        <li>To evaluate your business requirements and provide accurate software architecture quotes and timelines.</li>
                        <li>To facilitate scheduled meetings, product demonstrations, and technical discovery sessions.</li>
                        <li>To authenticate clients into the Client Portal and deliver real-time sprint milestones, invoices, and helpdesk support.</li>
                        <li>To maintain audit logging, prevent brute-force login attempts, and safeguard infrastructure security.</li>
                        <li>To dispatch transactional notifications regarding invoice generation, support ticket replies, and milestone sign-offs.</li>
                    </ul>
                </div>

                <!-- 4. Data Security -->
                <div style="margin-bottom: 36px;">
                    <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin-bottom: 12px; font-family: 'Outfit', sans-serif; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">4. Data Security</h2>
                    <p>
                        We apply defense-in-depth technical and operational safeguards to protect your personal and commercial data:
                    </p>
                    <ul style="padding-left: 20px;">
                        <li>All web traffic is transmitted via modern Transport Layer Security (TLS/HTTPS).</li>
                        <li>Database queries are executed using strictly parameterized Prepared Statements via PDO, preventing SQL injection vulnerabilities.</li>
                        <li>Account passwords are encrypted using one-way cryptographic algorithms (`password_hash` with BCRYPT).</li>
                        <li>Cross-Site Request Forgery (CSRF) tokens and rate-limiting middleware protect all administrative and customer endpoints.</li>
                    </ul>
                </div>

                <!-- 5. Data Retention -->
                <div style="margin-bottom: 36px;">
                    <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin-bottom: 12px; font-family: 'Outfit', sans-serif; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">5. Data Retention</h2>
                    <p>
                        We retain personal data only for as long as necessary to fulfill the purposes for which it was collected, comply with legal accounting obligations, resolve support disputes, and enforce our service contracts. Inactive lead inquiries are periodically purged from active sales queues.
                    </p>
                </div>

                <!-- 6. Third-Party Services -->
                <div style="margin-bottom: 36px;">
                    <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin-bottom: 12px; font-family: 'Outfit', sans-serif; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">6. Third-Party Services & Integrations</h2>
                    <p>
                        We do not sell, rent, or trade your personal information. We may share necessary data with trusted infrastructure providers who assist in operating our platform under strict confidentiality agreements, such as secure cloud servers, transactional email relays, and verified payment gateways.
                    </p>
                </div>

                <!-- 7. Cookies -->
                <div style="margin-bottom: 36px;">
                    <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin-bottom: 12px; font-family: 'Outfit', sans-serif; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">7. Cookie & Session Policy</h2>
                    <p>
                        Our website utilizes minimal, essential session cookies required for authentication, CSRF validation, and maintaining login sessions across the Client Portal and Admin Console. We do not deploy invasive third-party cross-site trackers.
                    </p>
                </div>

                <!-- 8. User Rights -->
                <div style="margin-bottom: 36px;">
                    <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin-bottom: 12px; font-family: 'Outfit', sans-serif; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">8. Your Data Protection Rights</h2>
                    <p>
                        Depending on your jurisdiction, you possess the right to request access to your personal data, request corrections to inaccurate records, request deletion of non-statutory records, or opt out of promotional communications at any time by contacting our support team.
                    </p>
                </div>

                <!-- 9. Client Portal Data -->
                <div style="margin-bottom: 36px;">
                    <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin-bottom: 12px; font-family: 'Outfit', sans-serif; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">9. Client Portal Data Isolation</h2>
                    <p>
                        Client Portal data—including project deliverables, task milestones, invoices, and support tickets—is segregated by strict account identifiers (`client_id`). One client organization can never view, query, or access data belonging to another client organization.
                    </p>
                </div>

                <!-- 10. Admin & Manager Access -->
                <div style="margin-bottom: 36px;">
                    <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin-bottom: 12px; font-family: 'Outfit', sans-serif; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">10. Staff Access Controls (Admin & Manager)</h2>
                    <p>
                        Internal access to customer records is restricted via Role-Based Access Control (RBAC). Only designated Operations Managers and Super Administrators with verified multi-factor credentials can view assigned leads, project tasks, or support inquiries.
                    </p>
                </div>

                <!-- 11. Changes to Privacy Policy -->
                <div style="margin-bottom: 36px;">
                    <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin-bottom: 12px; font-family: 'Outfit', sans-serif; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">11. Changes to This Privacy Policy</h2>
                    <p>
                        We may update this Privacy Policy from time to time to reflect operational, legal, or regulatory adjustments. Revisions will be published on this page with the updated revision timestamp.
                    </p>
                </div>

                <!-- 12. Contact Information -->
                <div style="background: #f1f5f9; padding: 24px; border-radius: 12px; border-left: 4px solid #10b981;">
                    <h2 style="color: #0f172a; font-size: 20px; font-weight: 700; margin-top: 0; margin-bottom: 12px; font-family: 'Outfit', sans-serif;">12. Privacy Contact Details</h2>
                    <p style="margin-bottom: 8px;">For questions regarding this policy or to request data updates, please contact our data desk:</p>
                    <p style="margin-bottom: 4px;"><strong>Company:</strong> <?= htmlspecialchars(getSetting('company_name', 'GrihGO Digital & Tech')) ?></p>
                    <p style="margin-bottom: 4px;"><strong>Headquarters:</strong> <?= htmlspecialchars(getSetting('office_address', 'AIIMS Patna, Phulwari Sharif, Patna, Bihar, India')) ?></p>
                    <p style="margin-bottom: 4px;"><strong>Mobile:</strong> <a href="tel:<?= htmlspecialchars(getSetting('phone', '8092932060')) ?>" style="color: #0099ff; text-decoration: none;"><?= htmlspecialchars(getSetting('phone', '8092932060')) ?></a></p>
                    <p style="margin-bottom: 4px;"><strong>WhatsApp:</strong> <a href="https://wa.me/91<?= htmlspecialchars(getSetting('whatsapp', '8092932060')) ?>" target="_blank" rel="noopener noreferrer" style="color: #10b981; text-decoration: none;">+91 <?= htmlspecialchars(getSetting('whatsapp', '8092932060')) ?></a></p>
                    <p style="margin-bottom: 0;"><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars(getSetting('email', 'contact@grihgo.com')) ?>" style="color: #0099ff; text-decoration: none;"><?= htmlspecialchars(getSetting('email', 'contact@grihgo.com')) ?></a></p>
                </div>

            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
