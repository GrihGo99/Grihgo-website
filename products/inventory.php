<?php
$page_title = 'Inventory Management System | GrihGO Digital & Tech';
$meta_desc = 'Multi-location inventory management, barcode scanning, stock transfer notes, and automated reorder alerts for warehouses and distributors.';
$current_page = 'product_inventory';
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
            <span class="breadcrumb-current">Inventory Management</span>
        </nav>
        <div class="section-tag">Logistics & Supply Chain</div>
        <h1 style="margin-bottom: 16px;">Multi-Branch <span class="gradient-text">Inventory Management</span></h1>
        <p class="section-subtitle" style="max-width: 760px; margin-left: 0;">
            Track stock movements in real time across multiple warehouses, centralize purchase orders, eliminate phantom inventory, and automate supplier reordering.
        </p>
        <div class="btn-group" style="margin-top: 24px;">
            <a href="../appointment.php?service=inventory" class="btn btn-primary btn-lg">Request Inventory Demo</a>
            <a href="../contact.php" class="btn btn-outline-primary btn-lg">Consult Inventory Lead</a>
        </div>
    </div>
</section>

<!-- Overview & UI Preview -->
<section class="section" id="product-overview">
    <div class="container">
        <div class="benefits-split-grid">
            <div>
                <span class="section-tag">Total Stock Control</span>
                <h2 class="section-title">Zero Stockouts. Zero Excess. <span class="gradient-text">Complete Traceability.</span></h2>
                <p>
                    Managing inventory without a synchronized database leads to stockouts of top-selling items, expired perishables, and untracked warehouse shrinkage.
                </p>
                <p>
                    GrihGO Inventory Management gives logistics managers full visibility into batch numbers, expiry dates, inter-branch transfer notes, and supplier lead times from any device.
                </p>
                <div class="benefits-list" style="margin-top: 24px;">
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Multi-Location Synchronization</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Real-time stock balance across central hubs, retail counters, and transit vehicles.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Automated Reorder Thresholds</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Trigger automated draft purchase orders when inventory falls below minimum buffer levels.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Barcode & QR Workflow</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Accelerate inbound receiving and outbound dispatch using handheld wireless barcode scanners.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UI Preview Box -->
            <div class="form-card" style="padding: 28px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <strong>Warehouse Stock Status</strong>
                    <span class="badge-dot green"></span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>SKU-9021 • Industrial Thermal Roll</span>
                            <span style="color: var(--secondary-green);">4,280 Units</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Main Warehouse (Zone B) • Optimal Buffer</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>SKU-4812 • Handheld 2D Barcode Scanner</span>
                            <span style="color: #ef4444; font-weight: 800;">18 Units (Low Stock)</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Branch 2 (Noida Hub) • PO Triggered</span>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700;">
                            <span>SKU-1109 • Power Adapter 12V 2A</span>
                            <span style="color: var(--primary-blue);">1,850 Units</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Transit Transfer • Dispatch Approved</span>
                    </div>
                </div>
                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light);">
                    <a href="../appointment.php?service=inventory" class="btn btn-primary w-100">Schedule Inventory Walkthrough &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="section section-dark" id="features">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Core Features</span>
            <h2 class="section-title">Warehouse Management <span class="gradient-text">Suite</span></h2>
            <p class="section-subtitle">Engineered for accuracy, speed, and loss prevention.</p>
        </div>

        <div class="services-grid-3">
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(2, 86, 196, 0.3);">📦</div>
                <h3 style="color: #fff;">Batch & Expiry Tracking</h3>
                <p style="color: var(--text-light-muted);">Support for FIFO/FEFO pick sequences, batch manufacture dates, and expiration risk notifications.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(16, 185, 129, 0.25);">🚚</div>
                <h3 style="color: #fff;">Inter-Branch Transfers</h3>
                <p style="color: var(--text-light-muted);">Create transfer challans, track in-transit items, and confirm branch receipts with digital sign-offs.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(0, 153, 255, 0.25);">📑</div>
                <h3 style="color: #fff;">Supplier Purchase Orders</h3>
                <p style="color: var(--text-light-muted);">Generate POs, track partial deliveries against Goods Receipt Notes (GRN), and verify bill variance.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section class="section">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Ready to Digitize Your Warehouse & Stock?</h2>
            <p>Schedule a live demo to explore multi-branch stock reconciliation and barcode management.</p>
            <div class="btn-group">
                <a href="../appointment.php?service=inventory" class="btn btn-primary btn-lg">Request Live Demo</a>
                <a href="../contact.php" class="btn btn-outline-white btn-lg">Talk to Product Specialist</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
