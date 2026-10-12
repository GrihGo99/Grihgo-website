<?php
/**
 * GrihGO Digital & Tech — Products Management Console
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Products Catalog';
$pageSection = 'products';

$error = '';
$success = '';

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $name      = sanitizeInput($_POST['name'] ?? '');
        $slug      = sanitizeInput($_POST['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name))));
        $category  = sanitizeInput($_POST['category'] ?? 'Business Software');
        $shortDesc = sanitizeInput($_POST['short_desc'] ?? '');
        $icon      = sanitizeInput($_POST['icon'] ?? '💻');
        $badge     = sanitizeInput($_POST['badge'] ?? '');
        $isActive  = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (empty($name) || empty($shortDesc)) {
            $error = 'Product Title and Description are required.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (name, slug, category, short_desc, icon, badge, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $category, $shortDesc, $icon, $badge, $isActive, $sortOrder]);
                $pid = (int)$pdo->lastInsertId();

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Product Created', 'products', $pid, "Added product '{$name}'");
                $success = "Product '{$name}' added to catalog!";
            } catch (Exception $e) {
                $error = 'Failed to add product. Slug may already exist.';
            }
        }
    }
}

// Handle Toggle Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_product') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $pid = (int)($_POST['product_id'] ?? 0);
        $newActive = (int)($_POST['new_active'] ?? 0);
        if ($pid > 0) {
            try {
                $pdo->prepare("UPDATE products SET is_active = ? WHERE id = ?")->execute([$newActive, $pid]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Product Toggled', 'products', $pid, "Set is_active = {$newActive} for product #{$pid}");
                $success = "Product status updated.";
            } catch (Exception $e) {
                $error = 'Failed to toggle product.';
            }
        }
    }
}

// Fetch Products
$products = $pdo->query("SELECT * FROM products ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Software Products & CRM Modules</h1>
        <p class="page-subtitle">Manage proprietary software products, categories, features, and public availability.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" onclick="openAdminModal('newProductModal')">+ Add Product</button>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Icon</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Badge</th>
                    <th>URL Slug</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="7" class="empty-state">No products registered in database.</td></tr>
                <?php else: foreach ($products as $prod): ?>
                    <tr>
                        <td style="font-size: 24px;"><?= htmlspecialchars($prod['icon'] ?? '💻') ?></td>
                        <td>
                            <strong><?= htmlspecialchars($prod['name']) ?></strong>
                            <div style="font-size: 11.5px; color: var(--admin-text-muted); max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= htmlspecialchars($prod['short_desc']) ?>
                            </div>
                        </td>
                        <td><span class="badge badge-neutral"><?= htmlspecialchars($prod['category'] ?? 'General') ?></span></td>
                        <td>
                            <?php if (!empty($prod['badge'])): ?>
                                <span class="badge badge-purple"><?= htmlspecialchars($prod['badge']) ?></span>
                            <?php else: ?>
                                <span style="color: #cbd5e1;">-</span>
                            <?php endif; ?>
                        </td>
                        <td><code>/products/<?= htmlspecialchars($prod['slug']) ?>.php</code></td>
                        <td>
                            <span class="badge <?= $prod['is_active'] ? 'badge-success' : 'badge-neutral' ?>">
                                <span class="badge-dot"></span><?= $prod['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="product-edit.php?id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                                <form method="POST" action="products.php" style="margin: 0;">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="toggle_product">
                                    <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                    <input type="hidden" name="new_active" value="<?= $prod['is_active'] ? 0 : 1 ?>">
                                    <button type="submit" class="btn btn-sm <?= $prod['is_active'] ? 'btn-outline' : 'btn-success' ?>">
                                        <?= $prod['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Product -->
<div id="newProductModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 540px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Add Software Product</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('newProductModal')">&times;</button>
        </div>
        <form method="POST" action="products.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="add_product">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. WhatsApp Automation CRM" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">URL Slug</label>
                        <input type="text" name="slug" class="form-control" placeholder="e.g. whatsapp-crm">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Communication CRM">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Icon / Emoji</label>
                        <input type="text" name="icon" class="form-control" value="💬">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Promotional Badge</label>
                        <input type="text" name="badge" class="form-control" placeholder="e.g. Most Popular / New">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Product Short Description *</label>
                    <textarea name="short_desc" class="form-control" rows="3" placeholder="Key business capabilities and features..." required></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; padding-top: 24px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13.5px;">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>Published on Website</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('newProductModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
