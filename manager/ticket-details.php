<?php
/**
 * GrihGO Digital & Tech — Manager Ticket Thread & Resolution
 * Version: 4.5 (Antigravity 2.0 Integration)
 */

require_once __DIR__ . '/includes/auth.php';

$ticketId = (int)($_GET['id'] ?? 0);
if ($ticketId <= 0) {
    header('Location: support-tickets.php');
    exit;
}

$managerId = (int)$currentManager['id'];

// Check permission: manager can access assigned or unassigned tickets
$stmt = $pdo->prepare("
    SELECT t.*, c.company_name, c.contact_person, c.email AS client_email, c.phone AS client_phone 
    FROM support_tickets t 
    LEFT JOIN clients c ON t.client_id = c.id 
    WHERE t.id = ? AND (t.assigned_to = ? OR t.assigned_to IS NULL OR ? IN ('admin', 'super_admin'))
    LIMIT 1
");
$stmt->execute([$ticketId, $managerId, $currentManager['role']]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    header('Location: support-tickets.php?error=not_found');
    exit;
}

$pageTitle = 'Ticket #' . $ticket['ticket_number'] . ' — ' . htmlspecialchars($ticket['subject']);
$pageSection = 'support-tickets';

$error = '';
$success = '';
$validStatuses = ['Open', 'In Progress', 'Waiting for Client', 'Resolved', 'Closed'];
$validPriorities = ['Low', 'Medium', 'High', 'Urgent'];

// Handle Manager Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply_ticket') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $message   = sanitizeInput($_POST['message'] ?? '');
        $newStatus = in_array($_POST['status'] ?? '', $validStatuses, true) ? $_POST['status'] : 'Waiting for Client';

        if (empty($message)) {
            $error = 'Reply message cannot be empty.';
        } else {
            try {
                $pdo->beginTransaction();

                // Insert into ticket_messages
                $msgStmt = $pdo->prepare("
                    INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, sender_name, message) 
                    VALUES (?, 'staff', ?, ?, ?)
                ");
                $msgStmt->execute([$ticketId, $managerId, $currentManager['full_name'], $message]);

                // Update ticket status
                $pdo->prepare("UPDATE support_tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                    ->execute([$newStatus, $ticketId]);

                // Notify Client
                addSystemNotification(
                    (int)$ticket['client_id'],
                    "New Reply on Ticket {$ticket['ticket_number']}",
                    "Manager {$currentManager['full_name']} replied: \"" . substr($message, 0, 80) . "...\"",
                    'client',
                    'client/support.php?id=' . $ticketId
                );

                // Notify Admin
                addSystemNotification(
                    null,
                    "Ticket Replied: #{$ticket['ticket_number']}",
                    "Manager {$currentManager['full_name']} sent a response and updated status to {$newStatus}.",
                    'admin',
                    'admin/ticket-details.php?id=' . $ticketId
                );

                $pdo->commit();

                // Re-fetch ticket
                $stmt->execute([$ticketId, $managerId, $currentManager['role']]);
                $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
                $success = 'Reply posted and dispatched to Client Portal successfully.';
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Failed to post reply: ' . $e->getMessage();
            }
        }
    }
}

// Handle Status & Priority Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_meta') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $newStatus   = in_array($_POST['status'] ?? '', $validStatuses, true) ? $_POST['status'] : $ticket['status'];
        $newPriority = in_array($_POST['priority'] ?? '', $validPriorities, true) ? $_POST['priority'] : $ticket['priority'];

        try {
            $pdo->prepare("UPDATE support_tickets SET status = ?, priority = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                ->execute([$newStatus, $newPriority, $ticketId]);

            $stmt->execute([$ticketId, $managerId, $currentManager['role']]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            $success = 'Ticket metadata updated successfully.';
        } catch (Exception $e) {
            $error = 'Failed to update metadata.';
        }
    }
}

// Fetch Thread Messages
$threadStmt = $pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY id ASC");
$threadStmt->execute([$ticketId]);
$messages = $threadStmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-content">
    <div class="admin-header-bar">
        <div>
            <div style="font-size: 0.85rem; color: #6b7280; margin-bottom: 4px;">
                <a href="support-tickets.php" style="color: #059669; text-decoration: none;">&larr; Back to Tickets</a>
            </div>
            <h1 class="page-title">Ticket #<?= htmlspecialchars($ticket['ticket_number']) ?></h1>
            <p class="page-subtitle"><?= htmlspecialchars($ticket['subject']) ?></p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="tel:<?= htmlspecialchars($ticket['client_phone']) ?>" class="btn-primary" style="background: #0256c4; text-decoration: none; padding: 8px 14px; font-size: 0.85rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                Call Client
            </a>
            <a href="https://wa.me/91<?= preg_replace('/[^0-9]/', '', $ticket['client_phone']) ?>?text=<?= urlencode('Hello ' . $ticket['contact_person'] . ', this is ' . $currentManager['full_name'] . ' regarding GrihGO Support Ticket #' . $ticket['ticket_number'] . '.') ?>" target="_blank" class="btn-primary" style="background: #059669; text-decoration: none; padding: 8px 14px; font-size: 0.85rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                </svg>
                WhatsApp
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div style="background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Left: Messages & Reply -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Initial Query / Description -->
            <div class="admin-card" style="border-left: 4px solid #0256c4;">
                <div class="card-header" style="background: #f8fafc; padding: 12px 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 600; color: #1e3a8a;">Client Inquiry — <?= htmlspecialchars($ticket['contact_person']) ?></span>
                        <span style="font-size: 0.8rem; color: #64748b;"><?= date('d M Y, h:i A', strtotime($ticket['created_at'])) ?></span>
                    </div>
                </div>
                <div class="card-body" style="padding: 18px 20px; line-height: 1.6; color: #334155; white-space: pre-wrap; font-size: 0.95rem;"><?= htmlspecialchars($ticket['description'] ?? $ticket['message'] ?? 'No body specified.') ?></div>
            </div>

            <!-- Follow-up Conversation Thread -->
            <?php foreach ($messages as $m): ?>
                <?php $isStaff = ($m['sender_type'] === 'staff' || $m['sender_type'] === 'admin'); ?>
                <div class="admin-card" style="margin-left: <?= $isStaff ? '24px' : '0' ?>; margin-right: <?= $isStaff ? '0' : '24px' ?>; border-left: 4px solid <?= $isStaff ? '#10b981' : '#f59e0b' ?>;">
                    <div class="card-header" style="background: <?= $isStaff ? '#f0fdf4' : '#fffbeb' ?>; padding: 10px 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600; color: <?= $isStaff ? '#065f46' : '#92400e' ?>;">
                                <?= htmlspecialchars($m['sender_name']) ?> <?= $isStaff ? '(GrihGO Manager)' : '(Client)' ?>
                            </span>
                            <span style="font-size: 0.8rem; color: #6b7280;"><?= date('d M Y, h:i A', strtotime($m['created_at'])) ?></span>
                        </div>
                    </div>
                    <div class="card-body" style="padding: 16px 18px; line-height: 1.5; color: #1f2937; white-space: pre-wrap; font-size: 0.9rem;">
                        <?= htmlspecialchars($m['message']) ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Post Reply Box -->
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 1.05rem;">Submit Resolution Reply</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="reply_ticket">

                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 6px;">Your Message *</label>
                            <textarea name="message" rows="5" placeholder="Compose a clear and helpful response for the client..." class="form-control" required style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-family: inherit; font-size: 0.9rem;"></textarea>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <label style="font-size: 0.85rem; font-weight: 600; color: #374151;">Set Status To:</label>
                                <select name="status" class="form-control" style="padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.85rem;">
                                    <option value="Waiting for Client" selected>Waiting for Client</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Resolved">Resolved</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>

                            <button type="submit" class="btn-primary" style="background: #059669; border: none; padding: 10px 20px; font-weight: 600; border-radius: 6px; color: #fff; cursor: pointer;">
                                Send Reply &amp; Notify Client
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Status, Priority & Client Details -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Status & Priority Card -->
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 1rem;">Ticket Controls</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="update_meta">

                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Status</label>
                            <select name="status" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                                <?php foreach ($validStatuses as $st): ?>
                                    <option value="<?= $st ?>" <?= $ticket['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px;">Priority</label>
                            <select name="priority" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                                <?php foreach ($validPriorities as $pr): ?>
                                    <option value="<?= $pr ?>" <?= $ticket['priority'] === $pr ? 'selected' : '' ?>><?= $pr ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn-primary" style="width: 100%; background: #0256c4; border: none; padding: 8px; font-weight: 600; border-radius: 6px; color: #fff; cursor: pointer;">
                            Update Settings
                        </button>
                    </form>
                </div>
            </div>

            <!-- Client Info Card -->
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 1rem;">Client Contact</h3>
                </div>
                <div class="card-body" style="font-size: 0.9rem; color: #4b5563;">
                    <div style="margin-bottom: 8px;">
                        <strong>Company:</strong><br>
                        <?= htmlspecialchars($ticket['company_name'] ?: 'Not recorded') ?>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>Contact:</strong><br>
                        <?= htmlspecialchars($ticket['contact_person']) ?>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>Email:</strong><br>
                        <a href="mailto:<?= htmlspecialchars($ticket['client_email']) ?>" style="color: #0256c4;"><?= htmlspecialchars($ticket['client_email']) ?></a>
                    </div>
                    <div>
                        <strong>Phone:</strong><br>
                        <a href="tel:<?= htmlspecialchars($ticket['client_phone']) ?>" style="color: #059669; font-weight: 600;"><?= htmlspecialchars($ticket['client_phone']) ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
