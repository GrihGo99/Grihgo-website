<?php
/**
 * GrihGO Digital & Tech — Client Notifications Feed
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/includes/auth.php';

$portalPageTitle = 'Notifications';
$portalHeaderTitle = 'Notifications & System Alerts';
$current_portal_page = 'notifications';

$pdo = Database::getConnection();

// Scoped strictly to authenticated client + general system notices
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE client_id = :cid OR client_id IS NULL 
    ORDER BY is_read ASC, created_at DESC 
    LIMIT 50
");
$stmt->execute([':cid' => $clientId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hasUnread = false;
foreach ($notifications as $n) {
    if (empty($n['is_read'])) {
        $hasUnread = true;
        break;
    }
}

include __DIR__ . '/includes/header.php';
?>

<div style="max-width: 860px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--portal-text-main); margin: 0 0 4px 0;">Notification Feed</h2>
            <p style="font-size: 0.85rem; color: var(--portal-text-muted); margin: 0;">Milestone achievements, invoice dispatches, appointment updates, and system alerts</p>
        </div>

        <?php if ($hasUnread): ?>
            <button type="button" id="btnMarkAllNotifications" class="btn-portal-outline btn-portal-sm" style="background: #ffffff;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>Mark All as Read</span>
            </button>
        <?php endif; ?>
    </div>

    <div class="portal-card">
        <div class="portal-card-header">
            <h3 class="portal-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                <span>Recent Alerts (<?= count($notifications) ?>)</span>
            </h3>
        </div>

        <div class="portal-card-body" style="padding: 0;">
            <?php if (empty($notifications)): ?>
                <div class="portal-empty-state">
                    <div class="portal-empty-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path></svg>
                    </div>
                    <div class="portal-empty-title">You're All Caught Up</div>
                    <p class="portal-empty-desc">No new notifications are pending review.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column;">
                    <?php foreach ($notifications as $notif): ?>
                        <?php 
                        $isUnread = empty($notif['is_read']); 
                        $type = $notif['type'];
                        $iconColor = '#3b82f6';
                        if (str_contains(strtolower($type), 'invoice') || str_contains(strtolower($type), 'payment')) $iconColor = '#10b981';
                        elseif (str_contains(strtolower($type), 'appointment')) $iconColor = '#8b5cf6';
                        elseif (str_contains(strtolower($type), 'ticket')) $iconColor = '#f59e0b';
                        ?>
                        <div id="notif-item-<?= (int)$notif['id'] ?>" 
                             class="notification-item <?= $isUnread ? 'notification-unread' : '' ?>"
                             style="padding: 18px 24px; border-bottom: 1px solid var(--portal-border); display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; background: <?= $isUnread ? '#f8fafc' : '#ffffff' ?>; border-left: 4px solid <?= $isUnread ? 'var(--portal-primary)' : 'transparent' ?>; transition: var(--portal-transition);">
                            <div style="display: flex; gap: 14px; align-items: flex-start;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: <?= $isUnread ? 'rgba(30, 91, 168, 0.1)' : '#f1f5f9' ?>; color: <?= $iconColor ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1rem;">
                                    ⚡
                                </div>
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: <?= $iconColor ?>; letter-spacing: 0.5px;">
                                            <?= htmlspecialchars($notif['type'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <?php if ($isUnread): ?>
                                            <span style="background: var(--portal-primary); color: #ffffff; font-size: 0.65rem; font-weight: 700; padding: 1px 6px; border-radius: 4px;">NEW</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--portal-text-main); margin: 3px 0 2px 0;">
                                        <?= htmlspecialchars($notif['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: #475569; line-height: 1.5;">
                                        <?= htmlspecialchars($notif['message'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 6px;">
                                        <?= date('d M Y, h:i A', strtotime($notif['created_at'])) ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($isUnread): ?>
                                <div>
                                    <button type="button" class="btn-portal-outline btn-portal-sm btn-mark-notif-read" 
                                            data-id="<?= (int)$notif['id'] ?>" title="Mark Read" style="white-space: nowrap;">
                                        Mark as Read
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
