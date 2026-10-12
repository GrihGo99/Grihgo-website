<?php
$page_title = 'GST Billing & Invoicing Software | GrihGO Digital & Tech';
$meta_desc = 'Fast, accurate GST tax invoicing, inventory sync, expense tracking, and GSTR reporting engineered for retail, wholesale, and service enterprises.';
$current_page = 'product_billing';
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
            <span class="breadcrumb-current">Billing Software</span>
        </nav>
        <div class="section-tag" style="background: #fef3c7; color: #b45309; border-color: rgba(245, 158, 11, 0.3);">
            Finance & Accounting
        </div>
        <h1 style="margin-bottom: 16px;">Enterprise GST <span class="gradient-text">Billing Software</span></h1>
        <p class="section-subtitle" style="max-width: 760px; margin-left: 0;">
            Issue compliant GST tax invoices in under 10 seconds, track receivables, automate stock deduction, and generate one-click tax filing reports.
        </p>
        <div class="btn-group" style="margin-top: 24px;">
            <a href="../appointment.php?service=billing-software" class="btn btn-primary btn-lg">Request Live Demo</a>
            <a href="../contact.php" class="btn btn-outline-primary btn-lg">Consult Finance Specialist</a>
        </div>
    </div>
</section>

<!-- Overview & UI Preview -->
<section class="section" id="product-overview">
    <div class="container">
        <div class="billing-showcase-grid">
            <div>
                <span class="section-tag">High-Velocity Invoicing</span>
                <h2 class="section-title">Speed Up Billing. <span class="gradient-text">Eliminate Accounting Errors.</span></h2>
                <p>
                    Outdated desktop accounting software often creates bottlenecks at checkout counters, lacks mobile accessibility, and requires painful manual backups.
                </p>
                <p>
                    GrihGO Billing Software provides a modern, cloud-backed invoicing interface with instant barcode lookup, automated CGST/SGST/IGST tax calculation, thermal and PDF print options, and direct invoice sharing via WhatsApp and email.
                </p>
                <div class="benefits-list" style="margin-top: 24px;">
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>10-Second Invoicing Workflow</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Rapid item barcode scanning with automatic price level and tax calculation.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Automatic Inventory Depletion</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Stock records update instantly upon invoice save, preventing stockout overselling.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>1-Click WhatsApp Invoice Dispatch</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Send formatted PDF invoice links directly to the customer's WhatsApp chat instantly.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UI Preview Invoice Card -->
            <div class="billing-preview-card">
                <div class="invoice-preview-head">
                    <div>
                        <h4 style="margin: 0; font-size: 1.15rem;">GST TAX INVOICE</h4>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">INV-2026-0914 • Original for Recipient</span>
                    </div>
                    <span class="invoice-badge">PAID • VERIFIED</span>
                </div>

                <div style="font-size: 0.85rem; margin-bottom: 16px; color: var(--text-secondary);">
                    <strong>Billed To:</strong> Sharma Logistics Pvt Ltd (GSTIN: 07AAAAA0000A1Z5)<br>
                    <span>Sector 62, Noida, Uttar Pradesh</span>
                </div>

                <table class="invoice-table-preview">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>HSN</th>
                            <th>Qty</th>
                            <th>GST%</th>
                            <th style="text-align: right;">Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>GrihGO Enterprise Cloud License</td>
                            <td>998313</td>
                            <td>1</td>
                            <td>18%</td>
                            <td style="text-align: right;">35,000.00</td>
                        </tr>
                        <tr>
                            <td>Thermal Printer Hardware Integration</td>
                            <td>844332</td>
                            <td>1</td>
                            <td>18%</td>
                            <td style="text-align: right;">12,500.00</td>
                        </tr>
                        <tr class="invoice-total-row">
                            <td colspan="4" style="text-align: right; padding-top: 14px;">Total Invoice Amount:</td>
                            <td style="text-align: right; padding-top: 14px; color: var(--primary-blue);">₹ 56,050.00</td>
                        </tr>
                    </tbody>
                </table>

                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px dashed var(--border-light); font-size: 0.8rem; color: var(--text-muted);">
                    <span>Status: Auto-synced to GST Portal</span>
                    <span style="font-weight: 600; color: var(--secondary-green);">PDF / Print Ready</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Feature Matrix -->
<section class="section section-dark" id="features">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Complete Feature Set</span>
            <h2 class="section-title">Engineered For Zero <span class="gradient-text">Accounting Friction</span></h2>
            <p class="section-subtitle">Everything you need to manage commercial billing, inventory, and compliance.</p>
        </div>

        <div class="services-grid-3">
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(2, 86, 196, 0.3);">🧾</div>
                <h3 style="color: #fff;">GST & Non-GST Invoicing</h3>
                <p style="color: var(--text-light-muted);">Support for B2B tax invoices, B2C retail cash memos, proforma quotes, and credit notes with HSN/SAC codes.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(16, 185, 129, 0.25);">📦</div>
                <h3 style="color: #fff;">Product & Stock Management</h3>
                <p style="color: var(--text-light-muted);">Categorized item lists, batch numbers, serial tracking, low-stock threshold notifications, and purchase order sync.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(0, 153, 255, 0.25);">💳</div>
                <h3 style="color: #fff;">Payment Tracking & Ledgers</h3>
                <p style="color: var(--text-light-muted);">Record cash, UPI, bank transfers, and partial installments with automated aging receivable reminders.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(245, 158, 11, 0.25);">🖨️</div>
                <h3 style="color: #fff;">Multi-Format Printing</h3>
                <p style="color: var(--text-light-muted);">Seamless printing on A4, A5, and 3-inch thermal POS roll printers with your custom company logo and signature.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(139, 92, 246, 0.25);">📑</div>
                <h3 style="color: #fff;">Expense & Purchase Logs</h3>
                <p style="color: var(--text-light-muted);">Log vendor purchase invoices, operational overhead expenses, and track real net profit margins in real time.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(236, 72, 153, 0.25);">📊</div>
                <h3 style="color: #fff;">One-Click GSTR Reports</h3>
                <p style="color: var(--text-light-muted);">Generate clean GSTR-1 and GSTR-3B audit summaries formatted for effortless sharing with your chartered accountant.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section class="section">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Ready to Streamline Your Billing & GST Operations?</h2>
            <p>Request a personalized software demo configured with your industry catalog and tax slabs.</p>
            <div class="btn-group">
                <a href="../appointment.php?service=billing-software" class="btn btn-primary btn-lg">Request Live Demo</a>
                <a href="../contact.php" class="btn btn-outline-white btn-lg">Talk to Product Specialist</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
