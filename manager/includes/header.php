<?php
/**
 * GrihGO Digital & Tech — Dedicated Manager Portal Header
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

if (!isset($pdo)) {
    $pdo = Database::getConnection();
}

$managerId = (int)($_SESSION['manager_id'] ?? 0);
$unreadManagerNotifications = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND (user_role = 'manager' OR (client_id IS NULL AND user_role IS NULL))");
    $unreadManagerNotifications = (int)$stmt->fetchColumn();
} catch (Exception $e) {}

$activePage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Manager Console') ?> | GrihGO Digital &amp; Tech</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= htmlspecialchars(getCsrfToken()) ?>">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23059669'><path d='M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'></path><circle cx='8.5' cy='7' r='4'></circle><polyline points='17 11 19 13 23 9'></polyline></svg>">
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
                    <div class="admin-header-title">
                        <h1 style="font-size: 1.15rem; margin: 0; color: #0f172a;"><?= htmlspecialchars($pageTitle ?? 'Manager Workspace') ?></h1>
                        <span class="admin-header-subtitle" style="font-size: 0.8rem; color: #64748b;">
                            Manager Console &bull; <?= htmlspecialchars($currentManager['full_name'] ?? 'Assigned Manager') ?>
                        </span>
                    </div>
                </div>

                <div class="admin-header-right">
                    <!-- Notifications -->
                    <a href="notifications.php" class="header-icon-btn" title="View Notifications" style="position: relative;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <?php if ($unreadManagerNotifications > 0): ?>
                            <span class="notification-badge" style="background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; border-radius: 999px; padding: 2px 6px; position: absolute; top: -4px; right: -4px;">
                                <?= $unreadManagerNotifications ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- User Profile Dropdown Pill -->
                    <div class="admin-profile-pill">
                        <div class="profile-avatar" style="background: #059669; color: #fff; font-weight: 700; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                            <?= strtoupper(substr($currentManager['full_name'] ?? 'M', 0, 1)) ?>
                        </div>
                        <div class="profile-meta">
                            <strong style="display: block; font-size: 0.85rem; color: #0f172a;"><?= htmlspecialchars($currentManager['full_name'] ?? 'Manager') ?></strong>
                            <span style="font-size: 0.75rem; color: #059669; font-weight: 600;">Project &amp; Lead Manager</span>
                        </div>
                        <a href="logout.php" class="btn btn-sm btn-ghost" title="Logout" style="margin-left: 8px; font-size: 0.8rem; color: #ef4444;">
                            Exit
                        </a>
                    </div>
                </div>
            </header>

            <main class="admin-content-area" style="padding: 24px;">
