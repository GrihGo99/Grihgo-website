<?php
/**
 * GrihGO Digital & Tech — Support Ticket Desk & Thread Interface
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/includes/auth.php';

$pdo = Database::getConnection();

$ticketId = !empty($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
$activeTicket = null;
$ticketMessages = [];

// If a specific ticket is selected, verify ownership and load thread
if ($ticketId > 0) {
    $stmtTck = $pdo->prepare("SELECT * FROM support_tickets WHERE id = :tid AND client_id = :cid LIMIT 1");
    $stmtTck->execute([':tid' => $ticketId, ':cid' => $clientId]);
    $activeTicket = $stmtTck->fetch(PDO::FETCH_ASSOC);

    if ($activeTicket) {
        $stmtMsg = $pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id = :tid ORDER BY created_at ASC");
        $stmtMsg->execute([':tid' => $ticketId]);
        $ticketMessages = $stmtMsg->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Fetch all tickets for the client
$stmtAll = $pdo->prepare("SELECT * FROM support_tickets WHERE client_id = :cid ORDER BY updated_at DESC, id DESC");
$stmtAll->execute([':cid' => $clientId]);
$allTickets = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

$portalPageTitle = $activeTicket ? "Ticket #{$activeTicket['ticket_number']}" : 'Support Tickets';
$portalHeaderTitle = $activeTicket ? "Ticket #{$activeTicket['ticket_number']}" : 'Support Ticket Desk';
$current_portal_page = 'support';

include __DIR__ . '/includes/header.php';
?>

<?php if ($activeTicket): ?>
    <!-- ================= SINGLE TICKET CONVERSATION THREAD VIEW ================= -->
    <div style="margin-bottom: 20px;">
        <a href="support.php" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--portal-primary); text-decoration: none; font-weight: 600;">
            &larr; Back to All Tickets
        </a>
    </div>

    <div class="portal-card">
        <div class="portal-card-header" style="flex-wrap: wrap; gap: 12px; background: #fafafa;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--portal-primary); font-weight: 700;">
                    <span><?= htmlspecialchars($activeTicket['ticket_number'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span>&bull;</span>
                    <span><?= htmlspecialchars($activeTicket['category'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--portal-text-main); margin: 4px 0 0 0;">
                    <?= htmlspecialchars($activeTicket['subject'], ENT_QUOTES, 'UTF-8') ?>
                </h2>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="badge-status badge-<?= strtolower($activeTicket['priority']) ?>">
                    Priority: <?= htmlspecialchars($activeTicket['priority'], ENT_QUOTES, 'UTF-8') ?>
                </span>
                <span class="badge-status badge-<?= strtolower(str_replace(' ', '-', $activeTicket['status'])) ?>">
                    Status: <?= htmlspecialchars($activeTicket['status'], ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
        </div>

        <div class="portal-card-body">
            <!-- Messages Conversation Thread -->
            <div class="ticket-chat-container" id="ticketChatStream" style="margin-bottom: 24px; min-height: 200px;">
                <?php if (empty($ticketMessages)): ?>
                    <div style="text-align: center; color: var(--portal-text-muted); font-size: 0.88rem; padding: 24px;">
                        No messages recorded yet for this ticket.
                    </div>
                <?php else: ?>
                    <?php foreach ($ticketMessages as $tm): ?>
                        <?php $isClientMsg = ($tm['sender_type'] === 'client'); ?>
                        <div class="chat-bubble <?= $isClientMsg ? 'client' : 'staff' ?>">
                            <div class="chat-bubble-meta">
                                <strong><?= htmlspecialchars($tm['sender_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span>&bull; <?= date('d M Y, h:i A', strtotime($tm['created_at'])) ?></span>
                            </div>
                            <div style="word-break: break-word;">
                                <?= nl2br(htmlspecialchars($tm['message'], ENT_QUOTES, 'UTF-8')) ?>
                            </div>
                            <?php if (!empty($tm['attachment_path'])): ?>
                                <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed <?= $isClientMsg ? 'rgba(255,255,255,0.3)' : '#cbd5e1' ?>; font-size: 0.8rem;">
                                    📎 <a href="../<?= htmlspecialchars($tm['attachment_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" download style="color: <?= $isClientMsg ? '#ffffff' : 'var(--portal-primary)' ?>; text-decoration: underline; font-weight: 600;">
                                        Download Attached File
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Reply Box or Closed Banner -->
            <?php if (in_array($activeTicket['status'], ['Resolved', 'Closed'], true)): ?>
                <div style="background: #f8fafc; border: 1px solid var(--portal-border); padding: 16px; border-radius: 8px; text-align: center; color: var(--portal-text-muted); font-size: 0.88rem;">
                    This ticket has been marked as <strong><?= htmlspecialchars($activeTicket['status'], ENT_QUOTES, 'UTF-8') ?></strong>.
                    If you require assistance on another topic, please <a href="support-create.php" style="color: var(--portal-primary); font-weight: 600;">open a new ticket &rarr;</a>
                </div>
            <?php else: ?>
                <form id="ticketReplyForm" method="POST" action="../api/client-ticket-reply.php" enctype="multipart/form-data">
                    <?= getCsrfField() ?>
                    <input type="hidden" name="ticket_id" value="<?= (int)$activeTicket['id'] ?>">

                    <div class="portal-form-group" style="margin-bottom: 12px;">
                        <label for="replyMessage" class="portal-form-label">Post a Reply to Engineering Team</label>
                        <textarea class="portal-form-textarea" id="replyMessage" name="message" rows="4" 
                                  placeholder="Type your response, clarification, or feedback..." required></textarea>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <input type="file" name="attachment" id="replyAttachment" accept=".pdf,.png,.jpg,.jpeg,.zip,.docx,.txt" style="font-size: 0.8rem;">
                        </div>
                        <button type="submit" id="btnPostReply" class="btn-portal-primary">
                            <span>Submit Reply</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const replyForm = document.getElementById('ticketReplyForm');
        const replyBtn = document.getElementById('btnPostReply');
        const chatStream = document.getElementById('ticketChatStream');

        if (replyForm && replyBtn) {
            replyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                replyBtn.disabled = true;
                replyBtn.innerHTML = '<span>Sending Reply...</span>';

                const formData = new FormData(replyForm);

                fetch(replyForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const bubble = document.createElement('div');
                        bubble.className = 'chat-bubble client';
                        bubble.innerHTML = `
                            <div class="chat-bubble-meta">
                                <strong><?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8') ?></strong>
                                <span>&bull; Just now</span>
                            </div>
                            <div style="word-break: break-word;">${document.getElementById('replyMessage').value.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>
                        `;
                        chatStream.appendChild(bubble);
                        document.getElementById('replyMessage').value = '';
                        document.getElementById('replyAttachment').value = '';
                        chatStream.scrollTop = chatStream.scrollHeight;
                        window.showPortalToast('Reply submitted successfully.', 'success');
                    } else {
                        window.showPortalToast(data.message || 'Could not post reply.', 'error');
                    }
                    replyBtn.disabled = false;
                    replyBtn.innerHTML = '<span>Submit Reply</span>';
                })
                .catch(() => {
                    window.showPortalToast('Network error occurred.', 'error');
                    replyBtn.disabled = false;
                    replyBtn.innerHTML = '<span>Submit Reply</span>';
                });
            });
        }
    });
    </script>

<?php else: ?>
    <!-- ================= ALL TICKETS DIRECTORY VIEW ================= -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--portal-text-main); margin: 0 0 4px 0;">Support Ticket Desk</h2>
            <p style="font-size: 0.85rem; color: var(--portal-text-muted); margin: 0;">24/7 dedicated enterprise technical support, billing assistance, and feature queries</p>
        </div>
        <a href="support-create.php" class="btn-portal-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Open New Ticket</span>
        </a>
    </div>

    <div class="portal-card">
        <div class="portal-card-header">
            <h3 class="portal-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <span>Support Tickets (<?= count($allTickets) ?>)</span>
            </h3>
        </div>

        <div class="portal-card-body" style="padding: 0;">
            <?php if (empty($allTickets)): ?>
                <div class="portal-empty-state">
                    <div class="portal-empty-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <div class="portal-empty-title">No Tickets Created</div>
                    <p class="portal-empty-desc">You don't have any support tickets yet. Have a question or facing an issue? Our team is here to assist.</p>
                    <a href="support-create.php" class="btn-portal-primary">Open Support Ticket</a>
                </div>
            <?php else: ?>
                <div class="portal-table-container">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Subject</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Last Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allTickets as $tck): ?>
                                <tr>
                                    <td>
                                        <strong style="color: var(--portal-primary);">
                                            <?= htmlspecialchars($tck['ticket_number'], ENT_QUOTES, 'UTF-8') ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <a href="support.php?ticket_id=<?= (int)$tck['id'] ?>" style="font-weight: 600; color: var(--portal-text-main); text-decoration: none;">
                                            <?= htmlspecialchars($tck['subject'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </td>
                                    <td style="font-size: 0.82rem; color: #475569;">
                                        <?= htmlspecialchars($tck['category'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-<?= strtolower($tck['priority']) ?>">
                                            <?= htmlspecialchars($tck['priority'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-<?= strtolower(str_replace(' ', '-', $tck['status'])) ?>">
                                            <?= htmlspecialchars($tck['status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--portal-text-muted);">
                                        <?= date('d M Y', strtotime($tck['created_at'])) ?>
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--portal-text-muted);">
                                        <?= date('d M Y', strtotime($tck['updated_at'])) ?>
                                    </td>
                                    <td>
                                        <a href="support.php?ticket_id=<?= (int)$tck['id'] ?>" class="btn-portal-outline btn-portal-sm">
                                            View Thread
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
