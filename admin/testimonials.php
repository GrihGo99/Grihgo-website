<?php
/**
 * GrihGO Digital & Tech — Testimonial Management Console
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Client Testimonials';
$pageSection = 'testimonials';

$error = '';
$success = '';

// Handle Add Testimonial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_testimonial') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $clientName  = sanitizeInput($_POST['client_name'] ?? '');
        $companyName = sanitizeInput($_POST['company_name'] ?? '');
        $designation = sanitizeInput($_POST['designation'] ?? '');
        $rating      = min(5, max(1, (int)($_POST['rating'] ?? 5)));
        $content     = sanitizeInput($_POST['content'] ?? '');
        $status      = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);

        if (empty($clientName) || empty($content)) {
            $error = 'Client Name and Testimonial Review text are required.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO testimonials (client_name, company_name, designation, rating, content, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$clientName, $companyName, $designation, $rating, $content, $status, $sortOrder]);
                $tid = (int)$pdo->lastInsertId();

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Testimonial Added', 'testimonials', $tid, "Added testimonial from {$clientName} ({$companyName})");
                $success = "Testimonial from {$clientName} added successfully!";
            } catch (Exception $e) {
                $error = 'Failed to add testimonial.';
            }
        }
    }
}

// Handle Toggle Status (Publish / Unpublish)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $tid = (int)($_POST['testimonial_id'] ?? 0);
        $newStatus = in_array($_POST['new_status'] ?? '', ['published', 'draft']) ? $_POST['new_status'] : 'draft';
        if ($tid > 0) {
            try {
                $pdo->prepare("UPDATE testimonials SET status = ? WHERE id = ?")->execute([$newStatus, $tid]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Testimonial Status Updated', 'testimonials', $tid, "Set status to {$newStatus}");
                $success = "Testimonial is now " . ucfirst($newStatus) . "!";
            } catch (Exception $e) {
                $error = 'Failed to update testimonial status.';
            }
        }
    }
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_testimonial') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $tid = (int)($_POST['testimonial_id'] ?? 0);
        if ($tid > 0) {
            try {
                $pdo->prepare("DELETE FROM testimonials WHERE id = ?")->execute([$tid]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Testimonial Deleted', 'testimonials', $tid, "Deleted testimonial #{$tid}");
                $success = "Testimonial deleted successfully.";
            } catch (Exception $e) {
                $error = 'Failed to delete testimonial.';
            }
        }
    }
}

// Fetch Testimonials
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Client Testimonials</h1>
        <p class="page-subtitle">Manage client reviews, enterprise endorsements, and public website social proof.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" onclick="openAdminModal('newTestimonialModal')">+ Add Testimonial</button>
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
                    <th>Client & Company</th>
                    <th>Designation</th>
                    <th>Rating</th>
                    <th>Testimonial Review</th>
                    <th>Sort</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($testimonials)): ?>
                    <tr><td colspan="7" class="empty-state">No testimonials registered yet.</td></tr>
                <?php else: foreach ($testimonials as $t): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($t['client_name']) ?></strong>
                            <div style="font-size: 11px; color: var(--admin-text-muted);"><?= htmlspecialchars($t['company_name'] ?: '-') ?></div>
                        </td>
                        <td><?= htmlspecialchars($t['designation'] ?: '-') ?></td>
                        <td>
                            <span style="color: #f59e0b; font-weight: 700;">
                                <?= str_repeat('★', (int)$t['rating']) ?><?= str_repeat('☆', 5 - (int)$t['rating']) ?>
                            </span>
                        </td>
                        <td>
                            <div style="max-width: 340px; font-size: 12.5px; color: #475569;">
                                "<?= htmlspecialchars($t['content']) ?>"
                            </div>
                        </td>
                        <td><?= (int)$t['sort_order'] ?></td>
                        <td>
                            <span class="badge <?= $t['status'] === 'published' ? 'badge-success' : 'badge-neutral' ?>">
                                <span class="badge-dot"></span><?= ucfirst(htmlspecialchars($t['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <form method="POST" action="testimonials.php" style="margin: 0;">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="testimonial_id" value="<?= $t['id'] ?>">
                                    <input type="hidden" name="new_status" value="<?= $t['status'] === 'published' ? 'draft' : 'published' ?>">
                                    <button type="submit" class="btn btn-sm <?= $t['status'] === 'published' ? 'btn-outline' : 'btn-success' ?>">
                                        <?= $t['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
                                    </button>
                                </form>
                                <form method="POST" action="testimonials.php" style="margin: 0;" onsubmit="return confirm('Delete testimonial from <?= htmlspecialchars(addslashes($t['client_name'])) ?>?');">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="delete_testimonial">
                                    <input type="hidden" name="testimonial_id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-icon" title="Delete Testimonial" style="color: var(--admin-danger);">&times;</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Testimonial -->
<div id="newTestimonialModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 540px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Add Client Testimonial</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('newTestimonialModal')">&times;</button>
        </div>
        <form method="POST" action="testimonials.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="add_testimonial">
            <div class="admin-modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Client / Reviewer Name *</label>
                        <input type="text" name="client_name" class="form-control" placeholder="e.g. Rajesh Sharma" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" placeholder="e.g. Apex Enterprises">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Client Designation</label>
                        <input type="text" name="designation" class="form-control" placeholder="e.g. Managing Director">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Star Rating</label>
                        <select name="rating" class="form-control">
                            <option value="5" selected>5 Stars (Excellent)</option>
                            <option value="4">4 Stars (Very Good)</option>
                            <option value="3">3 Stars (Good)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Testimonial Review *</label>
                    <textarea name="content" class="form-control" rows="4" placeholder="Enter client feedback, quotes, and project impact..." required></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Sort Priority</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="published" selected>Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('newTestimonialModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Testimonial</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
