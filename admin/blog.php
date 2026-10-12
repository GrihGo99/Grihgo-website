<?php
/**
 * GrihGO Digital & Tech — Blog & Content Marketing Engine
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Blog & Insights';
$pageSection = 'blog';

$error = '';
$success = '';

// Handle Create Blog Post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_blog') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $title     = sanitizeInput($_POST['title'] ?? '');
        $rawSlug   = sanitizeInput($_POST['slug'] ?? '');
        $excerpt   = sanitizeInput($_POST['excerpt'] ?? '');
        $content   = sanitizeInput($_POST['content'] ?? '');
        $author    = sanitizeInput($_POST['author_name'] ?? $currentAdmin['full_name']);
        $category  = sanitizeInput($_POST['category'] ?? 'Technology');
        $tags      = sanitizeInput($_POST['tags'] ?? '');
        $metaTitle = sanitizeInput($_POST['meta_title'] ?? $title);
        $metaDesc  = sanitizeInput($_POST['meta_desc'] ?? $excerpt);
        $status    = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived']) ? $_POST['status'] : 'published';

        // SEO Slug Generation & Collision Prevention
        $slug = empty($rawSlug) ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-')) : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $rawSlug), '-'));

        if (empty($title) || empty($content)) {
            $error = 'Article Title and Content are required.';
        } else {
            try {
                // Ensure unique slug
                $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ?");
                $checkSlug->execute([$slug]);
                if ($checkSlug->fetchColumn() > 0) {
                    $slug .= '-' . rand(100, 999);
                }

                $stmt = $pdo->prepare("
                    INSERT INTO blog_posts (title, slug, excerpt, content, author_name, category, tags, meta_title, meta_desc, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $slug, $excerpt, $content, $author, $category, $tags, $metaTitle, $metaDesc, $status]);
                $bid = (int)$pdo->lastInsertId();

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Blog Post Created', 'blog_posts', $bid, "Published article '{$title}'");
                $success = "Blog article '{$title}' published successfully!";
            } catch (Exception $e) {
                $error = 'Failed to create blog post.';
            }
        }
    }
}

// Handle Toggle Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $bid = (int)($_POST['blog_id'] ?? 0);
        $newStatus = in_array($_POST['new_status'] ?? '', ['published', 'draft', 'archived']) ? $_POST['new_status'] : 'draft';
        if ($bid > 0) {
            try {
                $pdo->prepare("UPDATE blog_posts SET status = ? WHERE id = ?")->execute([$newStatus, $bid]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Blog Status Updated', 'blog_posts', $bid, "Set status to {$newStatus}");
                $success = "Article status updated to " . ucfirst($newStatus) . "!";
            } catch (Exception $e) {
                $error = 'Failed to update article status.';
            }
        }
    }
}

// Handle Delete Post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_blog') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $bid = (int)($_POST['blog_id'] ?? 0);
        if ($bid > 0) {
            try {
                $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$bid]);
                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Blog Post Deleted', 'blog_posts', $bid, "Deleted blog post #{$bid}");
                $success = "Blog post deleted.";
            } catch (Exception $e) {
                $error = 'Failed to delete blog post.';
            }
        }
    }
}

// Search and Filter
$search = sanitizeInput($_GET['search'] ?? '');
$statusFilter = in_array($_GET['status'] ?? '', ['published', 'draft', 'archived']) ? $_GET['status'] : '';

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(title LIKE ? OR category LIKE ? OR tags LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like]);
}

if (!empty($statusFilter)) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

$whereClause = implode(' AND ', $where);

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE {$whereClause}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE {$whereClause} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Articles & Engineering Insights</h1>
        <p class="page-subtitle">Publish articles, software engineering thought leadership, and SEO content.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" onclick="openAdminModal('newBlogModal')">+ New Article</button>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Filters -->
<form method="GET" action="blog.php" class="filter-bar">
    <div class="filter-group">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--admin-text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="search" class="filter-input" placeholder="Search by title, category, or tags..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div style="min-width: 150px;">
        <select name="status" class="filter-input" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="archived" <?= $statusFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
        </select>
    </div>
    <button type="submit" class="btn btn-outline">Filter</button>
    <?php if (!empty($search) || !empty($statusFilter)): ?>
        <a href="blog.php" class="btn btn-sm btn-outline" style="color: var(--admin-danger);">Clear</a>
    <?php endif; ?>
</form>

<!-- Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Article Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Slug</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr><td colspan="7" class="empty-state">No blog articles registered.</td></tr>
                <?php else: foreach ($posts as $b): 
                    $bStatusClass = match($b['status']) {
                        'published' => 'badge-success',
                        'archived' => 'badge-danger',
                        default => 'badge-neutral'
                    };
                ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($b['title']) ?></strong>
                            <?php if (!empty($b['excerpt'])): ?>
                                <div style="font-size: 11.5px; color: var(--admin-text-muted); max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars($b['excerpt']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-neutral"><?= htmlspecialchars($b['category'] ?? 'Technology') ?></span></td>
                        <td><?= htmlspecialchars($b['author_name']) ?></td>
                        <td><code><?= htmlspecialchars($b['slug']) ?></code></td>
                        <td style="font-size: 12px; color: var(--admin-text-muted);"><?= date('d M Y', strtotime($b['published_at'] ?? $b['created_at'])) ?></td>
                        <td>
                            <span class="badge <?= $bStatusClass ?>">
                                <span class="badge-dot"></span><?= ucfirst(htmlspecialchars($b['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <form method="POST" action="blog.php" style="margin: 0;">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="blog_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="new_status" value="<?= $b['status'] === 'published' ? 'draft' : 'published' ?>">
                                    <button type="submit" class="btn btn-sm <?= $b['status'] === 'published' ? 'btn-outline' : 'btn-success' ?>">
                                        <?= $b['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
                                    </button>
                                </form>
                                <form method="POST" action="blog.php" style="margin: 0;" onsubmit="return confirm('Delete article <?= htmlspecialchars(addslashes($b['title'])) ?>?');">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="delete_blog">
                                    <input type="hidden" name="blog_id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-icon" title="Delete" style="color: var(--admin-danger);">&times;</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <div>Showing <?= ($offset + 1) ?> to <?= min($totalRecords, $offset + $perPage) ?> of <?= $totalRecords ?> articles</div>
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= ($page - 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="pagination-item">&laquo; Prev</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="pagination-item <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= ($page + 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="pagination-item">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: New Blog Post -->
<div id="newBlogModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 680px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Write Technical Blog Article</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('newBlogModal')">&times;</button>
        </div>
        <form method="POST" action="blog.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="add_blog">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Article Headline *</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Modern High-Velocity Architecture with PHP 8 and Native Web APIs" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="Software Engineering">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Custom URL Slug (Leave blank for auto)</label>
                        <input type="text" name="slug" class="form-control" placeholder="auto-generated-from-title">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Summary / Excerpt *</label>
                    <textarea name="excerpt" class="form-control" rows="2" placeholder="Brief teaser for social preview and search index..." required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Full Article Body *</label>
                    <textarea name="content" class="form-control" rows="8" placeholder="Complete article content..." required></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Author Byline</label>
                        <input type="text" name="author_name" class="form-control" value="<?= htmlspecialchars($currentAdmin['full_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tags (comma separated)</label>
                        <input type="text" name="tags" class="form-control" placeholder="PHP, API, Performance">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">SEO Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" placeholder="Default: Same as Headline">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Publication Status</label>
                        <select name="status" class="form-control">
                            <option value="published" selected>Published to Live Site</option>
                            <option value="draft">Save as Draft</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('newBlogModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Publish Article</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
