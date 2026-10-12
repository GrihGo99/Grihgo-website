<?php
/**
 * GrihGO Digital & Tech — Manager Notification Center
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Manager Notifications';
$pageSection = 'notifications';
$managerId = (int)$currentManager['id'];

// Handle Mark All Read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_all_read') {
    if (validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE (user_role = 'manager' OR (client_id IS NULL AND user_role IS NULL))")
            ->execute();
        header('Location: notifications.php?msg=all_read');
        exit;
    }
}

// Handle Mark Single Read
if (isset($_GET['mark_read'])) {
    $notifId = (int)$_GET['mark_read'];
    if ($notifId > 0) {
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?")->execute([$notifId]);
    }
    header('Location: notifications.php');
    exit;
}

// Fetch notifications
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE (user_role = 'manager' OR (client_id IS NULL AND user_role IS NULL))
    ORDER BY id DESC LIMIT 50
");
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unreadCount = count(array_filter($notifications, fn($n) => (int)$n['is_read'] === 0));

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-content">
    <div class="admin-header-bar">
        <div>
            <h1 class="page-title">Notification Center</h1>
            <p class="page-subtitle">Real-time alerts on assigned leads, project tasks, client ticket responses, and appointment changes.</p>
        </div>
        <?php if ($unreadCount > 0): ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="btn-primary" style="background: #0256c4; border: none; padding: 8px 16px; border-radius: 6px; color: #fff; cursor: pointer; font-size: 0.85rem; font-weight: 500;">
                    Mark All as Read
                </button>
            </form>
        <?php endif; ?>
    </div>

    <div class="admin-card">
        <div class="card-body" style="padding: 0;">
            <?php if (empty($notifications)): ?>
                <div style="text-align: center; padding: 50px 20px; color: #6b7280;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; color: #9ca3af;">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <p style="font-size: 1rem; margin: 0;">You're completely caught up! No recent notifications.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column;">
                    <?php foreach ($notifications as $n): ?>
                        <div style="padding: 16px 20px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; background: <?= (int)$n['is_read'] === 0 ? '#f0fdf4' : '#fff' ?>;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <?php if ((int)$n['is_read'] === 0): ?>
                                        <span style="display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 9999px;"></span>
                                    <?php endif; ?>
                                    <strong style="color: #111827; font-size: 0.95rem;"><?= htmlspecialchars($n['title']) ?></strong>
                                    <span style="font-size: 0.75rem; color: #6b7280;">&bull; <?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></span>
                                </div>
                                <div style="color: #4b5563; font-size: 0.9rem; line-height: 1.4;">
                                    <?= htmlspecialchars($n['message']) ?>
                                </div>
                                <?php if (!empty($n['link'])): ?>
                                    <div style="margin-top: 6px;">
                                        <a href="../<?= htmlspecialchars($n['link']) ?>" style="font-size: 0.85rem; color: #059669; font-weight: 500; text-decoration: none;">
                                            View Related Record &rarr;
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ((int)$n['is_read'] === 0): ?>
                                <a href="notifications.php?mark_read=<?= $n['id'] ?>" title="Mark as read" style="color: #9ca3af; text-decoration: none; padding: 4px; font-size: 0.8rem;">
                                    Dismiss
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
