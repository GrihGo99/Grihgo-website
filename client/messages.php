<?php
/**
 * GrihGO Digital & Tech — Client Direct Messaging Interface
 * Version: 3.0 (Part 3 - Client Portal)
 */

require_once __DIR__ . '/includes/auth.php';

$portalPageTitle = 'Messages';
$portalHeaderTitle = 'Direct Communication Hub';
$current_portal_page = 'messages';

$pdo = Database::getConnection();

// Fetch Client Projects for selector
$stmtProj = $pdo->prepare("SELECT id, name FROM projects WHERE client_id = :cid ORDER BY name ASC");
$stmtProj->execute([':cid' => $clientId]);
$clientProjects = $stmtProj->fetchAll(PDO::FETCH_ASSOC);

// Optional project filter
$selectedProjectId = !empty($_GET['project_id']) ? (int)$_GET['project_id'] : null;

// Fetch messages scoped strictly to client
if ($selectedProjectId) {
    $stmtMsgs = $pdo->prepare("
        SELECT m.*, p.name AS project_name
        FROM messages m
        LEFT JOIN projects p ON m.project_id = p.id
        WHERE m.client_id = :cid AND m.project_id = :pid
        ORDER BY m.created_at ASC
    ");
    $stmtMsgs->execute([':cid' => $clientId, ':pid' => $selectedProjectId]);
} else {
    $stmtMsgs = $pdo->prepare("
        SELECT m.*, p.name AS project_name
        FROM messages m
        LEFT JOIN projects p ON m.project_id = p.id
        WHERE m.client_id = :cid
        ORDER BY m.created_at ASC
    ");
    $stmtMsgs->execute([':cid' => $clientId]);
}
$messages = $stmtMsgs->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--portal-text-main); margin: 0 0 4px 0;">Team Communication</h2>
        <p style="font-size: 0.85rem; color: var(--portal-text-muted); margin: 0;">Direct channel with your assigned project managers and technology architects</p>
    </div>

    <!-- Filter by Project -->
    <div style="display: flex; align-items: center; gap: 8px;">
        <label for="projectFilter" style="font-size: 0.85rem; color: var(--portal-text-muted); font-weight: 500;">Filter Project:</label>
        <select id="projectFilter" class="portal-form-select" style="width: auto; padding: 6px 12px;" onchange="location.href = this.value ? 'messages.php?project_id=' + this.value : 'messages.php';">
            <option value="">All Projects</option>
            <?php foreach ($clientProjects as $cp): ?>
                <option value="<?= (int)$cp['id'] ?>" <?= $selectedProjectId === (int)$cp['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cp['name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="portal-card" style="display: flex; flex-direction: column; min-height: 520px;">
    <div class="portal-card-header" style="background: #fafafa;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 10px; height: 10px; border-radius: 50%; background: var(--portal-accent);"></div>
            <strong style="color: var(--portal-text-main); font-size: 0.95rem;">GrihGO Engineering Team &bull; Live Stream</strong>
        </div>
        <span style="font-size: 0.78rem; color: var(--portal-text-muted);">
            Responses typically within 1-2 hours
        </span>
    </div>

    <div class="portal-card-body" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between; padding: 20px;">
        <!-- Chat Container -->
        <div class="ticket-chat-container" id="messagesChatStream" style="flex: 1; max-height: 460px; min-height: 320px; margin-bottom: 20px;">
            <?php if (empty($messages)): ?>
                <div style="text-align: center; color: var(--portal-text-muted); font-size: 0.88rem; padding: 48px 24px;">
                    <div style="font-size: 1.5rem; margin-bottom: 8px;">💬</div>
                    <strong>No messages yet.</strong>
                    <p style="margin: 4px 0 0 0;">Reach out below to coordinate regarding your requirements, milestones, or questions.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php $isClientMsg = ($msg['sender_type'] === 'client'); ?>
                    <div class="chat-bubble <?= $isClientMsg ? 'client' : 'staff' ?>">
                        <div class="chat-bubble-meta">
                            <strong><?= htmlspecialchars($msg['sender_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <?php if (!empty($msg['project_name'])): ?>
                                <span>&bull; [<?= htmlspecialchars($msg['project_name'], ENT_QUOTES, 'UTF-8') ?>]</span>
                            <?php endif; ?>
                            <span>&bull; <?= date('d M, h:i A', strtotime($msg['created_at'])) ?></span>
                        </div>
                        <div style="word-break: break-word;">
                            <?= nl2br(htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8')) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Input Box -->
        <form id="globalMessageForm" method="POST" action="../api/client-message-send.php">
            <?= getCsrfField() ?>
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <?php if (!empty($clientProjects)): ?>
                    <select name="project_id" class="portal-form-select" style="width: 220px; font-size: 0.85rem;">
                        <option value="">General Communication</option>
                        <?php foreach ($clientProjects as $cp): ?>
                            <option value="<?= (int)$cp['id'] ?>" <?= $selectedProjectId === (int)$cp['id'] ? 'selected' : '' ?>>
                                Project: <?= htmlspecialchars($cp['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <div style="flex: 1; display: flex; gap: 8px;">
                    <input type="text" name="message" id="inputChatMessage" class="portal-form-input" 
                           placeholder="Type your message to the GrihGO engineering leads..." required autocomplete="off">
                    <button type="submit" id="btnSubmitMessage" class="btn-portal-primary" style="white-space: nowrap;">
                        <span>Send</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('globalMessageForm');
    const input = document.getElementById('inputChatMessage');
    const btn = document.getElementById('btnSubmitMessage');
    const stream = document.getElementById('messagesChatStream');

    if (stream) {
        stream.scrollTop = stream.scrollHeight;
    }

    if (form && btn && input) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;

            btn.disabled = true;
            const formData = new FormData(form);

            fetch(form.action, {
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
                        <div style="word-break: break-word;">${text.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>
                    `;
                    stream.appendChild(bubble);
                    input.value = '';
                    stream.scrollTop = stream.scrollHeight;
                    window.showPortalToast('Message transmitted.', 'success');
                } else {
                    window.showPortalToast(data.message || 'Could not send message.', 'error');
                }
                btn.disabled = false;
            })
            .catch(() => {
                window.showPortalToast('Network error while dispatching message.', 'error');
                btn.disabled = false;
            });
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
