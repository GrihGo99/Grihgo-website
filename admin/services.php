<?php
/**
 * GrihGO Digital & Tech — Services Management Console
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Services Catalog';
$pageSection = 'services';

$error = '';
$success = '';

// Handle Service Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_service') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $name      = sanitizeInput($_POST['name'] ?? '');
        $slug      = sanitizeInput($_POST['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name))));
        $shortDesc = sanitizeInput($_POST['short_desc'] ?? '');
        $icon      = sanitizeInput($_POST['icon'] ?? '⚡');
        $isActive  = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (empty($name) || empty($shortDesc)) {
            $error = 'Service Name and Description are required.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO services (name, slug, short_desc, icon, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $shortDesc, $icon, $isActive, $sortOrder]);
                $sid = (int)$pdo->lastInsertId();

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Service Created', 'services', $sid, "Created service '{$name}'");
                $success = "Service '{$name}' added to catalog!";
            } catch (Exception $e) {
                $error = 'Failed to add service. Slug may already exist.';
            }
        }
    }
}

// Handle Service Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_service') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $sid = (int)($_POST['service_id'] ?? 0);
        $newActive = (int)($_POST['new_active'] ?? 0);
        if ($sid > 0) {
            try {
                $pdo->prepare("UPDATE services SET is_active = ? WHERE id = ?")->execute([$newActive, $sid]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Service Toggled', 'services', $sid, "Set is_active = {$newActive} for service #{$sid}");
                $success = "Service status updated.";
            } catch (Exception $e) {
                $error = 'Failed to toggle service status.';
            }
        }
    }
}

// Fetch Services
$services = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Solutions & Services Catalog</h1>
        <p class="page-subtitle">Manage public solution offerings, descriptions, and feature highlights.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" onclick="openAdminModal('newServiceModal')">+ Add Service</button>
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
                    <th>Service Name</th>
                    <th>URL Slug</th>
                    <th>Description</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr><td colspan="7" class="empty-state">No services registered in database.</td></tr>
                <?php else: foreach ($services as $srv): ?>
                    <tr>
                        <td style="font-size: 24px;"><?= htmlspecialchars($srv['icon'] ?? '⚡') ?></td>
                        <td><strong><?= htmlspecialchars($srv['name']) ?></strong></td>
                        <td><code>/services/<?= htmlspecialchars($srv['slug']) ?>.php</code></td>
                        <td><div style="font-size: 12px; color: #475569; max-width: 320px;"><?= htmlspecialchars($srv['short_desc']) ?></div></td>
                        <td><?= (int)$srv['sort_order'] ?></td>
                        <td>
                            <span class="badge <?= $srv['is_active'] ? 'badge-success' : 'badge-neutral' ?>">
                                <span class="badge-dot"></span><?= $srv['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="service-edit.php?id=<?= $srv['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                                <form method="POST" action="services.php" style="margin: 0;">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="toggle_service">
                                    <input type="hidden" name="service_id" value="<?= $srv['id'] ?>">
                                    <input type="hidden" name="new_active" value="<?= $srv['is_active'] ? 0 : 1 ?>">
                                    <button type="submit" class="btn btn-sm <?= $srv['is_active'] ? 'btn-outline' : 'btn-success' ?>">
                                        <?= $srv['is_active'] ? 'Deactivate' : 'Activate' ?>
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

<!-- Modal: New Service -->
<div id="newServiceModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 500px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Add New Service</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('newServiceModal')">&times;</button>
        </div>
        <form method="POST" action="services.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="add_service">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Service Title *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Artificial Intelligence Solutions" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">URL Slug</label>
                        <input type="text" name="slug" class="form-control" placeholder="e.g. ai-solutions">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Emoji / Symbol Icon</label>
                        <input type="text" name="icon" class="form-control" value="🤖">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Short Description *</label>
                    <textarea name="short_desc" class="form-control" rows="3" placeholder="Core value proposition and deliverable summary..." required></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Sort Priority</label>
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
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('newServiceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Service</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
