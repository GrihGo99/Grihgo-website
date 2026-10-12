<?php
/**
 * GrihGO Digital & Tech — Portfolio Case Studies Management
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Case Studies & Portfolio';
$pageSection = 'portfolio';

$error = '';
$success = '';

// Handle Add Portfolio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_portfolio') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $title       = sanitizeInput($_POST['title'] ?? '');
        $category    = sanitizeInput($_POST['category'] ?? 'Custom Software');
        $clientName  = sanitizeInput($_POST['client_name'] ?? '');
        $industry    = sanitizeInput($_POST['industry'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $solution    = sanitizeInput($_POST['solution'] ?? '');
        $technology  = sanitizeInput($_POST['technology'] ?? '');
        $impact      = sanitizeInput($_POST['impact'] ?? '');
        $projectUrl  = sanitizeInput($_POST['project_url'] ?? '');
        $status      = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';

        if (empty($title) || empty($solution)) {
            $error = 'Case Study Title and Solution description are required.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO portfolio (title, category, client_name, industry, description, solution, technology, impact, project_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $category, $clientName, $industry, $description, $solution, $technology, $impact, $projectUrl, $status]);
                $pid = (int)$pdo->lastInsertId();

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Portfolio Item Added', 'portfolio', $pid, "Added portfolio '{$title}'");
                $success = "Case study '{$title}' published successfully!";
            } catch (Exception $e) {
                $error = 'Failed to add portfolio case study.';
            }
        }
    }
}

// Handle Toggle Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $pid = (int)($_POST['portfolio_id'] ?? 0);
        $newStatus = in_array($_POST['new_status'] ?? '', ['published', 'draft']) ? $_POST['new_status'] : 'draft';
        if ($pid > 0) {
            try {
                $pdo->prepare("UPDATE portfolio SET status = ? WHERE id = ?")->execute([$newStatus, $pid]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Portfolio Status Updated', 'portfolio', $pid, "Set status to {$newStatus}");
                $success = "Portfolio case study is now " . ucfirst($newStatus) . "!";
            } catch (Exception $e) {
                $error = 'Failed to toggle status.';
            }
        }
    }
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_portfolio') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $pid = (int)($_POST['portfolio_id'] ?? 0);
        if ($pid > 0) {
            try {
                $pdo->prepare("DELETE FROM portfolio WHERE id = ?")->execute([$pid]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Portfolio Deleted', 'portfolio', $pid, "Deleted case study #{$pid}");
                $success = "Portfolio case study deleted.";
            } catch (Exception $e) {
                $error = 'Failed to delete case study.';
            }
        }
    }
}

// Fetch Portfolio
$portfolioItems = $pdo->query("SELECT * FROM portfolio ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Case Studies & Portfolio</h1>
        <p class="page-subtitle">Curate delivered client solutions, technology stacks, and quantified enterprise impact.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" onclick="openAdminModal('newPortfolioModal')">+ Add Case Study</button>
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
                    <th>Case Study Title</th>
                    <th>Category</th>
                    <th>Client / Industry</th>
                    <th>Technology Stack</th>
                    <th>Quantified Impact</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($portfolioItems)): ?>
                    <tr><td colspan="7" class="empty-state">No portfolio items added yet.</td></tr>
                <?php else: foreach ($portfolioItems as $item): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($item['title']) ?></strong>
                            <?php if (!empty($item['project_url'])): ?>
                                <div style="font-size: 11px;"><a href="<?= htmlspecialchars($item['project_url']) ?>" target="_blank" style="color: var(--admin-primary);"><?= htmlspecialchars($item['project_url']) ?></a></div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-neutral"><?= htmlspecialchars($item['category']) ?></span></td>
                        <td>
                            <strong><?= htmlspecialchars($item['client_name'] ?: 'Confidential Client') ?></strong>
                            <div style="font-size: 11px; color: var(--admin-text-muted);"><?= htmlspecialchars($item['industry'] ?: '-') ?></div>
                        </td>
                        <td><div style="font-size: 12px; color: #475569; max-width: 220px;"><?= htmlspecialchars($item['technology'] ?: 'PHP, MySQL') ?></div></td>
                        <td>
                            <div style="font-size: 12px; font-weight: 600; color: var(--admin-accent); max-width: 260px;">
                                <?= htmlspecialchars($item['impact'] ?: 'Positive commercial ROI') ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $item['status'] === 'published' ? 'badge-success' : 'badge-neutral' ?>">
                                <span class="badge-dot"></span><?= ucfirst(htmlspecialchars($item['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <form method="POST" action="portfolio.php" style="margin: 0;">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="portfolio_id" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="new_status" value="<?= $item['status'] === 'published' ? 'draft' : 'published' ?>">
                                    <button type="submit" class="btn btn-sm <?= $item['status'] === 'published' ? 'btn-outline' : 'btn-success' ?>">
                                        <?= $item['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
                                    </button>
                                </form>
                                <form method="POST" action="portfolio.php" style="margin: 0;" onsubmit="return confirm('Delete case study <?= htmlspecialchars(addslashes($item['title'])) ?>?');">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="delete_portfolio">
                                    <input type="hidden" name="portfolio_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-icon" title="Delete" style="color: var(--admin-danger);">&times;</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Portfolio -->
<div id="newPortfolioModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 620px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Publish Case Study</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('newPortfolioModal')">&times;</button>
        </div>
        <form method="POST" action="portfolio.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="add_portfolio">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Case Study Project Title *</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Real-Time Telematics & Fleet ERP" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Domain / Category</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Enterprise Cloud ERP">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Client Name</label>
                        <input type="text" name="client_name" class="form-control" placeholder="e.g. Zenith Logistics Ltd.">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Industry</label>
                        <input type="text" name="industry" class="form-control" placeholder="e.g. Logistics & Supply Chain">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Live Demo / Showcase URL</label>
                        <input type="url" name="project_url" class="form-control" placeholder="https://grihgo.com/showcase/zenith">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Technologies Employed</label>
                    <input type="text" name="technology" class="form-control" placeholder="e.g. PHP 8, MariaDB, Vanilla JS, Redis Sync">
                </div>
                <div class="form-group">
                    <label class="form-label">Client Challenge & Problem Statement</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Bottlenecks and technical limitations faced..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Architectural Solution Delivered *</label>
                    <textarea name="solution" class="form-control" rows="2" placeholder="Detailed engineering implementation..." required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Quantified Business Impact & Metric</label>
                    <input type="text" name="impact" class="form-control" placeholder="e.g. 42% latency reduction and 99.98% inventory accuracy">
                </div>
                <div class="form-group">
                    <label class="form-label">Publication Status</label>
                    <select name="status" class="form-control">
                        <option value="published" selected>Published to Live Website</option>
                        <option value="draft">Draft (Internal Only)</option>
                    </select>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('newPortfolioModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Case Study</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
