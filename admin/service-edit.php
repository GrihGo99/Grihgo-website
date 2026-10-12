<?php
/**
 * GrihGO Digital & Tech — Edit Service
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$serviceId = (int)($_GET['id'] ?? 0);
if ($serviceId <= 0) {
    header('Location: services.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? LIMIT 1");
$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    header('Location: services.php?error=not_found');
    exit;
}

$pageTitle = 'Edit Service: ' . htmlspecialchars($service['name']);
$pageSection = 'services';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $name      = sanitizeInput($_POST['name'] ?? '');
        $slug      = sanitizeInput($_POST['slug'] ?? $service['slug']);
        $shortDesc = sanitizeInput($_POST['short_desc'] ?? '');
        $icon      = sanitizeInput($_POST['icon'] ?? '⚡');
        $isActive  = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (empty($name) || empty($shortDesc)) {
            $error = 'Service Name and Description are required.';
        } else {
            try {
                $upd = $pdo->prepare("UPDATE services SET name = ?, slug = ?, short_desc = ?, icon = ?, is_active = ?, sort_order = ? WHERE id = ?");
                $upd->execute([$name, $slug, $shortDesc, $icon, $isActive, $sortOrder, $serviceId]);

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Service Updated', 'services', $serviceId, "Updated service {$name}");

                $stmt->execute([$serviceId]);
                $service = $stmt->fetch(PDO::FETCH_ASSOC);
                $success = 'Service updated successfully!';
            } catch (Exception $e) {
                $error = 'Failed to update service.';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <div style="font-size: 13px; color: var(--admin-text-muted); margin-bottom: 4px;">
            <a href="services.php" style="color: var(--admin-primary); text-decoration: none;">&larr; Back to Services</a>
        </div>
        <h1>Edit Service: <?= htmlspecialchars($service['name']) ?></h1>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="admin-card" style="max-width: 650px;">
    <form method="POST" action="service-edit.php?id=<?= $serviceId ?>" class="admin-card-body">
        <?= getCsrfField() ?>

        <div class="form-group">
            <label class="form-label">Service Title *</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($service['name']) ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">URL Slug *</label>
                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($service['slug']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Icon / Symbol</label>
                <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($service['icon'] ?? '⚡') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Short Description *</label>
            <textarea name="short_desc" class="form-control" rows="4" required><?= htmlspecialchars($service['short_desc']) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Sort Priority</label>
                <input type="number" name="sort_order" class="form-control" value="<?= (int)$service['sort_order'] ?>">
            </div>
            <div class="form-group" style="display: flex; align-items: center; padding-top: 24px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13.5px;">
                    <input type="checkbox" name="is_active" value="1" <?= $service['is_active'] ? 'checked' : '' ?>>
                    <span>Published on Website</span>
                </label>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 10px;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="services.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
