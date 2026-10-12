<?php
/**
 * GrihGO Digital & Tech — Support Ticket Thread & Reply Center
 * Version: 4.0 (Part 4 - Complete Admin Panel Architecture)
 */

require_once __DIR__ . '/includes/auth.php';

$ticketId = (int)($_GET['id'] ?? 0);
if ($ticketId <= 0) {
    header('Location: support-tickets.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT t.*, c.company_name, c.contact_person, c.email AS client_email, c.phone AS client_phone 
    FROM support_tickets t 
    LEFT JOIN clients c ON t.client_id = c.id 
    WHERE t.id = ? LIMIT 1
");
$stmt->execute([$ticketId]);
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

// Handle Admin Reply Submission (Section 22 & 43)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply_ticket') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $message = sanitizeInput($_POST['message'] ?? '');
        $newStatus = in_array($_POST['status'] ?? '', $validStatuses) ? $_POST['status'] : $ticket['status'];

        if (empty($message)) {
            $error = 'Please enter a reply message.';
        } else {
            try {
                $pdo->beginTransaction();

                // Insert into ticket_messages
                $msgStmt = $pdo->prepare("
                    INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, sender_name, message) 
                    VALUES (?, 'staff', ?, ?, ?)
                ");
                $msgStmt->execute([$ticketId, (int)$currentAdmin['id'], $currentAdmin['full_name'], $message]);

                // Update ticket status & updated_at
                $pdo->prepare("UPDATE support_tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                    ->execute([$newStatus, $ticketId]);

                // Create client notification
                $pdo->prepare("
                    INSERT INTO notifications (client_id, type, title, message, reference_id, is_read) 
                    VALUES (?, 'Support', ?, ?, ?, 0)
                ")->execute([
                    $ticket['client_id'],
                    "New Reply on Ticket {$ticket['ticket_number']}",
                    "Our support engineer has replied to your query: \"{$ticket['subject']}\".",
                    $ticketId
                ]);

                logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Ticket Replied', 'support_tickets', $ticketId, "Replied on ticket {$ticket['ticket_number']}");

                $pdo->commit();

                // Refresh ticket
                $stmt->execute([$ticketId]);
                $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
                $success = 'Reply posted successfully and dispatched to Client Portal!';
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Failed to post reply.';
            }
        }
    }
}

// Handle Status & Priority Setting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_meta') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $newStatus   = in_array($_POST['status'] ?? '', $validStatuses) ? $_POST['status'] : $ticket['status'];
        $newPriority = in_array($_POST['priority'] ?? '', $validPriorities) ? $_POST['priority'] : $ticket['priority'];

        try {
            $pdo->prepare("UPDATE support_tickets SET status = ?, priority = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                ->execute([$newStatus, $newPriority, $ticketId]);

            // Notify client if status changed
            if ($newStatus !== $ticket['status']) {
                $pdo->prepare("INSERT INTO notifications (client_id, type, title, message, reference_id, is_read) VALUES (?, 'Support', ?, ?, ?, 0)")
                    ->execute([
                        $ticket['client_id'],
                        "Ticket {$ticket['ticket_number']} Status: {$newStatus}",
                        "Ticket #{$ticket['ticket_number']} status updated to {$newStatus}.",
                        $ticketId
                    ]);
            }

            logAdminActivity($pdo, (int)$currentAdmin['id'], $currentAdmin['full_name'], 'Ticket Meta Updated', 'support_tickets', $ticketId, "Updated ticket {$ticket['ticket_number']} status to {$newStatus}, priority {$newPriority}");

            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            $success = 'Ticket status & priority updated!';
        } catch (Exception $e) {
            $error = 'Failed to update ticket settings.';
        }
    }
}

// Fetch Thread Messages
$msgStmt = $pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY id ASC");
$msgStmt->execute([$ticketId]);
$messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
    <div class="page-title-group">
        <div style="font-size: 13px; color: var(--admin-text-muted); margin-bottom: 4px;">
            <a href="support-tickets.php" style="color: var(--admin-primary); text-decoration: none;">&larr; Back to Support Tickets</a>
        </div>
        <h1>Ticket <?= htmlspecialchars($ticket['ticket_number']) ?>: <?= htmlspecialchars($ticket['subject']) ?></h1>
        <p class="page-subtitle">Client: <a href="client-details.php?id=<?= $ticket['client_id'] ?>" style="color: var(--admin-primary); text-decoration: none; font-weight: 600;"><?= htmlspecialchars($ticket['company_name']) ?></a> &bull; Category: <?= htmlspecialchars($ticket['category']) ?></p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- Left: Conversation Thread & Reply Box -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Conversation Thread (<?= count($messages) ?> Messages)</h3>
                <span class="badge badge-primary"><?= htmlspecialchars($ticket['status']) ?></span>
            </div>
            <div class="admin-card-body" style="padding: 24px; max-height: 520px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px;">
                <?php if (empty($messages)): ?>
                    <div class="empty-state">No messages logged in this ticket thread.</div>
                <?php else: foreach ($messages as $m): 
                    $isStaff = $m['sender_type'] === 'staff';
                ?>
                    <div style="display: flex; flex-direction: column; align-items: <?= $isStaff ? 'flex-end' : 'flex-start' ?>;">
                        <div style="font-size: 11.5px; color: var(--admin-text-muted); margin-bottom: 4px;">
                            <strong><?= htmlspecialchars($m['sender_name']) ?></strong> &bull; <?= date('d M Y, h:i A', strtotime($m['created_at'])) ?>
                        </div>
                        <div style="background: <?= $isStaff ? 'var(--admin-primary)' : '#f1f5f9' ?>; color: <?= $isStaff ? '#ffffff' : 'var(--admin-text-main)' ?>; padding: 14px 18px; border-radius: 12px; max-width: 80%; line-height: 1.5; font-size: 13.5px; border-top-<?= $isStaff ? 'right' : 'left' ?>-radius: 2px;">
                            <?= nl2br(htmlspecialchars($m['message'])) ?>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- Post Admin Reply Box -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Post Official Reply to Client</h3>
            </div>
            <form method="POST" action="ticket-details.php?id=<?= $ticketId ?>" class="admin-card-body">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="reply_ticket">

                <div class="form-group">
                    <label class="form-label">Response Message *</label>
                    <textarea name="message" class="form-control" rows="4" placeholder="Type your response to the client..." required></textarea>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <label class="form-label" style="margin: 0;">Set Ticket Status to:</label>
                        <select name="status" class="form-control" style="width: auto;">
                            <?php foreach ($validStatuses as $st): ?>
                                <option value="<?= $st ?>" <?= ($st === 'Waiting for Client') ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        <span>Send Response to Client</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right: Ticket Metadata & Quick Actions -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Ticket Information</h3>
            </div>
            <div class="admin-card-body" style="font-size: 13.5px; line-height: 1.8;">
                <div><strong style="color: var(--admin-text-muted);">Ticket ID:</strong><br><code><?= htmlspecialchars($ticket['ticket_number']) ?></code></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Client Entity:</strong><br><?= htmlspecialchars($ticket['company_name']) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Authorized Contact:</strong><br><?= htmlspecialchars($ticket['contact_person']) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Official Email:</strong><br><a href="mailto:<?= htmlspecialchars($ticket['client_email']) ?>" style="color: var(--admin-primary);"><?= htmlspecialchars($ticket['client_email']) ?></a></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Contact Phone:</strong><br><?= htmlspecialchars($ticket['client_phone']) ?></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Category:</strong><br><span class="badge badge-neutral"><?= htmlspecialchars($ticket['category']) ?></span></div>
                <div style="margin-top: 10px;"><strong style="color: var(--admin-text-muted);">Logged On:</strong><br><?= date('d M Y, h:i A', strtotime($ticket['created_at'])) ?></div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Manage Status & Priority</h3>
            </div>
            <form method="POST" action="ticket-details.php?id=<?= $ticketId ?>" class="admin-card-body">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="update_meta">

                <div class="form-group">
                    <label class="form-label">Current Status</label>
                    <select name="status" class="form-control">
                        <?php foreach ($validStatuses as $st): ?>
                            <option value="<?= $st ?>" <?= $ticket['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Priority Level</label>
                    <select name="priority" class="form-control">
                        <?php foreach ($validPriorities as $pr): ?>
                            <option value="<?= $pr ?>" <?= $ticket['priority'] === $pr ? 'selected' : '' ?>><?= $pr ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-outline btn-block">Save Ticket Status</button>
            </form>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
