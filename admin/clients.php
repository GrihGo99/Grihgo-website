<?php
/**
 * GrihGO Digital & Tech — Client Management Console
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Client Accounts';
$pageSection = 'clients';

$error = '';
$success = '';

// Handle Client Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_client') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $contactPerson = sanitizeInput($_POST['contact_person'] ?? '');
        $companyName   = sanitizeInput($_POST['company_name'] ?? '');
        $email         = sanitizeEmail($_POST['email'] ?? '');
        $phone         = sanitizePhone($_POST['phone'] ?? '');
        $password      = $_POST['password'] ?? '';
        $gstNumber     = sanitizeInput($_POST['gst_number'] ?? '');
        $address       = sanitizeInput($_POST['address'] ?? '');
        $status        = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if (empty($contactPerson) || empty($companyName) || empty($email) || empty($phone) || empty($password)) {
            $error = 'Please fill all mandatory fields (Contact Person, Company Name, Email, Phone, Password).';
        } elseif (!validateEmail($email)) {
            $error = 'Please provide a valid corporate email address.';
        } elseif (strlen($password) < 12) {
            $error = 'Password must contain at least 12 characters.';
        } else {
            // Check if email already registered in users or clients
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'A user account with this email address already exists.';
            } else {
                try {
                    $pdo->beginTransaction();

                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                    // Insert into users table
                    $userStmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, 'client', ?)");
                    $userStmt->execute([$contactPerson, $email, $phone, $passwordHash, $status]);
                    $userId = (int)$pdo->lastInsertId();

                    // Insert into clients table
                    $clientStmt = $pdo->prepare("INSERT INTO clients (user_id, company_name, contact_person, email, phone, address, gst_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $clientStmt->execute([$userId, $companyName, $contactPerson, $email, $phone, $address, $gstNumber, $status]);
                    $clientId = (int)$pdo->lastInsertId();

                    // Create welcome notification for the client
                    $notifStmt = $pdo->prepare("INSERT INTO notifications (client_id, type, title, message, is_read) VALUES (?, 'System', 'Welcome to GrihGO Client Portal', 'Your enterprise portal account has been provisioned. Access projects, invoices, and service requests here.', 0)");
                    $notifStmt->execute([$clientId]);

                    logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Client Created', 'clients', $clientId, "Created client {$companyName} ({$email})");

                    $pdo->commit();
                    $success = "Client account for {$companyName} created successfully!";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Failed to create client account. Please verify input data.';
                }
            }
        }
    }
}

// Handle Status Toggle (Activate / Deactivate)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $clientId = (int)($_POST['client_id'] ?? 0);
        $newStatus = ($_POST['new_status'] ?? '') === 'active' ? 'active' : 'inactive';

        if ($clientId > 0) {
            try {
                $clientInfo = $pdo->query("SELECT company_name, user_id FROM clients WHERE id = {$clientId}")->fetch(PDO::FETCH_ASSOC);
                if ($clientInfo) {
                    $pdo->beginTransaction();
                    $pdo->prepare("UPDATE clients SET status = ? WHERE id = ?")->execute([$newStatus, $clientId]);
                    if (!empty($clientInfo['user_id'])) {
                        $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$newStatus, $clientInfo['user_id']]);
                    }
                    logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Client Status Updated', 'clients', $clientId, "Set status of {$clientInfo['company_name']} to {$newStatus}");
                    $pdo->commit();
                    $success = "Client {$clientInfo['company_name']} has been set to " . ucfirst($newStatus) . ".";
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Unable to update client status.';
            }
        }
    }
}

// Search & Filtering
$search = sanitizeInput($_GET['search'] ?? '');
$statusFilter = in_array($_GET['status'] ?? '', ['active', 'inactive']) ? $_GET['status'] : '';

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(c.company_name LIKE ? OR c.contact_person LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if (!empty($statusFilter)) {
    $where[] = "c.status = ?";
    $params[] = $statusFilter;
}

$whereClause = implode(' AND ', $where);

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM clients c WHERE {$whereClause}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));
$offset = ($page - 1) * $perPage;

// Query Clients with Project and Invoice Aggregates
$sql = "
    SELECT 
        c.*,
        (SELECT COUNT(*) FROM projects p WHERE p.client_id = c.id AND p.status IN ('In Progress', 'Testing', 'Review')) AS active_projects_count,
        (SELECT COUNT(*) FROM invoices i WHERE i.client_id = c.id AND i.status NOT IN ('Paid', 'Cancelled')) AS pending_invoices_count
    FROM clients c
    WHERE {$whereClause}
    ORDER BY c.id DESC
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <h1>Client Accounts</h1>
        <p class="page-subtitle">Manage client profiles, active contract scopes, and portal access credentials.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" onclick="openAdminModal('newClientModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Create New Client</span>
        </button>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Filter Bar -->
<form method="GET" action="clients.php" class="filter-bar">
    <div class="filter-group">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--admin-text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="search" class="filter-input" placeholder="Search by company, name, email or phone..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div style="min-width: 150px;">
        <select name="status" class="filter-input" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
    <button type="submit" class="btn btn-outline">Filter</button>
    <?php if (!empty($search) || !empty($statusFilter)): ?>
        <a href="clients.php" class="btn btn-sm btn-outline" style="color: var(--admin-danger);">Reset</a>
    <?php endif; ?>
</form>

<!-- Clients Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Client ID</th>
                    <th>Company / Business</th>
                    <th>Contact Person</th>
                    <th>Contact Details</th>
                    <th>Active Projects</th>
                    <th>Pending Invoices</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients)): ?>
                    <tr><td colspan="9" class="empty-state">No client accounts matching your query.</td></tr>
                <?php else: foreach ($clients as $c): ?>
                    <tr>
                        <td><code>#CL-<?= str_pad($c['id'], 3, '0', STR_PAD_LEFT) ?></code></td>
                        <td>
                            <strong><?= htmlspecialchars($c['company_name']) ?></strong>
                            <?php if (!empty($c['gst_number'])): ?>
                                <div style="font-size: 11px; color: var(--admin-text-muted);">GST: <?= htmlspecialchars($c['gst_number']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($c['contact_person']) ?></td>
                        <td>
                            <div><a href="mailto:<?= htmlspecialchars($c['email']) ?>" style="color: var(--admin-primary); text-decoration: none;"><?= htmlspecialchars($c['email']) ?></a></div>
                            <div style="font-size: 11.5px; color: var(--admin-text-muted);"><?= htmlspecialchars($c['phone']) ?></div>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge <?= $c['active_projects_count'] > 0 ? 'badge-primary' : 'badge-neutral' ?>">
                                <?= (int)$c['active_projects_count'] ?> active
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge <?= $c['pending_invoices_count'] > 0 ? 'badge-warning' : 'badge-neutral' ?>">
                                <?= (int)$c['pending_invoices_count'] ?> pending
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $c['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                <span class="badge-dot"></span><?= ucfirst(htmlspecialchars($c['status'])) ?>
                            </span>
                        </td>
                        <td style="font-size: 12px; color: var(--admin-text-muted);"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                        <td>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <a href="client-details.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline" title="View Full 360 Client Dossier">View 360°</a>
                                <form method="POST" action="clients.php" style="margin: 0;" onsubmit="return confirm('Toggle status for <?= htmlspecialchars(addslashes($c['company_name'])) ?>?');">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="client_id" value="<?= $c['id'] ?>">
                                    <input type="hidden" name="new_status" value="<?= $c['status'] === 'active' ? 'inactive' : 'active' ?>">
                                    <button type="submit" class="btn btn-sm <?= $c['status'] === 'active' ? 'btn-outline' : 'btn-success' ?>" title="<?= $c['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                        <?= $c['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                    </button>
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
            <div>Showing <?= ($offset + 1) ?> to <?= min($totalRecords, $offset + $perPage) ?> of <?= $totalRecords ?> clients</div>
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

<!-- Modal: Create New Client -->
<div id="newClientModal" class="admin-modal-backdrop">
    <div class="admin-modal" style="max-width: 600px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Provision New Client Account</h3>
            <button type="button" class="btn-icon" onclick="closeAdminModal('newClientModal')">&times;</button>
        </div>
        <form method="POST" action="clients.php">
            <?= getCsrfField() ?>
            <input type="hidden" name="action" value="create_client">

            <div class="admin-modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Contact Person Name *</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="e.g. Ramesh Varma" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company / Business Name *</label>
                        <input type="text" name="company_name" class="form-control" placeholder="e.g. Nexus Logix Pvt Ltd" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Email Address (Login Username) *</label>
                        <input type="email" name="email" class="form-control" placeholder="client@company.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Portal Access Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="At least 12 characters" minlength="12" autocomplete="new-password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">GST / Tax Identification Number</label>
                        <input type="text" name="gst_number" class="form-control" placeholder="e.g. 07AAAAA0000A1Z5">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Office Registered Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Full postal address"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Account Initial Status</label>
                    <select name="status" class="form-control">
                        <option value="active">Active (Granted Immediate Portal Access)</option>
                        <option value="inactive">Inactive / On Hold</option>
                    </select>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAdminModal('newClientModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Client Account</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
