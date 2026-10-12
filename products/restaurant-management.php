<?php
$page_title = 'Restaurant POS & Management System | GrihGO Digital & Tech';
$meta_desc = 'High-speed restaurant POS, digital kitchen order tickets (KOT), table layout management, and inventory recipe tracking.';
$current_page = 'product_restaurant';
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
            <span class="breadcrumb-current">Restaurant Management</span>
        </nav>
        <div class="section-tag" style="background: #ffedd5; color: #9a3412; border-color: rgba(154, 52, 18, 0.3);">
            Hospitality & Dining
        </div>
        <h1 style="margin-bottom: 16px;">Restaurant POS & <span class="gradient-text">Operations Suite</span></h1>
        <p class="section-subtitle" style="max-width: 760px; margin-left: 0;">
            Accelerate table turns, dispatch digital kitchen order tickets (KOT) in seconds, track recipe ingredients, and unify dine-in and online delivery orders.
        </p>
        <div class="btn-group" style="margin-top: 24px;">
            <a href="../appointment.php?service=restaurant-management" class="btn btn-primary btn-lg">Request Restaurant Demo</a>
            <a href="../contact.php" class="btn btn-outline-primary btn-lg">Consult Hospitality Lead</a>
        </div>
    </div>
</section>

<!-- Overview & UI Preview -->
<section class="section" id="product-overview">
    <div class="container">
        <div class="benefits-split-grid">
            <div>
                <span class="section-tag">High-Volume Speed</span>
                <h2 class="section-title">Eliminate Order Delays and <span class="gradient-text">Food Wastage</span></h2>
                <p>
                    During dinner peak rushes, misplaced paper order slips and slow settlement lines frustrate guests and cause kitchen chaos.
                </p>
                <p>
                    GrihGO Restaurant POS delivers a touchscreen interface designed for lightning-fast order taking, instant thermal KOT printing directly in the kitchen, split-bill settlements, and automated inventory depletion of recipe ingredients.
                </p>
                <div class="benefits-list" style="margin-top: 24px;">
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Instant Kitchen Order Tickets (KOT)</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Orders punched on waitstaff tablets print directly at the kitchen and bar stations.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Visual Table Floor Plan</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Color-coded table status showing occupied, billing, reserved, and dirty tables at a glance.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon-box">✓</div>
                        <div>
                            <strong>Raw Ingredient Recipe Tracking</strong>
                            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Automatically deduct cheese, chicken, flour, and spices with every dish rung up.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UI Preview Box -->
            <div class="form-card" style="padding: 28px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                    <strong>Floor Table Layout Status</strong>
                    <span class="badge-dot green"></span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--secondary-green); border-radius: var(--radius-sm); padding: 14px;">
                        <strong style="color: var(--secondary-green);">Table 01 (Dining)</strong>
                        <p style="margin: 0; font-size: 0.75rem; color: var(--text-secondary);">4 Guests • Bill: ₹ 2,450 • 28 mins</p>
                    </div>
                    <div style="background: rgba(2, 86, 196, 0.1); border: 1px solid var(--primary-blue); border-radius: var(--radius-sm); padding: 14px;">
                        <strong style="color: var(--primary-blue);">Table 02 (Settling)</strong>
                        <p style="margin: 0; font-size: 0.75rem; color: var(--text-secondary);">2 Guests • UPI QR Generated</p>
                    </div>
                    <div style="background: var(--bg-light); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px;">
                        <strong>Table 03 (Available)</strong>
                        <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted);">6 Seats Clean & Ready</p>
                    </div>
                    <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid #f59e0b; border-radius: var(--radius-sm); padding: 14px;">
                        <strong style="color: #b45309);">Table 04 (Reserved)</strong>
                        <p style="margin: 0; font-size: 0.75rem; color: var(--text-secondary);">Party of 8 • Due 08:30 PM</p>
                    </div>
                </div>
                <div style="padding-top: 16px; border-top: 1px solid var(--border-light);">
                    <a href="../appointment.php?service=restaurant-management" class="btn btn-primary w-100">Schedule Live Restaurant POS Demo &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="section section-dark" id="features">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Restaurant Modules</span>
            <h2 class="section-title">Built For Busy <span class="gradient-text">Food & Beverage Brands</span></h2>
            <p class="section-subtitle">Scalable tools for cafes, fine dining, cloud kitchens, and franchise bars.</p>
        </div>

        <div class="services-grid-3">
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(2, 86, 196, 0.3);">🍽️</div>
                <h3 style="color: #fff;">Touch POS & Modifiers</h3>
                <p style="color: var(--text-light-muted);">Handle add-ons, spice customizations, item variants, and discount promo vouchers in two taps.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(16, 185, 129, 0.25);">🍳</div>
                <h3 style="color: #fff;">Kitchen Display System (KDS)</h3>
                <p style="color: var(--text-light-muted);">Paperless digital kitchen screens displaying preparation timer countdowns and order completion buttons.</p>
            </div>
            <div class="service-card" style="background: rgba(15, 26, 58, 0.85); border-color: rgba(255, 255, 255, 0.12);">
                <div class="service-card-icon" style="background: rgba(0, 153, 255, 0.25);">📦</div>
                <h3 style="color: #fff;">Raw Material Inventory</h3>
                <p style="color: var(--text-light-muted);">Ingredient consumption audits, vendor purchasing, shrinkage tracking, and automated stock alerts.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section class="section">
    <div class="container">
        <div class="cta-banner-box">
            <h2>Transform Your Dining Operations with GrihGO POS</h2>
            <p>Request a live walkthrough configured with your menu categories, table floor plan, and printer setup.</p>
            <div class="btn-group">
                <a href="../appointment.php?service=restaurant-management" class="btn btn-primary btn-lg">Request Live Demo</a>
                <a href="../contact.php" class="btn btn-outline-white btn-lg">Talk to Product Specialist</a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
