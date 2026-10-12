<?php
/**
 * GrihGO Digital & Tech — Admin Layout Header
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

if (!defined('ADMIN_LOADED')) {
    define('ADMIN_LOADED', true);
}

if (!isset($pdo)) {
    $pdo = Database::getConnection();
}

// Fetch unread notification count
$unreadAdminNotifications = 0;
try {
    $notifStmt = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND (client_id IS NULL OR client_id = 0)");
    $unreadAdminNotifications = (int)$notifStmt->fetchColumn();
} catch (Exception $e) {}

$activePage = basename($_SERVER['PHP_SELF']);
$currentRoleDisplay = ucwords(str_replace('_', ' ', $currentAdmin['role'] ?? 'Admin'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Console') ?> | GrihGO Digital & Tech</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= htmlspecialchars(getCsrfToken()) ?>">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%231e5ba8'><path d='M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'/></svg>">
    <script>
        window.GRIHGO_ADMIN = {
            csrfToken: '<?= htmlspecialchars(getCsrfToken()) ?>'
        };
    </script>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <div class="admin-main">
            <header class="admin-header">
                <div class="admin-header-left">
                    <button type="button" id="adminMobileToggle" class="admin-mobile-toggle" aria-label="Toggle navigation menu">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <h2 class="header-page-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h2>
                </div>

                <div class="admin-header-right">
                    <a href="../index.php" target="_blank" class="header-btn btn-outline-light" title="View Public Website">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                        <span>Live Site</span>
                    </a>

                    <a href="notifications.php" class="notification-bell-btn" title="System Notifications">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <?php if ($unreadAdminNotifications > 0): ?>
                            <span class="notification-indicator"><?= $unreadAdminNotifications > 99 ? '99+' : $unreadAdminNotifications ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="admin-user-pill">
                        <div class="admin-avatar">
                            <?= strtoupper(substr($currentAdmin['full_name'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div class="admin-user-info">
                            <div class="admin-user-name"><?= htmlspecialchars($currentAdmin['full_name'] ?? 'Administrator') ?></div>
                            <div class="admin-user-role"><?= htmlspecialchars($currentRoleDisplay) ?></div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-content">
